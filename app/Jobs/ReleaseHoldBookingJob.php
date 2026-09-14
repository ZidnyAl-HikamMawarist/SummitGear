<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rental;
use App\Services\AuditLogger;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseHoldBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();

        // Cari rental berstatus PENDING_PAYMENT yang melewati batas expires_at atau 10 menit dari created_at
        $expiredRentals = Rental::with('details.itemUnit', 'customer')
            ->where('status', Rental::STATUS_PENDING_PAYMENT)
            ->where(function ($q) use ($now) {
                $q->where('expires_at', '<=', $now)
                  ->orWhere(function ($sub) use ($now) {
                      $sub->whereNull('expires_at')
                          ->where('created_at', '<=', $now->copy()->subMinutes(10));
                  });
            })
            ->get();

        foreach ($expiredRentals as $rental) {
            DB::transaction(function () use ($rental) {
                $rental->update([
                    'status' => Rental::STATUS_CANCELLED,
                    'expires_at' => null,
                ]);

                // Kembalikan unit fisik ke gudang
                $unitCount = 0;
                foreach ($rental->details as $detail) {
                    if ($detail->itemUnit) {
                        $detail->itemUnit->update(['status' => 'Available']);
                        $unitCount++;
                    }
                }

                AuditLogger::log(
                    'SYSTEM',
                    'Rental',
                    $rental->id,
                    "Auto-cancel booking {$rental->rental_code} karena batas waktu pembayaran 10 menit telah habis. Sebanyak {$unitCount} unit dikembalikan ke katalog."
                );

                try {
                    WhatsAppService::sendBookingCancellation(
                        $rental,
                        "Batas waktu pembayaran 10 menit telah berakhir dan pesanan dibatalkan otomatis."
                    );
                } catch (\Throwable $e) {}
            });
        }
    }
}
