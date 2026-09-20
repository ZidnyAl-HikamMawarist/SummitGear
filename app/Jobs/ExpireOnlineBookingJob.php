<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rental;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireOnlineBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expireHoursSetting = Setting::where('key', 'online_booking_expire_hours')->first();
        $expireHours = $expireHoursSetting ? (int)$expireHoursSetting->value : 24;
        $deadline = Carbon::now()->subHours($expireHours);

        $now = Carbon::now();
        $expiredBookings = Rental::where(function ($rootQ) use ($deadline, $now, $expireHours) {
                $rootQ->where(function ($query) use ($deadline, $now) {
                    $query->where('source', 'online')
                          ->whereIn('status', ['PENDING_PAYMENT', 'BOOKED'])
                          ->where(function ($q) use ($deadline, $now) {
                              $q->where('created_at', '<=', $deadline)
                                ->orWhere(function ($sq) use ($now) {
                                    $sq->where('start_date', '<=', $now->copy()->subHours(2))
                                       ->where(function ($ssq) use ($now) {
                                           $ssq->whereNull('pickup_extended_until')
                                               ->orWhere('pickup_extended_until', '<=', $now);
                                       });
                                });
                          });
                })->orWhere(function ($query) use ($now, $expireHours) {
                    $query->where('source', 'online')
                          ->whereIn('status', ['DP_PAID', 'PAID'])
                          ->where(function ($sq) use ($now, $expireHours) {
                              $sq->where('start_date', '<=', $now->copy()->subHours($expireHours))
                                 ->where(function ($ssq) use ($now) {
                                     $ssq->whereNull('pickup_extended_until')
                                         ->orWhere('pickup_extended_until', '<=', $now);
                                 });
                          });
                });
            })
            ->with('details.itemUnit', 'customer')
            ->get();

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                // Kembalikan status unit ke 'Available'
                foreach ($booking->details as $detail) {
                    if ($detail->itemUnit) {
                        $detail->itemUnit->update(['status' => 'Available']);
                    }
                }
                
                // Ubah status booking ke CANCELLED (Expired)
                $booking->update(['status' => 'CANCELLED']);

                \App\Services\AuditLogger::log('SYSTEM', 'Rental', $booking->id, "Auto-cancel booking online {$booking->rental_code} karena melewati batas waktu pengambilan.");
            });

            try {
                \App\Services\WhatsAppService::sendBookingCancellation($booking, "Barang tidak diambil melewati batas toleransi waktu.");
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[EXPIRE ONLINE BOOKING] Gagal kirim WA cancel ke {$booking->rental_code}: " . $e->getMessage());
            }
        }
    }
}
