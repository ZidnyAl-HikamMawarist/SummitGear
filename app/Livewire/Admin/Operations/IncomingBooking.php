<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use App\Models\Rental;
use App\Models\ItemUnit;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class IncomingBooking extends Component
{
    public $search = '';
    public $filterStatus = 'all'; // all, expired, active

    public $showCancelModal = false;
    public $cancelRentalId = null;
    public $cancelBookingCode = '';
    public $cancelCustomerName = '';
    public $cancelCustomerPhone = '';
    public $cancelUnitCount = 0;
    public $cancelIsExpired = false;
    public $cancelDeadline = '';
    public $cancelType = 'normal'; // 'expired' or 'normal'

    // Fitur Pembatalan Booking Berbayar (Skenario 7: DP Hangus vs Refund)
    public $cancelPaidAmount = 0;
    public $cancelAction = 'NONE'; // 'NONE', 'FORFEIT', 'REFUND'
    public $refundAmount = 0;
    public $supervisorPin = '';

    public function extendPickupTolerance($rentalId, $hours = 2)
    {
        $rental = Rental::findOrFail($rentalId);
        $base = $rental->pickup_extended_until ? Carbon::parse($rental->pickup_extended_until) : Carbon::parse($rental->start_date)->addHours(2);
        $newDeadline = $base->copy()->addHours($hours);

        $rental->update([
            'pickup_extended_until' => $newDeadline,
        ]);

        AuditLogger::log(
            'UPDATE',
            'Rental',
            $rental->id,
            "Kasir memberikan perpanjangan toleransi waktu pickup sebesar {$hours} jam (hingga " . $newDeadline->format('d M Y, H:i') . " WIB)."
        );

        session()->flash('message', "Toleransi waktu pickup untuk booking {$rental->rental_code} diperpanjang hingga " . $newDeadline->format('d M Y, H:i') . " WIB.");
    }

    public function confirmCancelBooking($rentalId, $type = 'normal')
    {
        $rental = Rental::with(['details.itemUnit', 'customer', 'payments'])->findOrFail($rentalId);
        $this->cancelRentalId = $rental->id;
        $this->cancelBookingCode = $rental->rental_code;
        $this->cancelCustomerName = $rental->customer->name ?? '-';
        $this->cancelCustomerPhone = $rental->customer->phone ?? '-';
        $this->cancelUnitCount = $rental->details->whereNotNull('item_unit_id')->count();
        $this->cancelType = $type;

        $this->cancelPaidAmount = (int) $rental->payments->sum('amount');
        $this->cancelAction = $this->cancelPaidAmount > 0 ? 'FORFEIT' : 'NONE';
        $this->refundAmount = $this->cancelPaidAmount;
        $this->supervisorPin = '';

        $pickupTime = Carbon::parse($rental->start_date);
        $deadline = $rental->pickup_extended_until ? Carbon::parse($rental->pickup_extended_until) : $pickupTime->copy()->addHours(2);
        $this->cancelDeadline = $deadline->locale('id')->isoFormat('DD MMM YYYY, HH:mm') . ' WIB';
        $this->cancelIsExpired = Carbon::now()->greaterThan($deadline);

        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancelRentalId = null;
        $this->cancelBookingCode = '';
        $this->cancelCustomerName = '';
        $this->cancelCustomerPhone = '';
        $this->cancelUnitCount = 0;
        $this->cancelType = 'normal';
        $this->cancelPaidAmount = 0;
        $this->cancelAction = 'NONE';
        $this->refundAmount = 0;
        $this->supervisorPin = '';
    }

    public function executeCancellation()
    {
        if (!$this->cancelRentalId) return;

        if ($this->cancelPaidAmount > 0 && $this->cancelAction === 'REFUND') {
            $this->validate([
                'refundAmount' => 'required|numeric|min:1|max:' . $this->cancelPaidAmount,
                'supervisorPin' => 'required',
            ], [
                'refundAmount.min' => 'Nominal refund minimal Rp 1',
                'refundAmount.max' => 'Nominal refund tidak boleh melebihi total yang dibayar (Rp ' . number_format($this->cancelPaidAmount, 0, ',', '.') . ')',
                'supervisorPin.required' => 'PIN Supervisor wajib diisi untuk otorisasi refund kas.',
            ]);

            $validPin = \App\Models\Setting::where('key', 'admin_supervisor_pin')->value('value') ?? '1234';
            if ($this->supervisorPin !== $validPin) {
                $this->addError('supervisorPin', 'PIN Supervisor tidak valid.');
                return;
            }
        }

        $id = $this->cancelRentalId;
        $this->showCancelModal = false;

        DB::beginTransaction();
        try {
            $rental = Rental::with('details.itemUnit', 'customer')->findOrFail($id);

            $returnedCount = 0;
            foreach ($rental->details as $detail) {
                if ($detail->itemUnit) {
                    $detail->itemUnit->update(['status' => 'Available']);
                    $returnedCount++;
                }
            }

            $settlementNote = '';
            if ($this->cancelPaidAmount > 0) {
                if ($this->cancelAction === 'REFUND') {
                    \App\Models\Payment::create([
                        'rental_id' => $rental->id,
                        'type' => 'penalty',
                        'method' => 'TRANSFER',
                        'amount' => -$this->refundAmount,
                        'paid_at' => now(),
                    ]);
                    $settlementNote = "Dibatalkan dengan REFUND Rp " . number_format($this->refundAmount, 0, ',', '.') . " via Transfer Bank disetujui Supervisor.";
                } else {
                    $settlementNote = "Dibatalkan dengan DP HANGUS (Rp " . number_format($this->cancelPaidAmount, 0, ',', '.') . ") sesuai SOP pembatalan.";
                }
            }

            $rental->update([
                'status' => 'CANCELLED',
                'settlement_notes' => $settlementNote,
            ]);

            AuditLogger::log(
                'CANCEL',
                'Rental',
                $rental->id,
                "Kasir membatalkan booking online {$rental->rental_code} ({$rental->customer->name}). {$settlementNote} Sebanyak {$returnedCount} unit kembali ke gudang."
            );

            try {
                WhatsAppService::sendBookingCancellation($rental, $settlementNote ?: "Permintaan pembatalan kasir/pelanggan.");
            } catch (\Throwable $e) {}

            DB::commit();

            $this->closeCancelModal();
            session()->flash('message', "Booking {$rental->rental_code} berhasil dibatalkan. {$returnedCount} unit dikembalikan ke gudang.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membatalkan booking: ' . $e->getMessage());
        }
    }

    public function getIncomingBookingsProperty()
    {
        $query = Rental::with(['customer', 'details.itemUnit.item'])
            ->where('source', 'online')
            ->where(function ($q) {
                $q->whereIn('status', ['DP_PAID', 'PAID', 'BOOKED'])
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'PENDING_PAYMENT')
                         ->where(function ($ssq) {
                             $ssq->whereNull('expires_at')
                                 ->orWhere('expires_at', '>', Carbon::now());
                         });
                  });
            });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('rental_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('phone', 'like', '%' . $this->search . '%')
                         ->orWhere('nik', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $bookings = $query->orderBy('start_date', 'asc')->get();

        // Hitung status batas waktu pengambilan untuk setiap booking
        $now = Carbon::now();
        return $bookings->map(function ($booking) use ($now) {
            $pickupTime = Carbon::parse($booking->start_date);
            $deadline = $booking->pickup_extended_until 
                ? Carbon::parse($booking->pickup_extended_until) 
                : $pickupTime->copy()->addHours(2); // Default toleransi 2 jam
            
            $booking->is_tolerance_extended = !empty($booking->pickup_extended_until);
            $booking->pickup_date_formatted = $pickupTime->locale('id')->isoFormat('DD MMM YYYY');
            $booking->pickup_clock_formatted = $pickupTime->format('H:i') . ' WIB';
            $booking->pickup_time_formatted = $pickupTime->locale('id')->isoFormat('DD MMM YYYY, HH:mm') . ' WIB';

            $booking->deadline_date_formatted = $deadline->locale('id')->isoFormat('DD MMM YYYY');
            $booking->deadline_clock_formatted = $deadline->format('H:i') . ' WIB';
            $booking->deadline_formatted = $deadline->locale('id')->isoFormat('DD MMM YYYY, HH:mm') . ' WIB';
            $booking->is_expired = $now->greaterThan($deadline);
            
            if ($booking->is_expired) {
                $booking->delay_for_humans = $now->locale('id')->diffForHumans($deadline, [
                    'parts' => 2,
                    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                ]);
            } else {
                $booking->remaining_time_for_humans = $now->locale('id')->diffForHumans($deadline, [
                    'parts' => 2,
                    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                ]);
            }

            return $booking;
        })->filter(function ($booking) {
            if ($this->filterStatus === 'expired') {
                return $booking->is_expired;
            } elseif ($this->filterStatus === 'active') {
                return !$booking->is_expired;
            } elseif ($this->filterStatus === 'dp') {
                return $booking->status === 'DP_PAID';
            } elseif ($this->filterStatus === 'paid') {
                return $booking->status === 'PAID';
            }
            return true;
        });
    }

    public function processBooking($rentalId)
    {
        return redirect()->route('admin.transactions.invoice', $rentalId);
    }

    /**
     * Validasi kasir bahwa booking batal karena barang tidak diambil dalam batas waktu.
     * Mengembalikan stok unit ke 'Available' di gudang.
     */
    public function validateExpiredAndCancel($rentalId)
    {
        DB::beginTransaction();
        try {
            $rental = Rental::with('details.itemUnit', 'customer')->findOrFail($rentalId);
            
            // 1. Ubah status rental menjadi CANCELLED
            $rental->update(['status' => 'CANCELLED']);

            // 2. Kembalikan semua unit fisik ke gudang (Available)
            $returnedCount = 0;
            foreach ($rental->details as $detail) {
                if ($detail->itemUnit) {
                    $detail->itemUnit->update(['status' => 'Available']);
                    $returnedCount++;
                }
            }

            // 3. Catat audit log kasir
            $pickupStr = Carbon::parse($rental->start_date)->format('d M Y, H:i');
            AuditLogger::log(
                'CANCEL', 
                'Rental', 
                $rental->id, 
                "Kasir memvalidasi pembatalan booking {$rental->rental_code} karena pelanggan ({$rental->customer->name}) tidak mengambil barang hingga batas waktu toleransi (Jadwal: {$pickupStr}). Sebanyak {$returnedCount} unit dikembalikan ke stok gudang."
            );

            // 4. Kirim notifikasi WA ke pelanggan jika memungkinkan
            WhatsAppService::sendBookingCancellation(
                $rental, 
                "Barang tidak diambil di outlet kami melewati batas toleransi waktu pengambilan (Jadwal: {$pickupStr} WIB + toleransi 2 jam)."
            );

            DB::commit();
            session()->flash('message', "Validasi Berhasil! Booking {$rental->rental_code} telah dibatalkan karena tidak diambil. {$returnedCount} unit barang otomatis kembali ke stok gudang.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memvalidasi pembatalan booking: ' . $e->getMessage());
        }
    }

    public function cancelBooking($rentalId)
    {
        DB::beginTransaction();
        try {
            $rental = Rental::with('details.itemUnit', 'customer')->findOrFail($rentalId);
            $rental->update(['status' => 'CANCELLED']);

            $returnedCount = 0;
            foreach ($rental->details as $detail) {
                if ($detail->itemUnit) {
                    $detail->itemUnit->update(['status' => 'Available']);
                    $returnedCount++;
                }
            }

            AuditLogger::log('CANCEL', 'Rental', $rental->id, "Kasir membatalkan booking online: {$rental->rental_code}. {$returnedCount} unit kembali ke gudang.");

            WhatsAppService::sendBookingCancellation($rental, "Permintaan pembatalan kasir/pelanggan.");

            DB::commit();
            session()->flash('message', "Booking {$rental->rental_code} berhasil dibatalkan. {$returnedCount} unit dikembalikan ke stok gudang.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membatalkan booking: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.operations.incoming-booking', [
            'bookings' => $this->incomingBookings
        ])->layout('components.layouts.app');
    }
}
