<div class="min-h-screen bg-slate-50 flex flex-col relative">
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200/80 shadow-sm sticky top-0 z-40 backdrop-blur-md bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-navy to-blue-900 flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-navy group-hover:text-emerald-600 transition-colors">SummitGear</h1>
                    <p class="text-[10px] text-gray-400 -mt-1 font-medium hidden sm:block">Katalog Sewa Online</p>
                </div>
            </a>

            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('home') }}" class="text-xs sm:text-sm font-bold text-gray-500 hover:text-navy transition-colors px-2 py-1">
                    Kembali
                </a>

                <!-- Cart Button with Dynamic Badge -->
                <button type="button" 
                        wire:click="openCart"
                        class="relative flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-bold transition-all transform active:scale-95 shadow-sm border {{ $this->totalCartCount > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100 hover:border-emerald-400' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $this->totalCartCount > 0 ? 'text-emerald-600' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        @if($this->totalCartCount > 0)
                            <span class="absolute -top-2.5 -right-2.5 min-w-[20px] h-5 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-[11px] font-black rounded-full flex items-center justify-center px-1 shadow-md animate-pulse">
                                {{ $this->totalCartCount }}
                            </span>
                        @endif
                    </div>
                    <span class="hidden sm:inline font-black">Keranjang</span>
                    @if($this->totalCartCount > 0)
                        <span class="text-xs font-black bg-emerald-600 text-white rounded-md px-1.5 py-0.5 ml-0.5">
                            {{ $this->totalCartCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content: Full-width Catalog Grid -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Filter & Search Section -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-sm mb-6">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Search Input with Flux -->
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="searchQuery" 
                        placeholder="Cari peralatan outdoor, tenda, carrier, matras..." 
                        icon="magnifying-glass" 
                        clearable 
                    />
                </div>

                <!-- Date Range & Category Filter -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto bg-slate-50 px-3 py-1.5 rounded-xl border border-gray-200">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wide shrink-0">Tgl Sewa:</span>
                        <input type="date" wire:model.live="start_date" class="bg-white py-1 px-2 text-xs font-semibold rounded-lg border-gray-300 text-gray-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <span class="text-xs text-gray-400 font-bold">-</span>
                        <input type="date" wire:model.live="end_date" class="bg-white py-1 px-2 text-xs font-semibold rounded-lg border-gray-300 text-gray-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="w-full sm:w-auto">
                        <select wire:model.live="selectedCategory" class="w-full sm:w-auto rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-xs sm:text-sm font-semibold py-2.5 px-3.5 bg-slate-50">
                            <option value="all">Semua Kategori</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <!-- Active Dates & Duration Indicator -->
            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    @if($start_date && $end_date && $this->duration_days > 0)
                        <span>Ketersediaan unit periode: <strong class="text-navy">{{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</strong> ({{ $this->duration_days }} Hari)</span>
                    @else
                        <span>Katalog Alat Outdoor SummitGear — Silakan pilih alat dan atur durasi sewa di formulir keranjang.</span>
                    @endif
                </div>
                @if($this->totalCartCount > 0)
                    <button type="button" wire:click="openCart" class="text-emerald-700 font-bold hover:underline flex items-center gap-1">
                        <span>{{ $this->totalCartCount }} barang di keranjang</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Notification Message if any -->
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{!! session('message') !!}</span>
                </div>
            </div>
        @endif

        @error('cart') 
            <div class="mb-6 p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-bold rounded-xl flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- Catalog Items Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5 pb-20">
            @forelse($this->availableItems as $item)
                <div class="bg-white rounded-2xl p-3.5 border {{ $item->available_count > 0 ? 'border-gray-200 hover:border-emerald-500 hover:shadow-xl' : 'border-gray-200 opacity-60' }} transition-all flex flex-col h-full group relative overflow-hidden">
                    
                    <!-- Availability & Cart Badge -->
                    <div class="absolute top-2.5 left-2.5 right-2.5 flex flex-wrap items-start justify-between gap-1 z-10 pointer-events-none">
                        @if($item->available_count > 0)
                            <span class="bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm shrink-0">
                                Sisa {{ $item->available_count }}
                            </span>
                        @else
                            <span class="bg-red-500/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm shrink-0">
                                Habis
                            </span>
                        @endif

                        @if($item->cart_quantity > 0)
                            <span class="bg-orange-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-md shrink-0">
                                {{ $item->cart_quantity }} di Keranjang
                            </span>
                        @endif
                    </div>

                    <!-- Product Image -->
                    <div class="w-full h-36 sm:h-40 bg-gray-50 rounded-xl mb-3 overflow-hidden border border-gray-100 flex items-center justify-center relative">
                        @if($item->photo_url)
                            <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <!-- Item Details -->
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">{{ $item->category }}</span>
                            <h3 class="text-xs sm:text-sm font-bold text-navy leading-snug line-clamp-2 mb-1 group-hover:text-emerald-600 transition-colors">
                                {{ $item->name }}
                            </h3>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-col gap-2">
                            <div class="text-emerald-600 font-black text-sm sm:text-base">
                                Rp {{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-[10px] font-normal text-gray-400">/hr</span>
                            </div>

                            @if($item->available_count > 0)
                                <button type="button" 
                                        wire:click="addToCart({{ $item->id }})" 
                                        class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95 {{ $item->cart_quantity > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-100 hover:bg-emerald-500 hover:text-white text-slate-800' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>{{ $item->cart_quantity > 0 ? '+ Tambah Lagi' : '+ Tambah' }}</span>
                                </button>
                            @else
                                <button type="button" disabled class="w-full py-2 px-3 rounded-xl text-xs font-bold bg-gray-100 text-gray-400 cursor-not-allowed">
                                    Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-300">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-gray-700 font-bold text-base mb-1">Tidak Ada Alat Ditemukan</h4>
                    <p class="text-xs text-gray-400">Silakan ubah kata kunci pencarian atau tanggal sewa.</p>
            @endforelse

            <!-- Sentinel Load More on Scroll (Lazy Loading) -->
            @if($this->hasMorePages)
                <div x-data="{
                        observe() {
                            const observer = new IntersectionObserver((entries) => {
                                if (entries[0].isIntersecting) {
                                    @this.loadMore();
                                }
                            }, { rootMargin: '300px' });
                            observer.observe($el);
                        }
                     }"
                     x-init="observe()"
                     class="col-span-full py-8 flex flex-col items-center justify-center gap-3">
                    <div class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-white border border-gray-200/90 rounded-full shadow-xs text-xs font-bold text-gray-600">
                        <svg class="animate-spin h-4 w-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat perlengkapan selanjutnya saat di-scroll...</span>
                    </div>
                    <button type="button" wire:click="loadMore" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                        + Tampilkan lebih banyak alat
                    </button>
                </div>
            @else
                @if(count($this->availableItems) > 0)
                    <div class="col-span-full py-8 text-center text-xs font-bold text-gray-400">
                        ✓ Seluruh {{ $this->totalFilteredItems }} perlengkapan telah ditampilkan
                    </div>
                @endif
            @endif
        </div>

    </main>

    <!-- Floating Bottom Bar When Cart Has Items -->
    @if($this->totalCartCount > 0 && !$isCartOpen)
        <div class="fixed bottom-5 left-0 right-0 z-40 px-4 flex justify-center pointer-events-none">
            <div class="pointer-events-auto bg-navy/95 backdrop-blur-md text-white rounded-2xl p-3 sm:px-6 sm:py-3.5 shadow-2xl border border-white/15 flex items-center justify-between gap-4 w-full max-w-xl animate-fade-in-up">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-[10px] font-black rounded-full w-5 h-5 flex items-center justify-center shadow-md">
                            {{ $this->totalCartCount }}
                        </span>
                    </div>
                    <div>
                        <div class="text-xs text-gray-300 font-medium">Total ({{ $this->totalCartCount }} barang - {{ $this->duration_days }} hr)</div>
                        <div class="text-base sm:text-lg font-black text-amber-400">Rp {{ number_format($total_price, 0, ',', '.') }}</div>
                    </div>
                </div>
                <button type="button" 
                        wire:click="openCart" 
                        style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff !important;"
                        class="text-xs sm:text-sm font-black py-2.5 px-4 sm:px-5 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center gap-2">
                    <span>Lihat & Reservasi</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Premium Right-Side Cart Drawer (Slide-Over Panel) -->
    @if($isCartOpen)
        <div class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
            <!-- Background Backdrop -->
            <div wire:click="closeCart" 
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
                <!-- Drawer Panel Container -->
                <div class="w-screen max-w-lg sm:max-w-xl bg-white shadow-2xl flex flex-col h-full overflow-hidden border-l border-gray-100">
                    
                    <!-- Drawer Header (Fixed, Pinned at Top) -->
                    <div class="bg-white border-b border-gray-200 shrink-0 shadow-2xs">
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base sm:text-lg font-black text-slate-800 leading-tight">Keranjang Sewa</h2>
                                        <span class="bg-emerald-600 text-white text-xs font-black px-2 py-0.5 rounded-full">
                                            {{ $this->totalCartCount }} Barang
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">SummitGear Outdoor Rental</p>
                                </div>
                            </div>
                            <button type="button" 
                                    wire:click="closeCart" 
                                    class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                    title="Tutup Keranjang">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Step Tabs Navigation -->
                        <div class="flex items-center px-5 pb-3 gap-2 text-xs font-bold">
                            <button type="button" 
                                    wire:click="setStep('items')"
                                    style="background-color: {{ $cartStep === 'items' ? '#0f172a' : '#f1f5f9' }} !important; color: {{ $cartStep === 'items' ? '#ffffff' : '#334155' }} !important; border: 1px solid {{ $cartStep === 'items' ? '#0f172a' : '#cbd5e1' }} !important;"
                                    class="flex-1 py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" style="color: {{ $cartStep === 'items' ? '#ffffff' : '#64748b' }} !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span style="color: {{ $cartStep === 'items' ? '#ffffff' : '#334155' }} !important; font-weight: 800;">1. Perlengkapan ({{ count($cart) }})</span>
                            </button>
                            <button type="button" 
                                    wire:click="setStep('form')"
                                    style="background-color: {{ $cartStep === 'form' ? '#0f172a' : '#f1f5f9' }} !important; color: {{ $cartStep === 'form' ? '#ffffff' : '#334155' }} !important; border: 1px solid {{ $cartStep === 'form' ? '#0f172a' : '#cbd5e1' }} !important;"
                                    class="flex-1 py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" style="color: {{ $cartStep === 'form' ? '#ffffff' : '#64748b' }} !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span style="color: {{ $cartStep === 'form' ? '#ffffff' : '#334155' }} !important; font-weight: 800;">2. Jadwal & Pemesan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Drawer Body (Single Clean Scrollbar, Spacious & Comfortable) -->
                    <div class="flex-1 overflow-y-auto min-h-0 p-5 bg-slate-50/70">
                        
                        <!-- TAB 1: DAFTAR PERLENGKAPAN -->
                        @if($cartStep === 'items')
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-200/80">
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">
                                        Daftar Alat Dipilih ({{ count($cart) }} Macam)
                                    </h3>
                                    <span class="text-xs font-semibold text-emerald-700">
                                        @if($this->duration_days > 0)
                                            Durasi: {{ $this->duration_days }} Hari
                                        @else
                                            Durasi: Belum Dipilih
                                        @endif
                                    </span>
                                </div>

                                @if(count($cart) === 0)
                                    <div class="p-10 text-center bg-white rounded-2xl border border-dashed border-gray-300">
                                        <div class="w-14 h-14 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-gray-400 mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 mb-1">Keranjang masih kosong</h4>
                                        <p class="text-xs text-gray-500 mb-4">Pilih alat perlengkapan outdoor yang ingin Anda sewa dari katalog.</p>
                                        <button type="button" wire:click="closeCart" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                                            + Pilih Alat Sekarang
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        @foreach($cart as $item)
                                            <div class="p-3.5 bg-white rounded-2xl border border-gray-200/90 shadow-2xs flex items-center gap-3.5 hover:border-gray-300 transition-all">
                                                <!-- Thumbnail 64x64 -->
                                                <div class="w-16 h-16 bg-slate-100 rounded-xl overflow-hidden border border-gray-200/80 shrink-0 flex items-center justify-center">
                                                    @if(isset($item['photo_url']) && $item['photo_url'])
                                                        <img src="{{ asset('storage/' . $item['photo_url']) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    @endif
                                                </div>

                                                <!-- Info Alat -->
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 truncate" title="{{ $item['item_name'] }}">
                                                        {{ $item['item_name'] }}
                                                    </h4>
                                                    <div class="text-xs text-emerald-600 font-bold mt-0.5">
                                                        Rp {{ number_format($item['base_price'], 0, ',', '.') }}<span class="text-gray-400 font-normal">/hari</span>
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 mt-1">
                                                        @if($this->duration_days > 0)
                                                            Subtotal: <strong class="text-slate-800">Rp {{ number_format($item['base_price'] * $item['quantity'] * $this->duration_days, 0, ',', '.') }}</strong> ({{ $this->duration_days }} hr)
                                                        @else
                                                            Subtotal: <strong class="text-slate-800">Rp {{ number_format($item['base_price'] * $item['quantity'], 0, ',', '.') }}</strong>/hari
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Quantity Control & Trash -->
                                                <div class="flex flex-col items-end gap-2 shrink-0">
                                                    <button type="button" 
                                                            wire:click="removeFromCart({{ $item['inventory_item_id'] }})"
                                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                                            title="Hapus barang ini">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>

                                                    <div class="inline-flex items-center bg-slate-100 border border-gray-200 rounded-lg p-0.5">
                                                        <button type="button" 
                                                                wire:click="decreaseQuantity({{ $item['inventory_item_id'] }})"
                                                                class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold text-gray-600 hover:bg-white transition-colors">
                                                            -
                                                        </button>
                                                        <span class="px-2.5 text-xs font-black text-slate-800">
                                                            x{{ $item['quantity'] }}
                                                        </span>
                                                        <button type="button" 
                                                                wire:click="addToCart({{ $item['inventory_item_id'] }})"
                                                                class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold text-emerald-700 hover:bg-white transition-colors">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Add More Items CTA -->
                                    <div class="pt-2 flex items-center justify-between">
                                        <button type="button" 
                                                wire:click="closeCart" 
                                                class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Tambah alat lainnya dari katalog</span>
                                        </button>
                                        <span class="text-xs text-gray-400">Total: {{ $this->totalCartCount }} unit alat</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- TAB 2: JADWAL & DATA PEMESAN -->
                        @if($cartStep === 'form')
                            <div class="space-y-4">
                                <!-- Ringkasan Singkat Pesanan -->
                                <div class="p-3.5 bg-white rounded-2xl border border-gray-200/90 shadow-2xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">{{ count($cart) }} Macam Alat ({{ $this->totalCartCount }} Unit)</div>
                                            @if($this->duration_days > 0)
                                                <div class="text-[11px] text-gray-500">Durasi: <strong class="text-emerald-700">{{ $this->duration_days }} Hari</strong> • Estimasi: <strong class="text-orange-600">Rp {{ number_format($total_price, 0, ',', '.') }}</strong></div>
                                            @else
                                                <div class="text-[11px] text-amber-600 font-semibold">Tentukan tanggal & durasi sewa di bawah</div>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="backToItems" 
                                            class="text-xs font-bold text-emerald-700 hover:underline shrink-0">
                                        Ubah Alat
                                    </button>
                                </div>

                                <!-- Formulir Bagian 1: Jadwal & Jam Pengambilan -->
                                <div class="p-4 bg-white rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5 border-b border-gray-100 pb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Jadwal Pengambilan & Durasi Sewa</span>
                                    </h4>

                                    <!-- Tanggal Ambil & Jam Ambil (Grid 2 Kolom Nyaman) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input 
                                            label="Tanggal Ambil" 
                                            type="date" 
                                            wire:model.live="start_date" 
                                            min="{{ now()->format('Y-m-d') }}" 
                                            required 
                                        />
                                        <flux:input 
                                            label="Jam Pengambilan" 
                                            type="time" 
                                            wire:model.live="pickup_time" 
                                            required 
                                        />
                                    </div>

                                    <!-- Pilihan Cepat Durasi Sewa (User Bisa Langsung Klik) -->
                                    <div>
                                        <label class="text-xs font-bold text-slate-700 block mb-1.5">
                                            Pilih Durasi Sewa (Berapa Hari?) <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach([1, 2, 3, 4, 5, 7] as $days)
                                                <button type="button" 
                                                        wire:click="setDuration({{ $days }})"
                                                        style="background-color: {{ $this->duration_days == $days ? '#059669' : '#f8fafc' }} !important; color: {{ $this->duration_days == $days ? '#ffffff' : '#334155' }} !important; border: 1.5px solid {{ $this->duration_days == $days ? '#059669' : '#cbd5e1' }} !important;"
                                                        class="py-2 px-3.5 rounded-xl text-xs font-bold transition-all hover:border-emerald-500 cursor-pointer shadow-2xs">
                                                    {{ $days == 7 ? '1 Minggu (7 Hr)' : $days . ' Hari' }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Atau Tanggal Kembali Manual -->
                                    <div>
                                        <flux:input 
                                            label="Atau Tentukan Tanggal Kembali Manual:" 
                                            type="date" 
                                            wire:model.live="end_date" 
                                            min="{{ $start_date ?: now()->format('Y-m-d') }}" 
                                        />
                                    </div>

                                    <!-- Indikator Durasi Sewa Terpilih -->
                                    <div>
                                        @if($this->duration_days > 0)
                                            <div class="px-4 py-3 bg-emerald-50 border border-emerald-300 rounded-xl text-xs font-bold text-emerald-800 flex flex-wrap items-center justify-between gap-2 shadow-2xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                                    <span>Durasi Sewa:</span>
                                                    <strong class="text-sm font-black text-emerald-950">{{ $this->duration_days }} Hari</strong>
                                                </div>
                                                <span class="text-xs text-emerald-700 font-bold">
                                                    {{ \Carbon\Carbon::parse($start_date)->format('d M') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-xs font-semibold text-amber-800 flex items-center gap-2 shadow-2xs">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Pilih tanggal ambil dan durasi hari di atas untuk memunculkan total perhitungan sewa.</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Jarak Pemisah Yang Jelas (Generous Gap) Sesuai Permintaan User -->
                                    <div style="margin-top: 26px !important; padding-top: 6px;">
                                        <!-- Alert Toleransi 2 Jam -->
                                        <div class="p-3.5 bg-amber-50/90 border border-amber-200/90 rounded-xl text-xs text-amber-900 flex items-start gap-2.5 shadow-2xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="leading-relaxed text-[11px]">
                                                <strong>Batas Waktu Pengambilan:</strong> Maksimal 2 jam dari jam yang dipilih (maks. pukul <strong class="text-amber-950 underline">{{ \Carbon\Carbon::parse($pickup_time ?: '10:00')->addHours(2)->format('H:i') }} WIB</strong>). Jika tidak diambil dalam jangka waktu tersebut, kasir berhak membatalkan booking dan stok unit dikembalikan ke gudang.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulir Bagian 2: Identitas Penyewa -->
                                <div class="p-4 bg-white rounded-2xl border border-gray-200/90 shadow-2xs space-y-3.5">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5 border-b border-gray-100 pb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Identitas Penyewa</span>
                                    </h4>

                                    <!-- Nama Lengkap -->
                                    <flux:input 
                                        label="Nama Lengkap Sesuai KTP" 
                                        wire:model="name" 
                                        placeholder="Contoh: Budi Santoso" 
                                        icon="user" 
                                        required 
                                    />

                                    <!-- WhatsApp (+62) & NIK KTP (16 Digit) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input 
                                            label="Nomor WhatsApp (+62)" 
                                            type="tel" 
                                            wire:model.live="phone_number" 
                                            maxlength="13" 
                                            placeholder="81234567890" 
                                            icon="phone" 
                                            description="Tanpa angka 0 di awal (maks. 13 digit)" 
                                            required 
                                        />

                                        <flux:input 
                                            label="NIK KTP (16 Digit)" 
                                            type="text" 
                                            wire:model.live="nik" 
                                            maxlength="16" 
                                            placeholder="16 Digit NIK KTP" 
                                            icon="identification" 
                                            description="Tepat 16 digit angka" 
                                            required 
                                        />
                                    </div>

                                    <!-- Alamat Tinggal -->
                                    <flux:textarea 
                                        label="Alamat Tinggal / Domisili" 
                                        wire:model="address" 
                                        rows="2" 
                                        placeholder="Tuliskan alamat lengkap domisili saat ini" 
                                        required 
                                    />
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- Drawer Footer (Fixed, Pinned at Bottom, NEVER CUT OFF) -->
                    <div class="bg-white border-t border-gray-200 p-4 sm:p-5 shrink-0 shadow-lg z-20 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-gray-500 block">Total Biaya Estimasi</span>
                                @if($this->duration_days > 0)
                                    <span class="text-xl sm:text-2xl font-black text-orange-600">
                                        Rp {{ number_format($total_price, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-base sm:text-lg font-black text-slate-700">
                                        Rp {{ number_format($this->subtotal_per_day, 0, ',', '.') }}<span class="text-xs font-normal text-gray-500">/hari</span>
                                    </span>
                                @endif
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                <div>{{ $this->totalCartCount }} barang</div>
                                @if($this->duration_days > 0)
                                    <div class="font-bold text-emerald-700">{{ $this->duration_days }} Hari Sewa</div>
                                @else
                                    <div class="font-bold text-amber-600">Pilih Durasi Sewa</div>
                                @endif
                            </div>
                        </div>

                        <!-- CTA Action Button with Flux -->
                        @if($cartStep === 'items')
                            <flux:button 
                                type="button" 
                                wire:click="proceedToForm" 
                                :disabled="count($cart) === 0" 
                                variant="primary" 
                                icon:trailing="arrow-right" 
                                class="w-full h-12 text-sm font-black justify-center">
                                Lanjut ke Formulir Reservasi
                            </flux:button>
                        @else
                            <flux:button 
                                type="button" 
                                wire:click="submitBooking" 
                                :disabled="count($cart) === 0" 
                                variant="primary" 
                                icon="check" 
                                class="w-full h-12 text-sm font-black justify-center">
                                Konfirmasi Booking Sekarang
                            </flux:button>

                            <div class="text-center pt-1">
                                <flux:button 
                                    type="button" 
                                    wire:click="backToItems" 
                                    variant="ghost" 
                                    class="text-xs font-bold text-gray-500 hover:text-slate-800">
                                    ← Kembali ke Rincian Perlengkapan
                                </flux:button>
                            </div>
                        @endif

                        @error('booking') 
                            <div class="text-center text-xs font-bold text-red-600 bg-red-50 rounded-lg px-2 py-1 border border-red-200">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
