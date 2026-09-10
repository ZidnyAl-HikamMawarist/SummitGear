<?php

namespace App\Services;

use App\Models\Rental;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Kirim notifikasi WA (Mocking & Real API Ready)
     */
    public static function sendMessage($phone, $message, $rentalCode = null)
    {
        $apiKey = config('services.whatsapp.api_key');
        $endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');

        // Jika API Key belum diatur, lakukan Mocking & simpan ke Log
        if (empty($apiKey) || $apiKey === 'MOCK_KEY') {
            Log::info("[MOCK WA GATEWAY] Mengirim pesan ke {$phone} (TRX: {$rentalCode}):\n{$message}");
            return [
                'status' => true,
                'mode' => 'MOCK',
                'message' => 'Pesan WA disimulasikan (Mocking Mode) dan tercatat di Log Sistem.'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post($endpoint, [
                'target' => $phone,
                'message' => $message,
            ]);

            return [
                'status' => $response->successful(),
                'mode' => 'LIVE',
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("[WA GATEWAY ERROR] Gagal mengirim ke {$phone}: " . $e->getMessage());
            return [
                'status' => false,
                'mode' => 'ERROR',
                'error' => $e->getMessage()
            ];
        }
    }

    public static function sendBookingConfirmation(Rental $rental)
    {
        $customer = $rental->customer;
        $items = $rental->details->map(fn($d) => "• " . ($d->itemUnit->item->name ?? 'Alat'))->implode("\n");
        $pickupTime = \Carbon\Carbon::parse($rental->start_date)->format('d M Y, H:i') . ' WIB';
        $deadlineTime = \Carbon\Carbon::parse($rental->start_date)->addHours(2)->format('d M Y, H:i') . ' WIB';
        
        $msg = "*KONFIRMASI BOOKING SUMMITGEAR*\n\n"
             . "Halo {$customer->name}, terima kasih telah menyewa di SummitGear!\n"
             . "Kode Booking: *{$rental->rental_code}*\n"
             . "Jadwal Pengambilan: *{$pickupTime}*\n"
             . "Batas Maksimal Pengambilan: *{$deadlineTime}* (+2 jam toleransi)\n\n"
             . "*PERHATIAN:* Jika barang tidak diambil melewati batas waktu di atas, kasir berhak membatalkan booking Anda dan mengembalikan stok ke gudang.\n\n"
             . "Daftar Alat:\n{$items}\n\n"
             . "Harap membawa KTP/Identitas asli saat pengambilan alat di outlet kami. Salam Lestari!";

        return self::sendMessage($customer->phone, $msg, $rental->rental_code);
    }

    public static function sendBookingCancellation(Rental $rental, $reason = 'Tidak diambil melewati batas waktu toleransi')
    {
        $customer = $rental->customer;
        $msg = "*PEMBATALAN BOOKING SUMMITGEAR*\n\n"
             . "Halo {$customer->name},\n"
             . "Pemberitahuan bahwa booking sewa dengan kode *{$rental->rental_code}* telah *DIBATALKAN* oleh kasir/sistem.\n\n"
             . "Alasan: *{$reason}*\n\n"
             . "Unit barang telah otomatis dikembalikan ke inventaris toko kami. Apabila Anda masih membutuhkan perlengkapan outdoor, silakan lakukan reservasi ulang melalui website atau kunjungi langsung outlet kami. Terima kasih!";

        return self::sendMessage($customer->phone, $msg, $rental->rental_code);
    }

    public static function sendReturnReminder(Rental $rental)
    {
        $customer = $rental->customer;
        $msg = "*PENGINGAT PENGEMBALIAN ALAT (H-1)*\n\n"
             . "Halo {$customer->name}, mengingatkan bahwa sewa dengan kode *{$rental->rental_code}* akan jatuh tempo pengembalian pada:\n"
             . "Hari/Tgl: *" . \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') . "*\n\n"
             . "Mohon kembalikan alat tepat waktu untuk menghindari biaya denda keterlambatan. Terima kasih!";

        return self::sendMessage($customer->phone, $msg, $rental->rental_code);
    }

    public static function sendOverdueAlert(Rental $rental)
    {
        $customer = $rental->customer;
        $msg = "*PERINGATAN: KETERLAMBATAN PENGEMBALIAN*\n\n"
             . "Halo {$customer->name}, batas pengembalian alat dengan kode *{$rental->rental_code}* telah terlewati.\n"
             . "Denda keterlambatan otomatis mulai dihitung per jam sesuai S&K yang disetujui.\n"
             . "Mohon segera mengembalikan alat ke toko kami atau hubungi admin untuk konfirmasi perpanjangan.";

        return self::sendMessage($customer->phone, $msg, $rental->rental_code);
    }
}
