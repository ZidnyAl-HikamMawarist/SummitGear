<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use App\Models\Rental;
use App\Models\Inspection;
use App\Models\ItemUnit;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CheckOut extends Component
{
    public $rentalId;
    public $rental;

    // Checklist per detail id: ['condition' => 'Baik', 'notes' => '']
    public $checklists = [];

    // Signature data URL or acknowledgment confirmation
    public $customerAgreed = false;
    public $notes = '';

    public function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->rental = Rental::with(['customer', 'details.itemUnit.item'])->findOrFail($rentalId);

        if ($this->rental->status !== 'BOOKED') {
            session()->flash('message', 'Transaksi ini tidak dalam status BOOKED (status saat ini: ' . $this->rental->status . ')');
        }

        foreach ($this->rental->details as $detail) {
            $this->checklists[$detail->id] = [
                'condition' => 'Baik', // Default: Baik (opsi: Baik, Cukup, Perhatian)
                'notes' => '',
            ];
        }
    }

    public function submitCheckOut()
    {
        // Guard: hanya rental berstatus BOOKED yang bisa di-checkout
        if ($this->rental->status !== 'BOOKED') {
            $this->addError('error', "Transaksi tidak dalam status BOOKED (status: {$this->rental->status}). Tidak dapat diproses.");
            return;
        }

        $this->validate([
            'customerAgreed' => 'accepted',
            'checklists.*.condition' => 'required|in:Baik,Cukup,Perhatian',
        ], [
            'customerAgreed.accepted' => 'Pelanggan wajib menyatakan telah memeriksa fisik barang dan menyetujui serah terima.',
        ]);

        DB::beginTransaction();
        try {
            foreach ($this->rental->details as $detail) {
                $cond = $this->checklists[$detail->id]['condition'] ?? 'Baik';
                $itemNotes = $this->checklists[$detail->id]['notes'] ?? null;

                // 1. Simpan QC Checklist ke Inspections
                Inspection::create([
                    'rental_detail_id' => $detail->id,
                    'stage' => 'CHECKOUT',
                    'condition_category' => $cond,
                    'notes' => $itemNotes,
                ]);

                // 2. Update status fisik unit menjadi 'Rented'
                if ($detail->itemUnit) {
                    $detail->itemUnit->update([
                        'status' => 'Rented',
                        'condition_notes' => $cond . ($itemNotes ? ": {$itemNotes}" : ''),
                    ]);
                }
            }

            // 3. Update status Rental ke RENTED_OUT
            $this->rental->update([
                'status' => 'RENTED_OUT',
            ]);

            AuditLogger::log('UPDATE', 'Rental', $this->rental->id, "Serah terima barang (Check-Out) selesai dengan verifikasi QC.");

            DB::commit();

            session()->flash('message', "Check-Out berhasil! Alat telah resmi diserahkan ke {$this->rental->customer->name}.");
            return redirect()->route('admin.operations.handover');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('error', 'Gagal memproses serah-terima: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.operations.check-out')->layout('components.layouts.app');
    }
}
