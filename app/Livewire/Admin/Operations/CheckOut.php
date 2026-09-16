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

    // Pelunasan Sisa Pokok Sewa (Booking DP)
    public $balancePaymentMethod = 'CASH';
    public $balancePaymentAmount = 0;

    // Signature data URL or acknowledgment confirmation
    public $customerAgreed = false;
    public $notes = '';

    public function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->rental = Rental::with(['customer', 'details.itemUnit.item'])->findOrFail($rentalId);

        if (!in_array($this->rental->status, ['BOOKED', 'DP_PAID', 'PAID'])) {
            session()->flash('message', 'Transaksi ini tidak dalam status BOOKED / DP_PAID / PAID (status saat ini: ' . $this->rental->status . ')');
        }

        if ($this->rental->balance_due > 0) {
            $this->balancePaymentAmount = (int)$this->rental->balance_due;
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
        // Guard: hanya rental berstatus BOOKED, DP_PAID, atau PAID yang bisa di-checkout
        if (!in_array($this->rental->status, ['BOOKED', 'DP_PAID', 'PAID'])) {
            $this->addError('error', "Transaksi tidak dalam status BOOKED / DP_PAID / PAID (status: {$this->rental->status}). Tidak dapat diproses.");
            return;
        }

        $rules = [
            'customerAgreed' => 'accepted',
            'checklists.*.condition' => 'required|in:Baik,Cukup,Perhatian',
        ];

        if ($this->rental->balance_due > 0) {
            $rules['balancePaymentMethod'] = 'required|in:CASH,TRANSFER,QRIS';
            $rules['balancePaymentAmount'] = 'required|numeric|min:' . $this->rental->balance_due;
        }

        $this->validate($rules, [
            'customerAgreed.accepted' => 'Pelanggan wajib menyatakan telah memeriksa fisik barang dan menyetujui serah terima.',
            'balancePaymentAmount.min' => 'Nominal pelunasan sisa sewa minimal Rp ' . number_format($this->rental->balance_due, 0, ',', '.'),
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

            // 3. Pelunasan sisa sewa pokok jika transaksi booking DP
            if ($this->rental->balance_due > 0) {
                \App\Models\Payment::create([
                    'rental_id' => $this->rental->id,
                    'type' => 'rental_balance',
                    'method' => $this->balancePaymentMethod,
                    'amount' => $this->balancePaymentAmount,
                    'paid_at' => now(),
                ]);

                $this->rental->update([
                    'down_payment_amount' => $this->rental->total_price,
                ]);

                AuditLogger::log('PAYMENT', 'Rental', $this->rental->id, "Pelunasan sisa sewa pokok sebesar Rp " . number_format($this->balancePaymentAmount, 0, ',', '.') . " via {$this->balancePaymentMethod} saat Check-Out.");
            }

            // 4. Update status Rental ke RENTED_OUT
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
