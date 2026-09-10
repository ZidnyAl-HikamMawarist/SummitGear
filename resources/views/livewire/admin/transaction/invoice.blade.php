<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Detail Invoice & Surat Perjanjian Sewa" />

        <div class="content-area">
            <!-- Header Bar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->role === 'kasir')
                    <a href="{{ route('admin.transactions.create') }}" class="btn-form-cancel text-xs font-bold py-2 px-3.5" title="Kembali ke Kasir">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kasir</span>
                    </a>
                    @else
                    <a href="{{ route('admin.operations.incoming_booking') }}" class="btn-form-cancel text-xs font-bold py-2 px-3.5" title="Kembali ke Booking Masuk">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate uppercase tracking-wider">Transaksi</span>
                            <span class="text-slate text-xs">/</span>
                            <span class="text-xs font-bold text-coral">Invoice</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            Detail Invoice: {{ $rental->rental_code }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if(auth()->user()->role === 'kasir')
                    <a href="{{ route('admin.transactions.create') }}" class="btn text-xs font-bold text-navy bg-white hover:bg-slate-50 border border-gray-300 rounded-xl py-2 px-3.5 transition shadow-sm">
                        + Transaksi Baru
                    </a>
                    @endif
                    @if($rental->status !== 'VOID' && $rental->status !== 'CANCELLED')
                    <a href="{{ route('admin.transactions.print', $rental->id) }}" target="_blank" class="btn text-xs font-bold text-white bg-navy hover:bg-blue-900 rounded-xl py-2 px-4 transition shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>
                        </svg>
                        <span>Cetak Invoice / SPK</span>
                    </a>
                    @endif
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-5 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Invoice Card -->
            <div class="form-section-card max-w-4xl mx-auto shadow-md relative {{ $rental->status === 'VOID' ? 'opacity-75 grayscale' : '' }}">
                
                @if($rental->status === 'VOID')
                <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
                    <div class="transform -rotate-45 text-red-500 font-black text-6xl opacity-30 border-8 border-red-500 p-4 rounded-xl">VOID / DIBATALKAN</div>
                </div>
                @endif

                <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-black text-navy tracking-tight mb-0">SUMMITGEAR</h1>
                            <span class="badge badge-coral text-xs">POS OUTDOOR</span>
                        </div>
                        <p class="text-gray-500 text-xs mt-1 mb-0">Surat Perjanjian Sewa & Bukti Pembayaran Resmi</p>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="text-2xl font-black text-coral font-mono">{{ $rental->rental_code }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Tanggal Cetak: {{ now()->format('d M Y, H:i') }} WIB</div>
                        <div class="mt-2">
                            @if($rental->status === 'COMPLETED')
                                <span class="badge badge-success">SELESAI (COMPLETED)</span>
                            @elseif($rental->status === 'ACTIVE')
                                <span class="badge badge-warning">SEDANG DISEWA</span>
                            @elseif($rental->status === 'BOOKED')
                                <span class="badge badge-info">TERBOOKING</span>
                            @else
                                <span class="badge badge-neutral">STATUS: {{ $rental->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Informasi Penyewa</h4>
                        <div class="font-bold text-navy text-lg">{{ $rental->customer->name }}</div>
                        <div class="text-gray-600 text-sm mt-0.5">WhatsApp: {{ $rental->customer->phone }}</div>
                        <div class="text-gray-600 text-sm font-mono">NIK: {{ $rental->customer->nik }}</div>
                    </div>
                    <div class="text-left md:text-right">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Periode Waktu Sewa</h4>
                        <div class="text-gray-800 text-sm">
                            Ambil: <span class="font-bold">{{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</span>
                        </div>
                        <div class="text-gray-800 text-sm">
                            Kembali: <span class="font-bold">{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</span>
                        </div>
                        <div class="text-red-600 text-sm mt-1 font-bold">
                            Batas Pengembalian: {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB
                        </div>
                    </div>
                </div>

                <div class="px-8 pb-8">
                    <div class="flex items-center justify-between mb-3.5" style="padding-left: 1.25rem; padding-right: 0.5rem;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background-color: var(--color-coral);"></span>
                            <h4 class="text-xs font-bold text-navy uppercase tracking-wider mb-0">Rincian Peralatan</h4>
                        </div>
                        <span class="badge badge-neutral text-xs font-mono font-bold">{{ $rental->details->count() }} Unit ({{ $rental->groupedDetails->count() }} Item)</span>
                    </div>

                    <div class="sg-table-container">
                        <table class="sg-table">
                            <thead>
                                <tr>
                                    <th>Barang & Serial Number</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-right">Tarif Dasar / Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rental->groupedDetails as $group)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-navy text-[14px]">{{ $group['item_name'] }}</span>
                                            @if($group['quantity'] > 1)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-black bg-[#101F42] text-white">
                                                    x{{ $group['quantity'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                            <span class="text-xs text-gray-400 font-semibold">SN:</span>
                                            @forelse($group['serial_numbers'] as $sn)
                                                <span class="text-[11px] font-mono font-bold text-slate-700 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded">
                                                    {{ $sn }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400 font-mono">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-center font-black text-[14px] text-navy tabular-nums">{{ $group['quantity'] }}</td>
                                    <td class="text-right font-bold text-navy">
                                        <span class="tabular-nums text-[14px]">Rp {{ number_format($group['total_price_per_day'], 0, ',', '.') }}</span>
                                        @if($group['quantity'] > 1)
                                            <div class="text-[11px] text-gray-400 font-normal mt-0.5 tabular-nums">
                                                ({{ $group['quantity'] }} &times; Rp {{ number_format($group['price_per_day'], 0, ',', '.') }})
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-50 p-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-center border-t border-gray-100">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Informasi Jaminan & Pembayaran</h4>
                        <div class="text-sm text-gray-700">Jaminan Deposit: <strong class="text-coral">Rp {{ number_format($rental->deposits->sum('amount'), 0, ',', '.') }}</strong></div>
                        <div class="text-sm text-gray-700">Metode Bayar: <strong class="text-navy">{{ $rental->payments->first()->method ?? 'CASH' }}</strong></div>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="text-gray-500 text-xs font-semibold uppercase">Total Tagihan (Include Dinamis)</div>
                        <div class="text-3xl font-black text-navy mt-1">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</div>
                        
                        @if($rental->payments->sum('amount') >= $rental->total_price)
                            <div class="mt-2 text-green-600 font-bold text-xs uppercase tracking-wider flex items-center md:justify-end gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>LUNAS TERBAYAR</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            
            @if($rental->status !== 'VOID' && $rental->status !== 'CANCELLED')
            <div class="mt-6 text-center">
                <button wire:click="confirmVoid" class="text-red-500 hover:text-red-700 text-xs font-bold underline transition">
                    Batalkan Transaksi Ini (VOID)
                </button>
                <p class="text-xs text-gray-400 mt-1">Memerlukan otorisasi PIN Admin.</p>
            </div>
            @endif

        </div>
    </main>
</div>
