<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rental;
use App\Mail\PickupReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendPickupReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = Carbon::today()->toDateString();

        // Cari rental online aktif yang jadwalnya hari ini dan belum pernah dikirim reminder
        $upcomingRentals = Rental::where('source', 'online')
            ->whereIn('status', ['BOOKED', 'DP_PAID', 'PAID'])
            ->whereDate('start_date', $today)
            ->whereNull('pickup_reminder_sent_at')
            ->with(['customer', 'details.itemUnit.item', 'payments'])
            ->get();

        Log::info("[PICKUP REMINDER JOB] Memeriksa jadwal hari-H ({$today}), ditemukan: " . $upcomingRentals->count() . " booking.");

        foreach ($upcomingRentals as $rental) {
            try {
                $customer = $rental->customer;

                // 1. Kirim Email Pengingat Hari-H
                if ($customer && !empty($customer->email)) {
                    Mail::to($customer->email)->send(new PickupReminderMail($rental));
                    Log::info("[PICKUP REMINDER EMAIL] Terkirim ke {$customer->email} untuk TRX: {$rental->rental_code}");
                }

                // 2. Kirim Notifikasi WhatsApp
                if ($customer && !empty($customer->phone)) {
                    $pickupTime = Carbon::parse($rental->start_date)->format('H:i') . ' WIB';
                    $deadline = Carbon::parse($rental->start_date)->addHours(2)->format('H:i') . ' WIB';

                    $balanceDue = (int) $rental->balance_due;
                    $paymentLine = $balanceDue > 0
                        ? "• *Sisa Pembayaran:* Rp " . number_format($balanceDue, 0, ',', '.') . " (Bayar di Kasir saat pickup)\n\n"
                        : "• *Status Pembayaran:* LUNAS\n\n";

                    $waMsg = "*PENGINGAT HARI-H PENGAMBILAN ALAT SUMMITGEAR*\n\n"
                           . "Halo {$customer->name}! Hari ini adalah jadwal pengambilan alat outdoor kamu di SummitGear.\n\n"
                           . "• *Kode Booking:* {$rental->rental_code}\n"
                           . "• *Jam Pengambilan:* {$pickupTime}\n"
                           . "• *Batas Maks. Toleransi:* {$deadline}\n"
                           . $paymentLine
                           . "Mohon bawa *KTP fisik asli* dan tunjukkan kode booking ke kasir. Sampai jumpa di toko!";
                    \App\Services\WhatsAppService::sendMessage($customer->phone, $waMsg, $rental->rental_code);
                }

                // 3. Tandai sudah terkirim agar tidak berulang
                $rental->update(['pickup_reminder_sent_at' => now()]);

                \App\Services\AuditLogger::log('SYSTEM', 'Rental', $rental->id, "Mengirim pengingat Hari-H ke {$customer->name} ({$customer->email})");

            } catch (\Throwable $e) {
                Log::error("[PICKUP REMINDER ERROR] Gagal mengirim reminder untuk TRX {$rental->rental_code}: " . $e->getMessage());
            }
        }
    }
}
