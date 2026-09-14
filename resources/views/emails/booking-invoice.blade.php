<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Booking SummitGear</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f1729 0%, #1e293b 100%); padding: 32px 30px; text-align: center;">
                            <div style="display: inline-block; background-color: #e8430a; width: 44px; height: 44px; border-radius: 12px; text-align: center; line-height: 44px; margin-bottom: 12px;">
                                <span style="color: #ffffff; font-size: 22px; font-weight: 900;">▲</span>
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">
                                Summit<span style="color: #e8430a;">Gear</span>
                            </h1>
                            <p style="margin: 4px 0 0; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;">
                                Invoice Pemesanan Online
                            </p>
                        </td>
                    </tr>

                    <!-- Success Banner -->
                    <tr>
                        <td style="padding: 24px 30px 10px; text-align: center;">
                            <div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; padding: 6px 16px; margin-bottom: 14px;">
                                <span style="color: #059669; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                                    ✓ Booking Berhasil Diterima
                                </span>
                            </div>
                            <h2 style="margin: 0 0 8px; font-size: 20px; font-weight: 800; color: #0f1729;">
                                Halo, {{ $rental->customer->name }}! 🎉
                            </h2>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
                                Terima kasih telah memesan peralatan outdoor di <strong>SummitGear</strong>. Pesanan sewa kamu telah kami amankan di sistem. Silakan datang ke toko sesuai jadwal yang telah ditentukan.
                            </p>
                        </td>
                    </tr>

                    <!-- Booking Key Details Box -->
                    <tr>
                        <td style="padding: 15px 30px;">
                            <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px;">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Kode Booking:</div>
                                        <div style="font-size: 18px; font-weight: 900; font-family: monospace; color: #0f1729; letter-spacing: 1px; margin-top: 2px;">
                                            {{ $rental->rental_code }}
                                        </div>
                                    </td>
                                    <td align="right">
                                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Status:</div>
                                        <div style="font-size: 12px; font-weight: 800; color: #059669; background-color: #ecfdf5; padding: 4px 10px; border-radius: 6px; display: inline-block; margin-top: 2px;">
                                            MENUNGGU DIAMBIL
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top: 14px; border-top: 1px dashed #cbd5e1; margin-top: 12px;">
                                        <table role="presentation" width="100%">
                                            <tr>
                                                <td width="50%" style="font-size: 12px; color: #475569;">
                                                    <strong>Jadwal Pengambilan:</strong><br>
                                                    <span style="color: #0f1729; font-weight: 700;">
                                                        {{ \Carbon\Carbon::parse($rental->start_date)->translatedFormat('d F Y, \p\u\k\u\l H:i') }} WIB
                                                    </span>
                                                </td>
                                                <td width="50%" style="font-size: 12px; color: #475569;">
                                                    <strong>Batas Toleransi:</strong><br>
                                                    <span style="color: #d97706; font-weight: 700;">
                                                        Maks. {{ \Carbon\Carbon::parse($rental->start_date)->addHours(2)->format('H:i') }} WIB (2 Jam)
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 12px; color: #475569; padding-top: 10px;">
                                                    <strong>Tanggal Kembali:</strong><br>
                                                    <span style="color: #0f1729; font-weight: 700;">
                                                        {{ \Carbon\Carbon::parse($rental->end_date)->translatedFormat('d F Y') }} (Pukul 18:00 WIB)
                                                    </span>
                                                </td>
                                                <td style="font-size: 12px; color: #475569; padding-top: 10px;">
                                                    <strong>Nomor WhatsApp:</strong><br>
                                                    <span style="color: #0f1729; font-weight: 700;">
                                                        {{ $rental->customer->phone }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Itemized Invoice Table -->
                    <tr>
                        <td style="padding: 10px 30px;">
                            <h3 style="margin: 0 0 10px; font-size: 14px; font-weight: 800; color: #0f1729; text-transform: uppercase; letter-spacing: 0.5px;">
                                Rincian Perlengkapan Disewa
                            </h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; width: 100%;">
                                <thead>
                                    <tr style="background-color: #f1f5f9; text-align: left;">
                                        <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: #475569; border-radius: 8px 0 0 8px;">Peralatan</th>
                                        <th style="padding: 10px 8px; font-size: 11px; font-weight: 800; color: #475569; text-align: center;">Qty</th>
                                        <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: #475569; text-align: right; border-radius: 0 8px 8px 0;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group items by item id to show friendly summary
                                        $groupedDetails = $rental->details->groupBy(function($d) {
                                            return $d->itemUnit->item->id ?? $d->id;
                                        });
                                        $startD = \Carbon\Carbon::parse($rental->start_date)->startOfDay();
                                        $endD = \Carbon\Carbon::parse($rental->end_date)->startOfDay();
                                        $duration = max(1, $startD->diffInDays($endD) + 1);
                                    @endphp
                                    @foreach($groupedDetails as $group)
                                        @php
                                            $first = $group->first();
                                            $itemName = $first->itemUnit->item->name ?? 'Perlengkapan Outdoor';
                                            $qty = $group->count();
                                            $pricePerDay = $first->price_per_day;
                                            $subtotal = $pricePerDay * $qty * $duration;
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 12px 12px; font-size: 13px; color: #1e293b; font-weight: 600;">
                                                {{ $itemName }}
                                                <div style="font-size: 11px; color: #94a3b8; font-weight: normal;">
                                                    Rp {{ number_format($pricePerDay, 0, ',', '.') }}/hari × {{ $duration }} hari
                                                </div>
                                            </td>
                                            <td style="padding: 12px 8px; font-size: 13px; color: #0f1729; font-weight: 800; text-align: center;">
                                                x{{ $qty }}
                                            </td>
                                            <td style="padding: 12px 12px; font-size: 13px; color: #0f1729; font-weight: 800; text-align: right;">
                                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="padding: 14px 12px; font-size: 13px; font-weight: 800; color: #0f1729; text-align: right;">
                                            Total Biaya Sewa ({{ $duration }} Hari):
                                        </td>
                                        <td style="padding: 14px 12px; font-size: 16px; font-weight: 900; color: #e8430a; text-align: right;">
                                            Rp {{ number_format($rental->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="padding: 4px 12px 10px; font-size: 11px; color: #64748b; text-align: right;">
                                            <em>*Pembayaran dilakukan langsung di toko SummitGear saat pengambilan alat (Kasir).</em>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>

                    <!-- Important Instructions Box -->
                    <tr>
                        <td style="padding: 10px 30px;">
                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 16px;">
                                <div style="font-size: 12px; font-weight: 800; color: #92400e; margin-bottom: 6px;">
                                    📌 Syarat & Ketentuan Pengambilan di Toko:
                                </div>
                                <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #78350f; line-height: 1.6;">
                                    <li>Wajib membawa dan menunjukkan <strong>KTP fisik asli</strong> penyewa saat di kasir.</li>
                                    <li>Tunjukkan email invoice ini atau <strong>Kode Booking: {{ $rental->rental_code }}</strong>.</li>
                                    <li>Batas toleransi pengambilan maksimal <strong>2 jam</strong> setelah waktu yang kamu tentukan. Jika tidak diambil, kasir berhak membatalkan booking agar stok dapat disewa pelanggan lain.</li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Contact & CTA -->
                    <tr>
                        <td style="padding: 24px 30px; text-align: center;">
                            @php
                                $waAdmin = config('services.whatsapp.admin_phone', '6281234567890');
                                $waText = urlencode("Halo SummitGear, saya ingin konfirmasi booking kode {$rental->rental_code} atas nama {$rental->customer->name}.");
                            @endphp
                            <a href="https://wa.me/{{ $waAdmin }}?text={{ $waText }}" 
                               style="display: inline-block; background-color: #e8430a; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 800; padding: 12px 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(232,67,10,0.3);">
                                Konfirmasi ke WhatsApp Toko
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 4px; font-weight: 700; color: #64748b;">SummitGear Outdoor Gear Rental</p>
                            <p style="margin: 0;">Email ini dibuat secara otomatis oleh sistem reservasi SummitGear.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
