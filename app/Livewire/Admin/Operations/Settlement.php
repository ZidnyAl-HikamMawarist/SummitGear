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

    public function getBalanceDueProperty()
    {
        return (int) $this->rental->balance_due;
    }

    public function getTotalObligationProperty()
    {
        return $this->balanceDue + $this->totalPenalty;
    }

    public function getNetBalanceProperty()
    {
        // Positif = Pelanggan harus bayar kekurangan (Sisa Pokok Sewa + Denda - Deposit Ditahan).
        // Negatif = Toko harus mengembalikan kelebihan uang deposit ke pelanggan.
        return $this->totalObligation - $this->heldDepositAmount;
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
        if (($data['action'] ?? null) === 'overridePenalty' && $this->overridePenaltyId == ($data['payload']['penalty_id'] ?? null)) {
            // Verifikasi One-Time Token Otorisasi PIN Admin dari Session
            $token = $data['token'] ?? null;
            $stored = session()->get('pin_approval_token_overridePenalty');
            session()->forget('pin_approval_token_overridePenalty'); // Hapus seketika

            if (!$stored || !isset($stored['token']) || !hash_equals($stored['token'], (string)$token) || now()->timestamp > ($stored['expires_at'] ?? 0)) {
                session()->flash('error', 'Otorisasi PIN Admin tidak valid, telah kedaluwarsa, atau ditolak.');
                return;
            }

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
            $balanceDue = $this->balanceDue;
            $totalObligation = $this->totalObligation;
            $heldDeposits = $this->rental->deposits->where('status', 'HELD');
            $heldDepositTotal = $heldDeposits->sum('amount');

            if ($totalObligation <= $heldDepositTotal) {
                // Deposit cukup untuk menutupi seluruh kewajiban (sisa pokok + denda)
                $remainingDeposit = $heldDepositTotal - $totalObligation;

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

                // Jika ada sisa sewa pokok, catat pelunasan via pemotongan deposit
                if ($balanceDue > 0) {
                    Payment::create([
                        'rental_id' => $this->rental->id,
                        'type' => 'rental_balance',
                        'method' => 'DEPOSIT_DEDUCTION',
                        'amount' => $balanceDue,
                        'paid_at' => now(),
                    ]);
                }

                $msg = "Penyelesaian berhasil! Total kewajiban sebesar Rp " . number_format($totalObligation, 0, ',', '.') . " dipotong dari deposit. Kembalikan sisa uang deposit Rp " . number_format($remainingDeposit, 0, ',', '.') . " ke pelanggan.";

            } else {
                // Kewajiban melebihi deposit -> Seluruh deposit disita & kasir menagih sisanya
                $shortage = $totalObligation - $heldDepositTotal;

                if ($this->amountPaid < $shortage) {
                    $this->addError('amountPaid', "Uang yang diterima (Rp " . number_format($this->amountPaid, 0, ',', '.') . ") belum mencukupi kekurangan pembayaran (Rp " . number_format($shortage, 0, ',', '.') . ").");
                    DB::rollBack();
                    return;
                }

                // Proses deposit: uang jaminan disita untuk menutup biaya, sedangkan dokumen identitas fisik (KTP) dikembalikan ke pelanggan
                foreach ($heldDeposits as $deposit) {
                    if ($deposit->type === 'DOC' || empty($deposit->amount)) {
                        $deposit->update([
                            'status' => 'RETURNED',
                            'retention_deadline' => now()->addDays(30),
                        ]);
                    } else {
                        $deposit->update([
                            'status' => 'FORFEITED',
                        ]);
                    }
                }

                // Catat pembayaran sisa pokok sewa jika ada
                if ($balanceDue > 0) {
                    Payment::create([
                        'rental_id' => $this->rental->id,
                        'type' => 'rental_balance',
                        'method' => $this->additionalPaymentMethod,
                        'amount' => $balanceDue,
                        'paid_at' => now(),
                    ]);
                }

                // Catat pembayaran denda jika ada
                $penaltyPaid = max(0, $this->amountPaid - $balanceDue);
                if ($totalPenalty > 0) {
                    Payment::create([
                        'rental_id' => $this->rental->id,
                        'type' => 'penalty',
                        'method' => $this->additionalPaymentMethod,
                        'amount' => $penaltyPaid,
                        'paid_at' => now(),
                    ]);
                }

                // Tandai denda lunas
                foreach ($this->pendingPenalties as $penalty) {
                    $penalty->update(['is_settled' => true]);
                }

                $msg = "Penyelesaian berhasil! Sisa kewajiban Rp " . number_format($shortage, 0, ',', '.') . " telah dibayar lunas.";
            }

            // Selesaikan transaksi & update down_payment_amount menjadi total_price (lunas penuh)
            $this->rental->update([
                'status' => 'COMPLETED',
                'down_payment_amount' => $this->rental->total_price,
            ]);

            AuditLogger::log('UPDATE', 'Rental', $this->rental->id, "Transaksi selesai sepenuhnya setelah penyelesaian sengketa & pelunasan sewa.");

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
