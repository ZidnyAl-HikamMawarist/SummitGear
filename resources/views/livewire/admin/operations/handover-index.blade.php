<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Serah Terima & Checklist QC Operasional" />

        <div class="content-area">
            <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-navy">Serah Terima & Checklist QC</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Monitoring serah-terima unit keluar (check-out) dan inspeksi fisik pengembalian (check-in).</p>
                    </div>
                    @if(auth()->user()->role === 'kasir')
                    <flux:button href="{{ route('admin.transactions.create') }}" variant="primary" icon="plus">
                        Transaksi Baru
                    </flux:button>
                    @endif
                </div>

                <!-- Summary KPI 2-column -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="clean-stat-card">
                        <div class="clean-stat-top">
                            <span class="clean-stat-label">Check-Out Hari Ini</span>
                            <div class="clean-stat-icon bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                        <span class="clean-stat-value">{{ $todayCheckouts }} <span class="text-xs font-semibold text-slate-400">Transaksi</span></span>
                        <div>
                            <span class="clean-stat-badge clean-badge-blue">Siap Serah Terima</span>
                        </div>
                    </div>
                    <div class="clean-stat-card">
                        <div class="clean-stat-top">
                            <span class="clean-stat-label">Pengembalian Hari Ini</span>
                            <div class="clean-stat-icon bg-amber-50 text-amber-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                </svg>
                            </div>
                        </div>
                        <span class="clean-stat-value">{{ $todayReturns }} <span class="text-xs font-semibold text-slate-400">Transaksi</span></span>
                        <div>
                            <span class="clean-stat-badge clean-badge-amber">Jadwal Masuk (QC)</span>
                        </div>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif

                <!-- Search & Status Filter -->
                <flux:card class="p-4">
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
                        <div class="flex-1">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode sewa, nama pelanggan, atau no WhatsApp..." icon="magnifying-glass" />
                        </div>
                        <!-- Status Pill Toggle -->
                        <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1 rounded-xl shrink-0">
                            <button type="button" wire:click="$set('statusFilter', 'ALL')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'ALL' ? 'bg-white text-navy shadow-xs' : 'text-slate-500 hover:text-navy' }}">Semua</button>
                            <button type="button" wire:click="$set('statusFilter', 'BOOKED')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'BOOKED' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-blue-600' }}">Check-Out</button>
                            <button type="button" wire:click="$set('statusFilter', 'RENTED_OUT')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'RENTED_OUT' ? 'bg-white text-emerald-600 shadow-xs' : 'text-slate-500 hover:text-emerald-600' }}">Disewa</button>
                            <button type="button" wire:click="$set('statusFilter', 'OVERDUE')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'OVERDUE' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-500 hover:text-rose-600' }}">Terlambat</button>
                            <button type="button" wire:click="$set('statusFilter', 'PENDING_SETTLEMENT')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'PENDING_SETTLEMENT' ? 'bg-white text-purple-600 shadow-xs' : 'text-slate-500 hover:text-purple-600' }}">Denda/Sengketa</button>
                            <button type="button" wire:click="$set('statusFilter', 'COMPLETED')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $statusFilter === 'COMPLETED' ? 'bg-white text-slate-700 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">Selesai</button>
                        </div>
                    </div>
                </flux:card>

                <!-- Handover Table -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-5">Kode TRX & Pelanggan</th>
                                    <th class="py-3 px-5">Periode & Batas Waktu</th>
                                    <th class="py-3 px-5">Unit / Barang Disewa</th>
                                    <th class="py-3 px-5">Status Operasional</th>
                                    <th class="py-3 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($rentals as $rental)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <a href="{{ route('admin.transactions.invoice', $rental->id) }}" class="font-bold text-navy hover:text-coral transition">
                                            {{ $rental->rental_code }}
                                        </a>
                                        <div class="text-xs font-semibold text-slate-700 mt-0.5">{{ $rental->customer->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $rental->customer->phone }}</div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="text-xs text-slate-700">Mulai: <strong>{{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</strong></div>
                                        <div class="text-xs text-slate-700">Kembali: <strong>{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</strong></div>
                                        @if($rental->scheduled_return_time)
                                        <div class="text-xs font-bold mt-0.5 text-rose-600">Batas: {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M H:i') }} WIB</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <flux:badge size="sm" color="zinc" class="mb-1 font-mono">{{ $rental->details->count() }} Unit</flux:badge>
                                        <div class="text-xs text-slate-500 space-y-0.5">
                                            @foreach($rental->details->take(2) as $d)
                                                <div>• {{ $d->itemUnit->item->name ?? 'Barang' }} <span class="text-slate-400 font-mono">({{ $d->itemUnit->serial_number ?? '-' }})</span></div>
                                            @endforeach
                                            @if($rental->details->count() > 2)
                                                <span class="text-coral font-bold text-[11px]">+{{ $rental->details->count() - 2 }} unit lainnya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        @if($rental->status === 'BOOKED')
                                            <flux:badge size="sm" color="blue">BOOKED</flux:badge>
                                        @elseif($rental->status === 'DP_PAID')
                                            <flux:badge size="sm" color="sky">DP TERBAYAR</flux:badge>
                                        @elseif($rental->status === 'PAID')
                                            <flux:badge size="sm" color="emerald">LUNAS ONLINE</flux:badge>
                                        @elseif($rental->status === 'RENTED_OUT')
                                            <flux:badge size="sm" color="emerald">DISEWA</flux:badge>
                                        @elseif($rental->status === 'OVERDUE')
                                            <flux:badge size="sm" color="red" class="animate-pulse">OVERDUE</flux:badge>
                                        @elseif($rental->status === 'PENDING_SETTLEMENT')
                                            <flux:badge size="sm" color="purple">DENDA/SENGKETA</flux:badge>
                                        @elseif($rental->status === 'COMPLETED')
                                            <flux:badge size="sm" color="zinc">SELESAI</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc">{{ $rental->status }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        <x-kebab-menu>
                                            @if(in_array($rental->status, ['BOOKED', 'DP_PAID', 'PAID']))
                                                <a href="{{ route('admin.operations.checkout', $rental->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                                    Check-Out QC
                                                </a>
                                            @elseif(in_array($rental->status, ['RENTED_OUT', 'OVERDUE']))
                                                <a href="{{ route('admin.operations.checkin', $rental->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                                                    Check-In QC
                                                </a>
                                                <a href="{{ route('admin.settlements.show', $rental->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Penyelesaian Denda
                                                </a>
                                            @elseif($rental->status === 'PENDING_SETTLEMENT')
                                                <a href="{{ route('admin.settlements.show', $rental->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Penyelesaian Denda
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.transactions.invoice', $rental->id) }}" class="kebab-item">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Lihat Invoice
                                            </a>
                                        </x-kebab-menu>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-slate-400 text-xs">
                                        Tidak ada transaksi operasional yang sesuai kriteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex justify-between items-center bg-white">
                        {{ $rentals->links('vendor.pagination.summitgear') }}
                    </div>
                </flux:card>
            </div>
        </div>
    </main>
</div>
