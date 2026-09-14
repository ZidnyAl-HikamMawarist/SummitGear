<div class="admin-layout" wire:poll.10s>
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Dashboard Pergudangan & Manajemen Unit" />

        <div class="content-area">
            <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">

            <!-- Flash Alert -->
            @if(session()->has('message'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold rounded-xl flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('message') }}</span>
                    </div>
                </div>
            @endif

            <!-- 1. Hero Hub Gudang -->
            <flux:card class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 text-white shadow-xl border-0">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] font-extrabold uppercase tracking-wider mb-2.5 text-orange-300">
                            <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                            Terminal Khusus Staf Gudang & QC
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Monitoring Unit & Pergudangan
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                            Pantau pergerakan unit fisik, kesiapan alat sewa di rak, antrean cuci/perawatan, dan serah-terima QC hari ini.
                        </p>
                    </div>

                    <!-- Quick Navigation Actions -->
                    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                        <flux:button href="{{ route('admin.maintenance.kanban') }}" variant="primary" icon="wrench" size="sm">
                            Kanban Cuci & Servis
                        </flux:button>

                        <a href="{{ route('admin.operations.handover') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                            <span>Serah Terima (QC)</span>
                        </a>

                        <a href="{{ route('admin.inventory.items') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <span>Daftar Unit & SN</span>
                        </a>
                    </div>
                </div>
            </flux:card>

            <!-- 2. KPI Cards Unit Fisik Gudang (5 Kolom Status) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                
                <!-- 1. Siap Sewa (Available) -->
                <flux:card class="p-4 sm:p-5 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Siap Sewa (Rak)</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 tabular-nums">
                        {{ $availableUnits }}
                    </div>
                    <div class="text-[11px] font-semibold text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $availabilityRate }}% siap disewa</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                </flux:card>

                <!-- 2. Sedang Disewa (Rented) -->
                <flux:card class="p-4 sm:p-5 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Sedang Disewa</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-navy tabular-nums">
                        {{ $rentedUnits }}
                    </div>
                    <div class="text-[11px] font-semibold text-gray-400 mt-1">
                        Di tangan pelanggan
                    </div>
                </flux:card>

                <!-- 3. Antrean Cuci (Cleaning) -->
                <a href="{{ route('admin.maintenance.kanban') }}" class="group block">
                    <flux:card class="p-4 sm:p-5 shadow-xs hover:shadow-md transition-all border-amber-200 hover:border-amber-400">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">Antrean Cuci</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-amber-600 tabular-nums">
                            {{ $cleaningUnits }}
                        </div>
                        <div class="text-[11px] font-semibold text-amber-700 mt-1 flex items-center justify-between">
                            <span>Perlu cuci & jemur</span>
                            <span class="text-[10px] underline group-hover:text-amber-900">Buka →</span>
                        </div>
                    </flux:card>
                </a>

                <!-- 4. Butuh Servis (Maintenance) -->
                <a href="{{ route('admin.maintenance.kanban') }}" class="group block">
                    <flux:card class="p-4 sm:p-5 shadow-xs hover:shadow-md transition-all border-rose-200 hover:border-rose-400">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Perbaikan/Servis</span>
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-rose-600 tabular-nums">
                            {{ $maintenanceUnits }}
                        </div>
                        <div class="text-[11px] font-semibold text-rose-700 mt-1 flex items-center justify-between">
                            <span>Sedang diperbaiki</span>
                            <span class="text-[10px] underline group-hover:text-rose-900">Buka →</span>
                        </div>
                    </flux:card>
                </a>

                <!-- 5. Total Unit Terdaftar -->
                <flux:card class="p-4 sm:p-5 shadow-xs col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Total Unit Terdaftar</span>
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-800 tabular-nums">
                        {{ $totalUnits }}
                    </div>
                    <div class="text-[11px] font-semibold text-gray-400 mt-1">
                        {{ $brokenOrMissingUnits > 0 ? $brokenOrMissingUnits . ' afkir/hilang' : 'Semua unit aktif' }}
                    </div>
                </flux:card>

            </div>

            <!-- 3. Operasional Serah Terima Hari Ini (2 Kolom: Ambil vs Kembali) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Kolom Kiri: Barang Harus Disiapkan / Diambil Hari Ini (Check-out) -->
                <flux:card class="p-5 sm:p-6 flex flex-col">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-orange-50 text-coral flex items-center justify-center shrink-0 border border-orange-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Siap Disiapkan / Ambil Hari Ini</h3>
                                <p class="text-xs text-gray-400">Jadwal serah-terima alat ke penyewa</p>
                            </div>
                        </div>
                        <flux:badge color="amber" size="sm" class="tabular-nums">
                            {{ count($todayPickups) }} Jadwal
                        </flux:badge>
                    </div>

                    @if(count($todayPickups) === 0)
                        <div class="py-12 flex flex-col items-center justify-center text-center flex-1">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 mb-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Tidak Ada Jadwal Pengambilan Hari Ini</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Semua pesanan booking hari ini telah diserah-terimakan.</p>
                        </div>
                    @else
                        <div class="space-y-3 flex-1 overflow-y-auto max-h-[380px] pr-1">
                            @foreach($todayPickups as $rental)
                                <div class="p-3.5 bg-slate-50 hover:bg-slate-100/70 border border-gray-200/80 rounded-2xl transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-navy">{{ $rental->rental_code }}</span>
                                            <flux:badge color="sky" size="sm">
                                                {{ \Carbon\Carbon::parse($rental->start_date)->format('H:i') }} WIB
                                            </flux:badge>
                                            @if($rental->source === 'online')
                                                <flux:badge color="emerald" size="sm">
                                                    Online
                                                </flux:badge>
                                            @endif
                                        </div>
                                        <div class="text-xs font-bold text-slate-700 mt-1">
                                            {{ $rental->customer->name ?? 'Pelanggan' }} • <span class="text-gray-400 font-normal">{{ $rental->customer->phone ?? '-' }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach($rental->details as $detail)
                                                <span class="text-[10px] font-semibold bg-white border border-gray-200 px-1.5 py-0.5 rounded text-gray-600">
                                                    {{ $detail->itemUnit->item->name ?? 'Alat' }} ({{ $detail->itemUnit->serial_number ?? 'Unit' }})
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <flux:button href="{{ route('admin.operations.checkout', $rental->id) }}" variant="primary" size="sm" icon="arrow-right">
                                        Proses QC
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:card>

                <!-- Kolom Kanan: Pengembalian Hari Ini & Jatuh Tempo (Check-in QC) -->
                <flux:card class="p-5 sm:p-6 flex flex-col">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Pengembalian & QC Masuk</h3>
                                <p class="text-xs text-gray-400">Penyewa yang jadwal kembali hari ini / terlambat</p>
                            </div>
                        </div>
                        <flux:badge color="sky" size="sm" class="tabular-nums">
                            {{ count($todayReturns) }} Unit Kembali
                        </flux:badge>
                    </div>

                    @if(count($todayReturns) === 0)
                        <div class="py-12 flex flex-col items-center justify-center text-center flex-1">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 mb-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Tidak Ada Pengembalian Menunggu</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Semua unit yang dijadwalkan kembali telah selesai di-check-in.</p>
                        </div>
                    @else
                        <div class="space-y-3 flex-1 overflow-y-auto max-h-[380px] pr-1">
                            @foreach($todayReturns as $rental)
                                @php
                                    $isOverdue = $rental->status === 'OVERDUE' || ($rental->scheduled_return_time && \Carbon\Carbon::now()->greaterThan($rental->scheduled_return_time));
                                @endphp
                                <div class="p-3.5 {{ $isOverdue ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-gray-200/80' }} hover:bg-slate-100/70 border rounded-2xl transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-navy">{{ $rental->rental_code }}</span>
                                            @if($isOverdue)
                                                <flux:badge color="red" size="sm" class="animate-pulse">
                                                    TERLAMBAT
                                                </flux:badge>
                                            @else
                                                <flux:badge color="amber" size="sm">
                                                    Maks. 18:00 WIB
                                                </flux:badge>
                                            @endif
                                        </div>
                                        <div class="text-xs font-bold text-slate-700 mt-1">
                                            {{ $rental->customer->name ?? 'Pelanggan' }} • <span class="text-gray-400 font-normal">{{ $rental->customer->phone ?? '-' }}</span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            {{ count($rental->details) }} alat disewa:
                                            <span class="font-semibold text-slate-700">
                                                {{ $rental->details->pluck('itemUnit.item.name')->filter()->take(3)->join(', ') }}
                                                {{ count($rental->details) > 3 ? '...' : '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <flux:button href="{{ route('admin.operations.checkin', $rental->id) }}" variant="{{ $isOverdue ? 'danger' : 'primary' }}" size="sm" icon="check">
                                        Check-in QC
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:card>

            </div>

            <!-- 4. Antrean Perawatan Aktif & Peringatan Stok Menipis -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Antrean Cuci & Servis Aktif (2 Kolom Lebar) -->
                <flux:card class="lg:col-span-2 p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-navy">Antrean Pemeliharaan Unit Fisik</h3>
                            <p class="text-xs text-gray-400">Unit yang sedang berada di antrean cuci atau servis perbaikan</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:input 
                                wire:model.live.debounce.300ms="searchUnit" 
                                placeholder="Cari Serial Number / Alat..." 
                                icon="magnifying-glass" 
                                size="sm"
                            />
                            <flux:button href="{{ route('admin.maintenance.kanban') }}" variant="ghost" size="sm">
                                Buka Kanban →
                            </flux:button>
                        </div>
                    </div>

                    @if(count($activeMaintenanceUnits) === 0)
                        <div class="py-10 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Tidak Ada Antrean Perawatan</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Semua unit bersih dan siap disewa di rak gudang.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[420px] overflow-y-auto pr-1">
                            @foreach($activeMaintenanceUnits as $unit)
                                <div class="p-3.5 bg-slate-50 hover:bg-slate-100/80 border border-gray-200/80 rounded-2xl transition-all flex flex-col justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-200 shrink-0 border border-slate-200 flex items-center justify-center">
                                            @if($unit->item && $unit->item->photo_url)
                                                <img src="{{ asset('storage/' . $unit->item->photo_url) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-mono font-black text-navy">{{ $unit->serial_number }}</span>
                                                <flux:badge :color="$unit->status === 'Cleaning' ? 'amber' : 'red'" size="sm">
                                                    {{ $unit->status === 'Cleaning' ? 'CUCI/JEMUR' : 'SERVIS' }}
                                                </flux:badge>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 truncate mt-0.5">
                                                {{ $unit->item->name ?? 'Item' }}
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-1 line-clamp-1 italic">
                                                "{{ $unit->condition_notes ?: 'Pembersihan rutin setelah sewa' }}"
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action Button: Langsung Pindahkan ke Siap Sewa -->
                                    <div class="pt-2 border-t border-gray-200/60 flex items-center justify-between gap-2">
                                        <span class="text-[10px] text-gray-400">
                                            Masuk: {{ \Carbon\Carbon::parse($unit->updated_at)->diffForHumans() }}
                                        </span>
                                        <flux:button type="button" 
                                                wire:click="quickMoveToAvailable({{ $unit->id }})" 
                                                variant="subtle" 
                                                size="sm" 
                                                icon="check" 
                                                class="text-emerald-700 bg-emerald-50 hover:bg-emerald-100">
                                            Selesai & Siap Sewa
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:card>

                <!-- Peringatan Stok Alat Menipis (1 Kolom) -->
                <flux:card class="p-5 sm:p-6 flex flex-col">
                    <div class="pb-4 mb-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-navy">Stok Rak Kritis</h3>
                            <p class="text-xs text-gray-400">Unit siap sewa tersisa &le; 1 unit</p>
                        </div>
                        <flux:badge color="red" size="sm">
                            {{ count($lowStockItems) }}
                        </flux:badge>
                    </div>

                    @if(count($lowStockItems) === 0)
                        <div class="py-12 flex flex-col items-center justify-center text-center flex-1">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Stok Gudang Aman</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Semua jenis alat memiliki cadangan unit siap sewa cukup.</p>
                        </div>
                    @else
                        <div class="space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                            @foreach($lowStockItems as $item)
                                <div class="p-3 bg-slate-50 border border-gray-200/80 rounded-2xl flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-slate-800 truncate">
                                            {{ $item->name }}
                                        </div>
                                        <div class="text-[10.5px] text-gray-400">
                                            Kategori: {{ $item->category }}
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <flux:badge :color="$item->available_units_count == 0 ? 'red' : 'amber'" size="sm">
                                            Sisa: {{ $item->available_units_count }} Unit
                                        </flux:badge>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            Total: {{ $item->total_units_count }} unit
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 mt-3 border-t border-gray-100">
                            <flux:button href="{{ route('admin.inventory.items') }}" variant="subtle" size="sm" class="w-full">
                                Cek Semua Inventaris & Unit Fisik
                            </flux:button>
                        </div>
                    @endif
                </flux:card>

            </div>

            <!-- 5. Distribusi Kategori Unit Fisik -->
            <flux:card class="p-5 sm:p-6">
                <div class="pb-4 mb-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-navy">Distribusi Perlengkapan per Kategori</h3>
                        <p class="text-xs text-gray-400">Komposisi ketersediaan unit fisik di rak gudang</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3.5">
                    @foreach($categoryBreakdown as $cat)
                        @php
                            $rate = $cat->total_units > 0 ? round(($cat->ready_units / $cat->total_units) * 100) : 0;
                        @endphp
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-gray-200/80 flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wide block truncate">
                                    {{ $cat->category }}
                                </span>
                                <div class="text-xl font-black text-navy mt-1 tabular-nums">
                                    {{ $cat->ready_units }} <span class="text-xs font-semibold text-gray-400">/ {{ $cat->total_units }}</span>
                                </div>
                            </div>
                            <div class="mt-2.5">
                                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full {{ $rate > 50 ? 'bg-emerald-500' : ($rate > 20 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $rate }}%"></div>
                                </div>
                                <span class="text-[9.5px] font-semibold text-gray-400 mt-1 block text-right">{{ $rate }}% siap</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            </div>
        </div>
    </main>
</div>
