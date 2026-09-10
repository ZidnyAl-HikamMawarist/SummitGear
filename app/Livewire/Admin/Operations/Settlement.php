<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use App\Models\Rental;
use App\Models\Penalty;
use App\Models\Deposit;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Settlement extends Component
{
    public $rentalId;
    public $rental;

    // Override State
    public $overridePenaltyId = null;
    public $newAmount = 0;
    public $overrideReason = '';

    // Additional payment state if penalty > deposit
    public $additionalPaymentMethod = 'CASH';
    public $amountPaid = 0;

    protected $listeners = [
        'pinApproved' => 'executeOverridePenalty',
    ];

    public function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->rental = Rental::with([
            'customer',
            'details.itemUnit.item',
            'penalties',
            'deposits',
            'payments'
        ])->findOrFail($this->rentalId);
    }

    public function getPendingPenaltiesProperty()
    {
        return $this->rental->penalties->where('is_settled', false);
    }

    public function getTotalPenaltyProperty()
    {
        return $this->pendingPenalties->sum('amount');
    }

    public function getHeldDepositAmountProperty()
    {
        return $this->rental->deposits->where('status', 'HELD')->sum('amount');
    }

    public function getNetBalanceProperty()
    {
        // Positif = Pelanggan harus bayar kekurangan.
        // Negatif = Toko harus mengembalikan kelebihan uang deposit ke pelanggan.
        return $this->totalPenalty - $this->heldDepositAmount;
    }

    // Modal Trigger for Admin PIN
    public function requestOverride($penaltyId)
    {
        $penalty = Penalty::findOrFail($penaltyId);
        $this->overridePenaltyId = $penalty->id;
        $this->newAmount = $penalty->amount;

        $this->dispatch('openPinApproval', action: 'overridePenalty', payload: [
            'penalty_id' => $penalty->id,
            'original_amount' => $penalty->amount,
        ]);
    }

    public function executeOverridePenalty($data)
    {
        if ($data['action'] === 'overridePenalty' && $this->overridePenaltyId == $data['payload']['penalty_id']) {
            $penalty = Penalty::findOrFail($this->overridePenaltyId);

            $penalty->update([
                'amount' => max(0, (int)$this->newAmount),
                'is_override' => true,
                'override_reason' => ($this->overrideReason ?: 'Diberikan diskon/kebijakan toko') . " (Disetujui Admin ID: {$data['approver_id']})",
            ]);

            AuditLogger::log('UPDATE', 'Penalty', $penalty->id, "Override denda dari Rp {$data['payload']['original_amount']} menjadi Rp {$this->newAmount}. Alasan: {$this->overrideReason}", $data['approver_id']);

            session()->flash('message', 'Nominal denda berhasil disesuaikan dengan otorisasi Admin.');
            $this->loadData();
        }
    }

    public function processSettlement()
    {
        DB::beginTransaction();
        try {
            $totalPenalty = $this->totalPenalty;
            $heldDeposits = $this->rental->deposits->where('status', 'HELD');
            $heldDepositTotal = $heldDeposits->sum('amount');

            if ($totalPenalty <= $heldDepositTotal) {
                // Deposit cukup untuk menutupi seluruh denda
                $remainingDeposit = $heldDepositTotal - $totalPenalty;

                foreach ($heldDeposits as $deposit) {
                    $deposit->update([
                        'status' => 'RETURNED',
                        'retention_deadline' => now()->addDays(30), // retensi log
                    ]);
                }

                // Tandai semua denda lunas
                foreach ($this->pendingPenalties as $penalty) {
                    $penalty->update(['is_settled' => true]);
                }

                $msg = "Penyelesaian berhasil! Denda sebesar Rp " . number_format($totalPenalty, 0, ',', '.') . " dipotong dari deposit. Kembalikan sisa uang deposit Rp " . number_format($remainingDeposit, 0, ',', '.') . " ke pelanggan.";

            } else {
                // Denda melebihi deposit -> Seluruh deposit disita & kasir menagih sisanya
                $shortage = $totalPenalty - $heldDepositTotal;

                if ($this->amountPaid < $shortage) {
                    $this->addError('amountPaid', "Uang yang diterima (Rp " . number_format($this->amountPaid, 0, ',', '.') . ") belum mencukupi kekurangan denda (Rp " . number_format($shortage, 0, ',', '.') . ").");
                    DB::rollBack();
                    return;
                }

                // Sita deposit
                foreach ($heldDeposits as $deposit) {
                    $deposit->update([
                        'status' => 'FORFEITED',
                    ]);
                }

                // Catat pembayaran tambahan ke tabel payments
                Payment::create([
                    'rental_id' => $this->rental->id,
                    'type' => 'penalty',
                    'method' => $this->additionalPaymentMethod,
                    'amount' => $this->amountPaid,
                    'paid_at' => now(),
                ]);

                // Tandai denda lunas
                foreach ($this->pendingPenalties as $penalty) {
                    $penalty->update(['is_settled' => true]);
                }

                $msg = "Penyelesaian berhasil! Deposit disita penuh dan sisa denda Rp " . number_format($shortage, 0, ',', '.') . " telah dibayar lunas.";
            }

            // Selesaikan transaksi
            $this->rental->update(['status' => 'COMPLETED']);

            AuditLogger::log('UPDATE', 'Rental', $this->rental->id, "Transaksi selesai sepenuhnya setelah penyelesaian sengketa & denda.");

            DB::commit();

            session()->flash('message', $msg);
            return redirect()->route('admin.transactions.invoice', $this->rental->id);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('settleError', 'Gagal memproses penyelesaian: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.operations.settlement')->layout('components.layouts.app');
    }
}
