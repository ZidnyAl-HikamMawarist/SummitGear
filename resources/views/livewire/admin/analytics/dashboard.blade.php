<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Dashboard Analitik & Laporan Bisnis" />

        <div class="content-area">

            <!-- Header + Action -->
            <div class="analytics-header-row">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Analitik & Laporan Bisnis</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Analisis pendapatan, utilisasi aset, rasio sengketa, dan ekspor data berkala.</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Export Button with date filter indicator -->
                    <button type="button" wire:click="exportCsv" class="btn text-xs font-bold text-white flex items-center gap-2" style="background-color: #059669; border-radius: 10px; padding: 0.65rem 1.25rem; box-shadow: 0 4px 12px rgba(5,150,105,0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Filter Card (Unified Dropdown + Inline Date Range) -->
            <div class="sg-card mb-5 p-4">
                <div class="filter-toolbar">
                    <!-- Period Select Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">Periode:</span>
                        <select wire:model.live="timeRange" class="form-control text-xs font-semibold" style="width: auto; min-width: 150px;">
                            <option value="1_DAY">1 Hari (Hari Ini)</option>
                            <option value="7_DAYS">7 Hari Terakhir</option>
                            <option value="30_DAYS">30 Hari Terakhir</option>
                            <option value="THIS_MONTH">Bulan Ini</option>
                            <option value="THIS_YEAR">Tahun Ini</option>
                            <option value="CUSTOM">Rentang Kustom</option>
                        </select>
                    </div>

                    <!-- Date Range: Dari & Sampai -->
                    <div class="filter-item-date">
                        <span>Dari:</span>
                        <input type="date" wire:model="dateFrom">
                    </div>
                    <div class="filter-item-date">
                        <span>Sampai:</span>
                        <input type="date" wire:model="dateTo">
                    </div>

                    <button type="button" wire:click="applyCustomRange" class="btn text-xs font-bold text-white shadow-sm" style="background-color: var(--color-navy); border-radius: 8px; padding: 0.5rem 1rem;">
                        Terapkan Filter
                    </button>

                    <!-- Active Period Indicator (Right-aligned) -->
                    <div class="ml-auto flex items-center gap-2 text-xs font-semibold text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Filter Aktif: <strong class="text-navy">{{ $activeDateRange[0]->format('d M Y') }}</strong> s/d <strong class="text-navy">{{ $activeDateRange[1]->format('d M Y') }}</strong></span>
                    </div>
                </div>
                @error('dateFrom') <div class="text-xs text-red-600 font-semibold mt-2">{{ $message }}</div> @enderror
                @error('dateTo') <div class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</div> @enderror
            </div>

            @if (session()->has('filter_applied'))
                <div class="mb-5 p-3 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('filter_applied') }}</span>
                </div>
            @endif

            <!-- ============================================= -->
            <!-- 4 KPI CARDS — All visible above the fold     -->
            <!-- ============================================= -->
            <div class="analytics-kpi-grid">

                <!-- Card 1: Total Transaksi -->
                <div class="clean-stat-card">
                    <span class="clean-stat-label">Total Transaksi</span>
                    <span class="clean-stat-value">{{ number_format($summary['totalRentals']) }}</span>
                    <span class="clean-stat-badge clean-badge-blue">Total Rental</span>
                </div>

                <!-- Card 2: Total Pendapatan -->
                <div class="clean-stat-card">
                    <span class="clean-stat-label">Total Pendapatan</span>
                    <span class="clean-stat-value" style="font-size: 1.85rem;">Rp {{ number_format($summary['totalRevenue'], 0, ',', '.') }}</span>
                    <span class="clean-stat-badge clean-badge-green">Kas Masuk</span>
                </div>

                <!-- Card 3: Penerimaan Denda -->
                <div class="clean-stat-card">
                    <span class="clean-stat-label">Penerimaan Denda</span>
                    <span class="clean-stat-value" style="font-size: 1.85rem;">Rp {{ number_format($summary['penaltyRevenue'], 0, ',', '.') }}</span>
                    <span class="clean-stat-badge clean-badge-coral">Denda & Klaim</span>
                </div>

                <!-- Card 4: Rasio Sengketa -->
                <div class="clean-stat-card">
                    <span class="clean-stat-label">Rasio Sengketa (KPI)</span>
                    <span class="clean-stat-value" style="color: {{ $summary['disputeRate'] > 5 ? '#DC2626' : '#101F42' }};">{{ $summary['disputeRate'] }}%</span>
                    <span class="clean-stat-badge {{ $summary['disputeRate'] > 5 ? 'clean-badge-red' : 'clean-badge-green' }}">
                        {{ $summary['disputeRate'] > 5 ? 'Melebihi Target' : 'Target < 5%' }}
                    </span>
                </div>

            </div>

            <!-- ============================================= -->
            <!-- 2-COLUMN SECTION: Utilization + Top 5 Items   -->
            <!-- ============================================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- Asset Utilization Table -->
                <div class="sg-card p-0 overflow-hidden">
                    <div class="analytics-table-header">
                        <div>
                            <h3 class="font-bold text-navy text-sm">Utilisasi Aset (% Keterpakaian)</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Efisiensi penyewaan per model alat</p>
                        </div>
                        <span class="kpi-badge" style="background:#EFF6FF; color:#101F42;">RASIO</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="sg-table">
                            <thead>
                                <tr>
                                    <th>Nama Alat</th>
                                    <th>Unit</th>
                                    <th>Frekuensi</th>
                                    <th>Utilisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($utilization as $u)
                                <tr>
                                    <td>
                                        <div class="font-bold text-navy text-xs">{{ $u['name'] }}</div>
                                    </td>
                                    <td>
                                        <span class="font-bold text-navy text-xs font-mono">{{ $u['units_count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="font-bold text-gray-600 text-xs font-mono">{{ $u['rented_times'] }}×</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div style="flex:1; background:#F1F5F9; border-radius:99px; height:6px; overflow:hidden;">
                                                <div style="width: {{ $u['utilization_rate'] }}%; background: var(--color-coral); height:100%; border-radius:99px;"></div>
                                            </div>
                                            <span class="font-bold text-xs text-navy" style="min-width:32px; text-align:right;">{{ $u['utilization_rate'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-400 text-xs">Belum ada data barang.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top 5 Items -->
                <div class="sg-card p-0 overflow-hidden">
                    <div class="analytics-table-header">
                        <div>
                            <h3 class="font-bold text-navy text-sm">Top 5 Barang Terlaris</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Berdasarkan akumulasi transaksi sewa</p>
                        </div>
                        <span class="kpi-badge" style="background:#FFF1EE; color:#FF4500;">TERLARIS</span>
                    </div>
                    <div>
                        @forelse($topItems as $idx => $item)
                        <div class="top-item-row">
                            <div class="flex items-center gap-3">
                                <span class="top-item-rank {{ $idx === 0 ? 'rank-gold' : ($idx === 1 ? 'rank-silver' : 'rank-default') }}">#{{ $idx + 1 }}</span>
                                <div>
                                    <div class="font-bold text-navy text-sm">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->category }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-navy text-xs">{{ $item->total_rents }}× Disewa</div>
                                <div class="text-xs font-bold mt-0.5" style="color:#059669;">Rp {{ number_format($item->total_earned, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-gray-400 text-xs">Belum ada riwayat transaksi.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>
