<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Manajemen Pelanggan" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Manajemen Data Pelanggan</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola identitas penyewa, verifikasi foto KTP/SIM terenkripsi, dan ekspor data pelanggan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="exportCsv" class="btn text-xs font-bold text-white flex items-center gap-2" style="background-color: #059669; border-radius: 10px; padding: 0.65rem 1.25rem; box-shadow: 0 4px 12px rgba(5,150,105,0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Export CSV</span>
                    </button>
                    <a href="{{ route('admin.customers.create') }}" class="btn text-xs font-bold text-white shadow-md transition flex items-center gap-1.5" style="background-color: var(--color-coral); border-radius: 10px; padding: 0.65rem 1.25rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Pelanggan Baru
                    </a>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <!-- Search & Date Filter Bar -->
            <div class="sg-card mb-5 p-4">
                <div class="filter-toolbar">
                    <!-- Search Input -->
                    <div class="filter-item-search">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, NIK, atau nomor WhatsApp..." class="form-control text-xs" style="padding-left: 2.4rem;">
                        <div style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94A3B8; pointer-events:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>

                    <!-- Date Range: Dari -->
                    <div class="filter-item-date">
                        <span>Tgl Daftar:</span>
                        <input type="date" wire:model.live="dateFrom">
                    </div>

                    <!-- Date Range: Sampai -->
                    <div class="filter-item-date">
                        <span>s/d:</span>
                        <input type="date" wire:model.live="dateTo">
                    </div>

                    <!-- Reset Filter -->
                    <div>
                        <button type="button" wire:click="resetFilters" class="btn text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg" style="padding: 0.5rem 0.85rem;">
                            Reset
                        </button>
                    </div>
                </div>

                @if($dateFrom || $dateTo || $search)
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                    <span class="font-bold text-navy">Filter Aktif:</span>
                    @if($search) <span class="badge badge-neutral">Cari: "{{ $search }}"</span> @endif
                    @if($dateFrom || $dateTo)
                        <span class="badge badge-info">Daftar: {{ $dateFrom ?: 'Awal' }} s/d {{ $dateTo ?: 'Sekarang' }}</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Customer Table -->
            <div class="sg-table-container">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Nomor Identitas (NIK)</th>
                            <th>Kontak</th>
                            <th>Status T&C</th>
                            <th>Bergabung</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        @php
                            $colors = ['#101F42','#059669','#7C3AED','#D97706','#DC2626','#0284C7'];
                            $colorIdx = ord(strtolower($customer->name[0] ?? 'a')) % count($colors);
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white text-sm shrink-0 shadow-sm" style="background-color: {{ $colors[$colorIdx] }}; letter-spacing: -0.5px;">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-navy text-sm">{{ $customer->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">ID #{{ $customer->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-xs font-semibold bg-gray-100 text-gray-800 px-2.5 py-1 rounded-md border border-gray-200">
                                    {{ $customer->nik ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $customer->phone ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($customer->consent_at)
                                    <span class="badge badge-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Setuju
                                    </span>
                                @else
                                    <span class="badge badge-warning">Belum Setuju</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($customer->created_at)->format('d M Y') }}</span>
                            </td>
                            <td class="text-right">
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

                <div class="mt-4">{{ $customers->links('vendor.pagination.summitgear') }}</div>
            </div>
        </div>
    </main>
</div>
