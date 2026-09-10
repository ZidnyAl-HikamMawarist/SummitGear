<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Serah Terima & Checklist QC Operasional" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-5">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Serah Terima & Checklist QC</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Monitoring serah-terima unit keluar (check-out) dan inspeksi fisik pengembalian (check-in).</p>
                </div>
                @if(auth()->user()->role === 'kasir')
                <a href="{{ route('admin.transactions.create') }}" class="btn text-xs font-bold text-white shadow-md flex items-center gap-1.5" style="background-color: var(--color-coral); border-radius: 10px; padding: 0.65rem 1.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Transaksi Baru
                </a>
                @endif
            </div>

            <!-- Summary KPI 2-column -->
            <div class="stat-grid-2">
                <div class="clean-stat-card">
                    <div class="clean-stat-top">
                        <span class="clean-stat-label">Check-Out Hari Ini</span>
                        <div class="clean-stat-icon" style="background:#EFF6FF; color:#1D4ED8;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                    <span class="clean-stat-value">{{ $todayCheckouts }} <span class="text-xs font-semibold text-gray-400">Transaksi</span></span>
                    <div>
                        <span class="clean-stat-badge clean-badge-blue">Siap Serah Terima</span>
                    </div>
                </div>
                <div class="clean-stat-card">
                    <div class="clean-stat-top">
                        <span class="clean-stat-label">Pengembalian Hari Ini</span>
                        <div class="clean-stat-icon" style="background:#FEF3C7; color:#D97706;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                            </svg>
                        </div>
                    </div>
                    <span class="clean-stat-value">{{ $todayReturns }} <span class="text-xs font-semibold text-gray-400">Transaksi</span></span>
                    <div>
                        <span class="clean-stat-badge clean-badge-amber">Jadwal Masuk (QC)</span>
                    </div>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <!-- Search & Status Filter -->
            <div class="sg-card mb-5 p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                    <div class="relative flex-1">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode sewa, nama pelanggan, atau no WhatsApp..." class="form-control text-xs" style="padding-left: 2.4rem;">
                        <div style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94A3B8; pointer-events:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <!-- Status Pill Toggle -->
                    <div class="analytics-period-tabs shrink-0">
                        <button type="button" wire:click="$set('statusFilter', 'ALL')" class="period-tab {{ $statusFilter === 'ALL' ? 'active' : '' }}">Semua</button>
                        <button type="button" wire:click="$set('statusFilter', 'BOOKED')" class="period-tab {{ $statusFilter === 'BOOKED' ? 'active' : '' }}">Check-Out</button>
                        <button type="button" wire:click="$set('statusFilter', 'RENTED_OUT')" class="period-tab {{ $statusFilter === 'RENTED_OUT' ? 'active' : '' }}">Disewa</button>
                        <button type="button" wire:click="$set('statusFilter', 'OVERDUE')" class="period-tab {{ $statusFilter === 'OVERDUE' ? 'active' : '' }}" style="{{ $statusFilter === 'OVERDUE' ? '' : 'color:#DC2626;' }}">Terlambat</button>
                        <button type="button" wire:click="$set('statusFilter', 'PENDING_SETTLEMENT')" class="period-tab {{ $statusFilter === 'PENDING_SETTLEMENT' ? 'active' : '' }}" style="{{ $statusFilter === 'PENDING_SETTLEMENT' ? '' : 'color:#E11D48;' }}">Denda/Sengketa</button>
                        <button type="button" wire:click="$set('statusFilter', 'COMPLETED')" class="period-tab {{ $statusFilter === 'COMPLETED' ? 'active' : '' }}">Selesai</button>
                    </div>
                </div>
            </div>

            <!-- Handover Table -->
            <div class="sg-table-container">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th>Kode TRX & Pelanggan</th>
                            <th>Periode & Batas Waktu</th>
                            <th>Unit / Barang Disewa</th>
                            <th>Status Operasional</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentals as $rental)
                        <tr>
                            <td>
                                <a href="{{ route('admin.transactions.invoice', $rental->id) }}" class="font-bold text-navy hover:text-coral transition">
                                    {{ $rental->rental_code }}
                                </a>
                                <div class="text-xs font-semibold text-gray-700 mt-0.5">{{ $rental->customer->name }}</div>
                                <div class="text-xs text-gray-400">{{ $rental->customer->phone }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-gray-700">Mulai: <strong>{{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</strong></div>
                                <div class="text-xs text-gray-700">Kembali: <strong>{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</strong></div>
                                @if($rental->scheduled_return_time)
                                <div class="text-xs font-bold mt-0.5" style="color:#DC2626;">Batas: {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M H:i') }} WIB</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-neutral mb-1 font-mono font-bold">{{ $rental->details->count() }} Unit:</span>
                                <div class="text-xs text-gray-500" style="margin-top:0.25rem; display:flex; flex-direction:column; gap:0.15rem;">
                                    @foreach($rental->details->take(2) as $d)
                                        <div>• {{ $d->itemUnit->item->name ?? 'Barang' }} <span class="text-gray-400 font-mono">({{ $d->itemUnit->serial_number ?? '-' }})</span></div>
                                    @endforeach
                                    @if($rental->details->count() > 2)
                                        <span class="text-coral font-bold" style="font-size:0.65rem;">+{{ $rental->details->count() - 2 }} unit lainnya</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($rental->status === 'BOOKED')
                                    <span class="badge badge-info">BOOKED</span>
                                @elseif($rental->status === 'RENTED_OUT')
                                    <span class="badge badge-success">DISEWA</span>
                                @elseif($rental->status === 'OVERDUE')
                                    <span class="badge badge-danger" style="animation: pulse 1.5s ease-in-out infinite;">OVERDUE</span>
                                @elseif($rental->status === 'PENDING_SETTLEMENT')
                                    <span class="badge badge-danger" style="background:#FFE4E6; color:#BE123C; border:1px solid #FDA4AF;">DENDA/SENGKETA</span>
                                @elseif($rental->status === 'COMPLETED')
                                    <span class="badge badge-neutral">SELESAI</span>
                                @else
                                    <span class="badge badge-neutral">{{ $rental->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-kebab-menu>
                                    @if($rental->status === 'BOOKED')
                                        <a href="{{ route('admin.operations.checkout', $rental->id) }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            Check-Out QC
                                        </a>
                                    @elseif(in_array($rental->status, ['RENTED_OUT', 'OVERDUE']))
                                        <a href="{{ route('admin.operations.checkin', $rental->id) }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                                            Check-In QC
                                        </a>
                                        <a href="{{ route('admin.settlements.show', $rental->id) }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Penyelesaian Denda
                                        </a>
                                    @elseif($rental->status === 'PENDING_SETTLEMENT')
                                        <a href="{{ route('admin.settlements.show', $rental->id) }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Penyelesaian Denda
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.transactions.invoice', $rental->id) }}" class="kebab-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Lihat Invoice
                                    </a>
                                </x-kebab-menu>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400 text-xs">
                                Tidak ada transaksi operasional yang sesuai kriteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t border-slate-100 flex justify-between items-center bg-white">
                    {{ $rentals->links('vendor.pagination.summitgear') }}
                </div>
            </div>
        </div>
    </main>
</div>
