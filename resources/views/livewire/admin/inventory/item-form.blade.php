<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar :title="$itemId ? 'Edit Master Barang' : 'Tambah Master Barang'" />

        <div class="content-area">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.inventory.items') }}" class="btn-form-cancel text-xs font-bold py-2 px-3.5" title="Kembali ke Inventaris">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate uppercase tracking-wider">Inventaris</span>
                            <span class="text-slate text-xs">/</span>
                            <span class="text-xs font-bold text-coral">{{ $itemId ? 'Edit Master' : 'Tambah Baru' }}</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            {{ $itemId ? 'Edit Barang: ' . $name : 'Tambah Master Barang Baru' }}
                        </h2>
                    </div>
                </div>

                @if($itemId)
                <div class="flex items-center gap-2">
                    <span class="badge badge-neutral">SKU: {{ $sku }}</span>
                    <span class="badge {{ $is_package ? 'badge-purple' : 'badge-info' }}">
                        {{ $is_package ? 'Paket Bundling' : 'Barang Satuan' }}
                    </span>
                </div>
                @endif
            </div>

            @if (session()->has('error'))
                <div class="mb-5 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form Card Wrapper -->
            <div class="form-section-card">
                
                <!-- Modern Tab Navigation -->
                <div class="p-4 bg-slate-50 border-b border-gray-100">
                    <div class="form-tabs-nav mb-0">
                        <button type="button" wire:click="$set('activeTab', 'basic')" class="form-tab-item {{ $activeTab === 'basic' ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>1. Informasi Dasar</span>
                        </button>
                        <button type="button" wire:click="$set('activeTab', 'pricing')" class="form-tab-item {{ $activeTab === 'pricing' ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>2. Aturan Harga Multiplier</span>
                        </button>
                        @if($is_package)
                        <button type="button" wire:click="$set('activeTab', 'package')" class="form-tab-item {{ $activeTab === 'package' ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>3. Komponen Paket ({{ count($packageItems) }})</span>
                        </button>
                        @endif
                    </div>
                </div>

                <form wire:submit.prevent="save" class="form-section-body">
                    
                    <!-- TAB 1: INFORMASI DASAR -->
                    <div class="{{ $activeTab === 'basic' ? 'block' : 'hidden' }}">
                        <!-- Tipe Barang (Interactive Radio Cards) -->
                        <div class="mb-6">
                            <label class="form-label-text mb-2.5">
                                Pilih Tipe Barang <span class="form-label-required">*</span>
                            </label>
                            <div class="radio-cards-grid">
                                <div 
                                    class="radio-select-card {{ $is_package == 0 ? 'active' : '' }}"
                                    wire:click="$set('is_package', 0)"
                                >
                                    <div class="radio-card-icon" style="background: #EFF6FF; color: #1D4ED8;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="radio-card-title">Barang Satuan</div>
                                        <div class="radio-card-desc">Barang fisik individual dengan serial number / barcode unit unik.</div>
                                    </div>
                                </div>

                                <div 
                                    class="radio-select-card {{ $is_package == 1 ? 'active' : '' }}"
                                    wire:click="$set('is_package', 1)"
                                >
                                    <div class="radio-card-icon" style="background: #F5F3FF; color: #7C3AED;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="radio-card-title">Paket Bundling</div>
                                        <div class="radio-card-desc">Paket hemat yang menggabungkan beberapa barang satuan sekaligus.</div>
                                    </div>
                                </div>
                            </div>
                            @error('is_package') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Grid Input Field -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Kategori Barang -->
                            <div class="form-group mb-0">
                                <label for="item_category" class="form-label-text">
                                    Kategori Barang <span class="form-label-required">*</span>
                                </label>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="item_category"
                                        wire:model="category" 
                                        class="form-control input-with-prefix font-medium" 
                                        placeholder="Contoh: Tenda, Kompor, Carrier, Matras"
                                        required
                                    >
                                </div>
                                @error('category') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- SKU (Kode Unik) -->
                            <div class="form-group mb-0">
                                <label for="item_sku" class="form-label-text">
                                    SKU (Kode Master Unik) <span class="form-label-required">*</span>
                                </label>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="item_sku"
                                        wire:model="sku" 
                                        class="form-control input-with-prefix uppercase font-mono tracking-wider font-semibold" 
                                        placeholder="Contoh: TND-001"
                                        required
                                    >
                                </div>
                                @error('sku') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nama Barang / Paket -->
                            <div class="form-group mb-0 md:col-span-2">
                                <label for="item_name" class="form-label-text">
                                    Nama Barang / Nama Paket Lengkap <span class="form-label-required">*</span>
                                </label>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="item_name"
                                        wire:model="name" 
                                        class="form-control input-with-prefix font-medium" 
                                        placeholder="Contoh: Tenda Dome Kapasitas 4 Orang Waterproof"
                                        required
                                    >
                                </div>
                                @error('name') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Jenis Layanan Sewa -->
                            <div class="form-group mb-0">
                                <label for="rental_type" class="form-label-text">
                                    Skema Layanan Sewa <span class="form-label-required">*</span>
                                </label>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    <select id="rental_type" wire:model="rental_type" class="form-control input-with-prefix font-semibold">
                                        <option value="daily">Harian (Per Hari / 24 Jam) - Standar Outdoor</option>
                                        <option value="hourly">Jam-jaman (Hourly)</option>
                                        <option value="trip">Per Trip (Borongan Event)</option>
                                    </select>
                                </div>
                                @error('rental_type') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Tarif Dasar per Hari -->
                            <div class="form-group mb-0">
                                <label for="price_per_day" class="form-label-text">
                                    Tarif Dasar Sewa (Rp / Hari) <span class="form-label-required">*</span>
                                </label>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix font-bold text-xs">Rp</span>
                                    <input 
                                        type="number" 
                                        id="price_per_day"
                                        wire:model="price_per_day" 
                                        min="0"
                                        class="form-control input-with-prefix font-bold tabular-nums" 
                                        placeholder="Contoh: 50000"
                                        required
                                    >
                                </div>
                                @error('price_per_day') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Foto Produk -->
                            <div class="form-group mb-0 md:col-span-2">
                                <label class="form-label-text">
                                    Foto Produk (Grid Minimarket & Booking Online)
                                </label>
                                <div class="mt-2 flex items-center gap-6">
                                    <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50 relative group">
                                        @if ($photo)
                                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                        @elseif ($existing_photo_url)
                                            <img src="{{ asset('storage/' . $existing_photo_url) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="text-center p-4">
                                                <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <span class="mt-1 block text-xs text-gray-500 font-medium">Belum ada foto</span>
                                            </div>
                                        @endif
                                        <!-- Loading overlay during upload -->
                                        <div wire:loading.flex wire:target="photo" class="photo-upload-loading" style="display: none;">
                                            <div class="w-6 h-6 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                                            <span class="text-[10px] font-bold text-emerald-700 mt-1">Mengunggah...</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors inline-block focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500">
                                            <span>Pilih Foto Baru</span>
                                            <input type="file" wire:model="photo" class="sr-only" accept="image/jpeg,image/png,image/webp">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format JPG, PNG, atau WEBP. Rekomendasi rasio 1:1 (persegi). Maksimal 2MB.
                                        </p>
                                        @error('photo') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ATURAN HARGA MULTIPLIER -->
                    <div class="{{ $activeTab === 'pricing' ? 'block' : 'hidden' }}">
                        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl mb-6 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-xs text-blue-900 leading-relaxed">
                                Atur pengali tarif dinamis sesuai tipe hari. <strong>Multiplier 1.00</strong> adalah harga dasar normal. <strong>1.25</strong> berarti harga naik 25% saat akhir pekan (Weekend), dan <strong>1.50</strong> naik 50% saat libur nasional (Holiday).
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($pricingRules as $index => $rule)
                            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-gray-200">
                                <div class="flex-1">
                                    <label class="form-label-text mb-1 text-xs">Tipe Hari</label>
                                    <select wire:model="pricingRules.{{ $index }}.day_type" class="form-control text-xs font-bold">
                                        <option value="weekday">Weekday (Senin - Kamis)</option>
                                        <option value="weekend">Weekend (Jumat - Minggu)</option>
                                        <option value="holiday">Hari Libur Nasional</option>
                                    </select>
                                    @error('pricingRules.'.$index.'.day_type') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="w-48">
                                    <label class="form-label-text mb-1 text-xs">Multiplier (Pengali)</label>
                                    <div class="form-input-group">
                                        <input type="number" step="0.01" wire:model="pricingRules.{{ $index }}.price_multiplier" class="form-control text-xs font-mono font-bold text-center" placeholder="1.00">
                                    </div>
                                    @error('pricingRules.'.$index.'.price_multiplier') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-5">
                                    <button type="button" wire:click="removePricingRule({{ $index }})" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Aturan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="button" wire:click="addPricingRule" class="btn text-xs font-bold text-navy bg-slate-100 hover:bg-slate-200 transition rounded-lg py-2 px-3.5 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-navy" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                <span>Tambah Aturan Harga Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 3: KOMPONEN PAKET BUNDLING (JIKA PAKET) -->
                    @if($is_package)
                    <div class="{{ $activeTab === 'package' ? 'block' : 'hidden' }}">
                        <p class="text-xs text-gray-500 mb-4">
                            Tentukan daftar barang satuan yang otomatis dialokasikan saat paket bundling ini disewa.
                        </p>

                        <div class="mb-6 bg-slate-50 p-4 border border-gray-200 rounded-xl">
                            <label class="form-label-text mb-2 text-xs">Tambahkan Komponen Barang Satuan</label>
                            <div class="flex gap-2.5">
                                <select id="componentSelect" class="form-control text-xs font-semibold flex-1">
                                    <option value="">-- Pilih Barang Satuan --</option>
                                    @foreach($availableComponents as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->sku }} - {{ $comp->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="
                                    const sel = document.getElementById('componentSelect');
                                    if(sel.value) {
                                        @this.call('addPackageItem', sel.value, sel.options[sel.selectedIndex].text);
                                        sel.value = '';
                                    }
                                " class="btn text-xs font-bold text-white bg-navy hover:bg-blue-900 transition rounded-xl px-4 shrink-0">
                                    + Tambahkan
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($packageItems as $index => $pi)
                            <div class="flex items-center gap-4 bg-white p-3.5 border border-gray-200 rounded-xl shadow-sm">
                                <div class="flex-1">
                                    <span class="font-bold text-navy text-sm">{{ $pi['name'] }}</span>
                                </div>
                                <div class="w-36">
                                    <label class="form-label-text text-xs mb-1">Jumlah Unit</label>
                                    <input type="number" min="1" wire:model="packageItems.{{ $index }}.quantity" class="form-control text-xs font-bold text-center">
                                    @error('packageItems.'.$index.'.quantity') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <button type="button" wire:click="removePackageItem({{ $index }})" class="mt-4 text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-400 text-xs border-2 border-dashed border-gray-200 rounded-xl">
                                Belum ada komponen barang satuan di dalam paket bundling ini.
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons Footer -->
                    <div class="form-action-bar border-t border-gray-100 pt-5 mt-8">
                        <a href="{{ route('admin.inventory.items') }}" class="btn-form-cancel">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Batal</span>
                        </a>

                        <button type="submit" class="btn-form-save" wire:loading.attr="disabled">
                            <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove wire:target="save">{{ $itemId ? 'Perbarui Master Barang' : 'Simpan Master Barang' }}</span>

                            <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>
</div>
