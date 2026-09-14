<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar :title="$itemId ? 'Edit Master Barang' : 'Tambah Master Barang'" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('admin.inventory.items') }}" variant="subtle" icon="arrow-left" size="sm">
                        Kembali
                    </flux:button>
                    <div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                            <span>Inventaris</span>
                            <span>/</span>
                            <span class="text-coral font-bold">{{ $itemId ? 'Edit Master' : 'Tambah Baru' }}</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            {{ $itemId ? 'Edit Barang: ' . $name : 'Tambah Master Barang Baru' }}
                        </h1>
                    </div>
                </div>

                @if($itemId)
                <div class="flex items-center gap-2">
                    <flux:badge color="zinc" size="sm">SKU: {{ $sku }}</flux:badge>
                    <flux:badge :color="$is_package ? 'purple' : 'sky'" size="sm">
                        {{ $is_package ? 'Paket Bundling' : 'Barang Satuan' }}
                    </flux:badge>
                </div>
                @endif
            </div>

            @if (session()->has('error'))
                <div class="p-4 text-xs font-bold text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form Card Wrapper -->
            <flux:card class="p-0 overflow-hidden shadow-sm border border-gray-200/80">
                
                <!-- Modern Tab Navigation -->
                <div class="p-3 bg-slate-50 border-b border-gray-200 flex items-center gap-2 overflow-x-auto">
                    <button type="button" wire:click="$set('activeTab', 'basic')" 
                            class="px-4 py-2 text-xs font-bold rounded-lg transition-colors flex items-center gap-2 {{ $activeTab === 'basic' ? 'bg-white text-navy shadow-xs border border-gray-200' : 'text-gray-500 hover:text-navy hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>1. Informasi Dasar</span>
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'pricing')" 
                            class="px-4 py-2 text-xs font-bold rounded-lg transition-colors flex items-center gap-2 {{ $activeTab === 'pricing' ? 'bg-white text-navy shadow-xs border border-gray-200' : 'text-gray-500 hover:text-navy hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>2. Aturan Harga Multiplier</span>
                    </button>
                    @if($is_package)
                    <button type="button" wire:click="$set('activeTab', 'package')" 
                            class="px-4 py-2 text-xs font-bold rounded-lg transition-colors flex items-center gap-2 {{ $activeTab === 'package' ? 'bg-white text-navy shadow-xs border border-gray-200' : 'text-gray-500 hover:text-navy hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>3. Komponen Paket ({{ count($packageItems) }})</span>
                    </button>
                    @endif
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    
                    <!-- TAB 1: INFORMASI DASAR -->
                    <div class="{{ $activeTab === 'basic' ? 'space-y-6' : 'hidden' }}">
                        <!-- Tipe Barang (Interactive Radio Cards) -->
                        <div>
                            <flux:label class="mb-2 text-xs font-bold">
                                Pilih Tipe Barang <span class="text-red-500">*</span>
                            </flux:label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div 
                                    class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-start gap-3.5 select-none {{ $is_package == 0 ? 'border-navy bg-blue-50/40 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                    wire:click="$set('is_package', 0)"
                                >
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $is_package == 0 ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-navy">Barang Satuan</div>
                                        <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">Barang fisik individual dengan serial number / barcode unit unik.</div>
                                    </div>
                                </div>

                                <div 
                                    class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-start gap-3.5 select-none {{ $is_package == 1 ? 'border-purple-600 bg-purple-50/40 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                    wire:click="$set('is_package', 1)"
                                >
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $is_package == 1 ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-600' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-navy">Paket Bundling</div>
                                        <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">Paket hemat yang menggabungkan beberapa barang satuan sekaligus.</div>
                                    </div>
                                </div>
                            </div>
                            @error('is_package') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Grid Input Field -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Kategori Barang -->
                            <div>
                                <flux:input 
                                    label="Kategori Barang" 
                                    wire:model="category" 
                                    placeholder="Contoh: Tenda, Kompor, Carrier, Matras" 
                                    required 
                                />
                                @error('category') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- SKU (Kode Unik) -->
                            <div>
                                <flux:input 
                                    label="SKU (Kode Master Unik)" 
                                    wire:model="sku" 
                                    class="uppercase font-mono font-semibold" 
                                    placeholder="Contoh: TND-001" 
                                    required 
                                />
                                @error('sku') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nama Barang / Paket -->
                            <div class="md:col-span-2">
                                <flux:input 
                                    label="Nama Barang / Nama Paket Lengkap" 
                                    wire:model="name" 
                                    placeholder="Contoh: Tenda Dome Kapasitas 4 Orang Waterproof" 
                                    required 
                                />
                                @error('name') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Jenis Layanan Sewa -->
                            <div>
                                <flux:select label="Skema Layanan Sewa" wire:model="rental_type">
                                    <option value="daily">Harian (Per Hari / 24 Jam) - Standar Outdoor</option>
                                    <option value="hourly">Jam-jaman (Hourly)</option>
                                    <option value="trip">Per Trip (Borongan Event)</option>
                                </flux:select>
                                @error('rental_type') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Tarif Dasar per Hari -->
                            <div>
                                <flux:input 
                                    label="Tarif Dasar Sewa (Rp / Hari)" 
                                    type="number" 
                                    min="0" 
                                    wire:model="price_per_day" 
                                    class="font-bold tabular-nums" 
                                    placeholder="50000" 
                                    required 
                                />
                                @error('price_per_day') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Foto Produk -->
                            <div class="md:col-span-2">
                                <flux:label class="text-xs font-bold mb-2 block">
                                    Foto Produk (Grid Minimarket & Booking Online)
                                </flux:label>
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
                                        <div wire:loading.flex wire:target="photo" class="absolute inset-0 bg-white/80 flex-col items-center justify-center">
                                            <div class="w-6 h-6 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                                            <span class="text-[10px] font-bold text-emerald-700 mt-1">Mengunggah...</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg shadow-2xs text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors inline-block">
                                            <span>Pilih Foto Baru</span>
                                            <input type="file" wire:model="photo" class="sr-only" accept="image/jpeg,image/png,image/webp">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format JPG, PNG, atau WEBP. Rekomendasi rasio 1:1 (persegi). Maksimal 2MB.
                                        </p>
                                        @error('photo') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ATURAN HARGA MULTIPLIER -->
                    <div class="{{ $activeTab === 'pricing' ? 'space-y-6' : 'hidden' }}">
                        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center gap-3">
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
                                    <flux:label class="mb-1 text-xs">Tipe Hari</flux:label>
                                    <flux:select wire:model="pricingRules.{{ $index }}.day_type">
                                        <option value="weekday">Weekday (Senin - Kamis)</option>
                                        <option value="weekend">Weekend (Jumat - Minggu)</option>
                                        <option value="holiday">Hari Libur Nasional</option>
                                    </flux:select>
                                    @error('pricingRules.'.$index.'.day_type') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="w-48">
                                    <flux:input 
                                        label="Multiplier" 
                                        type="number" 
                                        step="0.01" 
                                        wire:model="pricingRules.{{ $index }}.price_multiplier" 
                                        class="font-mono font-bold text-center" 
                                        placeholder="1.00" 
                                    />
                                    @error('pricingRules.'.$index.'.price_multiplier') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-6">
                                    <flux:button type="button" wire:click="removePricingRule({{ $index }})" variant="danger" icon="trash" size="sm" title="Hapus Aturan" />
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <flux:button type="button" wire:click="addPricingRule" variant="subtle" icon="plus" size="sm">
                                Tambah Aturan Harga Baru
                            </flux:button>
                        </div>
                    </div>

                    <!-- TAB 3: KOMPONEN PAKET BUNDLING (JIKA PAKET) -->
                    @if($is_package)
                    <div class="{{ $activeTab === 'package' ? 'space-y-6' : 'hidden' }}">
                        <p class="text-xs text-gray-500">
                            Tentukan daftar barang satuan yang otomatis dialokasikan saat paket bundling ini disewa.
                        </p>

                        <div class="bg-slate-50 p-4 border border-gray-200 rounded-xl space-y-2">
                            <flux:label class="text-xs font-semibold">Tambahkan Komponen Barang Satuan</flux:label>
                            <div class="flex gap-2.5">
                                <select id="componentSelect" class="w-full text-xs font-semibold py-2 px-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-navy focus:border-transparent outline-none flex-1">
                                    <option value="">-- Pilih Barang Satuan --</option>
                                    @foreach($availableComponents as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->sku }} - {{ $comp->name }}</option>
                                    @endforeach
                                </select>
                                <flux:button type="button" onclick="
                                    const sel = document.getElementById('componentSelect');
                                    if(sel.value) {
                                        @this.call('addPackageItem', sel.value, sel.options[sel.selectedIndex].text);
                                        sel.value = '';
                                    }
                                " variant="primary" icon="plus" size="sm">
                                    Tambahkan
                                </flux:button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($packageItems as $index => $pi)
                            <div class="flex items-center gap-4 bg-white p-3.5 border border-gray-200 rounded-xl shadow-xs">
                                <div class="flex-1">
                                    <span class="font-bold text-navy text-sm">{{ $pi['name'] }}</span>
                                </div>
                                <div class="w-36">
                                    <flux:input label="Jumlah Unit" type="number" min="1" wire:model="packageItems.{{ $index }}.quantity" class="text-center font-bold" />
                                    @error('packageItems.'.$index.'.quantity') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="pt-6">
                                    <flux:button type="button" wire:click="removePackageItem({{ $index }})" variant="danger" icon="trash" size="sm" title="Hapus" />
                                </div>
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
                    <div class="flex items-center justify-between border-t border-gray-200 pt-5 mt-8">
                        <flux:button href="{{ route('admin.inventory.items') }}" variant="subtle">
                            Batal
                        </flux:button>

                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">{{ $itemId ? 'Perbarui Master Barang' : 'Simpan Master Barang' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </flux:button>
                    </div>

                </form>
            </flux:card>

        </div>
    </main>
</div>
