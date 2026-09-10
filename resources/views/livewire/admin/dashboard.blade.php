<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Dashboard Utama" />

        <div class="content-area">
            
            <!-- Quick Actions Banner -->
            <div class="sg-card mb-6 rounded-2xl shadow-sm overflow-hidden relative" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #101F42 100%); border: 1px solid rgba(255, 255, 255, 0.08); padding: 1.75rem 2rem; color: #FFFFFF;">
                <!-- Ambient Subtle Glow -->
                <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
                <div class="absolute right-1/4 -bottom-20 w-64 h-64 rounded-full bg-orange-500/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
                    <!-- Greeting & Status -->
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-2.5 border border-white/10" style="background: rgba(255, 255, 255, 0.08); color: #93C5FD;">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</span>
                            <span class="text-white/30">•</span>
                            <span class="text-emerald-300 font-semibold">Sistem Siap Beroperasi</span>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-1 tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h3>
                        <p class="text-xs text-blue-200/90 leading-relaxed">Pantau transaksi kasir, kalender booking, dan alur pemeliharaan gudang hari ini secara real-time.</p>
                    </div>

                    <!-- Quick Action Buttons Hub -->
                    <div class="flex flex-wrap items-center gap-3 pt-1 xl:pt-0">
                        @if(auth()->user()->role === 'kasir')
                            <a href="{{ route('admin.transactions.create') }}" class="inline-flex items-center gap-2.5 px-4.5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md transition-all duration-200 transform active:scale-95 hover:brightness-110 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #FF4500 0%, #EA580C 100%); box-shadow: 0 4px 14px -2px rgba(255, 69, 0, 0.35); padding: 0.65rem 1.15rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Kasir Transaksi Baru</span>
                            </a>
                        @elseif(in_array(auth()->user()->role, ['admin', 'owner']))
                            <a href="{{ route('admin.operations.incoming_booking') }}" class="inline-flex items-center gap-2.5 px-4.5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md transition-all duration-200 transform active:scale-95 hover:brightness-110 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #FF4500 0%, #EA580C 100%); box-shadow: 0 4px 14px -2px rgba(255, 69, 0, 0.35); padding: 0.65rem 1.15rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span>Booking Masuk</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.operations.handover') }}" class="inline-flex items-center gap-2.5 px-4.5 py-2.5 rounded-xl font-bold text-xs text-white border border-white/20 transition-all duration-200 transform active:scale-95 hover:bg-white/20 hover:border-white/30 hover:-translate-y-0.5 shadow-sm" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); padding: 0.65rem 1.15rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Serah-Terima (QC)</span>
                        </a>

                        <a href="{{ route('admin.operations.calendar') }}" class="inline-flex items-center gap-2.5 px-4.5 py-2.5 rounded-xl font-bold text-xs text-white border border-white/20 transition-all duration-200 transform active:scale-95 hover:bg-white/20 hover:border-white/30 hover:-translate-y-0.5 shadow-sm" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); padding: 0.65rem 1.15rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Kalender Booking</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dynamic Metric KPI Cards with Symmetrical Iconography -->
            <div class="stat-grid-4">
                <!-- 1. Checkouts Today -->
                <a href="{{ route('admin.operations.handover') }}" class="clean-stat-card group">
                    <div class="clean-stat-top">
                        <span class="clean-stat-label">Check-Out Hari Ini</span>
                        <div class="clean-stat-icon bg-blue-50 text-blue-600 border border-blue-100 group-hover:scale-105 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <a href="{{ route('admin.maintenance.kanban') }}" class="clean-stat-card group">
                    <div class="clean-stat-top">
                        <span class="clean-stat-label">Unit Servis & Cuci</span>
                        <div class="clean-stat-icon bg-purple-50 text-purple-600 border border-purple-100 group-hover:scale-105 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="clean-stat-value">{{ $maintenanceCount }}</span>
                        <span class="clean-stat-badge clean-badge-purple">Papan Kanban</span>
                    </div>
                </a>

                <!-- 4. Monthly Revenue -->
                <a href="{{ route('admin.analytics.dashboard') }}" class="clean-stat-card group">
                    <div class="clean-stat-top">
                        <span class="clean-stat-label">Omset Bulan Ini</span>
                        <div class="clean-stat-icon bg-emerald-50 text-emerald-600 border border-emerald-100 group-hover:scale-105 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="clean-stat-value" style="font-size: 1.75rem;">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
                        <span class="clean-stat-badge clean-badge-green">Bulan Berjalan</span>
                    </div>
                </a>
            </div>
            
            <!-- Real Distribution & Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Asset Distribution by Category -->
                <div class="sg-card lg:col-span-1">
                    <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                        <div>
                            <h4 class="text-sm font-bold text-navy uppercase tracking-wider">Sebaran Aset Inventaris</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Proporsi unit fisik di gudang</p>
                        </div>
                        <span class="badge badge-neutral font-bold">{{ $totalUnits }} Total Unit</span>
                    </div>

                    <div class="space-y-4">
                        @forelse($categoryDistribution as $cat)
                            @php
                                $pct = $totalUnits > 0 ? round(($cat->unit_count / $totalUnits) * 100, 1) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1.5">
                                    <span class="text-navy flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" style="background-color: var(--color-coral);"></span>
                                        {{ $cat->category ?? 'Lainnya' }}
                                    </span>
                                    <span class="text-gray-500 font-mono">{{ $cat->unit_count }} unit ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all duration-500" style="background-color: var(--color-coral); width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-xs text-gray-400 py-8">
                                Belum ada unit inventaris terdaftar.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                        <a href="{{ route('admin.inventory.items') }}" class="text-xs font-bold text-navy hover:text-coral transition inline-flex items-center gap-1">
                            Kelola Master Barang & Unit
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Recent Transactions Table with Kebab Action Menu -->
                <div class="sg-card lg:col-span-2 p-0 overflow-visible">
                    <div class="p-5 flex justify-between items-center border-b border-gray-100">
                        <div>
                            <h4 class="text-sm font-bold text-navy uppercase tracking-wider">Transaksi & Reservasi Terbaru</h4>
                            <p class="text-xs text-gray-400 mt-0.5">5 aktivitas penyewaan terkini</p>
                        </div>
                        <a href="{{ route('admin.operations.handover') }}" class="text-xs font-bold text-coral hover:underline inline-flex items-center gap-1">
                            Lihat Semua Transaksi
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="sg-table">
                            <thead>
                                <tr>
                                    <th>Kode TRX & Pelanggan</th>
                                    <th>Periode Sewa</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRentals as $r)
                                <tr>
                                    <td>
                                        <div class="font-bold text-navy">{{ $r->rental_code }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $r->customer->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-xs font-semibold text-gray-700">
                                            {{ \Carbon\Carbon::parse($r->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($r->end_date)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($r->status === 'BOOKED')
                                            <span class="badge badge-info">BOOKED</span>
                                        @elseif($r->status === 'RENTED_OUT')
                                            <span class="badge badge-warning">RENTED</span>
                                        @elseif($r->status === 'OVERDUE')
                                            <span class="badge badge-danger animate-pulse">OVERDUE</span>
                                        @elseif($r->status === 'COMPLETED')
                                            <span class="badge badge-neutral">SELESAI</span>
                                        @else
                                            <span class="badge badge-neutral">{{ $r->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <!-- 3-Dots Kebab Action Menu -->
                                        <x-kebab-menu>
                                            @if($r->status === 'BOOKED')
                                                <a href="{{ route('admin.operations.checkout', $r->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                    Check-Out QC
                                                </a>
                                            @elseif(in_array($r->status, ['RENTED_OUT', 'OVERDUE']))
                                                <a href="{{ route('admin.operations.checkin', $r->id) }}" class="kebab-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14"/></svg>
                                                    Check-In QC
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.transactions.invoice', $r->id) }}" class="kebab-item">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
                </div>
            </div>

        </div>
    </main>
</div>
