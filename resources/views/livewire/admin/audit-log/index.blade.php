<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Log Audit Sistem" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-navy">Log Audit Keamanan</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Rekam jejak setiap aksi sensitif (void transaksi, override denda, ubah harga, hapus data).</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button type="button" wire:click="exportCsv" variant="primary" icon="arrow-down-tray" size="sm" class="bg-emerald-600 hover:bg-emerald-700">
                        Export Log CSV
                    </flux:button>
                </div>
            </div>

            <!-- Tab Navigation for Roles -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex gap-6 overflow-x-auto">
                    <button wire:click="$set('activeTab', 'semua')" class="cursor-pointer whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs transition-colors {{ $activeTab === 'semua' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Semua Aktivitas
                    </button>
                    <button wire:click="$set('activeTab', 'kasir')" class="cursor-pointer whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs transition-colors {{ $activeTab === 'kasir' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Kasir
                    </button>
                    <button wire:click="$set('activeTab', 'gudang')" class="cursor-pointer whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs transition-colors {{ $activeTab === 'gudang' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Staf Gudang
                    </button>
                    <button wire:click="$set('activeTab', 'admin')" class="cursor-pointer whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs transition-colors {{ $activeTab === 'admin' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Aktivitas Admin
                    </button>
                    <button wire:click="$set('activeTab', 'system')" class="cursor-pointer whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs transition-colors {{ $activeTab === 'system' ? 'border-coral text-coral' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Sistem Otomatis
                    </button>
                </nav>
            </div>

            <!-- Filter Card -->
            <flux:card class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <flux:input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            placeholder="Cari aksi, entitas, alasan..." 
                            icon="magnifying-glass" 
                        />
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <flux:select wire:model.live="actionFilter">
                            <option value="ALL">Semua Aksi</option>
                            <option value="CREATE">CREATE</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                            <option value="VOID">VOID</option>
                            <option value="OVERRIDE">OVERRIDE</option>
                            <option value="LOGIN">LOGIN</option>
                        </flux:select>
                    </div>

                    <!-- Date Range: Dari -->
                    <div>
                        <flux:input type="date" wire:model.live="dateFrom" placeholder="Dari" />
                    </div>

                    <!-- Date Range: Sampai & Reset -->
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <flux:input type="date" wire:model.live="dateTo" placeholder="Sampai" />
                        </div>
                        <flux:button type="button" wire:click="resetFilters" variant="subtle" size="sm" title="Reset Filter">
                            Reset
                        </flux:button>
                    </div>
                </div>

                @if($dateFrom || $dateTo || $actionFilter !== 'ALL' || $search)
                <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                    <span class="font-bold text-navy">Filter Aktif:</span>
                    @if($search) <flux:badge color="zinc" size="sm">Cari: "{{ $search }}"</flux:badge> @endif
                    @if($actionFilter !== 'ALL') <flux:badge color="zinc" size="sm">Aksi: {{ $actionFilter }}</flux:badge> @endif
                    @if($dateFrom || $dateTo)
                        <flux:badge color="sky" size="sm">Periode: {{ $dateFrom ?: 'Awal' }} s/d {{ $dateTo ?: 'Sekarang' }}</flux:badge>
                    @endif
                </div>
                @endif
            </flux:card>

            <!-- Table Card -->
            <flux:card class="p-0 overflow-hidden shadow-sm border border-gray-200/80">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-slate-50/80 text-gray-500 font-semibold uppercase tracking-wider">
                                <th class="py-3 px-4">Waktu Aktivitas</th>
                                <th class="py-3 px-4">Aktor / Pengguna</th>
                                <th class="py-3 px-4">Aksi & Target Entitas</th>
                                <th class="py-3 px-4">Detail / Alasan Otorisasi</th>
                                <th class="py-3 px-4">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] font-mono text-gray-400">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }} WIB
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white text-[10px] shrink-0 bg-navy">
                                            {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SYS' }}
                                        </div>
                                        <span class="font-bold text-navy">
                                            {{ $log->user ? $log->user->name : 'SISTEM OTOMATIS' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @php
                                        $badgeColor = 'zinc';
                                        if(in_array($log->action, ['CREATE', 'LOGIN'])) $badgeColor = 'emerald';
                                        elseif(in_array($log->action, ['UPDATE', 'OVERRIDE'])) $badgeColor = 'amber';
                                        elseif(in_array($log->action, ['DELETE', 'VOID'])) $badgeColor = 'red';
                                    @endphp
                                    <flux:badge :color="$badgeColor" size="sm">{{ $log->action }}</flux:badge>
                                    <span class="ml-1.5 font-mono text-gray-500">{{ $log->entity }} #{{ $log->entity_id }}</span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 max-w-xs truncate" title="{{ $log->reason }}">
                                    {{ $log->reason ?? '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($log->approver)
                                        <flux:badge color="emerald" size="sm">
                                            ✓ {{ $log->approver->name }}
                                        </flux:badge>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                    Belum ada catatan aktivitas di log audit yang sesuai filter.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
                @endif
            </flux:card>
        </div>
    </main>
</div>
