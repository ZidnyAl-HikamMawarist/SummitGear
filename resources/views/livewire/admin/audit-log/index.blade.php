<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Log Audit Sistem" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Log Audit Keamanan</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Rekam jejak setiap aksi sensitif (void transaksi, override denda, ubah harga, hapus data).</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Export CSV Button -->
                    <button type="button" wire:click="exportCsv" class="btn text-xs font-bold text-white flex items-center gap-2" style="background-color: #059669; border-radius: 10px; padding: 0.65rem 1.25rem; box-shadow: 0 4px 12px rgba(5,150,105,0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Export Log CSV</span>
                    </button>
                </div>
            </div>

            <!-- Tab Navigation for Roles -->
            <div class="mb-5 border-b border-gray-200">
                <nav class="-mb-px flex gap-6 overflow-x-auto">
                    <button wire:click="$set('activeTab', 'semua')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors {{ $activeTab === 'semua' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Semua Aktivitas
                    </button>
                    <button wire:click="$set('activeTab', 'kasir')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors {{ $activeTab === 'kasir' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Kasir
                    </button>
                    <button wire:click="$set('activeTab', 'gudang')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors {{ $activeTab === 'gudang' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Staf Gudang
                    </button>
                    <button wire:click="$set('activeTab', 'admin')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors {{ $activeTab === 'admin' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Admin
                    </button>
                    <button wire:click="$set('activeTab', 'system')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors {{ $activeTab === 'system' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Sistem Otomatis
                    </button>
                </nav>
            </div>

            <!-- Filter Card (Search + Period Range + Action Filter) -->
            <div class="sg-card mb-5 p-4">
                <div class="filter-toolbar">
                    <!-- Search Input -->
                    <div class="filter-item-search">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari aksi, entitas, alasan..." class="form-control pl-9 text-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>

                    <!-- Action Filter -->
                    <div class="filter-item-select">
                        <select wire:model.live="actionFilter" class="form-control text-xs font-semibold">
                            <option value="ALL">Semua Aksi</option>
                            <option value="CREATE">CREATE</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                            <option value="VOID">VOID</option>
                            <option value="OVERRIDE">OVERRIDE</option>
                            <option value="LOGIN">LOGIN</option>
                        </select>
                    </div>

                    <!-- Date Range: Dari -->
                    <div class="filter-item-date">
                        <span>Dari:</span>
                        <input type="date" wire:model.live="dateFrom">
                    </div>

                    <!-- Date Range: Sampai -->
                    <div class="filter-item-date">
                        <span>Sampai:</span>
                        <input type="date" wire:model.live="dateTo">
                    </div>

                    <!-- Reset Filter Button -->
                    <div>
                        <button type="button" wire:click="resetFilters" class="btn text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg" style="padding: 0.5rem 0.85rem;">
                            Reset Filter
                        </button>
                    </div>
                </div>

                @if($dateFrom || $dateTo || $actionFilter !== 'ALL' || $search)
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                    <span class="font-bold text-navy">Filter Aktif:</span>
                    @if($search) <span class="badge badge-neutral">Cari: "{{ $search }}"</span> @endif
                    @if($actionFilter !== 'ALL') <span class="badge badge-neutral">Aksi: {{ $actionFilter }}</span> @endif
                    @if($dateFrom || $dateTo)
                        <span class="badge badge-info">Periode: {{ $dateFrom ?: 'Awal' }} s/d {{ $dateTo ?: 'Sekarang' }}</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Table Card -->
            <div class="sg-table-container">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th>Waktu Aktivitas</th>
                            <th>Aktor / Pengguna</th>
                            <th>Aksi & Target Entitas</th>
                            <th>Detail / Alasan Otorisasi</th>
                            <th>Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="text-xs font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}
                                </div>
                                <div class="text-[11px] font-mono text-gray-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }} WIB
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white text-[10px] shrink-0" style="background-color: var(--color-navy);">
                                        {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SYS' }}
                                    </div>
                                    <span class="font-bold text-navy text-xs">
                                        {{ $log->user ? $log->user->name : 'SISTEM OTOMATIS' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = 'badge-neutral';
                                    if(in_array($log->action, ['CREATE', 'LOGIN'])) $badgeClass = 'badge-success';
                                    elseif(in_array($log->action, ['UPDATE', 'OVERRIDE'])) $badgeClass = 'badge-warning';
                                    elseif(in_array($log->action, ['DELETE', 'VOID'])) $badgeClass = 'badge-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $log->action }}</span>
                                <span class="ml-1.5 text-xs font-mono text-gray-500">{{ $log->entity }} #{{ $log->entity_id }}</span>
                            </td>
                            <td class="text-xs text-gray-600 max-w-xs truncate" title="{{ $log->reason }}">
                                {{ $log->reason ?? '-' }}
                            </td>
                            <td>
                                @if($log->approver)
                                    <span class="badge badge-success text-[11px]">
                                        ✓ {{ $log->approver->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">
                                Belum ada catatan aktivitas di log audit yang sesuai filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                {{ $logs->links('vendor.pagination.summitgear') }}
            </div>
        </div>
    </main>
</div>
