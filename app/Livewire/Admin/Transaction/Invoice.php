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

    public function mount($id)
    {
        $this->rentalId = $id;
        $this->rental = Rental::with(['customer', 'details.inventoryItem', 'details.itemUnit', 'payments', 'deposits'])->findOrFail($id);
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

    public function handlePinApproved($action, $data, $approvedBy)
    {
        $this->processVoid([
            'action' => $action,
            'payload' => $data,
            'approver_id' => $approvedBy,
        ]);
    }

    public function processVoid($data)
    {
        if ($data['action'] === 'voidTransaction' && ($data['payload']['rental_id'] ?? null) == $this->rentalId) {
            
            // Guard: hanya BOOKED atau PENDING_PAYMENT yang bisa di-VOID
            if (!in_array($this->rental->status, ['BOOKED', 'PENDING_PAYMENT'])) {
                session()->flash('error', "Transaksi status '{$this->rental->status}' tidak dapat di-VOID.");
                return;
            }

            DB::beginTransaction();
            try {
                // 1. Ubah status rental
                $this->rental->update(['status' => 'VOID']);
                
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

                AuditLogger::log('DELETE', 'Rental', $this->rentalId, "Membatalkan (VOID) Transaksi. Disetujui oleh: " . ($data['approver_id'] ?? Auth::id()));
                
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
