<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Inventaris Alat & Katalog" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Master Katalog Barang</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola katalog alat outdoor, skema harga harian, dan unit fisik serial number.</p>
                </div>
                @if(in_array(auth()->user()->role, ['admin']))
                <a href="{{ route('admin.inventory.items.create') }}" class="btn text-xs font-bold text-white shadow-md transition flex items-center gap-1.5" style="background-color: var(--color-coral); border-radius: 10px; padding: 0.65rem 1.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Barang Baru
                </a>
                @endif
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-5 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search & Filter Bar -->
            <div class="sg-card mb-6 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2 relative">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama barang atau SKU..." class="form-control text-xs" style="padding-left: 2.4rem;">
                        <div style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94A3B8; pointer-events:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <div>
                        <select wire:model.live="categoryFilter" class="form-control text-xs font-semibold">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="typeFilter" class="form-control text-xs font-semibold">
                            <option value="">Semua Tipe</option>
                            <option value="0">Satuan (Individual)</option>
                            <option value="1">Paket Bundling</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Inventory Card Grid (3-column catalog style) -->
            @if($items->count() > 0)
            <div class="inventory-card-grid">
                @foreach($items as $item)
                <div class="inventory-card">
                    <!-- Card Top: Icon + Category badge -->
                    <div class="inventory-card-top">
                        <div class="inventory-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="badge badge-neutral text-xs">{{ $item->category }}</span>
                            @if($item->is_package)
                                <span class="badge badge-purple text-xs">Paket Bundling</span>
                            @else
                                <span class="badge badge-info text-xs">Satuan</span>
                            @endif
                        </div>
                    </div>
                    <!-- Card Body: Name + SKU + Price + Units -->
                    <div class="inventory-card-body">
                        <div class="inventory-card-name">{{ $item->name }}</div>
                        <div class="inventory-card-sku">SKU: {{ $item->sku }}</div>
                        <div class="inventory-card-price">
                            Rp {{ number_format($item->price_per_day, 0, ',', '.') }}<span class="inventory-card-price-unit">/hari</span>
                        </div>
                    </div>
                    <!-- Card Footer: Units + Actions -->
                    <div class="inventory-card-footer">
                        @if($item->is_package)
                            <span class="text-xs text-gray-400 italic">Komponen Paket</span>
                        @else
                            <a href="{{ route('admin.inventory.units', $item->id) }}" class="inventory-unit-link">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                {{ $item->units_count }} Unit Fisik
                            </a>
                        @endif
                        <!-- Kebab Menu -->
                        <x-kebab-menu>
                            @if(in_array(auth()->user()->role, ['admin']))
                            <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="kebab-item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Edit Barang
                            </a>
                            @endif
                            @if(!$item->is_package)
                                <a href="{{ route('admin.inventory.units', $item->id) }}" class="kebab-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    Kelola Unit Fisik
                                </a>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin']))
                            <button type="button" wire:click="confirmDelete({{ $item->id }})" class="kebab-item danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Hapus Barang
                            </button>
                            @endif
                        </x-kebab-menu>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="sg-card p-10 text-center">
                <div style="width:56px;height:56px;background:#F1F5F9;border-radius:16px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Tidak ada master barang yang sesuai kriteria pencarian.</p>
            </div>
            @endif

            <!-- Pagination -->
            <div class="mt-6">
                {{ $items->links('vendor.pagination.summitgear') }}
            </div>
        </div>
    </main>

    <!-- Modal Konfirmasi Hapus Master Barang -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Konfirmasi Hapus Master Barang</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Periksa kembali detail barang berikut sebelum menghapusnya dari database.</p>
                    </div>
                </div>

                <!-- Detail Barang yang akan dihapus -->
                <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
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
                <div class="mt-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong class="font-bold block">Tidak Dapat Dihapus Langsung</strong>
                        <span>{{ $itemToDeleteBlockerReason }}</span>
                    </div>
                </div>
                @else
                <div class="mt-4 p-3 rounded-xl bg-rose-50/60 border border-rose-200/60 text-xs text-rose-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Tindakan ini permanen. Seluruh riwayat dan master barang ini akan dihapus dari sistem.</span>
                </div>
                @endif

                <!-- Actions -->
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" 
                            wire:click="cancelDelete" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    @if(!$itemToDeleteHasBlockers)
                    <button type="button" 
                            wire:click="executeDelete" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="executeDelete">Ya, Hapus Barang Ini</span>
                        <span wire:loading wire:target="executeDelete">Menghapus...</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
