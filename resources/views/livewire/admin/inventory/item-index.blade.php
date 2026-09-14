<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Inventaris Alat & Katalog" />

        <div class="content-area">
            <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-navy">Master Katalog Barang</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola katalog alat outdoor, skema harga harian, dan unit fisik serial number.</p>
                    </div>
                    @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                    <flux:button href="{{ route('admin.inventory.items.create') }}" variant="primary" icon="plus">
                        Tambah Barang Baru
                    </flux:button>
                    @endif
                </div>

                @if (session()->has('message'))
                    <div class="p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Search & Filter Bar -->
                <flux:card class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama barang atau SKU..." icon="magnifying-glass" />
                        </div>
                        <div>
                            <flux:select wire:model.live="categoryFilter">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:select wire:model.live="typeFilter">
                                <option value="">Semua Tipe</option>
                                <option value="0">Satuan (Individual)</option>
                                <option value="1">Paket Bundling</option>
                            </flux:select>
                        </div>
                    </div>
                </flux:card>

                <!-- Inventory Card Grid -->
                @if($items->count() > 0)
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($items as $item)
                    <flux:card class="p-0 overflow-hidden flex flex-col justify-between hover:-translate-y-1 transition-all duration-200 shadow-xs hover:shadow-md">
                        <!-- Card Top: Icon + Category badge -->
                        <div class="p-4 flex justify-between items-start border-b border-gray-100 bg-slate-50/50">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-navy to-navy-light flex items-center justify-center shrink-0 text-white shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <flux:badge size="sm" color="zinc">{{ $item->category }}</flux:badge>
                                @if($item->is_package)
                                    <flux:badge size="sm" color="purple">Paket</flux:badge>
                                @else
                                    <flux:badge size="sm" color="blue">Satuan</flux:badge>
                                @endif
                            </div>
                        </div>
                        <!-- Card Body: Name + SKU + Price + Units -->
                        <div class="p-4 flex-1">
                            <div class="font-bold text-navy text-sm leading-snug line-clamp-2">{{ $item->name }}</div>
                            <div class="text-[11px] font-mono text-slate-400 mt-1 uppercase tracking-wider">SKU: {{ $item->sku }}</div>
                            <div class="mt-3 text-lg font-black text-emerald-600">
                                Rp {{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-xs font-semibold text-slate-400 ml-1">/hari</span>
                            </div>
                        </div>
                        <!-- Card Footer: Units + Actions -->
                        <div class="p-3.5 px-4 border-t border-gray-100 bg-slate-50/40 flex justify-between items-center">
                            @if($item->is_package)
                                <span class="text-xs text-slate-400 italic">Komponen Paket</span>
                            @else
                                <a href="{{ route('admin.inventory.units', $item->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-coral hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    {{ $item->units_count }} Unit Fisik
                                </a>
                            @endif
                            <!-- Kebab Menu -->
                            <x-kebab-menu>
                                @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                                <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="kebab-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Edit Barang
                                </a>
                                @endif
                                @if(!$item->is_package)
                                    <a href="{{ route('admin.inventory.units', $item->id) }}" class="kebab-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        Kelola Unit Fisik
                                    </a>
                                @endif
                                @if(in_array(auth()->user()->role, ['admin']))
                                <button type="button" wire:click="confirmDelete({{ $item->id }})" class="kebab-item danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Barang
                                </button>
                                @endif
                            </x-kebab-menu>
                        </div>
                    </flux:card>
                    @endforeach
                </div>
                @else
                <flux:card class="p-10 text-center">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl mx-auto mb-3 flex items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-500">Tidak ada master barang yang sesuai kriteria pencarian.</p>
                </flux:card>
                @endif

                <!-- Pagination -->
                <div>
                    {{ $items->links('vendor.pagination.summitgear') }}
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Konfirmasi Hapus Master Barang (Flux Centered Modal) -->
    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <flux:heading size="lg">Konfirmasi Hapus Master Barang</flux:heading>
                    <flux:subheading size="sm">Periksa kembali detail barang berikut sebelum menghapusnya dari database.</flux:subheading>
                </div>
            </div>

            <!-- Detail Barang yang akan dihapus -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500">Nama Master Barang:</span>
                    <span class="font-bold text-slate-900 text-sm text-right">{{ $itemToDeleteName }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500">Kode SKU:</span>
                    <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-slate-200 text-slate-800">{{ $itemToDeleteSku }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500">Kategori:</span>
                    <span class="font-semibold text-slate-700 capitalize">{{ $itemToDeleteCategory }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-500">Jumlah Unit Fisik:</span>
                    <span class="font-bold {{ $itemToDeleteUnitsCount > 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ $itemToDeleteUnitsCount }} Unit Terdaftar</span>
                </div>
            </div>

            @if($itemToDeleteHasBlockers)
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong class="font-bold block">Tidak Dapat Dihapus Langsung</strong>
                    <span>{{ $itemToDeleteBlockerReason }}</span>
                </div>
            </div>
            @else
            <div class="p-3 rounded-xl bg-rose-50/60 border border-rose-200/60 text-xs text-rose-700 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Tindakan ini permanen. Seluruh riwayat dan master barang ini akan dihapus dari sistem.</span>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:button type="button" wire:click="cancelDelete" variant="ghost">
                    Batal
                </flux:button>
                @if(!$itemToDeleteHasBlockers)
                <flux:button type="button" wire:click="executeDelete" variant="danger" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="executeDelete">Ya, Hapus Barang Ini</span>
                    <span wire:loading wire:target="executeDelete">Menghapus...</span>
                </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
