<div class="min-h-screen bg-slate-50 flex flex-col relative"
     x-data="{
         copied: false,
         copyBookingCode(code) {
             if (navigator.clipboard) {
                 navigator.clipboard.writeText(code);
             } else {
                 const el = document.createElement('textarea');
                 el.value = code;
                 document.body.appendChild(el);
                 el.select();
                 document.execCommand('copy');
                 document.body.removeChild(el);
             }
             this.copied = true;
             setTimeout(() => this.copied = false, 2500);
         }
     }"
     @booking-confirmed.window="
         const b = $event.detail.booking;
         try {
             localStorage.setItem('summitgear_active_booking', JSON.stringify(b));
             let history = JSON.parse(localStorage.getItem('summitgear_booking_history') || '[]');
             history = history.filter(item => item.rental_code !== b.rental_code);
             history.unshift(b);
             if (history.length > 10) history = history.slice(0, 10);
             localStorage.setItem('summitgear_booking_history', JSON.stringify(history));
         } catch (e) {
             console.error('Error saving booking to localStorage:', e);
         }
     ">
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200/80 shadow-sm sticky top-0 z-40 backdrop-blur-md bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center transition-transform group-hover:scale-105 shadow-sm" style="background: #e8430a;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 3-9 17h18Z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-[18px] font-extrabold tracking-tight text-[#0f1729] leading-tight block">
                        Summit<span style="color: #e8430a;">Gear</span>
                    </span>
                    <p class="text-[10px] text-gray-400 font-medium hidden sm:block -mt-0.5">Katalog Sewa Online</p>
                </div>
            </a>

            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('home') }}" class="text-xs sm:text-sm font-bold text-gray-500 hover:text-navy transition-colors px-2 py-1">
                    Kembali
                </a>

                <!-- Cart Button with Clean Unified Badge -->
                <button type="button" 
                        wire:click="openCart"
                        @if($this->totalCartCount > 0)
                            style="background-color: #e8430a !important; color: #ffffff !important; border: 1.5px solid #e8430a; box-shadow: 0 4px 14px -3px rgba(232, 67, 10, 0.5);"
                        @else
                            style="background-color: #ffffff !important; color: #334155 !important; border: 1.5px solid #cbd5e1;"
                        @endif
                        class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all transform active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" style="color: {{ $this->totalCartCount > 0 ? '#ffffff' : '#475569' }} !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span style="color: {{ $this->totalCartCount > 0 ? '#ffffff' : '#334155' }} !important; font-weight: 800;">Keranjang</span>
                    @if($this->totalCartCount > 0)
                        <span style="background-color: #ffffff !important; color: #e8430a !important; font-weight: 900; font-size: 11px; padding: 2px 7px; border-radius: 9999px; line-height: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
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
                    <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; min-height: 52px; padding: 8px 16px;" 
                         class="flex items-center gap-3 w-full sm:w-auto rounded-xl shadow-2xs">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wide shrink-0">Tgl Sewa:</span>
                        <input type="date" 
                               wire:model.live="start_date" 
                               min="{{ date('Y-m-d') }}"
                               style="height: 36px; padding: 0 10px; font-size: 12px; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff;"
                               class="text-slate-800 shadow-2xs focus:border-[#e8430a] focus:ring-1 focus:ring-[#e8430a] outline-none">
                        <span class="text-xs text-slate-400 font-bold">-</span>
                        <input type="date" 
                               wire:model.live="end_date" 
                               min="{{ $start_date ?: date('Y-m-d') }}"
                               style="height: 36px; padding: 0 10px; font-size: 12px; font-weight: 600; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff;"
                               class="text-slate-800 shadow-2xs focus:border-[#e8430a] focus:ring-1 focus:ring-[#e8430a] outline-none">
                    </div>

                    <div class="w-full sm:w-auto">
                        <select wire:model.live="selectedCategory" 
                                style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; min-height: 52px; padding: 8px 16px; font-size: 13px; font-weight: 600; color: #1e293b;"
                                class="w-full sm:w-auto rounded-xl shadow-2xs focus:border-[#e8430a] focus:ring-1 focus:ring-[#e8430a] outline-none cursor-pointer">
                            <option value="all">Semua Kategori</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <!-- Active Dates & Duration Indicator -->
            <div style="margin-top: 7px; padding-top: 7px;" class="border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    @if($start_date && $end_date && $this->duration_days > 0)
                        <span>Ketersediaan unit periode: <strong class="text-navy">{{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</strong> ({{ $this->duration_days }} Hari)</span>
                    @else
                        <span>Katalog Alat Outdoor SummitGear — Silakan pilih alat dan atur durasi sewa di formulir keranjang.</span>
                    @endif
                </div>
                @if($this->totalCartCount > 0)
                    <button type="button" wire:click="openCart" class="text-[#e8430a] font-bold hover:underline flex items-center gap-1">
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
                    <div class="absolute top-2.5 left-2.5 right-2.5 flex items-start justify-between gap-1.5 z-10 pointer-events-none">
                        @if($item->available_count > 2)
                            <span class="bg-slate-900/85 backdrop-blur-md text-white text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-md border border-white/20 flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>Sisa <strong class="text-emerald-300 font-black">{{ $item->available_count }}</strong></span>
                            </span>
                        @elseif($item->available_count > 0)
                            <span class="bg-slate-900/90 backdrop-blur-md text-amber-200 text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-md border border-amber-500/30 flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                <span>Sisa <strong class="text-amber-300 font-black">{{ $item->available_count }}</strong></span>
                            </span>
                        @else
                            <span class="bg-rose-900/90 backdrop-blur-md text-rose-100 text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-md border border-rose-500/30 flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                <span>Habis</span>
                            </span>
                        @endif

                        @if($item->cart_quantity > 0)
                            <span class="bg-[#e8430a] text-white text-[11px] font-extrabold px-2.5 py-1 rounded-lg shadow-md border border-orange-400/30 flex items-center gap-1 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $item->cart_quantity }} dipilih</span>
                            </span>
                        @endif
                    </div>

                    <!-- Product Image (Clickable to Add to Cart) -->
                    <div @if($item->available_count > 0) wire:click="addToCart({{ $item->id }})" title="Klik gambar untuk menambah ke keranjang" role="button" tabindex="0" @endif
                         class="w-full h-36 sm:h-40 bg-gray-50 rounded-xl mb-3 overflow-hidden border border-gray-100 flex items-center justify-center relative select-none transition-all {{ $item->available_count > 0 ? 'cursor-pointer hover:opacity-95 group/img' : 'cursor-not-allowed' }}">
                        @if($item->photo_url)
                            <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 pointer-events-none">
                        @else
                            <svg class="h-12 w-12 text-gray-300 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif

                        @if($item->available_count > 0)
                            <!-- Hover Quick-Add Badge Overlay -->
                            <div class="absolute inset-0 bg-black/15 opacity-0 group-hover/img:opacity-100 flex items-center justify-center transition-opacity pointer-events-none">
                                <span class="bg-white/95 backdrop-blur-xs text-slate-800 text-[11px] font-extrabold px-3 py-1.5 rounded-xl shadow-md flex items-center gap-1.5 border border-slate-200 transform group-hover/img:scale-100 scale-90 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#e8430a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Klik Tambah</span>
                                </span>
                            </div>
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
                                        wire:loading.attr="disabled"
                                        wire:target="addToCart({{ $item->id }})"
                                        class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95 cursor-pointer {{ $item->cart_quantity > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-100 hover:bg-emerald-500 hover:text-white text-slate-800' }}">
                                    
                                    <span wire:loading.remove wire:target="addToCart({{ $item->id }})" class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ $item->cart_quantity > 0 ? '+ Tambah Lagi' : '+ Tambah' }}</span>
                                    </span>

                                    <span wire:loading wire:target="addToCart({{ $item->id }})" class="inline-flex items-center gap-1.5">
                                        <svg class="animate-spin h-3.5 w-3.5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Memasukkan...</span>
                                    </span>
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

    <!-- Floating Bottom Bar When Cart Has Items (Solid, Opaque & High-Contrast) -->
    @if($this->totalCartCount > 0 && !$isCartOpen)
        <div class="fixed bottom-5 left-0 right-0 z-40 px-4 flex justify-center pointer-events-none">
            <div style="background-color: #0f172a !important; border: 1.5px solid #334155; box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.75), 0 0 0 1px rgba(255,255,255,0.08);" 
                 class="pointer-events-auto text-white rounded-2xl p-3 sm:px-6 sm:py-3.5 flex items-center justify-between gap-4 w-full max-w-xl animate-fade-in-up">
                
                <div class="flex items-center gap-3.5">
                    <!-- Cart Icon with Badge -->
                    <div class="relative shrink-0">
                        <div style="background-color: #1e293b !important; border: 1px solid #475569;" class="w-11 h-11 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: #ff6b35 !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span style="background-color: #e8430a !important; color: #ffffff !important; border: 2px solid #0f172a !important; font-weight: 900; font-size: 11px;" 
                              class="absolute -top-1.5 -right-1.5 rounded-full min-w-[20px] h-5 px-1 flex items-center justify-center shadow-md">
                            {{ $this->totalCartCount }}
                        </span>
                    </div>

                    <!-- Details: Text & Price -->
                    <div class="min-w-0">
                        <div style="color: #94a3b8 !important; font-size: 11.5px; font-weight: 600;" class="truncate">
                            @if($this->duration_days > 0)
                                Total ({{ $this->totalCartCount }} barang • {{ $this->duration_days }} hr)
                            @else
                                Estimasi ({{ $this->totalCartCount }} barang)
                            @endif
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <div style="color: #fbbf24 !important; font-weight: 900; line-height: 1.2;" class="text-base sm:text-xl font-black tabular-nums">
                                Rp {{ number_format($this->estimated_total_price, 0, ',', '.') }}
                            </div>
                            @if($this->duration_days <= 0)
                                <span style="color: #fde68a !important; font-size: 11px; font-weight: 700;">/ hari</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <button type="button" 
                        wire:click="openCart" 
                        style="background-color: #e8430a !important; color: #ffffff !important; box-shadow: 0 4px 14px rgba(232, 67, 10, 0.45); font-weight: 800;"
                        class="text-xs sm:text-sm py-2.5 px-4 sm:px-5 rounded-xl transition-all transform active:scale-95 flex items-center gap-2 hover:brightness-110 shrink-0">
                    <span>Lihat & Reservasi</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: #ffffff !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
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
                                <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#e8430a] flex items-center justify-center shrink-0 border border-orange-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base sm:text-lg font-black text-slate-800 leading-tight">Keranjang Sewa</h2>
                                        <span class="bg-[#e8430a] text-white text-xs font-black px-2 py-0.5 rounded-full">
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
                        <div class="flex items-center px-5 pb-3 gap-1.5 text-xs font-bold">
                            <button type="button" 
                                    wire:click="setStep('items')"
                                    @if($activeRentalId) disabled @endif
                                    style="background-color: {{ $cartStep === 'items' ? '#0f172a' : '#f1f5f9' }} !important; color: {{ $cartStep === 'items' ? '#ffffff' : '#334155' }} !important; border: 1px solid {{ $cartStep === 'items' ? '#0f172a' : '#cbd5e1' }} !important;"
                                    class="flex-1 py-2 px-2 rounded-xl flex items-center justify-center gap-1 transition-all shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span style="color: {{ $cartStep === 'items' ? '#ffffff' : '#334155' }} !important; font-weight: 800;" class="truncate">1. Alat ({{ count($cart) }})</span>
                            </button>
                            <button type="button" 
                                    wire:click="setStep('form')"
                                    @if($activeRentalId) disabled @endif
                                    style="background-color: {{ $cartStep === 'form' ? '#0f172a' : '#f1f5f9' }} !important; color: {{ $cartStep === 'form' ? '#ffffff' : '#334155' }} !important; border: 1px solid {{ $cartStep === 'form' ? '#0f172a' : '#cbd5e1' }} !important;"
                                    class="flex-1 py-2 px-2 rounded-xl flex items-center justify-center gap-1 transition-all shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span style="color: {{ $cartStep === 'form' ? '#ffffff' : '#334155' }} !important; font-weight: 800;" class="truncate">2. Jadwal</span>
                            </button>
                            <button type="button" 
                                    @if(!$activeRentalId) disabled @endif
                                    style="background-color: {{ $cartStep === 'payment' ? '#0f172a' : '#f1f5f9' }} !important; color: {{ $cartStep === 'payment' ? '#ffffff' : '#334155' }} !important; border: 1px solid {{ $cartStep === 'payment' ? '#0f172a' : '#cbd5e1' }} !important;"
                                    class="flex-1 py-2 px-2 rounded-xl flex items-center justify-center gap-1 transition-all shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span style="color: {{ $cartStep === 'payment' ? '#ffffff' : '#334155' }} !important; font-weight: 800;" class="truncate">3. Bayar / DP</span>
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
                                    <div class="p-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 flex flex-col items-center justify-center text-center">
                                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 bg-white border border-slate-200/80 shadow-xs text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                        </div>
                                        <p class="text-[13.5px] font-bold text-[#101F42]">Keranjang Masih Kosong</p>
                                        <p class="text-[11.5px] text-slate-400 mt-1 max-w-[260px] leading-relaxed">Pilih perlengkapan camping dari katalog untuk dimasukkan ke sewa</p>
                                        <button type="button" wire:click="closeCart" class="mt-4 px-4 py-2 bg-[#101F42] hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                            + Pilih Alat Sekarang
                                        </button>
                                    </div>
                                @else
                                    <!-- Daftar Alat Sesuai Layout Keranjang Kasir -->
                                    <div class="flex flex-col" style="display: flex; flex-direction: column; gap: 14px;">
                                        @foreach($cart as $item)
                                            <div wire:key="cart-item-{{ $item['inventory_item_id'] }}"
                                                 class="group transition-all bg-white hover:bg-slate-50/70 border border-slate-200/90 hover:border-slate-300 rounded-2xl shadow-xs"
                                                 style="display: flex; align-items: flex-start; gap: 14px; padding: 14px;">
                                                
                                                <!-- Thumbnail 52x52 (Mirip Kasir POS) -->
                                                <div class="rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200/80 mt-0.5 flex items-center justify-center"
                                                     style="width: 52px; height: 52px; min-width: 52px;">
                                                    @if(isset($item['photo_url']) && $item['photo_url'])
                                                        <img src="{{ asset('storage/' . $item['photo_url']) }}"
                                                             class="object-cover"
                                                             style="width: 52px; height: 52px; max-width: 52px; max-height: 52px;"
                                                             alt="{{ $item['item_name'] }}">
                                                    @else
                                                        <div class="flex items-center justify-center text-slate-300" style="width: 52px; height: 52px;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Info Alat -->
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h5 class="text-[14px] font-bold text-[#101F42] truncate leading-snug" title="{{ $item['item_name'] }}">
                                                            {{ $item['item_name'] }}
                                                        </h5>
                                                        @if($item['quantity'] > 1)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-black bg-[#101F42] text-white shadow-2xs">
                                                                x{{ $item['quantity'] }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if(!empty($item['category']))
                                                        <div class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                                            {{ $item['category'] }}
                                                        </div>
                                                    @endif

                                                    <!-- Harga Total / Satuan -->
                                                    <div class="flex items-baseline gap-1.5" style="margin-top: 5px;">
                                                        <span class="text-[13.5px] font-extrabold text-[#101F42] tabular-nums">
                                                            Rp {{ number_format($item['base_price'] * $item['quantity'], 0, ',', '.') }}<span class="text-[11px] font-semibold text-slate-400">/hari</span>
                                                        </span>
                                                        @if($item['quantity'] > 1)
                                                            <span class="text-[11px] font-medium text-slate-400 tabular-nums">
                                                                ({{ $item['quantity'] }} &times; Rp {{ number_format($item['base_price'], 0, ',', '.') }})
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($this->duration_days > 0)
                                                        <div class="text-[11px] font-medium text-emerald-700 mt-1">
                                                            Subtotal ({{ $this->duration_days }} hr): <strong class="font-extrabold">Rp {{ number_format($item['base_price'] * $item['quantity'] * $this->duration_days, 0, ',', '.') }}</strong>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Quantity Stepper & Remove Action (Mirip Kasir POS) -->
                                                <div class="flex flex-col items-end shrink-0" style="gap: 12px;">
                                                    <!-- Remove Button -->
                                                    <button type="button" wire:click="removeFromCart({{ $item['inventory_item_id'] }})"
                                                            class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                                            title="Hapus {{ $item['item_name'] }} dari keranjang">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>

                                                    <!-- Stepper: Minus, Count, Plus -->
                                                    <div class="flex items-center border border-slate-200 rounded-lg bg-slate-50/80 p-0.5 shadow-2xs">
                                                        <button type="button" wire:click="decreaseQuantity({{ $item['inventory_item_id'] }})"
                                                                class="w-6 h-6 flex items-center justify-center rounded text-slate-600 hover:text-white hover:bg-slate-700 active:scale-95 transition-all cursor-pointer"
                                                                title="Kurangi 1 unit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                                            </svg>
                                                        </button>
                                                        <span class="min-w-6 text-center text-[12.5px] font-black text-[#101F42] tabular-nums select-none px-1">
                                                            {{ $item['quantity'] }}
                                                        </span>
                                                        <button type="button" wire:click="addToCart({{ $item['inventory_item_id'] }})"
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

                                    <!-- Summary Inset Card (Mirip Struk Kasir POS) -->
                                    <div class="p-4 rounded-2xl bg-gradient-to-b from-slate-50 to-slate-100/70 border border-slate-200/80 shadow-xs space-y-3 mt-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11.5px] font-extrabold uppercase tracking-wider text-slate-400">Rincian Perhitungan</span>
                                            <span class="text-[11px] font-semibold text-slate-400">Ringkasan Biaya</span>
                                        </div>

                                        <!-- Subtotal row -->
                                        <div class="flex justify-between items-center text-[13px] pt-0.5">
                                            <span class="font-medium text-slate-500">Subtotal ({{ $this->totalCartCount }} alat &times; {{ $this->duration_days > 0 ? $this->duration_days : 1 }} hari)</span>
                                            <span class="font-bold text-[#101F42] tabular-nums">Rp {{ number_format($this->duration_days > 0 ? $total_price : $this->subtotal_per_day, 0, ',', '.') }}</span>
                                        </div>

                                        <!-- Divider & Total -->
                                        <div class="border-t border-dashed border-slate-200 pt-3 flex justify-between items-end">
                                            <div>
                                                <span class="text-[11.5px] font-extrabold uppercase tracking-wider text-slate-400 block leading-none">Total Tagihan Estimasi</span>
                                                <span class="text-[10px] text-slate-400 font-medium mt-1 block">
                                                    {{ $this->duration_days > 0 ? $this->duration_days . ' hari masa sewa' : 'Pilih tanggal di tahap berikutnya' }}
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[22px] font-black tabular-nums tracking-tight text-emerald-600 block leading-none">
                                                    Rp {{ number_format($this->duration_days > 0 ? $total_price : $this->subtotal_per_day, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add More Items CTA -->
                                    <div class="pt-3 flex items-center justify-between">
                                        <button type="button" 
                                                wire:click="closeCart" 
                                                class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline flex items-center gap-1 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Tambah alat lainnya dari katalog</span>
                                        </button>
                                        <span class="text-xs text-slate-400 font-semibold">{{ $this->totalCartCount }} unit alat</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- TAB 2: JADWAL & DATA PEMESAN -->
                        @if($cartStep === 'form')
                            <div class="space-y-4">
                                <!-- Error Limit Booking Anti-Hoarding -->
                                @error('booking_limit')
                                    <div class="p-4 bg-rose-50 border-2 border-rose-300 rounded-2xl text-xs text-rose-900 font-bold flex items-start gap-3 shadow-xs">
                                        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-black text-sm">
                                            ⚠️
                                        </div>
                                        <div>
                                            <span class="block font-black text-sm text-rose-950 mb-0.5">Batas Booking Aktif Tercapai</span>
                                            <span class="leading-relaxed font-semibold">{{ $message }}</span>
                                        </div>
                                    </div>
                                @enderror

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

                                    <!-- Tanggal Ambil & Jam Ambil (Batas Jelas & Kontras) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="text-xs font-bold text-slate-800 flex items-center justify-between gap-2 mb-1.5">
                                                <span class="flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>Tanggal Ambil</span>
                                                    <span class="text-red-500">*</span>
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium">Mulai sewa</span>
                                            </label>
                                            <div class="relative">
                                                <input 
                                                    type="date" 
                                                    wire:model.live="start_date" 
                                                    min="{{ now()->format('Y-m-d') }}" 
                                                    required 
                                                    style="border: 2px solid #cbd5e1 !important; background-color: #f8fafc !important; color: #0f1729 !important;"
                                                    class="w-full h-11 px-3.5 rounded-xl text-xs sm:text-sm font-bold shadow-2xs transition-all focus:border-[#e8430a] focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-500/15 cursor-pointer"
                                                    onfocus="this.style.borderColor='#e8430a'; this.style.backgroundColor='#ffffff';"
                                                    onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc';"
                                                />
                                            </div>
                                            @error('start_date')
                                                <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-800 flex items-center justify-between gap-2 mb-1.5">
                                                <span class="flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Jam Pengambilan</span>
                                                    <span class="text-red-500">*</span>
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium">Buka 08:00 - 20:00 WIB</span>
                                            </label>
                                            <div class="relative">
                                                <input 
                                                    type="time" 
                                                    wire:model.live="pickup_time" 
                                                    required 
                                                    style="border: 2px solid #cbd5e1 !important; background-color: #f8fafc !important; color: #0f1729 !important;"
                                                    class="w-full h-11 px-3.5 rounded-xl text-xs sm:text-sm font-bold shadow-2xs transition-all focus:border-[#e8430a] focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-500/15 cursor-pointer"
                                                    onfocus="this.style.borderColor='#e8430a'; this.style.backgroundColor='#ffffff';"
                                                    onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc';"
                                                />
                                            </div>
                                            @error('pickup_time')
                                                <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                        </div>
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
                                        <label class="text-xs font-bold text-slate-800 flex items-center justify-between gap-2 mb-1.5">
                                            <span class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>Atau Tentukan Tanggal Kembali Manual</span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-medium">Otomatis hitung durasi</span>
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="date" 
                                                wire:model.live="end_date" 
                                                min="{{ $start_date ?: now()->format('Y-m-d') }}" 
                                                style="border: 2px solid #cbd5e1 !important; background-color: #f8fafc !important; color: #0f1729 !important;"
                                                class="w-full h-11 px-3.5 rounded-xl text-xs sm:text-sm font-bold shadow-2xs transition-all focus:border-[#e8430a] focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-500/15 cursor-pointer"
                                                onfocus="this.style.borderColor='#e8430a'; this.style.backgroundColor='#ffffff';"
                                                onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc';"
                                            />
                                        </div>
                                        @error('end_date')
                                            <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @enderror
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
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <label class="text-xs font-bold text-slate-800">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-gray-400 font-medium">Sesuai KTP</span>
                                        </div>
                                        <flux:input 
                                            wire:model="name" 
                                            placeholder="Contoh: Budi Santoso" 
                                            icon="user" 
                                            required 
                                        />
                                        @error('name')
                                            <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Email Aktif (Untuk Pengiriman Invoice & Notifikasi) -->
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <label class="text-xs font-bold text-slate-800">Alamat Email <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-gray-400 font-medium">Invoice & bukti booking dikirim ke sini</span>
                                        </div>
                                        <flux:input 
                                            type="email"
                                            wire:model="email" 
                                            placeholder="nama@email.com" 
                                            icon="envelope" 
                                            required 
                                        />
                                        @error('email')
                                            <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- WhatsApp (+62) & NIK KTP (16 Digit) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <label class="text-xs font-bold text-slate-800">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                                <span class="text-[11px] text-gray-400 font-medium">Tanpa 0 (maks. 13 digit)</span>
                                            </div>
                                            <flux:input 
                                                type="tel" 
                                                wire:model.live="phone_number" 
                                                maxlength="16" 
                                                placeholder="+62 812-3456-7890" 
                                                icon="phone" 
                                                required 
                                            />
                                            @error('phone_number')
                                                <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <label class="text-xs font-bold text-slate-800">NIK KTP <span class="text-red-500">*</span></label>
                                                <span class="text-[11px] text-gray-400 font-medium">Tepat 16 digit</span>
                                            </div>
                                            <flux:input 
                                                type="text" 
                                                wire:model.live="nik" 
                                                maxlength="16" 
                                                placeholder="16 Digit NIK KTP" 
                                                icon="identification" 
                                                required 
                                            />
                                            @error('nik')
                                                <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Alamat Tinggal -->
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <label class="text-xs font-bold text-slate-800">Alamat Tinggal / Domisili <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-gray-400 font-medium">Alamat domisili saat ini</span>
                                        </div>
                                        <flux:textarea 
                                            wire:model="address" 
                                            rows="2" 
                                            placeholder="Tuliskan alamat lengkap domisili saat ini" 
                                            required 
                                        />
                                        @error('address')
                                            <p class="text-[11px] font-bold text-red-600 mt-1 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- TAB 3: PEMBAYARAN / DP ONLINE (HOLD STOK 10 MENIT) -->
                        @if($cartStep === 'payment')
                            <div class="space-y-4" wire:poll.5s="checkBookingStatus">
                                
                                <!-- Countdown Timer Box (Alpine.js Real-time Countdown) -->
                                <div x-data="{
                                        timeLeft: @entangle('remainingSeconds'),
                                        timer: null,
                                        formatTime(sec) {
                                            const m = Math.floor(Math.max(0, sec) / 60).toString().padStart(2, '0');
                                            const s = (Math.max(0, sec) % 60).toString().padStart(2, '0');
                                            return `${m}:${s}`;
                                        },
                                        init() {
                                            this.timer = setInterval(() => {
                                                if (this.timeLeft > 0) {
                                                    this.timeLeft--;
                                                } else {
                                                    clearInterval(this.timer);
                                                    $wire.handleExpiredBooking();
                                                }
                                            }, 1000);
                                        }
                                     }"
                                     class="p-4 rounded-2xl bg-amber-500/10 border-2 border-amber-500/30 shadow-xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black shadow-sm shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-black uppercase tracking-wider text-amber-900 block leading-tight">Sisa Waktu Pembayaran</span>
                                            <span class="text-[11.5px] text-amber-800 font-medium">Stok unit dikunci untuk Anda</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-mono font-black text-2xl sm:text-3xl text-amber-950 tracking-tight" x-text="formatTime(timeLeft)">
                                            10:00
                                        </div>
                                        <span class="text-[10px] text-amber-700 font-bold block">menit : detik</span>
                                    </div>
                                </div>

                                <!-- Booking Code Info -->
                                <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between text-xs shadow-2xs">
                                    <span class="text-slate-500 font-semibold">Kode Reservasi:</span>
                                    <span class="font-mono font-black text-[#0f172a] bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                        {{ $activeRentalCode }}
                                    </span>
                                </div>

                                <!-- Skema Pembayaran: DP vs Lunas -->
                                <div>
                                    <label class="text-xs font-black text-slate-800 block mb-2">Pilih Skema Pembayaran:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        
                                        <!-- Opsi DP 30% -->
                                        <button type="button" 
                                                wire:click="setPaymentOption('dp')" 
                                                style="border: 2px solid {{ $paymentOption === 'dp' ? '#0f172a' : '#cbd5e1' }} !important; background-color: {{ $paymentOption === 'dp' ? '#f8fafc' : '#ffffff' }} !important;"
                                                class="p-3 rounded-2xl text-left transition-all shadow-2xs cursor-pointer relative overflow-hidden">
                                            @if($paymentOption === 'dp')
                                                <div class="absolute top-0 right-0 bg-[#0f172a] text-white text-[9px] font-black px-2 py-0.5 rounded-bl-lg">
                                                    DIPILIH
                                                </div>
                                            @endif
                                            <span class="text-xs font-bold text-slate-700 block">Uang Muka (DP 30%)</span>
                                            <span class="text-lg font-black text-emerald-600 block mt-0.5 tabular-nums">
                                                Rp {{ number_format($this->downPaymentAmount, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10.5px] text-slate-500 block mt-1 leading-tight">
                                                Sisa <strong>Rp {{ number_format($this->remainingBalance, 0, ',', '.') }}</strong> dilunasi di outlet saat ambil barang
                                            </span>
                                        </button>

                                        <!-- Opsi Pelunasan 100% -->
                                        <button type="button" 
                                                wire:click="setPaymentOption('full')" 
                                                style="border: 2px solid {{ $paymentOption === 'full' ? '#0f172a' : '#cbd5e1' }} !important; background-color: {{ $paymentOption === 'full' ? '#f8fafc' : '#ffffff' }} !important;"
                                                class="p-3 rounded-2xl text-left transition-all shadow-2xs cursor-pointer relative overflow-hidden">
                                            @if($paymentOption === 'full')
                                                <div class="absolute top-0 right-0 bg-[#0f172a] text-white text-[9px] font-black px-2 py-0.5 rounded-bl-lg">
                                                    DIPILIH
                                                </div>
                                            @endif
                                            <span class="text-xs font-bold text-slate-700 block">Pelunasan Penuh (100%)</span>
                                            <span class="text-lg font-black text-slate-900 block mt-0.5 tabular-nums">
                                                Rp {{ number_format($total_price, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10.5px] text-slate-500 block mt-1 leading-tight">
                                                Langsung lunas. Di outlet tinggal verifikasi KTP & ambil barang
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div>
                                    <label class="text-xs font-black text-slate-800 block mb-2">Pilih Metode Pembayaran:</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" 
                                                wire:click="setPaymentMethod('qris')"
                                                style="border: 1.5px solid {{ $selectedPaymentMethod === 'qris' ? '#e8430a' : '#cbd5e1' }} !important; background-color: {{ $selectedPaymentMethod === 'qris' ? '#fff7ed' : '#ffffff' }} !important;"
                                                class="p-2.5 rounded-xl text-center cursor-pointer transition-all shadow-2xs">
                                            <span class="text-xs font-black text-slate-900 block">QRIS</span>
                                            <span class="text-[10px] text-slate-500 font-semibold block">Semua Dompet</span>
                                        </button>
                                        <button type="button" 
                                                wire:click="setPaymentMethod('gopay')"
                                                style="border: 1.5px solid {{ $selectedPaymentMethod === 'gopay' ? '#e8430a' : '#cbd5e1' }} !important; background-color: {{ $selectedPaymentMethod === 'gopay' ? '#fff7ed' : '#ffffff' }} !important;"
                                                class="p-2.5 rounded-xl text-center cursor-pointer transition-all shadow-2xs">
                                            <span class="text-xs font-black text-slate-900 block">GoPay / OVO</span>
                                            <span class="text-[10px] text-slate-500 font-semibold block">E-Wallet</span>
                                        </button>
                                        <button type="button" 
                                                wire:click="setPaymentMethod('bca_va')"
                                                style="border: 1.5px solid {{ $selectedPaymentMethod === 'bca_va' ? '#e8430a' : '#cbd5e1' }} !important; background-color: {{ $selectedPaymentMethod === 'bca_va' ? '#fff7ed' : '#ffffff' }} !important;"
                                                class="p-2.5 rounded-xl text-center cursor-pointer transition-all shadow-2xs">
                                            <span class="text-xs font-black text-slate-900 block">BCA VA</span>
                                            <span class="text-[10px] text-slate-500 font-semibold block">Virtual Account</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Box QRIS Display / Bank VA -->
                                <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center shadow-xs space-y-3">
                                    @if($selectedPaymentMethod === 'qris')
                                        <div class="inline-block bg-red-600 text-white text-[10px] font-black px-3 py-0.5 rounded tracking-widest uppercase">
                                            QRIS Standar Pembayaran Nasional
                                        </div>
                                        <div class="w-40 h-40 bg-white p-2 border border-slate-300 rounded-xl mx-auto flex items-center justify-center shadow-inner relative">
                                            <svg viewBox="0 0 100 100" class="w-full h-full text-slate-900">
                                                <rect x="5" y="5" width="25" height="25" fill="none" stroke="currentColor" stroke-width="4"/>
                                                <rect x="11" y="11" width="13" height="13" fill="currentColor"/>
                                                <rect x="70" y="5" width="25" height="25" fill="none" stroke="currentColor" stroke-width="4"/>
                                                <rect x="76" y="11" width="13" height="13" fill="currentColor"/>
                                                <rect x="5" y="70" width="25" height="25" fill="none" stroke="currentColor" stroke-width="4"/>
                                                <rect x="11" y="76" width="13" height="13" fill="currentColor"/>
                                                <rect x="36" y="8" width="6" height="6" fill="currentColor"/>
                                                <rect x="46" y="14" width="6" height="6" fill="currentColor"/>
                                                <rect x="56" y="8" width="6" height="6" fill="currentColor"/>
                                                <rect x="36" y="24" width="6" height="6" fill="currentColor"/>
                                                <rect x="46" y="30" width="6" height="6" fill="currentColor"/>
                                                <rect x="10" y="38" width="6" height="6" fill="currentColor"/>
                                                <rect x="20" y="44" width="6" height="6" fill="currentColor"/>
                                                <rect x="74" y="38" width="6" height="6" fill="currentColor"/>
                                                <rect x="84" y="44" width="6" height="6" fill="currentColor"/>
                                                <rect x="37" y="38" width="26" height="24" rx="4" fill="#e8430a"/>
                                                <text x="50" y="53" fill="#ffffff" font-size="9" font-weight="900" text-anchor="middle">SG</text>
                                                <rect x="36" y="68" width="6" height="6" fill="currentColor"/>
                                                <rect x="46" y="74" width="6" height="6" fill="currentColor"/>
                                                <rect x="56" y="68" width="6" height="6" fill="currentColor"/>
                                                <rect x="68" y="74" width="6" height="6" fill="currentColor"/>
                                                <rect x="78" y="68" width="6" height="6" fill="currentColor"/>
                                                <rect x="88" y="78" width="6" height="6" fill="currentColor"/>
                                            </svg>
                                        </div>
                                        <p class="text-[11px] font-bold text-slate-700">Scan QRIS menggunakan BCA, Mandiri, GoPay, OVO, ShopeePay, DANA</p>
                                    @elseif($selectedPaymentMethod === 'gopay')
                                        <div class="py-4 space-y-2">
                                            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto text-xl font-black border border-sky-200">
                                                📱
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-800">E-Wallet Direct Payment</h4>
                                            <p class="text-[11px] text-slate-500">Nomor Ponsel: <strong>{{ $phone }}</strong></p>
                                        </div>
                                    @else
                                        <div class="py-4 space-y-2">
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto text-xl font-black border border-indigo-200">
                                                🏦
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-800">Nomor Virtual Account BCA</h4>
                                            <p class="text-sm font-mono font-black text-slate-900 bg-slate-100 py-1.5 px-3 rounded-lg inline-block">
                                                8801 0812 3456 7890
                                            </p>
                                        </div>
                                    @endif

                                    <div class="py-2 px-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 font-semibold">Nominal Wajib Bayar:</span>
                                        <span class="text-base font-black text-emerald-600 tabular-nums">
                                            Rp {{ number_format($this->payableAmount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons in Payment Body -->
                                <div class="flex items-center gap-2 pt-1">
                                    <button type="button" 
                                            wire:click="checkBookingStatus"
                                            class="flex-1 py-2 px-3 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all cursor-pointer">
                                        Cek Status
                                    </button>
                                    <button type="button" 
                                            wire:click="cancelActiveBooking"
                                            wire:confirm="Yakin ingin membatalkan pesanan ini? Unit barang akan langsung dikembalikan ke katalog."
                                            class="py-2 px-3 rounded-xl border border-red-200 bg-red-50 text-xs font-bold text-red-700 hover:bg-red-100 transition-all cursor-pointer">
                                        Batalkan Pesanan
                                    </button>
                                </div>

                            </div>
                        @endif

                    </div>

                    <!-- Drawer Footer (Fixed, Pinned at Bottom, NEVER CUT OFF) -->
                    <div class="bg-white border-t border-gray-200 p-4 sm:p-5 shrink-0 shadow-lg z-20 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-gray-500 block">
                                    {{ $cartStep === 'payment' ? 'Tagihan Pembayaran Saat Ini' : 'Total Biaya Estimasi' }}
                                </span>
                                @if($cartStep === 'payment')
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600">
                                        Rp {{ number_format($this->payableAmount, 0, ',', '.') }}
                                    </span>
                                @elseif($this->duration_days > 0)
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
                                @if($cartStep === 'payment')
                                    <div class="font-bold text-slate-900">{{ strtoupper($selectedPaymentMethod) }}</div>
                                    <div class="font-bold {{ $paymentOption === 'dp' ? 'text-blue-700' : 'text-emerald-700' }}">
                                        {{ $paymentOption === 'dp' ? 'Uang Muka (DP 30%)' : 'Pelunasan 100%' }}
                                    </div>
                                @else
                                    <div>{{ $this->totalCartCount }} barang</div>
                                    @if($this->duration_days > 0)
                                        <div class="font-bold text-emerald-700">{{ $this->duration_days }} Hari Sewa</div>
                                    @else
                                        <div class="font-bold text-amber-600">Pilih Durasi Sewa</div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Error Alert Banner in Footer -->
                        @if($errors->any())
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-semibold space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-red-800">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Mohon periksa kembali:</span>
                                </div>
                                <ul class="list-disc list-inside text-[11px] text-red-600 pl-1 space-y-0.5">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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
                        @elseif($cartStep === 'form')
                            <button 
                                type="button" 
                                wire:click="submitBooking" 
                                wire:loading.attr="disabled"
                                :disabled="count($cart) === 0" 
                                style="background-color: #e8430a !important; color: #ffffff !important; box-shadow: 0 4px 14px -3px rgba(232, 67, 10, 0.5);"
                                class="w-full h-12 rounded-xl text-sm font-black flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-98 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <span>Lanjut ke Pembayaran / DP (Hold 10 Mnt)</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </span>
                                <span wire:loading.flex wire:target="submitBooking" class="items-center gap-2" style="display: none;">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Mengunci Unit (ACID Lock)...</span>
                                </span>
                            </button>

                            <div class="text-center pt-1">
                                <flux:button 
                                    type="button" 
                                    wire:click="backToItems" 
                                    variant="ghost" 
                                    class="text-xs font-bold text-gray-500 hover:text-slate-800">
                                    ← Kembali ke Rincian Perlengkapan
                                </flux:button>
                            </div>
                        @elseif($cartStep === 'payment')
                            <button 
                                type="button" 
                                wire:click="confirmOnlinePayment" 
                                wire:loading.attr="disabled"
                                style="background-color: #059669 !important; color: #ffffff !important; box-shadow: 0 4px 14px -3px rgba(5, 150, 105, 0.5);"
                                class="w-full h-12 rounded-xl text-sm font-black flex items-center justify-center gap-2 transition-all hover:brightness-110 active:scale-98 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="confirmOnlinePayment" class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Konfirmasi Pembayaran Berhasil (Simulasi)</span>
                                </span>
                                <span wire:loading.flex wire:target="confirmOnlinePayment" class="items-center gap-2" style="display: none;">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memverifikasi Transaksi ACID...</span>
                                </span>
                            </button>
                        @endif

                        @error('cart') 
                            <div class="text-center text-xs font-bold text-red-600 bg-red-50 rounded-lg px-2 py-1.5 border border-red-200">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('booking') 
                            <div class="text-center text-xs font-bold text-red-600 bg-red-50 rounded-lg px-2 py-1.5 border border-red-200">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- ===================================================================== --}}
    {{-- MODAL KONFIRMASI BOOKING SUKSES (Clean, Reassuring, Premium Design)   --}}
    {{-- ===================================================================== --}}
    @if($showSuccessModal && $confirmedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 sm:py-8 overflow-y-auto">
            
            {{-- Backdrop Blur --}}
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm transition-opacity" wire:click="closeSuccessModal"></div>

            {{-- Modal Content Card (Spacious, Scrollable, Never Squeezed) --}}
            <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 pb-8 sm:pb-9 z-10 text-center my-auto animate-in fade-in zoom-in-95 duration-200">
                
                {{-- Close Button (Top Right) --}}
                <button type="button" 
                        wire:click="closeSuccessModal" 
                        class="absolute top-4 right-4 sm:top-5 sm:right-5 p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer z-20"
                        title="Tutup Modal"
                        aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                {{-- Decorative background glow --}}
                <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 rounded-full bg-orange-500/10 blur-2xl pointer-events-none"></div>

                {{-- Success Icon Badge --}}
                <div class="mx-auto w-16 h-16 rounded-2xl bg-emerald-50 border-2 border-emerald-100 flex items-center justify-center shadow-inner mb-4 relative">
                    <span class="absolute inset-0 rounded-2xl bg-emerald-400/20 animate-ping"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                {{-- Main Header --}}
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100/80 text-emerald-800 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                    Booking Berhasil Dikonfirmasi
                </span>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-2">
                    Pesanan Kamu Sudah Kami Terima! 🎉
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-5">
                    Terima kasih, <strong class="text-slate-900">{{ $confirmedBooking['customer_name'] }}</strong>! Pesanan sewa alat outdoor kamu telah berhasil masuk ke sistem kami. Silakan datang ke toko <strong class="text-slate-900">SummitGear</strong> sesuai waktu pengambilan barang booking yang telah kamu pilih.
                </p>

                {{-- Ticket Details Box --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 sm:p-5 text-left mb-4 space-y-3.5 shadow-2xs">
                    
                    {{-- Kode Booking with Copy Button --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                        <div>
                            <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block">Kode Booking</span>
                            <span class="text-base sm:text-lg font-black font-mono tracking-wider text-[#0f1729]">
                                {{ $confirmedBooking['rental_code'] }}
                            </span>
                        </div>
                        <button type="button" 
                                @click="copyBookingCode('{{ $confirmedBooking['rental_code'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                :class="copied ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:border-[#e8430a] hover:text-[#e8430a]'">
                            <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="copied ? 'Tersalin!' : 'Salin Kode'"></span>
                        </button>
                    </div>

                    {{-- Schedule Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Jadwal Pengambilan</span>
                            <span class="font-extrabold text-slate-800 text-xs">
                                {{ $confirmedBooking['pickup_formatted'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Batas Waktu Toleransi</span>
                            <span class="font-extrabold text-amber-700 text-xs">
                                Maks. {{ $confirmedBooking['tolerance_formatted'] }} (2 Jam)
                            </span>
                        </div>
                    </div>

                    {{-- Total Price & Payment Method --}}
                    <div class="pt-2 border-t border-slate-200/60 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Biaya Sewa</span>
                            <span class="text-sm sm:text-base font-black text-slate-900">
                                {{ $confirmedBooking['total_formatted'] }}
                            </span>
                        </div>
                        @if(($confirmedBooking['payment_type'] ?? '') === 'dp')
                            <div class="flex items-center justify-between text-emerald-800 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200">
                                <span>DP Terbayar (30% via {{ $confirmedBooking['payment_method'] ?? 'ONLINE' }}):</span>
                                <span class="font-black text-emerald-900">Rp {{ number_format($confirmedBooking['down_payment_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-amber-900 font-bold bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-200">
                                <span>Sisa Pelunasan di Toko:</span>
                                <span class="font-black text-amber-950">Rp {{ number_format($confirmedBooking['balance_due'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-emerald-800 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200">
                                <span>Status Pembayaran:</span>
                                <span class="font-black text-emerald-900">LUNAS (100% via {{ $confirmedBooking['payment_method'] ?? 'ONLINE' }})</span>
                            </div>
                        @endif
                    </div>

                    {{-- Items Summary --}}
                    @if(!empty($confirmedBooking['items']))
                        <div class="pt-2 border-t border-slate-200/60">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1.5">
                                Daftar Perlengkapan ({{ count($confirmedBooking['items']) }} Jenis Alat)
                            </span>
                            <div class="max-h-28 overflow-y-auto space-y-1.5 pr-1 text-xs">
                                @foreach($confirmedBooking['items'] as $it)
                                    <div class="flex items-center justify-between py-1 px-2 rounded-lg bg-white border border-slate-100 text-slate-700">
                                        <span class="truncate font-semibold">{{ $it['name'] }}</span>
                                        <span class="font-black text-slate-900 shrink-0 ml-2">x{{ $it['quantity'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Important Notice --}}
                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-left text-amber-900 text-xs mb-5 flex items-start gap-2.5 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="leading-relaxed text-[11px]">
                        <strong>Petunjuk Pengambilan:</strong> Silakan datang ke toko SummitGear dengan membawa <strong>KTP fisik asli</strong> dan tunjukkan <strong>Kode Booking</strong> di atas kepada kasir untuk verifikasi unit dan pengambilan barang.
                    </div>
                </div>

                {{-- Action Buttons (Generous Spacing & Comfortable Layout) --}}
                <div class="flex flex-col gap-3.5 pt-1">
                    <button type="button" 
                            wire:click="closeSuccessModal"
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-5 rounded-2xl text-sm font-black text-white transition-all shadow-md hover:brightness-110 active:scale-[0.99] cursor-pointer"
                            style="background: #e8430a; box-shadow: 0 4px 14px -3px rgba(232, 67, 10, 0.4);">
                        <span>Kembali ke Beranda</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    @php
                        $waPhone = config('services.whatsapp.admin_phone', '6281234567890');
                        $waText = urlencode("Halo SummitGear, saya sudah booking melalui web dengan Kode: " . ($confirmedBooking['rental_code'] ?? '') . " atas nama " . ($confirmedBooking['customer_name'] ?? '') . ". Mohon dipersiapkan barangnya. Terima kasih!");
                    @endphp
                    <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="w-full flex items-center justify-center gap-2.5 py-3 px-4 rounded-2xl text-xs sm:text-sm font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100/90 border border-emerald-200/80 transition-all shadow-2xs hover:shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        <span>Konfirmasi ke WhatsApp Kasir</span>
                    </a>
                </div>

            </div>
        </div>
    @endif

    <!-- Modal Waktu Pembayaran Habis (Auto-Expired 10 Menit) -->
    @if($isExpiredModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="closeExpiredModal"></div>
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl border-2 border-rose-200 z-10 space-y-4 animate-scale-up">
                <div class="w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-sm">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="inline-block bg-rose-100 text-rose-800 text-[10px] font-black uppercase tracking-wider px-3 py-0.5 rounded-full mb-1.5">
                        Waktu Pembayaran Habis
                    </span>
                    <h3 class="text-xl font-black text-slate-900 leading-tight">Booking Dibatalkan Otomatis</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Batas waktu 10 menit untuk pembayaran booking telah berakhir. Demi memberi kesempatan kepada penyewa lain dan menghindari penimbunan barang, <strong>stok unit yang sempat ditahan telah otomatis dikembalikan ke katalog toko</strong>.
                </p>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 text-left flex items-start gap-2">
                    <span class="text-emerald-600 font-black">✓</span>
                    <span>Anda sekarang dapat memilih kembali alat dari katalog jika masih berminat menyewa.</span>
                </div>
                <button type="button" 
                        wire:click="closeExpiredModal" 
                        class="w-full py-3 px-4 rounded-xl text-sm font-black text-white bg-slate-900 hover:bg-slate-800 transition-all cursor-pointer shadow-sm">
                    Mengerti & Kembali ke Katalog
                </button>
            </div>
        </div>
    @endif

</div>
