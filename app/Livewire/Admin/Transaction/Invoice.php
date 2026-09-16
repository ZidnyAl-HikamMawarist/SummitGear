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
