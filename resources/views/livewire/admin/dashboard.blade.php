<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Dashboard Utama" />

        <div class="content-area">
            <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
                
                <!-- 1. Quick Actions Hero Banner -->
                <section>
                    <flux:card class="dashboard-hero relative overflow-hidden rounded-2xl p-6 sm:p-7">
                        <!-- Ambient Subtle Glow -->
                        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
                        <div class="absolute right-1/4 -bottom-20 w-64 h-64 rounded-full bg-orange-500/10 blur-3xl pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
                            <!-- Greeting & Status -->
                            <div class="max-w-xl">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-2.5 border border-white/10 bg-white/10 text-blue-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</span>
                                    <span class="text-white/30">•</span>
                                    <span class="text-emerald-300 font-semibold">Sistem Siap Beroperasi</span>
                                </div>
                                <h3 class="text-2xl font-black text-white mb-1 tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h3>
                                <p class="text-xs text-blue-100/90 leading-relaxed">Pantau transaksi kasir, kalender booking, dan alur pemeliharaan gudang hari ini secara real-time.</p>
                            </div>

                            <!-- Quick Action Buttons Hub -->
                            <div class="flex flex-wrap items-center gap-3 pt-1 xl:pt-0">
                                @if(auth()->user()->role === 'kasir')
                                    <flux:button href="{{ route('admin.transactions.create') }}" variant="primary" icon="plus" class="font-bold text-xs">
                                        Kasir Transaksi Baru
                                    </flux:button>
                                @elseif(in_array(auth()->user()->role, ['admin', 'owner']))
                                    <flux:button href="{{ route('admin.operations.incoming_booking') }}" variant="primary" icon="inbox-arrow-down" class="font-bold text-xs">
                                        Booking Masuk
                                    </flux:button>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                                <a href="{{ route('gudang.dashboard') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    <span>Terminal Gudang</span>
                                </a>
                                @endif

                                <a href="{{ route('admin.operations.handover') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                                    <span>Serah-Terima (QC)</span>
                                </a>

                                <a href="{{ route('admin.operations.calendar') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    <span>Kalender Booking</span>
                                </a>
                            </div>
                        </div>
                    </flux:card>
                </section>

                <!-- 2. Dynamic Metric KPI Cards -->
                <section>
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        <!-- 1. Checkouts Today -->
                        <a href="{{ route('admin.operations.handover') }}" class="clean-stat-card group">
                            <div class="clean-stat-top">
                                <span class="clean-stat-label">Check-Out Hari Ini</span>
                                <div class="clean-stat-icon bg-blue-50 text-blue-600 border border-blue-100 group-hover:scale-105 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span class="clean-stat-value">{{ $todayCheckouts }}</span>
                                <span class="clean-stat-badge clean-badge-blue">Siap Serah Terima</span>
                            </div>
                        </a>

                        <!-- 2. Overdue / Late Check-in -->
                        <a href="{{ route('admin.operations.handover') }}" class="clean-stat-card group">
                            <div class="clean-stat-top">
                                <span class="clean-stat-label">Check-In Terlambat</span>
                                <div class="clean-stat-icon {{ $overdueCheckins > 0 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }} group-hover:scale-105 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span class="clean-stat-value" style="color: {{ $overdueCheckins > 0 ? '#DC2626' : '#101F42' }};">{{ $overdueCheckins }}</span>
                                <span class="clean-stat-badge {{ $overdueCheckins > 0 ? 'clean-badge-red' : 'clean-badge-green' }}">
                                    {{ $overdueCheckins > 0 ? 'Butuh Perhatian' : 'Tepat Waktu' }}
                                </span>
                            </div>
                        </a>

                        <!-- 3. Units in Maintenance / Cleaning -->
                        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                        <a href="{{ route('admin.maintenance.kanban') }}" class="clean-stat-card group">
                        @else
                        <div class="clean-stat-card group">
                        @endif
                            <div class="clean-stat-top">
                                <span class="clean-stat-label">Unit Servis & Cuci</span>
                                <div class="clean-stat-icon bg-purple-50 text-purple-600 border border-purple-100 group-hover:scale-105 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span class="clean-stat-value">{{ $maintenanceCount }}</span>
                                <span class="clean-stat-badge clean-badge-purple">Papan Kanban</span>
                            </div>
                        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                        </a>
                        @else
                        </div>
                        @endif

                        <!-- 4. Monthly Revenue -->
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.analytics.dashboard') }}" class="clean-stat-card group">
                        @else
                        <div class="clean-stat-card group">
                        @endif
                            <div class="clean-stat-top">
                                <span class="clean-stat-label">Omset Bulan Ini</span>
                                <div class="clean-stat-icon bg-emerald-50 text-emerald-600 border border-emerald-100 group-hover:scale-105 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span class="clean-stat-value text-xl sm:text-2xl">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
                                <span class="clean-stat-badge clean-badge-green">Bulan Berjalan</span>
                            </div>
                        @if(auth()->user()->role === 'admin')
                        </a>
                        @else
                        </div>
                        @endif
                    </div>
                </section>
                
                <!-- 3. Asset Distribution & Recent Transactions -->
                <section>
                    <div class="grid gap-6 lg:grid-cols-3">
                        <!-- Asset Distribution by Category -->
                        <flux:card class="lg:col-span-1 space-y-4">
                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                <div>
                                    <flux:heading size="sm" class="uppercase tracking-wider">Sebaran Aset Inventaris</flux:heading>
                                    <flux:subheading size="xs">Proporsi unit fisik di gudang</flux:subheading>
                                </div>
                                <flux:badge size="sm" color="zinc">{{ $totalUnits }} Unit</flux:badge>
                            </div>

                            <div class="space-y-4">
                                @forelse($categoryDistribution as $cat)
                                    @php
                                        $pct = $totalUnits > 0 ? round(($cat->unit_count / $totalUnits) * 100, 1) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1.5">
                                            <span class="text-navy flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-coral"></span>
                                                {{ $cat->category ?? 'Lainnya' }}
                                            </span>
                                            <span class="text-gray-500 font-mono">{{ $cat->unit_count }} unit ({{ $pct }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full bg-coral transition-all duration-500" style="width: {{ $pct }}%;"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-xs text-gray-400 py-8">
                                        Belum ada unit inventaris terdaftar.
                                    </div>
                                @endforelse
                            </div>

                            <div class="pt-3 border-t border-gray-100 text-center">
                                <flux:button href="{{ route('admin.inventory.items') }}" variant="ghost" size="sm" class="w-full text-xs font-bold text-navy hover:text-coral" icon-trailing="chevron-right">
                                    Kelola Master Barang & Unit
                                </flux:button>
                            </div>
                        </flux:card>

                        <!-- Recent Transactions Table with Kebab Action Menu -->
                        <flux:card class="lg:col-span-2 overflow-hidden p-0">
                            <div class="p-5 flex justify-between items-center border-b border-gray-100">
                                <div>
                                    <flux:heading size="sm" class="uppercase tracking-wider">Transaksi & Reservasi Terbaru</flux:heading>
                                    <flux:subheading size="xs">5 aktivitas penyewaan terkini</flux:subheading>
                                </div>
                                <flux:button href="{{ route('admin.operations.handover') }}" variant="ghost" size="sm" class="text-xs font-bold text-coral" icon-trailing="chevron-right">
                                    Lihat Semua Transaksi
                                </flux:button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            <th class="py-3 px-5">Kode TRX & Pelanggan</th>
                                            <th class="py-3 px-5">Periode Sewa</th>
                                            <th class="py-3 px-5">Status</th>
                                            <th class="py-3 px-5 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($recentRentals as $r)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3.5 px-5">
                                                <div class="font-bold text-navy text-xs sm:text-sm">{{ $r->rental_code }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $r->customer->name ?? '-' }}</div>
                                            </td>
                                            <td class="py-3.5 px-5">
                                                <div class="text-xs font-semibold text-gray-700">
                                                    {{ \Carbon\Carbon::parse($r->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($r->end_date)->format('d M Y') }}
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-5">
                                                @if($r->status === 'BOOKED')
                                                    <flux:badge size="sm" color="blue">BOOKED</flux:badge>
                                                @elseif($r->status === 'RENTED_OUT')
                                                    <flux:badge size="sm" color="amber">RENTED</flux:badge>
                                                @elseif($r->status === 'OVERDUE')
                                                    <flux:badge size="sm" color="red">OVERDUE</flux:badge>
                                                @elseif($r->status === 'COMPLETED')
                                                    <flux:badge size="sm" color="zinc">SELESAI</flux:badge>
                                                @else
                                                    <flux:badge size="sm" color="zinc">{{ $r->status }}</flux:badge>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-5 text-right">
                                                <x-kebab-menu>
                                                    @if($r->status === 'BOOKED')
                                                        <a href="{{ route('admin.operations.checkout', $r->id) }}" class="kebab-item">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                            Check-Out QC
                                                        </a>
                                                    @elseif(in_array($r->status, ['RENTED_OUT', 'OVERDUE']))
                                                        <a href="{{ route('admin.operations.checkin', $r->id) }}" class="kebab-item">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14"/></svg>
                                                            Check-In QC
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('admin.transactions.invoice', $r->id) }}" class="kebab-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        Lihat Invoice
                                                    </a>
                                                </x-kebab-menu>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="p-8 text-center text-gray-400 text-xs">
                                                Belum ada transaksi rental tercatat.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </flux:card>
                    </div>
                </section>

            </div>
        </div>
    </main>
</div>
