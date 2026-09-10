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

    public function confirmCancelBooking($rentalId, $type = 'normal')
    {
        $rental = Rental::with('details.itemUnit', 'customer')->findOrFail($rentalId);
        $this->cancelRentalId = $rental->id;
        $this->cancelBookingCode = $rental->rental_code;
        $this->cancelCustomerName = $rental->customer->name ?? '-';
        $this->cancelCustomerPhone = $rental->customer->phone ?? '-';
        $this->cancelUnitCount = $rental->details->whereNotNull('item_unit_id')->count();
        $this->cancelType = $type;

        $pickupTime = Carbon::parse($rental->start_date);
        $deadline = $pickupTime->copy()->addHours(2);
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
    }

    public function executeCancellation()
    {
        if (!$this->cancelRentalId) return;

        $id = $this->cancelRentalId;
        $type = $this->cancelType;
        $this->closeCancelModal();

        if ($type === 'expired') {
            $this->validateExpiredAndCancel($id);
        } else {
            $this->cancelBooking($id);
        }
    }

    public function getIncomingBookingsProperty()
    {
        $query = Rental::with(['customer', 'details.itemUnit.item'])
            ->where('source', 'online')
            ->whereIn('status', ['PENDING_PAYMENT', 'BOOKED']);

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
            $deadline = $pickupTime->copy()->addHours(2); // Toleransi 2 jam
            
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
