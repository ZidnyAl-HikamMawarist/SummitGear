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

    // Verifikasi Jaminan Identitas (Khusus Online Booking yang belum ada deposit)
    public $hasHeldDeposit = false;
    public $is_ktp_verified = false;
    public $counter_deposit_type = 'DOC'; // DOC or CASH
    public $counter_deposit_amount = 0;

    // Unit Swap / Reassignment (Skenario 4 & 5)
    public $showSwapModal = false;
    public $swapDetailId = null;
    public $swapItemName = '';
    public $swapOldSN = '';
    public $newUnitId = null;
    public $availableUnitsForSwap = [];

    // Signature data URL or acknowledgment confirmation
    public $customerAgreed = false;
    public $notes = '';

    public function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->rental = Rental::with(['customer', 'details.itemUnit.item', 'deposits'])->findOrFail($rentalId);

        $this->hasHeldDeposit = $this->rental->deposits->where('status', 'HELD')->isNotEmpty();
        if ($this->hasHeldDeposit) {
            $this->is_ktp_verified = true;
        }

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

    public function openSwapModal($detailId)
    {
        $detail = $this->rental->details->firstWhere('id', $detailId);
        if (!$detail || !$detail->itemUnit) return;

        $this->swapDetailId = $detailId;
        $this->swapItemName = $detail->itemUnit->item->name;
        $this->swapOldSN = $detail->itemUnit->serial_number;

        $start = \Carbon\Carbon::parse($this->rental->start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($this->rental->end_date)->endOfDay();
        $currentCartIds = $this->rental->details->pluck('item_unit_id')->toArray();

        $this->availableUnitsForSwap = ItemUnit::where('item_id', $detail->itemUnit->item_id)
            ->where('status', 'Available')
            ->whereNotIn('id', $currentCartIds)
            ->whereDoesntHave('rentalDetails.rental', function ($q) use ($start, $end) {
                $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                  ->where(function ($query) use ($start, $end) {
                      $query->whereBetween('start_date', [$start, $end])
                            ->orWhereBetween('end_date', [$start, $end])
                            ->orWhere(function ($sub) use ($start, $end) {
                                $sub->where('start_date', '<=', $start)
                                    ->where('end_date', '>=', $end);
                            });
                  });
            })
            ->get();

        $this->newUnitId = $this->availableUnitsForSwap->first()?->id ?? null;
        $this->showSwapModal = true;
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
        $this->swapDetailId = null;
        $this->newUnitId = null;
        $this->availableUnitsForSwap = [];
    }

    public function executeSwapUnit()
    {
        $this->validate([
            'newUnitId' => 'required|exists:item_units,id',
        ], ['newUnitId.required' => 'Pilih unit pengganti yang tersedia.']);

        DB::beginTransaction();
        try {
            $detail = \App\Models\RentalDetail::with('itemUnit.item')->findOrFail($this->swapDetailId);
            $oldSN = $detail->itemUnit?->serial_number ?? '-';
            $newUnit = ItemUnit::findOrFail($this->newUnitId);

            $detail->update([
                'item_unit_id' => $newUnit->id,
            ]);

            AuditLogger::log('UPDATE', 'Rental', $this->rental->id, "Swap unit fisik alat {$detail->itemUnit->item->name} dari SN: {$oldSN} ke SN: {$newUnit->serial_number} pada transaksi {$this->rental->rental_code}.");

            DB::commit();

            $this->closeSwapModal();
            $this->rental->refresh();
            $this->rental->load(['customer', 'details.itemUnit.item']);

            $this->checklists[$detail->id] = [
                'condition' => 'Baik',
                'notes' => "Unit ditukar dari SN {$oldSN}",
            ];

            session()->flash('message', "Berhasil menukar unit fisik ke SN: {$newUnit->serial_number}.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('swapError', 'Gagal menukar unit: ' . $e->getMessage());
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

        if (!$this->hasHeldDeposit) {
            $rules['is_ktp_verified'] = 'accepted';
        }

        if ($this->rental->balance_due > 0) {
            $rules['balancePaymentMethod'] = 'required|in:CASH,TRANSFER,QRIS';
            $rules['balancePaymentAmount'] = 'required|numeric|min:' . $this->rental->balance_due;
        }

        $this->validate($rules, [
            'customerAgreed.accepted' => 'Pelanggan wajib menyatakan telah memeriksa fisik barang dan menyetujui serah terima.',
            'is_ktp_verified.accepted' => 'Kasir wajib memverifikasi dan menahan jaminan identitas (KTP) sebelum barang diserahkan.',
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

            // 4. Rekam jaminan identitas / deposit jika belum ada dari walk-in/POS
            if (!$this->hasHeldDeposit) {
                if ($this->counter_deposit_type === 'CASH' && (float)$this->counter_deposit_amount > 0) {
                    \App\Models\Deposit::create([
                        'rental_id' => $this->rental->id,
                        'type' => 'CASH',
                        'amount' => (float)$this->counter_deposit_amount,
                        'doc_type' => null,
                        'status' => 'HELD',
                        'retention_deadline' => \Carbon\Carbon::parse($this->rental->end_date)->addDays(30),
                    ]);
                    AuditLogger::log('CREATE', 'Deposit', $this->rental->id, "Penerimaan uang jaminan tunai di kasir sebesar Rp " . number_format($this->counter_deposit_amount, 0, ',', '.') . ".");
                } else {
                    \App\Models\Deposit::create([
                        'rental_id' => $this->rental->id,
                        'type' => 'DOC',
                        'doc_type' => 'KTP',
                        'amount' => 0,
                        'status' => 'HELD',
                        'retention_deadline' => \Carbon\Carbon::parse($this->rental->end_date)->addDays(30),
                    ]);
                    AuditLogger::log('CREATE', 'Deposit', $this->rental->id, "Penerimaan dan penahanan fisik jaminan KTP asli pelanggan.");
                }
            }

            // 5. Update status Rental ke RENTED_OUT
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
