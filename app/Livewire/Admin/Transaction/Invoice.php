<?php

namespace App\Livewire\Admin\Transaction;

use Livewire\Component;
use App\Models\Rental;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Invoice extends Component
{
    public $rentalId;
    public $rental;

    // Pin Approval properties untuk fitur Void
    public $showVoidModal = false;
    public $voidReason = '';

    // Perpanjangan Masa Sewa (Extension) properties
    public $showExtendModal = false;
    public $newEndDate = '';
    public $extendDays = 0;
    public $extendCost = 0;
    public $extendCollisions = [];

    public function mount($id)
    {
        $this->rentalId = $id;
        $this->rental = Rental::with(['customer', 'details.inventoryItem', 'details.itemUnit.item', 'payments', 'deposits'])->findOrFail($id);
    }

    public function openExtendModal()
    {
        $this->newEndDate = \Carbon\Carbon::parse($this->rental->end_date)->addDays(1)->format('Y-m-d');
        $this->calculateExtension();
        $this->showExtendModal = true;
    }

    public function closeExtendModal()
    {
        $this->showExtendModal = false;
        $this->newEndDate = '';
        $this->extendDays = 0;
        $this->extendCost = 0;
        $this->extendCollisions = [];
    }

    public function updatedNewEndDate($value)
    {
        $this->calculateExtension();
    }

    public function calculateExtension()
    {
        $this->extendCollisions = [];
        $this->extendCost = 0;
        $this->extendDays = 0;

        if (!$this->newEndDate) return;

        $oldEndDate = \Carbon\Carbon::parse($this->rental->end_date)->startOfDay();
        $newEnd = \Carbon\Carbon::parse($this->newEndDate)->startOfDay();

        if ($newEnd->lessThanOrEqualTo($oldEndDate)) {
            $this->addError('newEndDate', 'Tanggal baru harus setelah tanggal kembali saat ini (' . $this->rental->end_date . ').');
            return;
        }

        $this->extendDays = $oldEndDate->diffInDays($newEnd);

        // Hitung biaya tambahan: sum(detail->price_per_day) * extendDays
        $dailyTotal = $this->rental->details->sum('price_per_day');
        $this->extendCost = $dailyTotal * $this->extendDays;

        // Cek bentrok jadwal unit dengan transaksi lain
        $startCheck = $oldEndDate->copy()->addDay()->startOfDay();
        $endCheck = $newEnd->copy()->endOfDay();

        foreach ($this->rental->details as $detail) {
            if (!$detail->item_unit_id) continue;

            $colliding = \App\Models\RentalDetail::where('item_unit_id', $detail->item_unit_id)
                ->where('rental_id', '!=', $this->rental->id)
                ->whereHas('rental', function ($q) use ($startCheck, $endCheck) {
                    $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                      ->where(function ($query) use ($startCheck, $endCheck) {
                          $query->whereBetween('start_date', [$startCheck, $endCheck])
                                ->orWhereBetween('end_date', [$startCheck, $endCheck])
                                ->orWhere(function ($sub) use ($startCheck, $endCheck) {
                                    $sub->where('start_date', '<=', $startCheck)
                                        ->where('end_date', '>=', $endCheck);
                                });
                      });
                })
                ->with(['rental.customer', 'itemUnit.item'])
                ->first();

            if ($colliding) {
                $this->extendCollisions[] = [
                    'item_name' => $detail->itemUnit->item->name ?? 'Barang',
                    'serial_number' => $detail->itemUnit->serial_number,
                    'colliding_rental_code' => $colliding->rental->rental_code,
                    'colliding_customer' => $colliding->rental->customer->name ?? 'Pelanggan',
                    'colliding_start' => $colliding->rental->start_date,
                ];
            }
        }
    }

    public function executeExtension()
    {
        $this->calculateExtension();

        if ($this->extendDays <= 0) {
            $this->addError('newEndDate', 'Tanggal baru tidak valid.');
            return;
        }

        DB::beginTransaction();
        try {
            $oldEnd = $this->rental->end_date;
            $newEnd = $this->newEndDate;
            $newScheduled = \Carbon\Carbon::parse($newEnd)->endOfDay();
            $newTotal = $this->rental->total_price + $this->extendCost;

            // Jika status sebelumnya OVERDUE karena terlambat kembali tapi akhirnya minta perpanjangan sewa:
            // kita ubah kembali ke RENTED_OUT dan bersihkan denda keterlambatan otomatis
            if ($this->rental->status === 'OVERDUE') {
                \App\Models\Penalty::where('rental_id', $this->rental->id)
                    ->where(function ($q) {
                        $q->where('reason', 'like', '%keterlambatan%');
                    })
                    ->where('is_settled', false)
                    ->delete();
            }

            $this->rental->update([
                'end_date' => $newEnd,
                'scheduled_return_time' => $newScheduled,
                'total_price' => $newTotal,
                'status' => 'RENTED_OUT',
            ]);

            $collisionNote = count($this->extendCollisions) > 0 
                ? " (PERHATIAN: terdapat " . count($this->extendCollisions) . " unit bentrok dengan booking berikutnya, lakukan swap unit pada booking terkait)" 
                : "";

            AuditLogger::log(
                'UPDATE', 
                'Rental', 
                $this->rental->id, 
                "Perpanjangan masa sewa selama {$this->extendDays} hari (dari {$oldEnd} s/d {$newEnd}). Tambahan biaya sewa pokok: Rp " . number_format($this->extendCost, 0, ',', '.') . ". Total sewa kini Rp " . number_format($newTotal, 0, ',', '.') . "{$collisionNote}."
            );

            DB::commit();

            $this->closeExtendModal();
            $this->rental->refresh();
            session()->flash('message', "Masa sewa berhasil diperpanjang hingga {$newEnd} (+{$this->extendDays} hari). Tambahan biaya sewa: Rp " . number_format($this->extendCost, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('extendError', 'Gagal memperpanjang sewa: ' . $e->getMessage());
        }
    }

    public function confirmVoid()
    {
        $this->dispatch('openPinApproval', action: 'voidTransaction', payload: ['rental_id' => $this->rentalId]);
    }

    // Listener dari PinApproval
    protected $listeners = [
        'pinApproved' => 'processVoid',
        'pin-approved' => 'handlePinApproved',
    ];

    public function handlePinApproved($action, $data, $approvedBy, $token = null)
    {
        $this->processVoid([
            'action' => $action,
            'payload' => $data,
            'approver_id' => $approvedBy,
            'token' => $token,
        ]);
    }

    public function processVoid($data)
    {
        if (($data['action'] ?? null) === 'voidTransaction' && ($data['payload']['rental_id'] ?? null) == $this->rentalId) {
            
            // Verifikasi One-Time Token Otorisasi PIN Admin dari Session (Cegah Client Forgery)
            $token = $data['token'] ?? null;
            $stored = session()->get('pin_approval_token_voidTransaction');
            session()->forget('pin_approval_token_voidTransaction'); // Hapus seketika (One-Time Token)

            if (!$stored || !isset($stored['token']) || !hash_equals($stored['token'], (string)$token) || now()->timestamp > ($stored['expires_at'] ?? 0)) {
                session()->flash('error', 'Otorisasi PIN Admin tidak valid, telah kedaluwarsa, atau ditolak.');
                return;
            }

            // Guard: transaksi yang sudah serah terima / selesai tidak dapat di-VOID
            if (in_array($this->rental->status, ['COMPLETED', 'RENTED_OUT', 'OVERDUE', 'VOID', 'CANCELLED'])) {
                session()->flash('error', "Transaksi status '{$this->rental->status}' tidak dapat di-VOID.");
                return;
            }

            DB::beginTransaction();
            try {
                $reason = !empty($this->voidReason) ? $this->voidReason : 'Permintaan pembatalan kasir';

                // 1. Ubah status rental
                $this->rental->update([
                    'status' => 'VOID',
                    'settlement_notes' => "VOID disetujui Admin ID: " . ($data['approver_id'] ?? Auth::id()) . ". Alasan: {$reason}",
                ]);
                
                // 2. Batalkan detail rental & kembalikan unit fisik ke Available
                foreach ($this->rental->details as $detail) {
                    $detail->update(['return_status' => 'VOID']);
                    if ($detail->itemUnit) {
                        $detail->itemUnit->update(['status' => 'Available']);
                    }
                }

                // 3. Batalkan payment (soft delete)
                foreach ($this->rental->payments as $payment) {
                    $payment->delete();
                }

                // 4. Kembalikan deposit
                foreach ($this->rental->deposits as $deposit) {
                    $deposit->update(['status' => 'RETURNED']);
                }

                AuditLogger::log('DELETE', 'Rental', $this->rentalId, "Membatalkan (VOID) Transaksi. Alasan: {$reason}. Disetujui oleh: " . ($data['approver_id'] ?? Auth::id()), $data['approver_id'] ?? Auth::id());
                
                DB::commit();
                
                $this->rental->refresh();
                session()->flash('message', 'Transaksi berhasil dibatalkan (VOID).');

            } catch (\Exception $e) {
                DB::rollBack();
                session()->flash('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.transaction.invoice')->layout('components.layouts.app');
    }
}
