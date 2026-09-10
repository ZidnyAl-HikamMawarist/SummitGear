<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Papan Kerja Maintenance & Gudang" />

        <div class="content-area">
            <!-- Header -->
            <div class="mb-5">
                <h2 class="text-2xl font-bold text-navy">Papan Kerja Maintenance & Gudang</h2>
                <p class="text-sm text-gray-500 mt-0.5">Kelola alur pembersihan, perbaikan alat rusak, dan kesiapan unit sebelum disewakan.</p>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <!-- Segmented Status Tabs Navigation -->
            <div class="warehouse-tabs-nav">
                <!-- Tab 1: Pembersihan -->
                <button type="button" wire:click="setTab('cleaning')" class="warehouse-tab-btn tab-cleaning {{ $activeTab === 'cleaning' ? 'active' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600 shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="tab-label text-xs tracking-wider uppercase">Pembersihan</span>
                            <span class="text-[11px] text-gray-400 font-medium">Antrian cuci unit</span>
                        </div>
                    </div>
                    <span class="warehouse-tab-badge">{{ $cleaningCount }}</span>
                </button>

                <!-- Tab 2: Servis / Rusak -->
                <button type="button" wire:click="setTab('maintenance')" class="warehouse-tab-btn tab-repair {{ $activeTab === 'maintenance' ? 'active' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="tab-label text-xs tracking-wider uppercase">Servis / Rusak</span>
                            <span class="text-[11px] text-gray-400 font-medium">Perbaikan teknisi</span>
                        </div>
                    </div>
                    <span class="warehouse-tab-badge">{{ $maintenanceCount }}</span>
                </button>

                <!-- Tab 3: Sedang Disewa -->
                <button type="button" wire:click="setTab('rented')" class="warehouse-tab-btn tab-rented {{ $activeTab === 'rented' ? 'active' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="tab-label text-xs tracking-wider uppercase">Sedang Disewa</span>
                            <span class="text-[11px] text-gray-400 font-medium">Unit di pelanggan</span>
                        </div>
                    </div>
                    <span class="warehouse-tab-badge">{{ $rentedCount }}</span>
                </button>

                <!-- Tab 4: Siap Sewa -->
                <button type="button" wire:click="setTab('available')" class="warehouse-tab-btn tab-available {{ $activeTab === 'available' ? 'active' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="tab-label text-xs tracking-wider uppercase">Siap Sewa</span>
                            <span class="text-[11px] text-gray-400 font-medium">Tersedia di rak</span>
                        </div>
                    </div>
                    <span class="warehouse-tab-badge">{{ $availableCount }}</span>
                </button>
            </div>

            <!-- ========================================== -->
            <!-- ACTIVE TAB CONTENT (RESPONSIVE CARD GRID)  -->
            <!-- ========================================== -->

            @if($activeTab === 'cleaning')
                <!-- TAB: PEMBERSIHAN -->
                <div class="warehouse-card-grid">
                    @forelse($cleaningUnits as $unit)
                        <div class="warehouse-unit-card" style="border-top: 3.5px solid #7C3AED;">
                            <div class="warehouse-unit-card-top">
                                @if($unit->item && $unit->item->photo_url)
                                    <img src="{{ asset('storage/' . $unit->item->photo_url) }}" alt="{{ $unit->item->name }}" class="warehouse-unit-thumb">
                                @else
                                    <div class="warehouse-unit-thumb-fallback">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider bg-purple-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                        {{ $unit->item->category ?? 'Peralatan' }}
                                    </span>
                                    <h4 class="font-extrabold text-sm text-navy truncate leading-snug" title="{{ $unit->item->name }}">
                                        {{ $unit->item->name }}
                                    </h4>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="warehouse-unit-sn-badge">SN: {{ $unit->serial_number }}</span>
                                    </div>
                                    @if($unit->condition_notes)
                                        <div class="mt-2 text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium">
                                            {{ $unit->condition_notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="warehouse-unit-actions">
                                <button type="button" wire:click="moveStatus({{ $unit->id }}, 'Available')" class="warehouse-btn-primary warehouse-btn-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Selesai Cuci (Ready)</span>
                                </button>
                                <button type="button" wire:click="moveStatus({{ $unit->id }}, 'Maintenance')" class="warehouse-btn-primary warehouse-btn-danger">
                                    <span>Butuh Servis / Perbaikan →</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full warehouse-empty-state">
                            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h4 class="text-base font-extrabold text-navy">Semua Unit Bersih!</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm">Tidak ada antrian alat yang sedang perlu dibersihkan atau dicuci saat ini.</p>
                        </div>
                    @endforelse
                </div>

                @if($cleaningUnits->hasPages())
                    <div class="mt-6 p-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between text-xs text-slate-600 font-medium shadow-2xs">
                        <span class="text-xs font-bold text-slate-700">
                            Halaman {{ $cleaningUnits->currentPage() }} dari {{ $cleaningUnits->lastPage() }} (Total {{ $cleaningUnits->total() }} unit)
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="previousPage('cleaningPage')" @disabled($cleaningUnits->onFirstPage()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                ‹ Sebelumnya
                            </button>
                            <button type="button" wire:click="nextPage('cleaningPage')" @disabled(!$cleaningUnits->hasMorePages()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                Berikutnya ›
                            </button>
                        </div>
                    </div>
                @endif

            @elseif($activeTab === 'maintenance')
                <!-- TAB: SERVIS / RUSAK -->
                <div class="warehouse-card-grid">
                    @forelse($maintenanceUnits as $unit)
                        <div class="warehouse-unit-card" style="border-top: 3.5px solid #DC2626;">
                            <div class="warehouse-unit-card-top">
                                @if($unit->item && $unit->item->photo_url)
                                    <img src="{{ asset('storage/' . $unit->item->photo_url) }}" alt="{{ $unit->item->name }}" class="warehouse-unit-thumb">
                                @else
                                    <div class="warehouse-unit-thumb-fallback">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider bg-red-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                        {{ $unit->item->category ?? 'Peralatan' }}
                                    </span>
                                    <h4 class="font-extrabold text-sm text-navy truncate leading-snug" title="{{ $unit->item->name }}">
                                        {{ $unit->item->name }}
                                    </h4>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="warehouse-unit-sn-badge">SN: {{ $unit->serial_number }}</span>
                                    </div>
                                    @if($unit->condition_notes)
                                        <div class="mt-2 text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg p-2 font-medium">
                                            ⚠️ {{ $unit->condition_notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="warehouse-unit-actions">
                                <button type="button" wire:click="logServiceAndMove({{ $unit->id }})" class="warehouse-btn-primary warehouse-btn-navy">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Catat Servis Selesai</span>
                                </button>
                                <button type="button" wire:click="moveStatus({{ $unit->id }}, 'Cleaning')" class="warehouse-btn-primary warehouse-btn-secondary">
                                    <span>‹ Pindah ke Antrian Cuci</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full warehouse-empty-state">
                            <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-extrabold text-navy">Semua Alat Siap & Prima!</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm">Tidak ada peralatan yang dilaporkan rusak atau membutuhkan perbaikan teknisi.</p>
                        </div>
                    @endforelse
                </div>

                @if($maintenanceUnits->hasPages())
                    <div class="mt-6 p-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between text-xs text-slate-600 font-medium shadow-2xs">
                        <span class="text-xs font-bold text-slate-700">
                            Halaman {{ $maintenanceUnits->currentPage() }} dari {{ $maintenanceUnits->lastPage() }} (Total {{ $maintenanceUnits->total() }} unit)
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="previousPage('maintenancePage')" @disabled($maintenanceUnits->onFirstPage()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                ‹ Sebelumnya
                            </button>
                            <button type="button" wire:click="nextPage('maintenancePage')" @disabled(!$maintenanceUnits->hasMorePages()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                Berikutnya ›
                            </button>
                        </div>
                    </div>
                @endif

            @elseif($activeTab === 'rented')
                <!-- TAB: SEDANG DISEWA -->
                <div class="warehouse-card-grid">
                    @forelse($rentedUnits as $unit)
                        <div class="warehouse-unit-card" style="border-top: 3.5px solid #D97706;">
                            <div class="warehouse-unit-card-top">
                                @if($unit->item && $unit->item->photo_url)
                                    <img src="{{ asset('storage/' . $unit->item->photo_url) }}" alt="{{ $unit->item->name }}" class="warehouse-unit-thumb">
                                @else
                                    <div class="warehouse-unit-thumb-fallback">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider bg-amber-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                        {{ $unit->item->category ?? 'Peralatan' }}
                                    </span>
                                    <h4 class="font-extrabold text-sm text-navy truncate leading-snug" title="{{ $unit->item->name }}">
                                        {{ $unit->item->name }}
                                    </h4>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="warehouse-unit-sn-badge">SN: {{ $unit->serial_number }}</span>
                                    </div>

                                    @if($unit->activeRental && $unit->activeRental->rental)
                                        <div class="mt-2 text-xs text-amber-900 bg-amber-50 border border-amber-200 rounded-lg p-2 font-medium">
                                            <span class="font-bold block">Penyewa: {{ $unit->activeRental->rental->customer->name ?? 'Pelanggan' }}</span>
                                            <span>Jadwal Kembali: {{ \Carbon\Carbon::parse($unit->activeRental->rental->end_date ?? $unit->activeRental->rental->scheduled_return_time)->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="warehouse-unit-actions">
                                <div class="py-1.5 px-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Unit Sedang Aktif Disewa</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full warehouse-empty-state">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-extrabold text-navy">Tidak Ada Unit Disewa</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm">Saat ini belum ada peralatan yang sedang berada di tangan penyewa.</p>
                        </div>
                    @endforelse
                </div>

                @if($rentedUnits->hasPages())
                    <div class="mt-6 p-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between text-xs text-slate-600 font-medium shadow-2xs">
                        <span class="text-xs font-bold text-slate-700">
                            Halaman {{ $rentedUnits->currentPage() }} dari {{ $rentedUnits->lastPage() }} (Total {{ $rentedUnits->total() }} unit)
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="previousPage('rentedPage')" @disabled($rentedUnits->onFirstPage()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                ‹ Sebelumnya
                            </button>
                            <button type="button" wire:click="nextPage('rentedPage')" @disabled(!$rentedUnits->hasMorePages()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                Berikutnya ›
                            </button>
                        </div>
                    </div>
                @endif

            @elseif($activeTab === 'available')
                <!-- TAB: SIAP SEWA -->
                <div class="warehouse-card-grid">
                    @forelse($availableUnits as $unit)
                        <div class="warehouse-unit-card" style="border-top: 3.5px solid #059669;">
                            <div class="warehouse-unit-card-top">
                                @if($unit->item && $unit->item->photo_url)
                                    <img src="{{ asset('storage/' . $unit->item->photo_url) }}" alt="{{ $unit->item->name }}" class="warehouse-unit-thumb">
                                @else
                                    <div class="warehouse-unit-thumb-fallback">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                        {{ $unit->item->category ?? 'Peralatan' }}
                                    </span>
                                    <h4 class="font-extrabold text-sm text-navy truncate leading-snug" title="{{ $unit->item->name }}">
                                        {{ $unit->item->name }}
                                    </h4>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="warehouse-unit-sn-badge">SN: {{ $unit->serial_number }}</span>
                                    </div>
                                    <div class="mt-2 text-[11.5px] text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg p-2 font-bold flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>✓ Siap di rak toko</span>
                                    </div>
                                </div>
                            </div>

                            <div class="warehouse-unit-actions">
                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" wire:click="moveStatus({{ $unit->id }}, 'Cleaning')" class="warehouse-btn-primary warehouse-btn-secondary !text-[11px] !py-1.5 flex-1">
                                        Cuci Ulang
                                    </button>
                                    <button type="button" wire:click="moveStatus({{ $unit->id }}, 'Maintenance')" class="warehouse-btn-primary warehouse-btn-danger !text-[11px] !py-1.5 flex-1">
                                        Perlu Servis
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full warehouse-empty-state">
                            <div class="w-14 h-14 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <h4 class="text-base font-extrabold text-navy">Belum Ada Unit Siap Sewa</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm">Periksa tab pembersihan atau servis untuk menyelesaikan persiapan peralatan.</p>
                        </div>
                    @endforelse
                </div>

                @if($availableUnits->hasPages())
                    <div class="mt-6 p-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between text-xs text-slate-600 font-medium shadow-2xs">
                        <span class="text-xs font-bold text-slate-700">
                            Halaman {{ $availableUnits->currentPage() }} dari {{ $availableUnits->lastPage() }} (Total {{ $availableUnits->total() }} unit)
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="previousPage('availablePage')" @disabled($availableUnits->onFirstPage()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                ‹ Sebelumnya
                            </button>
                            <button type="button" wire:click="nextPage('availablePage')" @disabled(!$availableUnits->hasMorePages()) class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 active:bg-gray-100 disabled:opacity-30 disabled:pointer-events-none text-slate-700 font-bold text-xs flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                Berikutnya ›
                            </button>
                        </div>
                    </div>
                @endif

            @endif

            <!-- Service Log Modal with Flux -->
            <flux:modal wire:model="showServiceModal" class="max-w-md">
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">Catat Log Servis</flux:heading>
                        <flux:subheading class="text-xs">
                            Unit: <strong class="text-navy">{{ $selectedUnit?->item?->name }} ({{ $selectedUnit?->serial_number }})</strong>
                        </flux:subheading>
                    </div>

                    <flux:textarea 
                        label="Catatan Kondisi / Hasil Servis" 
                        wire:model="serviceNote" 
                        rows="3" 
                        placeholder="Contoh: Ganti tali, perbaiki ritsleting, bersihkan sambungan..." 
                    />

                    <div class="flex justify-end gap-2.5 pt-2 border-t border-gray-100 dark:border-zinc-700">
                        <flux:button type="button" wire:click="cancelModal" variant="ghost">
                            Batal
                        </flux:button>
                        <flux:button type="button" wire:click="confirmService" variant="primary" color="emerald">
                            ✓ Selesai & Pindah ke Cuci
                        </flux:button>
                    </div>
                </div>
            </flux:modal>

        </div>
    </main>
</div>
