<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Dashboard Analitik & Laporan Bisnis" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">

            <!-- Header + Action -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-navy">Analitik & Laporan Bisnis</h1>
                    <p class="text-sm text-gray-500 mt-1">Analisis pendapatan, utilisasi aset, rasio sengketa, dan ekspor data berkala.</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button type="button" wire:click="exportCsv" variant="filled" icon="arrow-down-tray">
                        Export CSV
                    </flux:button>
                </div>
            </div>

            <!-- Filter Card -->
            <flux:card>
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <!-- Period Select Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">Periode:</span>
                            <select wire:model.live="timeRange" class="text-xs font-semibold px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                                <option value="1_DAY">1 Hari (Hari Ini)</option>
                                <option value="7_DAYS">7 Hari Terakhir</option>
                                <option value="30_DAYS">30 Hari Terakhir</option>
                                <option value="THIS_MONTH">Bulan Ini</option>
                                <option value="THIS_YEAR">Tahun Ini</option>
                                <option value="CUSTOM">Rentang Kustom</option>
                            </select>
                        </div>

                        <!-- Date Range: Dari & Sampai -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500">Dari:</span>
                            <input type="date" wire:model="dateFrom" class="text-xs font-semibold px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500">Sampai:</span>
                            <input type="date" wire:model="dateTo" class="text-xs font-semibold px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                        </div>

                        <flux:button type="button" wire:click="applyCustomRange" size="sm" variant="primary">
                            Terapkan Filter
                        </flux:button>
                    </div>

                    <!-- Active Period Indicator (Right-aligned) -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Filter Aktif: <strong class="text-navy">{{ $activeDateRange[0]->format('d M Y') }}</strong> s/d <strong class="text-navy">{{ $activeDateRange[1]->format('d M Y') }}</strong></span>
                    </div>
                </div>
                @error('dateFrom') <div class="text-xs text-red-600 font-semibold mt-2">{{ $message }}</div> @enderror
                @error('dateTo') <div class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</div> @enderror
            </flux:card>

            @if (session()->has('filter_applied'))
                <div class="p-3 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('filter_applied') }}</span>
                </div>
            @endif

            <!-- 4 KPI CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Transaksi -->
                <flux:card class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Transaksi</span>
                        <flux:badge color="sky" size="sm">Total Rental</flux:badge>
                    </div>
                    <div class="text-2xl font-bold tracking-tight text-navy">
                        {{ number_format($summary['totalRentals']) }}
                    </div>
                </flux:card>

                <!-- Card 2: Total Pendapatan -->
                <flux:card class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Pendapatan</span>
                        <flux:badge color="emerald" size="sm">Kas Masuk</flux:badge>
                    </div>
                    <div class="text-2xl font-bold tracking-tight text-navy">
                        Rp {{ number_format($summary['totalRevenue'], 0, ',', '.') }}
                    </div>
                </flux:card>

                <!-- Card 3: Penerimaan Denda -->
                <flux:card class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Penerimaan Denda</span>
                        <flux:badge color="coral" size="sm">Denda & Klaim</flux:badge>
                    </div>
                    <div class="text-2xl font-bold tracking-tight text-navy">
                        Rp {{ number_format($summary['penaltyRevenue'], 0, ',', '.') }}
                    </div>
                </flux:card>

                <!-- Card 4: Rasio Sengketa -->
                <flux:card class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Rasio Sengketa</span>
                        <flux:badge color="{{ $summary['disputeRate'] > 5 ? 'red' : 'emerald' }}" size="sm">
                            {{ $summary['disputeRate'] > 5 ? 'Melebihi Target' : 'Target < 5%' }}
                        </flux:badge>
                    </div>
                    <div class="text-2xl font-bold tracking-tight {{ $summary['disputeRate'] > 5 ? 'text-red-600' : 'text-navy' }}">
                        {{ $summary['disputeRate'] }}%
                    </div>
                </flux:card>
            </div>

            <!-- 2-COLUMN SECTION: Utilization + Top 5 Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Asset Utilization Table -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-white">
                        <div>
                            <h3 class="font-bold text-navy text-sm">Utilisasi Aset (% Keterpakaian)</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Efisiensi penyewaan per model alat</p>
                        </div>
                        <flux:badge color="sky" size="sm">RASIO</flux:badge>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200/80 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-gray-500">
                                    <th class="py-3 px-4">Nama Alat</th>
                                    <th class="py-3 px-4">Unit</th>
                                    <th class="py-3 px-4">Frekuensi</th>
                                    <th class="py-3 px-4">Utilisasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse($utilization as $u)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-navy text-xs">{{ $u['name'] }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-navy text-xs font-mono">{{ $u['units_count'] }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-gray-600 text-xs font-mono">{{ $u['rented_times'] }}×</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-coral h-full rounded-full" style="width: {{ $u['utilization_rate'] }}%;"></div>
                                            </div>
                                            <span class="font-bold text-xs text-navy min-w-[32px] text-right">{{ $u['utilization_rate'] }}%</span>
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
                </flux:card>

                <!-- Top 5 Items -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-white">
                        <div>
                            <h3 class="font-bold text-navy text-sm">Top 5 Barang Terlaris</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Berdasarkan akumulasi transaksi sewa</p>
                        </div>
                        <flux:badge color="coral" size="sm">TERLARIS</flux:badge>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($topItems as $idx => $item)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50/80 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs {{ $idx === 0 ? 'bg-amber-100 text-amber-800' : ($idx === 1 ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-800') }}">#{{ $idx + 1 }}</span>
                                <div>
                                    <div class="font-bold text-navy text-sm">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->category }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-navy text-xs">{{ $item->total_rents }}× Disewa</div>
                                <div class="text-xs font-bold text-emerald-600 mt-0.5">Rp {{ number_format($item->total_earned, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-gray-400 text-xs">Belum ada riwayat transaksi.</div>
                        @endforelse
                    </div>
                </flux:card>

            </div>

        </div>
    </main>
</div>
