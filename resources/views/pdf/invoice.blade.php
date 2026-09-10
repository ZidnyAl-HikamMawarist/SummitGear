<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $rental->rental_code }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #1E293B; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1E293B; font-size: 28px; font-weight: 900; }
        .header p { margin: 5px 0 0 0; color: #64748B; }
        
        .row { width: 100%; display: table; margin-bottom: 30px; }
        .col { display: table-cell; width: 50%; }
        .col-right { text-align: right; }
        
        .section-title { font-size: 12px; font-weight: bold; color: #94A3B8; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #E2E8F0; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 10px; background-color: #F8FAFC; color: #475569; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #CBD5E1; }
        td { padding: 10px; border-bottom: 1px solid #E2E8F0; }
        th.right, td.right { text-align: right; }
        
        .totals { width: 50%; margin-left: 50%; margin-bottom: 30px;}
        .totals-row { width: 100%; display: table; padding: 5px 0; }
        .totals-label { display: table-cell; width: 50%; font-weight: bold; color: #64748B; text-align: right; padding-right: 20px;}
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: bold;}
        .totals-row.grand-total .totals-value { font-size: 18px; color: #1E293B; }
        
        .footer { border-top: 1px solid #E2E8F0; padding-top: 20px; font-size: 12px; color: #64748B; text-align: center; }
        
        .status-badge { display: inline-block; padding: 3px 8px; background: #DBEAFE; color: #1E40AF; border-radius: 12px; font-size: 12px; font-weight: bold;}
    </style>
</head>
<body>
    <div class="header">
        <div class="row">
            <div class="col">
                <h1>SUMMITGEAR</h1>
                <p>Invoice & SPK (Surat Perjanjian Sewa)</p>
            </div>
            <div class="col col-right">
                <div style="font-size: 20px; font-weight: bold; color: #F97316;">{{ $rental->rental_code }}</div>
                <div>Tgl Cetak: {{ now()->format('d M Y, H:i') }}</div>
                <div style="margin-top: 5px;"><span class="status-badge">{{ $rental->status }}</span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="section-title">Pelanggan</div>
            <strong>{{ $rental->customer->name }}</strong><br>
            {{ $rental->customer->phone }}<br>
            NIK: {{ $rental->customer->nik }}
        </div>
        <div class="col col-right">
            <div class="section-title">Periode Sewa</div>
            <strong>Ambil:</strong> {{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}<br>
            <strong>Kembali:</strong> {{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}<br>
            <strong style="color:#DC2626;">Batas Jam:</strong> {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Barang & Serial Number</th>
                <th class="right">Tarif Dasar/Hr</th>
                <th class="right">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rental->groupedDetails as $group)
            <tr>
                <td>
                    <strong>{{ $group['item_name'] }}</strong>
                    @if($group['quantity'] > 1)
                        <span style="display:inline-block; padding:1px 6px; background:#1E293B; color:#fff; border-radius:10px; font-size:10px; font-weight:bold; margin-left:4px;">x{{ $group['quantity'] }}</span>
                    @endif
                    <br>
                    <span style="font-size: 11px; color: #64748B;">
                        SN: {{ implode(', ', $group['serial_numbers']) ?: '-' }}
                    </span>
                </td>
                <td class="right">
                    Rp {{ number_format($group['total_price_per_day'], 0, ',', '.') }}
                    @if($group['quantity'] > 1)
                        <br><span style="font-size: 10px; color: #64748B;">({{ $group['quantity'] }} &times; Rp {{ number_format($group['price_per_day'], 0, ',', '.') }})</span>
                    @endif
                </td>
                <td class="right font-bold">{{ $group['quantity'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Total Deposit:</div>
            <div class="totals-value">Rp {{ number_format($rental->deposits->sum('amount'), 0, ',', '.') }}</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Metode Bayar:</div>
            <div class="totals-value">{{ $rental->payments->first()->method ?? 'CASH' }}</div>
        </div>
        <div class="totals-row grand-total">
            <div class="totals-label">TOTAL TAGIHAN:</div>
            <div class="totals-value">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih telah menyewa di SummitGear!</p>
        <p>Dengan membawa dokumen ini, pelanggan menyatakan setuju dengan seluruh Syarat & Ketentuan sewa yang berlaku.</p>
    </div>
</body>
</html>
