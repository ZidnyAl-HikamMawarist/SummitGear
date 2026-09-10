<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rental;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseHoldBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $holdHours = DB::table('settings')->where('key', 'hold_duration_hours')->value('value') ?? 2;
        $cutoffTime = Carbon::now()->subHours((int)$holdHours);

        // Cari rental berstatus PENDING_PAYMENT yang melewati batas hold hours
        $expiredRentals = Rental::where('status', 'PENDING_PAYMENT')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        foreach ($expiredRentals as $rental) {
            $rental->update([
                'status' => 'CANCELLED',
            ]);

            AuditLogger::log('SYSTEM', 'Rental', $rental->id, "Auto-release hold booking karena melewati batas waktu hold ({$holdHours} jam).");
        }
    }
}
