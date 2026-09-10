<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rental;
use App\Models\Penalty;
use App\Services\WhatsAppService;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalculateLatePenaltyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lateFeePerHour = DB::table('settings')->where('key', 'late_fee_per_hour')->value('value') ?? 5000;

        // Cari rental RENTED_OUT atau OVERDUE yang melewati scheduled_return_time
        $overdueRentals = Rental::whereIn('status', ['RENTED_OUT', 'OVERDUE'])
            ->where('scheduled_return_time', '<', Carbon::now())
            ->get();

        foreach ($overdueRentals as $rental) {
            $hoursLate = Carbon::parse($rental->scheduled_return_time)->diffInHours(Carbon::now()) + 1;
            $penaltyAmount = $hoursLate * (int)$lateFeePerHour;

            // Cari apakah sudah ada baris penalty 'late_return'
            $penalty = Penalty::where('rental_id', $rental->id)
                ->where('reason', 'like', 'Denda keterlambatan otomatis%')
                ->where('is_settled', false)
                ->first();

            if ($penalty) {
                $penalty->update([
                    'amount' => $penaltyAmount,
                    'reason' => "Denda keterlambatan otomatis ({$hoursLate} jam)",
                ]);
            } else {
                Penalty::create([
                    'rental_id' => $rental->id,
                    'reason' => "Denda keterlambatan otomatis ({$hoursLate} jam)",
                    'amount' => $penaltyAmount,
                    'is_settled' => false,
                ]);

                // Kirim notifikasi WA peringatan keterlambatan
                WhatsAppService::sendOverdueAlert($rental);
            }

            if ($rental->status !== 'OVERDUE') {
                $rental->update(['status' => 'OVERDUE']);
                AuditLogger::log('SYSTEM', 'Rental', $rental->id, "Transaksi terdeteksi OVERDUE. Denda otomatis Rp {$penaltyAmount} diterapkan.");
            }
        }
    }
}
