<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use App\Models\Rental;
use App\Models\Inspection;
use App\Models\Penalty;
use App\Models\ItemUnit;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckIn extends Component
{
    public $rentalId;
    public $rental;

    // Condition & status for each detail:
    // $checkinData[$detailId] = ['condition' => 'Baik', 'return_status' => 'RETURNED', 'notes' => '']
    public $checkinData = [];

    // Summary of differences
    public $initialConditions = []; // [detailId => Inspection checkout record]

    public function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->rental = Rental::with([
            'customer',
            'details.itemUnit.item',
            'details.inspections',
            'deposits'
        ])->findOrFail($rentalId);

        foreach ($this->rental->details as $detail) {
            // Find checkout inspection if any
            $checkoutInspection = $detail->inspections->where('stage', 'CHECKOUT')->first();
            $this->initialConditions[$detail->id] = $checkoutInspection ? $checkoutInspection->condition_category : 'Baik';

            $this->checkinData[$detail->id] = [
                'condition' => 'Baik',
                'return_status' => 'RETURNED', // Options: RETURNED, DAMAGED, LOST
                'notes' => '',
            ];
        }
    }

    public function updated($propertyName, $value)
    {
        if (preg_match('/checkinData\.(\d+)\.condition/', $propertyName, $matches)) {
            $detailId = $matches[1];
            if ($value === 'Rusak') {
                $this->checkinData[$detailId]['return_status'] = 'DAMAGED';
            } elseif ($value === 'Hilang') {
                $this->checkinData[$detailId]['return_status'] = 'LOST';
            } elseif (in_array($value, ['Baik', 'Cukup', 'Perhatian'])) {
                if (($this->checkinData[$detailId]['return_status'] ?? '') !== 'RETURNED') {
                    $this->checkinData[$detailId]['return_status'] = 'RETURNED';
                }
            }
        }
    }

    public function submitCheckIn()
    {
        // Guard: hanya rental yang sedang RENTED_OUT yang bisa diproses check-in
        if ($this->rental->status !== 'RENTED_OUT') {
            $this->addError('error', "Transaksi ini tidak dalam status RENTED_OUT (status: {$this->rental->status}).");
            return;
        }

        $this->validate([
            'checkinData.*.condition' => 'required|in:Baik,Cukup,Perhatian,Rusak,Hilang',
            'checkinData.*.return_status' => 'required|in:RETURNED,DAMAGED,LOST',
        ]);

        DB::beginTransaction();
        try {
            $totalDamageOrLostPenalty = 0;
            $hasIssues = false;

            foreach ($this->rental->details as $detail) {
                $data = $this->checkinData[$detail->id];
                $cond = $data['condition'];
                $returnStatus = $data['return_status'];
                $notes = $data['notes'] ?? '';

                // 1. Simpan QC Check-in ke Inspections
                Inspection::create([
                    'rental_detail_id' => $detail->id,
                    'stage' => 'CHECKIN',
                    'condition_category' => $cond,
                    'notes' => $notes,
                ]);

                // 2. Update status detail
                $detail->update([
                    'return_status' => $returnStatus,
                ]);

                // 3. Update status unit fisik
                if ($detail->itemUnit) {
                    if ($returnStatus === 'LOST' || $cond === 'Hilang') {
                        $detail->itemUnit->update(['status' => 'Lost', 'condition_notes' => 'Hilang saat disewa: ' . $notes]);
                        $totalDamageOrLostPenalty += $detail->itemUnit->replacement_value;
                        $hasIssues = true;

                        Penalty::create([
                            'rental_id' => $this->rental->id,
                            'reason' => "Ganti rugi barang hilang: {$detail->itemUnit->item->name} (SN: {$detail->itemUnit->serial_number})",
                            'amount' => $detail->itemUnit->replacement_value,
                            'is_settled' => false,
                        ]);
                    } elseif ($returnStatus === 'DAMAGED' || $cond === 'Rusak') {
                        $detail->itemUnit->update(['status' => 'Maintenance', 'condition_notes' => 'Rusak saat pengembalian: ' . $notes]);
                        // Estimasi denda kerusakan (misal 30% dari replacement value jika rusak)
                        $damageCost = (int) ($detail->itemUnit->replacement_value * 0.3);
                        $totalDamageOrLostPenalty += $damageCost;
                        $hasIssues = true;

                        Penalty::create([
                            'rental_id' => $this->rental->id,
                            'reason' => "Denda kerusakan alat: {$detail->itemUnit->item->name} ({$notes})",
                            'amount' => $damageCost,
                            'is_settled' => false,
                        ]);
                    } else {
                        // Masuk ke status Cleaning untuk pembersihan gudang
                        $detail->itemUnit->update(['status' => 'Cleaning', 'condition_notes' => 'Selesai disewa, siap dibersihkan']);
                    }
                }
            }

            // 4. Cek apakah ada keterlambatan pengembalian
            if (now()->greaterThan(Carbon::parse($this->rental->scheduled_return_time))) {
                $hoursLate = Carbon::parse($this->rental->scheduled_return_time)->diffInHours(now()) + 1;
                $lateFeePerHour = DB::table('settings')->where('key', 'late_fee_per_hour')->value('value') ?? 5000;
                $lateAmount = $hoursLate * (int)$lateFeePerHour;

                Penalty::create([
                    'rental_id' => $this->rental->id,
                    'reason' => "Keterlambatan pengembalian ({$hoursLate} jam)",
                    'amount' => $lateAmount,
                    'is_settled' => false,
                ]);

                $hasIssues = true;
            }

            // 5. Update Status Rental
            $newRentalStatus = $hasIssues ? 'PENDING_SETTLEMENT' : 'COMPLETED';
            $this->rental->update(['status' => $newRentalStatus]);

            // 6. Jika tidak ada denda/masalah, kembalikan deposit jaminan ke pelanggan
            if (!$hasIssues) {
                foreach ($this->rental->deposits as $deposit) {
                    if ($deposit->status === 'HELD') {
                        $deposit->update([
                            'status' => 'RETURNED',
                            'retention_deadline' => now()->addDays(30),
                        ]);
                    }
                }
            }

            AuditLogger::log('UPDATE', 'Rental', $this->rental->id, "Pengembalian barang (Check-In) selesai dengan status: {$newRentalStatus}");

            DB::commit();

            if ($hasIssues) {
                session()->flash('message', 'Check-In selesai dengan catatan sengketa/denda. Silakan selesaikan denda di modul penyelesaian transaksi.');
                return redirect()->route('admin.settlements.show', $this->rental->id);
            } else {
                session()->flash('message', 'Pengembalian barang sukses! Semua unit telah kembali dalam kondisi baik.');
                return redirect()->route('admin.operations.handover');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.operations.check-in')->layout('components.layouts.app');
    }
}
