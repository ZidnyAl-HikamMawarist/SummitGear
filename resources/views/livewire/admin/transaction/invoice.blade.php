<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Detail Invoice & Surat Perjanjian Sewa" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->role === 'kasir')
                    <flux:button href="{{ route('admin.transactions.create') }}" variant="subtle" icon="arrow-left" size="sm">
                        Kasir
                    </flux:button>
                    @else
                    <flux:button href="{{ route('admin.operations.incoming_booking') }}" variant="subtle" icon="arrow-left" size="sm">
                        Kembali
                    </flux:button>
                    @endif
                    <div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                            <span>Transaksi</span>
                            <span>/</span>
                            <span class="text-coral font-bold">Invoice</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            Detail Invoice: {{ $rental->rental_code }}
                        </h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if(auth()->user()->role === 'kasir')
                    <flux:button href="{{ route('admin.transactions.create') }}" variant="subtle" size="sm" icon="plus">
                        Transaksi Baru
                    </flux:button>
                    @endif

                    @if(in_array($rental->status, ['BOOKED', 'DP_PAID', 'PAID']))
                    <flux:button href="{{ route('admin.operations.checkout', $rental->id) }}" variant="primary" size="sm" icon="arrow-right">
                        Serah Terima Barang (Check-Out)
                    </flux:button>
                    @elseif(in_array($rental->status, ['RENTED_OUT', 'OVERDUE']))
                    <flux:button href="{{ route('admin.operations.checkin', $rental->id) }}" variant="primary" size="sm" icon="arrow-down-tray">
                        Pengembalian Barang (Check-In)
                    </flux:button>
                    <flux:button type="button" wire:click="openExtendModal" variant="subtle" size="sm" icon="calendar">
                        Perpanjang Masa Sewa
                    </flux:button>
                    @elseif($rental->status === 'PENDING_SETTLEMENT')
                    <flux:button href="{{ route('admin.settlements.show', $rental->id) }}" variant="primary" size="sm" icon="calculator">
                        Selesaikan Tagihan (Settlement)
                    </flux:button>
                    @endif

                    @if($rental->status !== 'VOID' && $rental->status !== 'CANCELLED')
                    <flux:button href="{{ route('admin.transactions.print', $rental->id) }}" target="_blank" variant="subtle" size="sm" icon="printer">
                        Cetak Invoice / SPK
                    </flux:button>
                    @endif
                </div>
            </div>

            @if (session()->has('message'))
                <div class="p-4 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 text-xs font-bold text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Invoice Card -->
            <flux:card class="max-w-4xl mx-auto p-0 overflow-hidden shadow-md border border-gray-200/80 relative {{ $rental->status === 'VOID' ? 'opacity-75 grayscale' : '' }}">
                
                @if($rental->status === 'VOID')
                <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
                    <div class="transform -rotate-45 text-red-500 font-black text-6xl opacity-30 border-8 border-red-500 p-4 rounded-xl">VOID / DIBATALKAN</div>
                </div>
                @endif

                <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-2xl font-black text-navy tracking-tight">SUMMITGEAR</h2>
                            <flux:badge color="coral" size="sm">POS OUTDOOR</flux:badge>
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Surat Perjanjian Sewa & Bukti Pembayaran Resmi</p>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="text-2xl font-black text-coral font-mono">{{ $rental->rental_code }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Tanggal Cetak: {{ now()->format('d M Y, H:i') }} WIB</div>
                        <div class="mt-2">
                            @if($rental->status === 'COMPLETED')
                                <flux:badge color="emerald" size="sm">SELESAI (COMPLETED)</flux:badge>
                            @elseif($rental->status === 'ACTIVE')
                                <flux:badge color="amber" size="sm">SEDANG DISEWA</flux:badge>
                            @elseif($rental->status === 'DP_PAID')
                                <flux:badge color="sky" size="sm">DP TERBAYAR (BOOKING)</flux:badge>
                            @elseif($rental->status === 'PAID')
                                <flux:badge color="emerald" size="sm">LUNAS ONLINE (BOOKING)</flux:badge>
                            @elseif($rental->status === 'PENDING_PAYMENT')
                                <flux:badge color="amber" size="sm">MENUNGGU PEMBAYARAN (HOLD)</flux:badge>
                            @elseif($rental->status === 'BOOKED')
                                <flux:badge color="sky" size="sm">TERBOOKING</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">STATUS: {{ $rental->status }}</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Informasi Penyewa</h3>
                        <div class="font-bold text-navy text-lg">{{ $rental->customer->name }}</div>
                        <div class="text-gray-600 text-sm mt-0.5">WhatsApp: {{ $rental->customer->phone }}</div>
                        <div class="text-gray-600 text-sm font-mono">NIK: {{ $rental->customer->nik }}</div>
                    </div>
                    <div class="text-left md:text-right">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Periode Waktu Sewa</h3>
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

                <div class="px-8 pb-8 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-coral"></span>
                            <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Rincian Peralatan</h3>
                        </div>
                        <flux:badge color="zinc" size="sm" class="font-mono font-bold">{{ $rental->details->count() }} Unit ({{ $rental->groupedDetails->count() }} Item)</flux:badge>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 bg-slate-50/80 text-gray-500 font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Barang & Serial Number</th>
                                    <th class="py-3 px-4 text-center">Kuantitas</th>
                                    <th class="py-3 px-4 text-right">Tarif Dasar / Hari</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($rental->groupedDetails as $group)
                                <tr>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-navy text-sm">{{ $group['item_name'] }}</span>
                                            @if($group['quantity'] > 1)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-black bg-navy text-white">
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
                                    <td class="py-3 px-4 text-center font-black text-sm text-navy tabular-nums">{{ $group['quantity'] }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-navy">
                                        <span class="tabular-nums text-sm">Rp {{ number_format($group['total_price_per_day'], 0, ',', '.') }}</span>
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
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Informasi Jaminan & Pembayaran</h3>
                        <div class="text-sm text-gray-700">Jaminan Deposit: <strong class="text-coral">Rp {{ number_format($rental->deposits->sum('amount'), 0, ',', '.') }}</strong></div>
                        <div class="text-sm text-gray-700">Metode Bayar: <strong class="text-navy">{{ $rental->payments->first()->method ?? 'CASH' }}</strong></div>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="text-gray-500 text-xs font-semibold uppercase">Total Tagihan (Include Dinamis)</div>
                        <div class="text-3xl font-black text-navy mt-1">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</div>
                        
                        @if($rental->status === 'DP_PAID' || ($rental->down_payment_amount > 0 && $rental->balance_due > 0))
                            <div class="mt-2 text-sky-600 font-bold text-xs uppercase tracking-wider flex items-center md:justify-end gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>DP TERBAYAR: Rp {{ number_format($rental->down_payment_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-xs text-amber-700 font-bold mt-1">
                                Sisa Pelunasan di Outlet: Rp {{ number_format($rental->balance_due, 0, ',', '.') }}
                            </div>
                        @elseif($rental->payments->sum('amount') >= $rental->total_price || $rental->status === 'PAID')
                            <div class="mt-2 text-emerald-600 font-bold text-xs uppercase tracking-wider flex items-center md:justify-end gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>LUNAS TERBAYAR (Rp {{ number_format($rental->total_price, 0, ',', '.') }})</span>
                            </div>
                        @endif
                    </div>
                </div>

            </flux:card>
            
            @if($rental->status !== 'VOID' && $rental->status !== 'CANCELLED')
            <div class="pt-4 text-center">
                <flux:button wire:click="confirmVoid" variant="danger" size="sm">
                    Batalkan Transaksi Ini (VOID)
                </flux:button>
                <p class="text-xs text-gray-400 mt-1">Memerlukan otorisasi PIN Admin.</p>
            </div>
            @endif

            @if($showExtendModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-base text-navy">Perpanjang Masa Sewa (Extension)</h3>
                        </div>
                        <button type="button" wire:click="closeExtendModal" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div class="space-y-3">
                        <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal Kembali Saat Ini:</span>
                                <strong class="text-navy">{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Sewa Saat Ini:</span>
                                <strong class="text-emerald-700">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div>
                            <flux:label class="text-xs mb-1 block">Pilih Tanggal Kembali Baru:</flux:label>
                            <flux:input type="date" wire:model.live="newEndDate" min="{{ \Carbon\Carbon::parse($rental->end_date)->addDays(1)->format('Y-m-d') }}" />
                            @error('newEndDate') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($extendDays > 0)
                        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-emerald-800">Tambahan Durasi:</span>
                                <strong class="text-emerald-900 font-bold">+{{ $extendDays }} Hari</strong>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-emerald-800">Tambahan Biaya Sewa Pokok:</span>
                                <strong class="text-emerald-900 font-bold text-sm">Rp {{ number_format($extendCost, 0, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between items-center text-xs border-t border-emerald-200/60 pt-1.5">
                                <span class="text-emerald-900 font-bold">Total Sewa Baru:</span>
                                <strong class="text-navy font-black text-base">Rp {{ number_format($rental->total_price + $extendCost, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        @endif

                        @if(count($extendCollisions) > 0)
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Peringatan Bentrok Booking Lanjutan ({{ count($extendCollisions) }} Unit):</span>
                            </div>
                            <div class="space-y-1 max-h-32 overflow-y-auto text-[11px] text-amber-800">
                                @foreach($extendCollisions as $col)
                                    <div class="p-1.5 bg-white/70 rounded border border-amber-200/60">
                                        Unit <strong>{{ $col['serial_number'] }}</strong> ({{ $col['item_name'] }}) sudah dibooking oleh <strong>{{ $col['colliding_customer'] }}</strong> ({{ $col['colliding_rental_code'] }}) tgl {{ \Carbon\Carbon::parse($col['colliding_start'])->format('d M') }}.
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-[10.5px] text-amber-700 leading-tight">
                                Anda tetap dapat memperpanjang sewa ini, namun segera lakukan <em>Unit Swap</em> pada booking yang bentrok di atas.
                            </p>
                        </div>
                        @endif

                        @error('extendError')
                            <div class="p-3 text-xs font-bold text-red-700 bg-red-50 rounded-xl">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <flux:button type="button" wire:click="closeExtendModal" variant="subtle" size="sm">
                            Batal
                        </flux:button>
                        @if($extendDays > 0)
                        <flux:button type="button" wire:click="executeExtension" variant="primary" size="sm">
                            Konfirmasi Perpanjangan
                        </flux:button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </main>
</div>
