<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Jadwal Pengambilan Alat - SummitGear</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f1729 0%, #1e293b 100%); padding: 30px; text-align: center;">
                            <div style="display: inline-block; background-color: #e8430a; width: 44px; height: 44px; border-radius: 12px; text-align: center; line-height: 44px; margin-bottom: 12px;">
                                <span style="color: #ffffff; font-size: 22px; font-weight: 900;">▲</span>
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">
                                Summit<span style="color: #e8430a;">Gear</span>
                            </h1>
                            <p style="margin: 4px 0 0; color: #fbbf24; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;">
                                🔔 Pengingat Hari-H Pengambilan Alat
                            </p>
                        </td>
                    </tr>

                    <!-- Alert Greeting -->
                    <tr>
                        <td style="padding: 26px 30px 10px; text-align: center;">
                            <div style="display: inline-block; background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 9999px; padding: 6px 16px; margin-bottom: 14px;">
                                <span style="color: #b45309; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                                    ⏰ HARI INI JADWAL PENGAMBILAN!
                                </span>
                            </div>
                            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #0f1729;">
                                Halo, {{ $rental->customer->name }}!
                            </h2>
                            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;">
                                Hari ini adalah hari keberangkatan petualanganmu! Kami ingin mengingatkan bahwa pesanan alat outdoor kamu sudah disiapkan oleh tim SummitGear dan siap diambil.
                            </p>
                        </td>
                    </tr>

                    <!-- Action Time Card -->
                    <tr>
                        <td style="padding: 16px 30px;">
                            <table role="presentation" width="100%" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 2px solid #fde68a; border-radius: 16px; padding: 20px;">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #92400e;">Kode Booking Anda:</div>
                                        <div style="font-size: 20px; font-weight: 900; font-family: monospace; color: #0f1729; margin: 2px 0 10px;">
                                            {{ $rental->rental_code }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 10px; border-top: 1px solid #fde68a;">
                                        <div style="font-size: 13px; color: #78350f; line-height: 1.7;">
                                            🕒 <strong>Waktu Pengambilan:</strong> Hari ini pukul <span style="font-size: 15px; font-weight: 900; color: #0f1729;">{{ \Carbon\Carbon::parse($rental->start_date)->format('H:i') }} WIB</span><br>
                                            ⏳ <strong>Batas Toleransi:</strong> Maksimal pukul <span style="font-size: 15px; font-weight: 900; color: #dc2626;">{{ \Carbon\Carbon::parse($rental->start_date)->addHours(2)->format('H:i') }} WIB</span> (Toleransi 2 Jam)
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- What to Bring Checklist -->
                    <tr>
                        <td style="padding: 10px 30px;">
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px;">
                                <div style="font-size: 13px; font-weight: 800; color: #0f1729; margin-bottom: 8px;">
                                    📋 Yang Wajib Dibawa ke Toko:
                                </div>
                                <table role="presentation" width="100%">
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 13px; color: #334155;">
                                            ✅ <strong>KTP Fisik Asli</strong> pemesan (sesuai identitas booking).
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 13px; color: #334155;">
                                            ✅ <strong>Kode Booking: {{ $rental->rental_code }}</strong> (cukup tunjukkan dari HP).
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 13px; color: #334155;">
                                            ✅ Biaya sewa sebesar <strong>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong> (bisa tunai atau QRIS di kasir).
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <!-- Important Warning -->
                    <tr>
                        <td style="padding: 6px 30px 16px;">
                            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 16px; font-size: 12px; color: #991b1b;">
                                ⚠️ <em>Perhatian: Jika kamu tidak hadir hingga batas toleransi jam {{ \Carbon\Carbon::parse($rental->start_date)->addHours(2)->format('H:i') }} WIB tanpa konfirmasi, sistem kami akan membatalkan pemesanan dan unit akan dilepaskan untuk pelanggan lain.</em>
                            </div>
                        </td>
                    </tr>

                    <!-- Buttons -->
                    <tr>
                        <td style="padding: 10px 30px 24px; text-align: center;">
                            @php
                                $waAdmin = config('services.whatsapp.admin_phone', '6281234567890');
                                $waText = urlencode("Halo SummitGear, saya mengonfirmasi jadwal pengambilan booking {$rental->rental_code} atas nama {$rental->customer->name} hari ini. Saya sedang dalam perjalanan/ada konfirmasi.");
                            @endphp
                            <a href="https://wa.me/{{ $waAdmin }}?text={{ $waText }}" 
                               style="display: inline-block; background-color: #059669; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 800; padding: 12px 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(5,150,105,0.3);">
                                Konfirmasi ke Kasir via WhatsApp
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 4px; font-weight: 700; color: #64748b;">SummitGear Outdoor Gear Rental</p>
                            <p style="margin: 0;">Salam lestari dan selamat bertualang dengan aman!</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
