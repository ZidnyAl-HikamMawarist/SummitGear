<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Manajemen Pelanggan" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-navy">Manajemen Data Pelanggan</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola identitas penyewa, verifikasi dokumen, dan ekspor data pelanggan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button type="button" wire:click="exportCsv" variant="filled" icon="arrow-down-tray">
                        Export CSV
                    </flux:button>
                    <flux:button href="{{ route('admin.customers.create') }}" variant="primary" icon="plus">
                        Tambah Pelanggan Baru
                    </flux:button>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="p-4 text-xs font-bold text-green-800 bg-green-50 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <!-- Search & Date Filter Bar -->
            <flux:card class="space-y-3">
                <div class="flex flex-col md:flex-row gap-3 items-center">
                    <!-- Search Input -->
                    <div class="flex-1 w-full">
                        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari nama, NIK, atau nomor WhatsApp..." />
                    </div>

                    <!-- Date Range: Dari -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-xs font-semibold text-gray-500 shrink-0">Tgl Daftar:</span>
                        <input type="date" wire:model.live="dateFrom" class="text-xs font-semibold px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                    </div>

                    <!-- Date Range: Sampai -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-xs font-semibold text-gray-500 shrink-0">s/d:</span>
                        <input type="date" wire:model.live="dateTo" class="text-xs font-semibold px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                    </div>

                    <!-- Reset Filter -->
                    <flux:button type="button" wire:click="resetFilters" size="sm" variant="subtle">
                        Reset
                    </flux:button>
                </div>

                @if($dateFrom || $dateTo || $search)
                <div class="pt-2.5 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                    <span class="font-bold text-navy">Filter Aktif:</span>
                    @if($search) <flux:badge color="zinc" size="sm">Cari: "{{ $search }}"</flux:badge> @endif
                    @if($dateFrom || $dateTo)
                        <flux:badge color="sky" size="sm">Daftar: {{ $dateFrom ?: 'Awal' }} s/d {{ $dateTo ?: 'Sekarang' }}</flux:badge>
                    @endif
                </div>
                @endif
            </flux:card>

            <!-- Customer Table -->
            <flux:card class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200/80 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-gray-500">
                                <th class="py-3.5 px-4">Pelanggan</th>
                                <th class="py-3.5 px-4">Nomor Identitas (NIK)</th>
                                <th class="py-3.5 px-4">Kontak</th>
                                <th class="py-3.5 px-4">Status T&C</th>
                                <th class="py-3.5 px-4">Bergabung</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($customers as $customer)
                            @php
                                $colors = ['#101F42','#059669','#7C3AED','#D97706','#DC2626','#0284C7'];
                                $colorIdx = ord(strtolower($customer->name[0] ?? 'a')) % count($colors);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-xs" style="background-color: {{ $colors[$colorIdx] }};">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-navy">{{ $customer->name }}</div>
                                            <div class="text-xs text-gray-400">ID #{{ $customer->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-xs font-semibold bg-gray-100 text-gray-800 px-2 py-0.5 rounded border border-gray-200">
                                        {{ $customer->nik ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $customer->phone ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($customer->consent_at)
                                        <flux:badge color="emerald" size="sm">Setuju</flux:badge>
                                    @else
                                        <flux:badge color="amber" size="sm">Belum Setuju</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($customer->created_at)->format('d M Y') }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <x-kebab-menu>
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Edit & Riwayat Sewa
                                        </a>
                                        @if(auth()->user()->role === 'kasir')
                                        <a href="{{ route('admin.transactions.create') }}?customer_id={{ $customer->id }}" class="kebab-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-coral shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Buat Transaksi Kasir
                                        </a>
                                        @endif
                                    </x-kebab-menu>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-gray-400 text-xs">
                                    Tidak ada data pelanggan yang cocok.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($customers->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $customers->links('vendor.pagination.summitgear') }}
                    </div>
                @endif
            </flux:card>
        </div>
    </main>
</div>
