<div class="cashier-workspace w-screen h-screen flex flex-col overflow-hidden" style="background: #f4f4f5; position: fixed; inset: 0;">
    <!-- POS Dedicated Topbar (Full width, no sidebar) -->
    <header class="bg-white px-5 py-3 flex items-center justify-between border-b border-gray-200 shrink-0 shadow-sm z-30">
        <!-- Brand & Info -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #FF4500">
                        <path d="m12 3-9 17h18Z"/>
                        <path d="m12 3 3 8-6 4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-[#101F42] leading-none tracking-tight">SummitGear <span class="text-[#FF4500]">POS</span></h1>
                    <p class="text-[11px] font-semibold text-gray-400 mt-0.5">Terminal Kasir & Transaksi Sewa</p>
                </div>
            </div>

            <div class="h-6 bg-gray-200 shrink-0 mx-1 hidden sm:block" style="width: 1px;"></div>

            <!-- Shift Status Badge -->
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-[11px] font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Shift Kasir Aktif
            </div>
        </div>

        <!-- Actions & Cashier Controls -->
        <div class="flex items-center gap-3">
            <!-- Booking Masuk Link -->
            <a href="{{ route('admin.operations.incoming_booking') }}" 
               class="btn text-xs font-bold text-gray-700 hover:text-[#101F42] bg-gray-100 hover:bg-gray-200/80 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5" 
               title="Daftar Booking Online Masuk">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Booking Masuk</span>
            </a>

            @if(auth()->user()->role === 'admin')
            <!-- Admin Dashboard return button -->
            <a href="{{ route('dashboard') }}" 
               class="btn text-xs font-bold text-[#101F42] hover:text-white bg-blue-50 hover:bg-[#101F42] border border-blue-100 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5" 
               title="Kembali ke Dashboard Utama Admin">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Dashboard Admin</span>
            </a>
            @endif

            @if(auth()->user()->role === 'kasir')
            <!-- Lock Screen Button (Kasir only) -->
            <button type="button" x-data x-on:click="$dispatch('lockScreen')" 
                    class="text-gray-500 hover:text-[#101F42] hover:bg-gray-100 p-2 rounded-xl transition cursor-pointer" 
                    title="Kunci Layar Kasir">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </button>
            @endif

            <div class="h-6 bg-gray-200 shrink-0 mx-0.5" style="width: 1px;"></div>

            <!-- Cashier Identity -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white" style="background: #101F42;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xs font-bold text-[#101F42] leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] font-bold text-[#FF4500] leading-tight">{{ strtoupper(auth()->user()->role) }}</span>
                </div>
            </div>

            <!-- Logout / Selesai Shift -->
            <button type="button" 
                    @click="$dispatch('open-logout-modal')" 
                    class="btn text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100/80 px-3 py-1.5 rounded-xl transition flex items-center gap-1 cursor-pointer" 
                    title="Selesai Shift & Keluar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                </svg>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden cashier-shell" style="flex-direction: row; min-height: 0;">

            <!-- ===== LEFT: Product catalog ===== -->
            <div class="flex-1 flex flex-col overflow-hidden" style="min-width: 0; min-height: 0;">

                <!-- Search & Filter toolbar -->
                <div class="bg-white flex items-center gap-3.5 cashier-toolbar" 
                     style="padding-top: 22px; padding-bottom: 18px; padding-left: 24px; padding-right: 24px; border-bottom: 1px solid #E2E8F0;">
                    <div class="cashier-toolbar-title">
                        <p class="cashier-eyebrow">Katalog</p>
                        <p class="cashier-toolbar-heading">Pilih peralatan sewa</p>
                    </div>
                    <div class="flex-1 relative max-w-xs cashier-search">
                        <input type="text" wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Cari alat atau SKU..."
                               class="w-full pl-10 pr-4 rounded-xl text-sm font-medium outline-none transition-all" 
                               style="height: 42px; border: 1px solid #E2E8F0; background: #F8FAFC;" 
                               onfocus="this.style.borderColor='#101F42'; this.style.background='#FFFFFF'" 
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC'">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <select wire:model.live="selectedCategory"
                            class="rounded-xl text-sm font-semibold px-3.5 outline-none transition-all cashier-category" 
                            style="height: 42px; border: 1px solid #E2E8F0; background: #F8FAFC;" 
                            aria-label="Filter kategori">
                        <option value="all">Semua Kategori</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>

                    <span class="ml-auto text-xs font-bold text-slate-400 whitespace-nowrap tabular-nums">
                        {{ count($this->availableItems) }} produk
                    </span>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 overflow-y-auto cashier-catalog-grid" style="padding: 22px 24px 28px 24px;">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3.5">
                        @forelse($this->availableItems as $item)
                            <div wire:key="catalog-item-{{ $item->id }}"
                                 wire:click="{{ $item->available_count > 0 ? 'addToCart(' . $item->id . ')' : '' }}"
                                 class="bg-white rounded-xl overflow-hidden flex flex-col relative group transition-all duration-200
                                        {{ $item->available_count > 0
                                            ? 'cursor-pointer hover:-translate-y-px hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.08)]'
                                            : 'opacity-50 cursor-not-allowed' }}"
                                 style="border: 1px solid #ebebeb;">

                                <!-- Image -->
                                <div class="w-full h-28 bg-[#f8f8f8] overflow-hidden relative flex items-center justify-center">
                                    @if($item->photo_url)
                                        <img src="{{ asset($item->photo_url) }}"
                                             alt="{{ $item->name }}"
                                             class="w-full h-full object-cover {{ $item->available_count > 0 ? 'group-hover:scale-105' : '' }} transition-transform duration-300">
                                    @else
                                        <svg class="h-7 w-7 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif

                                    @if($item->available_count > 0)
                                        <div class="absolute top-1.5 right-1.5 text-[9px] font-bold text-white px-1.5 py-0.5 rounded" style="background: rgba(5,150,105,0.75); backdrop-filter: blur(4px);">
                                            {{ $item->available_count }}
                                        </div>
                                    @else
                                        <div class="absolute inset-0 bg-white/60 flex items-center justify-center backdrop-blur-[1px]">
                                            <span class="text-[10px] font-bold text-zinc-500 bg-white px-2 py-0.5 rounded shadow-sm" style="border: 1px solid #e4e4e7;">Kosong</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="p-2.5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-zinc-300 block mb-0.5">{{ $item->category }}</span>
                                        <h4 class="text-[12px] font-bold text-[#0f1729] leading-tight line-clamp-2 {{ $item->available_count > 0 ? 'group-hover:text-[#e8430a]' : '' }} transition-colors">{{ $item->name }}</h4>
                                    </div>
                                    <div class="mt-2 pt-2 flex items-center justify-between" style="border-top: 1px solid #f4f4f5;">
                                        <p class="text-[13px] font-extrabold text-[#059669] tabular-nums">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-[9px] font-normal text-zinc-400">/hr</span></p>
                                        @if($item->available_count > 0)
                                            <div class="w-5 h-5 rounded-md flex items-center justify-center group-hover:bg-[#059669] transition-all" style="background: #f0fdf4; border: 1px solid #d1fae5;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#059669] group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-xl" style="border: 1px dashed #e4e4e7;">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3" style="background: #f4f4f5;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-[#0f1729] mb-0.5">Tidak ada alat ditemukan</h4>
                                <p class="text-[12px] text-zinc-400">Ubah filter atau kata kunci</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT: Cart & Checkout — Scrollable, Spacious & Elegant ===== -->
            <aside class="flex-shrink-0 flex flex-col bg-white cashier-cart shadow-[-4px_0_24px_rgba(0,0,0,0.03)]"
                   style="width: 460px; min-width: 420px; max-width: 500px; border-left: 1px solid #E2E8F0; height: 100%; overflow: hidden; font-feature-settings: 'tnum' 1;"
                   aria-label="Ringkasan transaksi">

                <!-- 1. Fixed Header at Top -->
                <div class="h-16 flex items-center justify-between flex-shrink-0 bg-white"
                     style="padding-left: 24px; padding-right: 24px; border-bottom: 1px solid #F1F5F9;">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-xs flex-shrink-0" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-bold text-[#101F42] tracking-tight leading-none">Keranjang Sewa</h2>
                            <p class="text-[11px] font-medium text-slate-400 mt-1">Terminal Kasir POS</p>
                        </div>
                    </div>
                    @if(count($cart) > 0)
                        <div class="flex items-center gap-1.5">
                            <span class="px-3 py-1 rounded-full text-[11.5px] font-extrabold text-white shadow-xs tabular-nums" style="background: #FF4500;">
                                {{ count($cart) }} alat
                            </span>
                        </div>
                    @endif
                </div>

                <!-- 2. Scrollable Body: Everything flows downwards with generous spacing -->
                <div class="flex-1 overflow-y-auto cashier-cart-scroll" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">

                    @error('cart')
                        <div class="p-3 text-red-600 text-[12px] font-bold text-center bg-red-50 border border-red-200 rounded-xl">{{ $message }}</div>
                    @enderror

                    <!-- A. Customer & Dates Card -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-4 shadow-xs">
                        <!-- Pelanggan -->
                        <div>
                            <label for="pos-customer" class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500 block mb-1.5">Pelanggan</label>
                            <div class="flex gap-2.5 items-center">
                                <div class="relative flex-1 flex items-center bg-white border border-slate-200 rounded-xl shadow-xs focus-within:border-[#101F42] focus-within:ring-2 focus-within:ring-[#101F42]/10 transition-all">
                                    <span class="pl-3 text-slate-400 pointer-events-none flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <select id="pos-customer" wire:model.live="customer_id"
                                            class="w-full h-11 text-[13.5px] font-semibold text-[#101F42] bg-transparent pl-2.5 pr-8 border-0 outline-none cursor-pointer focus:ring-0">
                                        <option value="">Pilih pelanggan terdaftar...</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <a href="{{ route('admin.customers.create') }}" target="_blank"
                                   class="w-11 h-11 flex-shrink-0 flex items-center justify-center text-slate-500 hover:text-white hover:bg-[#101F42] bg-white border border-slate-200 rounded-xl transition-all shadow-xs"
                                   title="Tambah Pelanggan Baru">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                            </div>
                            @error('customer_id') <span class="text-[11px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Jadwal Sewa -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500">Jadwal Sewa</span>
                                @if($this->durationDays > 0)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#101F42] bg-white border border-slate-200 px-2.5 py-0.5 rounded-md shadow-xs">
                                        <svg class="w-3 h-3 text-[#FF4500]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Durasi: {{ $this->durationDays }} Hari</span>
                                    </span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="pos-start-date" class="text-[10.5px] font-semibold text-slate-400 mb-1 block">Tgl Ambil</label>
                                    <input id="pos-start-date" type="date" wire:model.live="start_date"
                                           class="w-full h-11 text-[12.5px] font-bold text-[#101F42] px-3 bg-white border border-slate-200 rounded-xl shadow-xs outline-none focus:border-[#101F42] focus:ring-2 focus:ring-[#101F42]/10 transition-all">
                                    @error('start_date') <span class="text-[10.5px] font-bold text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="pos-end-date" class="text-[10.5px] font-semibold text-slate-400 mb-1 block">Tgl Kembali</label>
                                    <input id="pos-end-date" type="date" wire:model.live="end_date"
                                           class="w-full h-11 text-[12.5px] font-bold text-[#101F42] px-3 bg-white border border-slate-200 rounded-xl shadow-xs outline-none focus:border-[#101F42] focus:ring-2 focus:ring-[#101F42]/10 transition-all">
                                    @error('end_date') <span class="text-[10.5px] font-bold text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- B. Cart Items / Empty State -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500">Daftar Alat Disewa</span>
                            <span class="text-[11px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md tabular-nums">{{ count($cart) }} unit ({{ count($this->groupedCart) }} item)</span>
                        </div>

                        @if(count($cart) === 0)
                            <div class="p-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 flex flex-col items-center justify-center text-center">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 bg-white border border-slate-200/80 shadow-xs text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <p class="text-[13.5px] font-bold text-[#101F42]">Keranjang Masih Kosong</p>
                                <p class="text-[11.5px] text-slate-400 mt-1 max-w-[260px] leading-relaxed">Pilih alat camping dari katalog di sebelah kiri untuk memasukkan ke sewa</p>
                            </div>
                        @else
                        <div class="flex flex-col" style="display: flex; flex-direction: column; gap: 16px;">
                            @foreach($this->groupedCart as $group)
                                <div wire:key="cart-group-{{ $group['inventory_item_id'] }}"
                                     class="group transition-all bg-white hover:bg-slate-50/70 border border-slate-200/90 hover:border-slate-300 rounded-2xl shadow-xs cashier-cart-item"
                                     style="display: flex; align-items: flex-start; gap: 16px; padding: 16px;">
                                    <!-- Thumbnail -->
                                    <div class="rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200/80 mt-0.5"
                                         style="width: 52px; height: 52px; min-width: 52px;">
                                        @if($group['photo_url'])
                                            <img src="{{ asset($group['photo_url']) }}"
                                                 class="object-cover"
                                                 style="width: 52px; height: 52px; max-width: 52px; max-height: 52px;"
                                                 alt="{{ $group['item_name'] }}">
                                        @else
                                            <div class="flex items-center justify-center text-slate-300"
                                                 style="width: 52px; height: 52px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h5 class="text-[14px] font-bold text-[#101F42] truncate leading-snug">{{ $group['item_name'] }}</h5>
                                            @if($group['quantity'] > 1)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black bg-[#101F42] text-white shadow-2xs">
                                                    x{{ $group['quantity'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Harga Total / Satuan -->
                                        <div class="flex items-baseline gap-1.5" style="margin-top: 5px;">
                                            <span class="text-[13.5px] font-extrabold text-[#101F42] tabular-nums">
                                                Rp {{ number_format($group['total_base_price'], 0, ',', '.') }}<span class="text-[11px] font-semibold text-slate-400">/hari</span>
                                            </span>
                                            @if($group['quantity'] > 1)
                                                <span class="text-[11px] font-medium text-slate-400 tabular-nums">
                                                    ({{ $group['quantity'] }} &times; Rp {{ number_format($group['base_price'], 0, ',', '.') }})
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Serial Numbers Badge List -->
                                        <div class="flex flex-wrap gap-1.5" style="margin-top: 8px;">
                                            @foreach($group['serial_numbers'] as $sn)
                                                <span class="text-[10px] font-mono font-bold text-slate-600 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs">
                                                    {{ $sn }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Quantity Stepper & Remove Action -->
                                    <div class="flex flex-col items-end shrink-0" style="gap: 12px;">
                                        <!-- Remove All Button -->
                                        <button type="button" wire:click="removeItemCompletely({{ $group['inventory_item_id'] }})"
                                                class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cashier-remove cursor-pointer"
                                                title="Hapus {{ $group['item_name'] }} dari keranjang"
                                                aria-label="Hapus {{ $group['item_name'] }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>

                                        <!-- Stepper: Minus, Count, Plus -->
                                        <div class="flex items-center border border-slate-200 rounded-lg bg-slate-50/80 p-0.5 shadow-2xs">
                                            <button type="button" wire:click="decreaseItem({{ $group['inventory_item_id'] }})"
                                                    class="w-6 h-6 flex items-center justify-center rounded text-slate-600 hover:text-white hover:bg-slate-700 active:scale-95 transition-all cursor-pointer"
                                                    title="Kurangi 1 unit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                                </svg>
                                            </button>
                                            <span class="min-w-6 text-center text-[12.5px] font-black text-[#101F42] tabular-nums select-none px-1">
                                                {{ $group['quantity'] }}
                                            </span>
                                            <button type="button" wire:click="addToCart({{ $group['inventory_item_id'] }})"
                                                    class="w-6 h-6 flex items-center justify-center rounded text-slate-600 hover:text-white hover:bg-[#101F42] active:scale-95 transition-all cursor-pointer"
                                                    title="Tambah 1 unit lagi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- C. Summary: Inset Receipt Card -->
                    <div class="p-4 rounded-2xl bg-gradient-to-b from-slate-50 to-slate-100/70 border border-slate-200/80 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11.5px] font-extrabold uppercase tracking-wider text-slate-400">Rincian Pembayaran</span>
                            <span class="text-[11px] font-semibold text-slate-400">Ringkasan Struk</span>
                        </div>

                        <!-- Subtotal row -->
                        <div class="flex justify-between items-center text-[13px] pt-0.5">
                            <span class="font-medium text-slate-500">Subtotal ({{ count($cart) }} alat &times; {{ $this->durationDays }} hari)</span>
                            <span class="font-bold text-[#101F42] tabular-nums">Rp {{ number_format($total_price + $discount, 0, ',', '.') }}</span>
                        </div>

                        @if($discount > 0)
                        <!-- Diskon row -->
                        <div class="flex justify-between items-center text-[13px] text-[#FF4500]">
                            <span class="font-semibold flex items-center gap-1">Diskon Promo</span>
                            <span class="font-bold tabular-nums">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <!-- Divider & Total -->
                        <div class="border-t border-dashed border-slate-200 pt-3 flex justify-between items-end">
                            <div>
                                <span class="text-[11.5px] font-extrabold uppercase tracking-wider text-slate-400 block leading-none">Total Tagihan</span>
                                <span class="text-[10px] text-slate-400 font-medium mt-1 block">Termasuk jaminan & sewa</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[24px] font-black tabular-nums tracking-tight text-emerald-600 block leading-none">Rp {{ number_format($total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- D. Payment Card (Metode, Dibayar, Kembalian) -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col gap-3.5" style="display: flex; flex-direction: column; gap: 14px;">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="pos-payment-method" class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 block">Metode</label>
                                <div class="relative flex items-center bg-white border border-slate-200 rounded-xl shadow-xs focus-within:border-[#101F42] focus-within:ring-2 focus-within:ring-[#101F42]/10 transition-all">
                                    <select id="pos-payment-method" wire:model="payment_method"
                                            class="w-full h-11 text-[13px] font-bold text-[#101F42] bg-transparent pl-3 pr-7 border-0 outline-none cursor-pointer focus:ring-0 appearance-none">
                                        <option value="CASH">💵 Cash (Tunai)</option>
                                        <option value="TRANSFER">🏦 Transfer Bank</option>
                                        <option value="QRIS">📱 QRIS / E-Wallet</option>
                                    </select>
                                    <span class="absolute right-2.5 pointer-events-none text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="pos-payment-amount" class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500">Dibayar</label>
                                    @if($total_price > 0)
                                        <button type="button"
                                                x-data
                                                x-on:click="$wire.set('payment_amount', {{ $total_price }})"
                                                class="text-[11px] font-bold text-[#FF4500] hover:underline cursor-pointer">
                                            Uang Pas
                                        </button>
                                    @endif
                                </div>
                                <div class="flex items-stretch h-11 border border-slate-200 rounded-xl bg-white shadow-xs overflow-hidden transition-all focus-within:border-[#101F42] focus-within:ring-2 focus-within:ring-[#101F42]/10"
                                     x-data>
                                    <span class="flex items-center px-3 text-[12.5px] font-black text-slate-400 bg-slate-50 border-r border-slate-100 select-none flex-shrink-0">Rp</span>
                                    <input id="pos-payment-amount" type="number" wire:model.live.debounce.300ms="payment_amount" min="0" placeholder="0"
                                           x-on:focus="if ($el.value === '0') { $el.value = ''; $wire.set('payment_amount', ''); }"
                                           x-on:input="if ($el.value.length > 1 && $el.value.startsWith('0')) { $el.value = $el.value.replace(/^0+/, ''); }"
                                           class="flex-1 min-w-0 h-full text-[14px] font-extrabold text-[#101F42] outline-none tabular-nums bg-transparent px-3 border-0 focus:ring-0">
                                </div>
                            </div>
                            @error('payment_amount') <div class="col-span-2 text-red-500 text-[11px] font-bold">{{ $message }}</div> @enderror
                        </div>

                        <!-- Kembalian / Selisih Indicator -->
                        @if((float)$payment_amount > 0 && (float)$total_price > 0)
                            @if((float)$payment_amount >= (float)$total_price)
                                <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[12px]">
                                    <span class="font-semibold flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Kembalian</span>
                                    </span>
                                    <span class="font-black tabular-nums text-[13.5px]">Rp {{ number_format((float)$payment_amount - (float)$total_price, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[12px]">
                                    <span class="font-semibold flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Kekurangan</span>
                                    </span>
                                    <span class="font-black tabular-nums text-[13.5px]">Rp {{ number_format((float)$total_price - (float)$payment_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- E. KTP Verification Interactive Card -->
                    <label for="is_ktp_valid"
                           class="flex items-start gap-3.5 p-3.5 rounded-xl border cursor-pointer transition-all select-none {{ $is_ktp_valid ? 'bg-emerald-50/70 border-emerald-300 ring-1 ring-emerald-400/20 shadow-xs' : 'bg-amber-50/70 border-amber-200/90 hover:bg-amber-50 hover:border-amber-300 shadow-xs' }}">
                        <div class="mt-0.5 shrink-0 flex items-center">
                            <input type="checkbox" wire:model.live="is_ktp_valid" id="is_ktp_valid"
                                   class="w-4.5 h-4.5 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[12.5px] font-bold {{ $is_ktp_valid ? 'text-emerald-900' : 'text-amber-950' }}">KTP Asli Valid & Ditahan</span>
                                @if($is_ktp_valid)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-extrabold bg-emerald-100 text-emerald-800">Terverifikasi</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-extrabold bg-amber-100 text-amber-800">Wajib Jaminan</span>
                                @endif
                            </div>
                            <p class="text-[11.5px] leading-relaxed mt-1 {{ $is_ktp_valid ? 'text-emerald-700/90' : 'text-amber-800/80' }}">
                                Identitas penyewa sudah dicek keasliannya dan ditahan kasir sebagai jaminan fisik.
                            </p>
                        </div>
                    </label>
                    @error('is_ktp_valid') <div class="text-red-500 text-[11px] font-bold">{{ $message }}</div> @enderror

                    <!-- F. Submit Button -->
                    <div class="pt-1 pb-6">
                        <button type="button" wire:click="saveTransaction"
                                class="w-full flex items-center justify-center gap-2.5 text-[14.5px] font-bold text-white h-[52px] rounded-xl transition-all duration-150 active:scale-[0.98] shadow-md hover:shadow-lg cursor-pointer bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600"
                                style="box-shadow: 0 4px 16px 0 rgba(5, 150, 105, 0.35);">
                            <svg wire:loading wire:target="saveTransaction" class="animate-spin h-4.5 w-4.5 text-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg wire:loading.remove wire:target="saveTransaction" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span wire:loading.remove wire:target="saveTransaction">Proses Transaksi Sekarang</span>
                            <span wire:loading wire:target="saveTransaction">Memproses Transaksi...</span>
                        </button>

                        @error('checkout')
                            <div class="mt-3 text-center text-[12px] font-bold text-red-600 p-2.5 rounded-xl bg-red-50 border border-red-200">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </aside>

        </div>
</div>