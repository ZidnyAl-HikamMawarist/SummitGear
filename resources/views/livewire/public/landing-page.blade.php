<div x-data="{
    activeBooking: null,
    bookingHistory: [],
    showHistoryModal: false,
    selectedBooking: null,
    copied: false,
    dismissedBanner: false,
    init() {
        try {
            this.activeBooking = JSON.parse(localStorage.getItem('summitgear_active_booking') || 'null');
            var rawHistory = JSON.parse(localStorage.getItem('summitgear_booking_history') || '[]');
            this.bookingHistory = Array.isArray(rawHistory) ? rawHistory : [];
            if (this.activeBooking) {
                this.selectedBooking = this.activeBooking;
            } else if (this.bookingHistory.length > 0) {
                this.selectedBooking = this.bookingHistory[0];
            }
        } catch (e) {
            console.error('Error reading bookings from localStorage:', e);
            this.bookingHistory = [];
        }
    },
    openBookingModal(booking = null) {
        if (booking) {
            this.selectedBooking = booking;
        } else if (this.activeBooking) {
            this.selectedBooking = this.activeBooking;
        } else if (this.bookingHistory.length > 0) {
            this.selectedBooking = this.bookingHistory[0];
        }
        this.showHistoryModal = true;
        const m = document.getElementById('booking-history-modal');
        if (m) {
            m.style.setProperty('display', 'flex', 'important');
        }
    },
    closeBookingModal() {
        this.showHistoryModal = false;
        const m = document.getElementById('booking-history-modal');
        if (m) {
            m.style.setProperty('display', 'none', 'important');
        }
    },
    copyCode(code) {
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
    },
    removeBooking(code) {
        if (confirm('Hapus riwayat booking ' + code + ' dari perangkat ini?')) {
            if (this.activeBooking && this.activeBooking.rental_code === code) {
                this.activeBooking = null;
                localStorage.removeItem('summitgear_active_booking');
            }
            this.bookingHistory = this.bookingHistory.filter(item => item.rental_code !== code);
            localStorage.setItem('summitgear_booking_history', JSON.stringify(this.bookingHistory));
            if (this.bookingHistory.length > 0) {
                this.selectedBooking = this.bookingHistory[0];
            } else {
                this.selectedBooking = null;
                this.closeBookingModal();
            }
        }
    }
}">

    {{-- ===================================================================== --}}
    {{-- NAVBAR                                                                --}}
    {{-- ===================================================================== --}}
    <header x-data="{ scrolled: false, mobileOpen: false }"
            x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 60)"
            :class="scrolled ? 'bg-white/90 shadow-[0_1px_0_rgba(0,0,0,0.04)]' : 'bg-transparent'"
            class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500"
            :style="scrolled ? 'backdrop-filter: blur(16px) saturate(180%)' : ''">
        <div class="max-w-6xl mx-auto px-6 lg:px-10 flex items-center justify-between h-[68px]">

            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 flex-shrink-0 group">
                <x-app-logo size="sm" />
                <span class="text-[17px] font-extrabold tracking-tight transition-colors" :class="scrolled ? 'text-[#0f1729]' : 'text-white'">
                    Summit<span style="color: #FF4D00;">Gear</span>
                </span>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-7">
                @foreach([
                    ['#katalog', 'Katalog'],
                    ['#cara-sewa', 'Cara Sewa'],
                    ['#keunggulan', 'Keunggulan'],
                    ['#testimonial', 'Testimoni'],
                    ['#faq', 'FAQ'],
                ] as [$href, $text])
                <a href="{{ $href }}" class="text-[13px] font-semibold tracking-tight transition-colors" :class="scrolled ? 'text-zinc-500 hover:text-[#0f1729]' : 'text-white/60 hover:text-white'">{{ $text }}</a>
                @endforeach
            </nav>

            {{-- CTA --}}
            <div class="flex items-center gap-2.5 sm:gap-4">
                {{-- Active Booking Button if user has booking in localStorage --}}
                <template x-if="activeBooking || bookingHistory.length > 0">
                    <button type="button" 
                            @click="openBookingModal()"
                            class="relative flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 rounded-full text-xs font-black transition-all shadow-sm cursor-pointer"
                            :class="scrolled ? 'bg-orange-50 text-orange-950 border border-orange-200 hover:bg-orange-100' : 'bg-white/15 text-white border border-white/20 hover:bg-white/25 backdrop-blur-md'">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#e8430a] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#e8430a]"></span>
                        </span>
                        <span class="hidden md:inline">Booking Aktif:</span>
                        <span class="font-mono text-[11px] font-extrabold text-[#e8430a]" x-text="activeBooking ? activeBooking.rental_code : bookingHistory[0]?.rental_code"></span>
                    </button>
                </template>

                <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold transition-colors" :class="scrolled ? 'text-zinc-400 hover:text-[#0f1729]' : 'text-white/50 hover:text-white'">Masuk</a>
                <a href="{{ route('booking') }}" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-bold text-white transition-all hover:brightness-110 active:scale-[0.98]" style="background: #e8430a; box-shadow: 0 4px 14px -4px rgba(232,67,10,0.4); border-radius: 9999px; padding: 12px 28px;">
                    <span>Booking</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-1.5 rounded-md" :class="scrolled ? 'text-[#0f1729]' : 'text-white'" :aria-expanded="mobileOpen.toString()" aria-controls="mobile-navigation" aria-label="Buka menu navigasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div id="mobile-navigation" x-cloak x-show="mobileOpen" x-collapse class="md:hidden bg-white border-t border-zinc-100">
            <div class="px-5 py-4 space-y-2.5">
                @foreach(['Katalog' => '#katalog', 'Cara Sewa' => '#cara-sewa', 'Keunggulan' => '#keunggulan', 'Testimoni' => '#testimonial', 'FAQ' => '#faq', 'Masuk' => '/login'] as $text => $href)
                <a href="{{ $href }}" @click="mobileOpen=false" class="block text-sm font-semibold text-zinc-600 hover:text-[#0f1729] py-1">{{ $text }}</a>
                @endforeach
            </div>
        </div>
    </header>

    {{-- ===================================================================== --}}
    {{-- HERO — Split layout, left-aligned (anti-center bias per skill)        --}}
    {{-- ===================================================================== --}}
    <section x-data="{ 
                activeSlide: 0, 
                slides: ['{{ asset('images/hero_backpack.jpg') }}', '{{ asset('images/hero_tent.jpg') }}', '{{ asset('images/hero_hiker_ridge.jpg') }}', '{{ asset('images/hero_basecamp_bg.jpg') }}'],
                init() {
                    setInterval(() => {
                        this.activeSlide = this.activeSlide === this.slides.length - 1 ? 0 : this.activeSlide + 1;
                    }, 5000);
                }
             }" 
             class="relative overflow-hidden" style="min-height: 100dvh; background-color: #0a0f1e;">
        
        {{-- Carousel Backgrounds --}}
        <template x-for="(slide, index) in slides" :key="index">
            <div class="absolute inset-0"
                 :style="`
                    background-image: url('${slide}'); 
                    background-size: cover; 
                    background-position: center; 
                    transition: opacity 1s ease-in-out;
                    opacity: ${activeSlide === index ? '0.85' : '0'};
                 `">
            </div>
        </template>

        {{-- Gradient Overlay for readability (Softened for brighter image) --}}
        <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(90deg, rgba(10,15,30,0.9) 0%, rgba(10,15,30,0.5) 45%, transparent 100%);"></div>

        {{-- Subtle ambient glow (no neon, tinted to warm) --}}
        <div class="absolute inset-0 pointer-events-none select-none" style="mix-blend-mode: screen;">
            <div class="absolute -top-32 right-0 rounded-full" style="width: 600px; height: 600px; opacity: 0.6; background: radial-gradient(circle, rgba(232,67,10,0.15), transparent 60%);"></div>
            <div class="absolute bottom-0 -left-32 rounded-full" style="width: 400px; height: 400px; opacity: 0.6; background: radial-gradient(circle, rgba(30,58,138,0.15), transparent 60%);"></div>
            {{-- Dot grid --}}
            <div class="absolute inset-0" style="opacity: 0.025; background-image: radial-gradient(rgba(255,255,255,0.8) 1px, transparent 1px); background-size: 32px 32px;"></div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-6 lg:px-10 w-full flex items-center" style="min-height: 100dvh; padding-top: 80px; padding-bottom: 48px;">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center w-full">

                {{-- Left: Text content (7 cols) --}}
                <div class="lg:col-span-7">
                    {{-- Status pill --}}
                    <div class="inline-flex items-center gap-2 mb-8 text-[11px] font-bold uppercase tracking-[0.12em] px-3.5 py-1.5 rounded-full" style="color: #e8430a; border: 1px solid rgba(232,67,10,0.2); background: rgba(232,67,10,0.05);">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: #e8430a; animation: pulse 2s infinite;"></span>
                        Booking Online Tersedia
                    </div>

                    {{-- Headline — controlled scale, no gradient text (per skill anti-slop) --}}
                    <h1 class="text-white tracking-tighter mb-6 text-5xl md:text-6xl lg:text-7xl" style="font-weight: 800; line-height: 1.05;">
                        Sewa Alat Outdoor<br/>
                        <span style="color: #e8430a;">Berkualitas</span> untuk<br/>
                        Pendakianmu.
                    </h1>

                    {{-- Subheadline --}}
                    <p class="text-lg md:text-xl leading-relaxed mb-10 max-w-xl" style="color: rgba(255,255,255,0.7);">
                        Tenda, carrier, sleeping bag & {{ $this->totalItems }}+ peralatan lainnya. Dicek kualitasnya, tinggal booking online, ambil di toko.
                    </p>

                    {{-- CTA row --}}
                    <div class="flex flex-col sm:flex-row gap-4 mb-14">
                        <a href="{{ route('booking') }}" class="inline-flex items-center justify-center gap-2 text-base font-bold text-white rounded-xl px-9 py-4 transition-all hover:brightness-110 active:scale-[0.97]" style="background: #e8430a; box-shadow: 0 8px 24px -8px rgba(232,67,10,0.35);">
                            Mulai Booking
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="#katalog" class="inline-flex items-center justify-center gap-2 text-base font-semibold rounded-xl px-9 py-4 transition-all hover:bg-white/[0.04]" style="color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.15);">
                            Lihat Katalog
                        </a>
                    </div>

                    {{-- Stats row --}}
                    <div class="flex flex-wrap items-center gap-8 lg:gap-10">
                        <div>
                            <p class="text-2xl font-extrabold text-white tracking-tight">{{ $this->totalItems }}+</p>
                            <p class="text-[11px] font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.3);">Jenis Alat</p>
                        </div>
                        <div class="hidden sm:block w-px h-8" style="background: rgba(255,255,255,0.06);"></div>
                        <div>
                            <p class="text-2xl font-extrabold text-white tracking-tight">1.200+</p>
                            <p class="text-[11px] font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.3);">Transaksi</p>
                        </div>
                        <div class="hidden sm:block w-px h-8" style="background: rgba(255,255,255,0.06);"></div>
                        <div>
                            <p class="text-2xl font-extrabold text-white tracking-tight">4.9/5</p>
                            <p class="text-[11px] font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.3);">Rating</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Glassmorphism feature cards (5 cols) --}}
                <div class="hidden lg:flex lg:col-span-5 flex-col gap-3.5">
                    @foreach([
                        ['Booking 100% Online', 'Pilih alat, atur tanggal, isi data. Selesai.', '#e8430a', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['Alat Terawat & Bersih', 'Setiap unit melewati QC ketat sebelum diserahkan.', '#10B981', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['Harga Transparan', 'Bayar sesuai hari pakai. Tanpa biaya tersembunyi.', '#3B82F6', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Ambil di Toko', 'Lokasi strategis. Proses cepat, tidak ribet.', '#8B5CF6', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                    ] as [$title, $desc, $color, $icon])
                    <div class="p-4 rounded-2xl transition-all duration-300 hover:-translate-y-px" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: {{ $color }}10;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="{{ $color }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </div>
                            <div>
                                <h3 class="text-[13px] font-bold text-white mb-0.5">{{ $title }}</h3>
                                <p class="text-[12px] leading-relaxed" style="color: rgba(255,255,255,0.35);">{{ $desc }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- SOCIAL PROOF — Minimal, no-frills                                     --}}
    {{-- ===================================================================== --}}
    <section class="py-8 bg-white" style="border-bottom: 1px solid #f4f4f5;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10 flex flex-wrap items-center justify-center gap-x-12 gap-y-3">
            <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-zinc-300">Dipercaya oleh</span>
            @foreach(['MAPALA UI', 'WANADRI', 'HIMALAYA CLUB', 'PECINTA ALAM ITB'] as $brand)
            <span class="text-[13px] font-extrabold tracking-wide text-zinc-300">{{ $brand }}</span>
            @endforeach
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- CARA SEWA — Zig-zag layout (anti 3-column card per skill)             --}}
    {{-- ===================================================================== --}}
    <section id="cara-sewa" class="bg-[#fafafa]" style="padding-top: 80px; padding-bottom: 100px;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10">
            <div class="max-w-lg" style="margin-bottom: 30px;">
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#e8430a] mb-3 block">Proses Mudah</span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#0f1729] mb-4">Sewa Dalam 3 Langkah</h2>
                <p class="text-base md:text-lg text-zinc-500 leading-relaxed max-w-md">Tidak perlu registrasi akun atau download aplikasi. Buka browser, booking, selesai.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px;">
                @foreach([
                    ['01', 'Pilih Alat & Tanggal', 'Telusuri katalog, pilih yang kamu butuhkan, dan tentukan tanggal pemakaian. Stok diupdate real-time.', '#e8430a', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['02', 'Isi Data Singkat', 'Masukkan nama, nomor HP, alamat. Data hanya dipakai untuk konfirmasi pesanan, tidak disebar ke pihak lain.', '#10B981', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['03', 'Ambil & Bayar di Toko', 'Datang di hari yang ditentukan, tunjukkan KTP, bayar, dan alat siap dibawa. Pembayaran cash, transfer, atau QRIS.', '#3B82F6', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                ] as [$num, $title, $desc, $color, $icon])
                <div class="flex items-start gap-5 lg:gap-8 p-6 lg:p-8 bg-white rounded-3xl shadow-[0_8px_30px_-12px_rgba(0,0,0,0.08)] transition-all duration-300 hover:shadow-[0_12px_40px_-12px_rgba(0,0,0,0.12)] hover:-translate-y-1" style="border: 1px solid #f0f0f0;">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: {{ $color }}0C;">
                            <span class="text-sm font-extrabold" style="color: {{ $color }};">{{ $num }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="{{ $color }}" stroke-width="2.5" style="position: relative; top: -1px;"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            <h3 class="text-sm font-bold text-[#0f1729]" style="position: relative; top: 2px;">{{ $title }}</h3>
                        </div>
                        <p class="text-[13px] text-zinc-400 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- KATALOG PRODUK                                                        --}}
    {{-- ===================================================================== --}}
    <section id="katalog" class="bg-white" style="padding-top: 80px; padding-bottom: 100px;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10">
            {{-- Header + Controls --}}
            <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-6" style="margin-bottom: 40px;">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#e8430a] mb-3 block">Katalog Kami</span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#0f1729] mb-3">Alat Tersedia</h2>
                    <p class="text-base md:text-lg text-zinc-500 leading-relaxed max-w-md">Pilih perlengkapan untuk petualangan berikutnya. Stok real-time.</p>
                </div>

                <div class="flex gap-2.5 w-full lg:w-auto">
                    <div class="relative flex-1 lg:flex-initial">
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari alat..."
                               class="w-full lg:w-72 pl-11 pr-4 py-3 rounded-xl text-base font-medium outline-none transition-all" style="border: 1px solid #e4e4e7; background: #fafafa;" onfocus="this.style.borderColor='#e8430a'; this.style.boxShadow='0 0 0 3px rgba(232,67,10,0.06)'" onblur="this.style.borderColor='#e4e4e7'; this.style.boxShadow='none'">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                    <select wire:model.live="selectedCategory" class="rounded-xl text-base font-semibold py-3 px-4 outline-none transition-all" style="border: 1px solid #e4e4e7; background: #fafafa;">
                        <option value="all">Semua Kategori</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Grid: 2 cols on mobile, scaling up --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4" style="margin-bottom: 30px;">
                @forelse($this->items as $item)
                    <a href="{{ route('booking') }}" class="group bg-white rounded-2xl overflow-hidden flex flex-col transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_14px_32px_-10px_rgba(0,0,0,0.1)] block text-inherit no-underline" style="border: 1px solid #f0f0f0;">
                        {{-- Image --}}
                        <div class="w-full aspect-[4/3] bg-[#f8f8f8] overflow-hidden relative">
                            @if($item->photo_url)
                            @php
                                $inStorage = file_exists(public_path('storage/' . $item->photo_url));
                                $inPublic = file_exists(public_path($item->photo_url));
                                $hasPhoto = !empty($item->photo_url) && ($inStorage || $inPublic);
                                $imgSrc = $inStorage ? asset('storage/' . $item->photo_url) : ($inPublic ? asset($item->photo_url) : 'https://placehold.co/400x400/1e293b/ffffff?text=' . urlencode(str_replace(' ', "\n", $item->name)));
                            @endphp
                            @if($hasPhoto)
                                <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            @else
                                <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="w-full h-full object-contain bg-[#1e293b] group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-zinc-50 to-zinc-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md text-white" style="background: rgba(15,23,41,0.65); backdrop-filter: blur(4px);">{{ $item->category }}</div>
                            @if(($item->available_count ?? 0) > 0)
                                <div class="absolute top-3 right-3 text-sm font-black" style="color: #059669; text-shadow: 0 1px 4px rgba(255,255,255,0.8);">{{ $item->available_count }}</div>
                            @else
                                <div class="absolute inset-0 bg-white/60 flex items-center justify-center backdrop-blur-[2px]">
                                    <span class="text-[10px] font-bold text-zinc-500 bg-white px-2.5 py-1 rounded-full shadow-sm" style="border: 1px solid #e4e4e7;">Kosong</span>
                                </div>
                            @endif
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 flex flex-col p-3.5">
                            <h3 class="text-[13px] font-bold leading-snug line-clamp-2 text-[#0f1729] group-hover:text-[#e8430a] transition-colors mb-auto">{{ $item->name }}</h3>
                            <div class="mt-2.5 pt-2.5" style="border-top: 1px solid #f4f4f5;">
                                <p class="text-[11px] text-zinc-400 font-medium mb-0.5">Harga /hari</p>
                                <p class="text-sm font-extrabold text-[#059669] tracking-tight" style="padding-left: 2px;">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: #f4f4f5;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-[#0f1729] mb-1">Tidak ada alat ditemukan</h3>
                        <p class="text-[13px] text-zinc-400">Coba kata kunci atau kategori lain.</p>
                    </div>
                @endforelse
            </div>

            {{-- CTA Periksa Semua Alat & Booking --}}
            <div class="relative overflow-hidden rounded-3xl p-6 sm:p-10 lg:p-12 text-center" 
                 style="background: radial-gradient(circle at 50% -20%, rgba(232, 67, 10, 0.22) 0%, rgba(15, 23, 42, 0.98) 70%), #0b0f19; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.15);">
                
                {{-- Decorative ambient glows --}}
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-48 bg-orange-500/15 blur-3xl pointer-events-none rounded-full"></div>
                <div class="absolute -bottom-16 -right-16 w-64 h-64 bg-orange-600/10 blur-2xl pointer-events-none rounded-full"></div>

                {{-- Subtle Mountain Silhouette Watermark --}}
                <div class="absolute inset-0 pointer-events-none opacity-[0.04] flex items-end justify-center overflow-hidden">
                    <svg viewBox="0 0 1200 200" class="w-full h-auto min-w-[800px]" preserveAspectRatio="none" fill="white">
                        <path d="M0,200 L0,120 L150,40 L300,140 L450,20 L600,110 L750,50 L900,150 L1050,60 L1200,130 L1200,200 Z"></path>
                    </svg>
                </div>

                {{-- Content Container --}}
                <div class="relative z-10 max-w-3xl mx-auto flex flex-col items-center">
                    
                    {{-- Status / Feature Pill --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/[0.07] border border-white/[0.12] backdrop-blur-md shadow-inner mb-4 sm:mb-5">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-200">
                            Menampilkan <span class="text-orange-400 font-bold">12 Alat Pilihan</span>
                            @if($this->hasMoreItems)
                                <span class="text-slate-400">•</span>
                                <span class="text-slate-300">Tersedia <strong>{{ $this->totalItems }}</strong> Total Alat Siap Sewa</span>
                            @endif
                        </span>
                    </div>

                    {{-- Main Headline --}}
                    <h3 class="text-xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight mb-2.5 sm:mb-3">
                        Ingin Lihat Seluruh Koleksi & <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-orange-500">Booking Online</span>?
                    </h3>

                    {{-- Description --}}
                    <p class="text-xs sm:text-sm md:text-base text-slate-300 leading-relaxed max-w-xl mb-5 sm:mb-6">
                        Seluruh katalog lengkap, filter kategori, serta pengecekan tanggal sewa real-time dapat kamu akses langsung di sistem booking online.
                    </p>

                    {{-- Value Highlights / Trust Chips --}}
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 mb-6 sm:mb-8">
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[11px] sm:text-xs font-medium text-slate-300">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Stok Diperbarui Real-Time</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[11px] sm:text-xs font-medium text-slate-300">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Katalog & Spesifikasi Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[11px] sm:text-xs font-medium text-slate-300">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Booking Cepat Tanpa Akun</span>
                        </div>
                    </div>

                    {{-- CTA Button --}}
                    <div class="w-full sm:w-auto flex justify-center">
                        <a href="{{ route('booking') }}" 
                           class="group relative inline-flex items-center justify-center gap-2.5 sm:gap-3 w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold text-white transition-all duration-300 shadow-[0_12px_28px_-6px_rgba(232,67,10,0.5)] hover:shadow-[0_18px_36px_-6px_rgba(232,67,10,0.65)] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] overflow-hidden cursor-pointer whitespace-nowrap"
                           style="background: linear-gradient(135deg, #ff5722 0%, #e8430a 100%); border: 1px solid rgba(255,255,255,0.2);">
                            
                            {{-- Shimmer effect on hover --}}
                            <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/20 to-transparent pointer-events-none"></div>

                            <span class="relative tracking-wide">Cek Semua Alat & Booking</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5 transition-transform duration-300 group-hover:translate-x-1.5 relative shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>

                    {{-- Micro Reassurance Note --}}
                    <p class="mt-3.5 sm:mt-4 text-[11px] sm:text-xs text-slate-400 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-center">
                        <span class="inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-orange-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Bayar di kasir saat ambil alat</span>
                        </span>
                        <span class="hidden sm:inline text-slate-600">•</span>
                        <span>Bebas batalkan kapan saja</span>
                    </p>

                </div>
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- KEUNGGULAN — Asymmetric 2-col                                         --}}
    {{-- ===================================================================== --}}
    <section id="keunggulan" class="bg-[#fafafa]" style="padding-top: 80px; padding-bottom: 100px;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-20 items-start">
                {{-- Left: Features list (7 cols) --}}
                <div class="lg:col-span-7">
                    <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#e8430a] mb-3 block">Mengapa SummitGear</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#0f1729] mb-4">Alat Premium, Harga Wajar</h2>
                    <p class="text-sm text-zinc-400 leading-relaxed mb-10 max-w-lg">Peralatan outdoor berkualitas tinggi dengan harga sewa yang bersahabat. Hemat jutaan rupiah tanpa mengorbankan keselamatan.</p>

                    <div class="grid sm:grid-cols-2 gap-5">
                        @foreach([
                            ['50+ Jenis Alat', 'Tenda, carrier, matras, sleeping bag, trek pole, headlamp, kompor, dan lainnya.', '#e8430a', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            ['QC Ketat', 'Setiap alat dicek kondisinya sebelum dan sesudah pemakaian.', '#10B981', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                            ['Booking Fleksibel', 'Booking kapan saja, ubah tanggal mudah, batalkan tanpa biaya.', '#3B82F6', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Harga per Hari', 'Bayar per hari. Tanpa biaya admin tambahan.', '#8B5CF6', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ] as [$title, $desc, $color, $icon])
                        <div class="flex items-start gap-3.5 p-4 bg-white rounded-xl transition-all hover:shadow-[0_4px_16px_-4px_rgba(0,0,0,0.04)]" style="border: 1px solid #f0f0f0;">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: {{ $color }}0A;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="{{ $color }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </div>
                            <div>
                                <h4 class="text-[13px] font-bold text-[#0f1729] mb-0.5">{{ $title }}</h4>
                                <p class="text-[12px] text-zinc-400 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: CTA Card (5 cols) --}}
                <div class="lg:col-span-5 sticky top-24">
                    <div class="rounded-2xl p-8 lg:p-10 relative overflow-hidden" style="background: #0f1729;">
                        <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full" style="background: radial-gradient(circle, rgba(232,67,10,0.1), transparent);"></div>

                        <div class="relative z-10">
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: rgba(232,67,10,0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#e8430a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 3-9 17h18Z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-extrabold text-white tracking-tight mb-2">Siap Mendaki?</h3>
                            <p class="text-[13px] leading-relaxed mb-7" style="color: rgba(255,255,255,0.4);">Jangan biarkan alat mahal menghalangi petualanganmu. Sewa aja.</p>

                            <a href="{{ route('booking') }}" class="w-full inline-flex items-center justify-center gap-2 text-sm font-bold text-white rounded-xl px-6 py-3.5 transition-all hover:brightness-110 active:scale-[0.97]" style="background: #e8430a;">
                                Booking Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>

                            <div class="grid grid-cols-3 gap-3 mt-6">
                                @foreach([['100%', 'Aman'], ['0', 'Biaya Admin'], ['Fast', 'Proses']] as [$stat, $statLabel])
                                <div class="py-2.5 text-center rounded-lg" style="background: rgba(255,255,255,0.04);">
                                    <p class="text-base font-extrabold text-white tracking-tight">{{ $stat }}</p>
                                    <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.3);">{{ $statLabel }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- TESTIMONIAL — 2-col zig-zag (anti 3-col card per skill)               --}}
    {{-- ===================================================================== --}}
    <section id="testimonial" class="bg-white" style="padding-top: 80px; padding-bottom: 100px;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10">
            <div class="max-w-lg mb-14">
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#e8430a] mb-3 block">Testimoni</span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#0f1729]">Apa Kata Pelanggan</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                @foreach([
                    ['Rizki Ananda Pratama', 'Pendaki Reguler', 'Pertama kali sewa di SummitGear, langsung jatuh cinta. Alatnya bersih, terawat, dan proses booking online-nya cepat banget. Sangat recommended.'],
                    ['Diana Kusumawati', 'Komunitas Hiking Jakarta', 'Kami sering sewa alat untuk trip komunitas. SummitGear selalu bisa handle pesanan banyak dan kondisi alat selalu prima.'],
                    ['Andi Putra Wijaya', 'Travel Content Creator', 'Sebagai content creator yang sering ke gunung, sewa alat di sini sangat menghemat budget. Harga fair dan kualitas bagus.'],
                    ['Sinta Rahayu', 'Mahasiswa Pecinta Alam', 'Booking online tanpa ribet, tinggal datang ambil. Kualitas tenda dan sleeping bag setara merek premium. Pasti sewa lagi.'],
                ] as [$name, $role, $text])
                <div class="p-6 rounded-2xl bg-[#fafafa] transition-all duration-300 hover:shadow-[0_4px_16px_-4px_rgba(0,0,0,0.04)]" style="border: 1px solid #f0f0f0;">
                    {{-- Stars --}}
                    <div class="flex items-center gap-0.5 mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="#e8430a"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>

                    <p class="text-[13px] text-zinc-500 leading-relaxed mb-5">"{{ $text }}"</p>

                    <div class="flex items-center gap-2.5 pt-4" style="border-top: 1px solid #ebebeb;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background: #0f1729;">
                            {{ strtoupper(substr($name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $name)[1] ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-[13px] font-bold text-[#0f1729]">{{ $name }}</p>
                            <p class="text-[11px] text-zinc-400">{{ $role }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- FAQ — Accordion                                                       --}}
    {{-- ===================================================================== --}}
    <section id="faq" class="bg-[#fafafa]" style="padding-top: 80px; padding-bottom: 100px;">
        <div class="max-w-2xl mx-auto px-5 lg:px-8">
            <div class="max-w-lg mb-12">
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#e8430a] mb-3 block">FAQ</span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#0f1729]">Pertanyaan Umum</h2>
            </div>

            <div class="space-y-2.5">
                @foreach([
                    ['Apakah harus daftar akun dulu?', 'Tidak. Kamu bisa langsung booking tanpa registrasi. Cukup isi nama, nomor HP, dan alamat saja.'],
                    ['Bagaimana cara pembayaran?', 'Pembayaran dilakukan saat mengambil alat di toko. Kami menerima Cash, Transfer Bank, dan QRIS.'],
                    ['Bisa membatalkan booking?', 'Bisa. Hubungi kami via WhatsApp atau datang ke toko untuk membatalkan sebelum hari-H.'],
                    ['Kalau alat rusak gimana?', 'Setiap alat di-QC sebelum diserahkan. Jika ada kerusakan saat pemakaian, biaya penggantian disepakati bersama.'],
                    ['Berapa minimal durasi sewa?', 'Minimum sewa 1 hari. Untuk sewa lebih dari 7 hari, hubungi kami untuk penawaran khusus.'],
                ] as [$question, $answer])
                <div x-data="{ open: false }" class="bg-white rounded-xl overflow-hidden transition-all" style="border: 1px solid {{ 'f0f0f0' }};" :style="open ? 'border-color: #e8e8eb; box-shadow: 0 4px 16px -4px rgba(0,0,0,0.04)' : ''">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left gap-3">
                        <span class="text-[13px] font-bold text-[#0f1729]">{{ $question }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-zinc-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse>
                        <div class="px-4 pb-4 -mt-1">
                            <p class="text-[13px] text-zinc-400 leading-relaxed">{{ $answer }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================================================================== --}}
    {{-- FOOTER                                                                --}}
    {{-- ===================================================================== --}}
    <footer style="background: #080c18;">
        <div class="max-w-6xl mx-auto px-6 lg:px-10" style="padding-top: 80px; padding-bottom: 44px;">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-16 mb-12">
                {{-- Column 1: Brand & Kontak --}}
                <div class="flex flex-col">
                    {{-- Brand --}}
                    <div class="flex items-center gap-3" style="margin-bottom: 16px;">
                        <div class="w-8 h-8 rounded-md flex items-center justify-center" style="background: rgba(232,67,10,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#e8430a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m12 3-9 17h18Z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-extrabold text-white tracking-tight">Summit<span class="text-[#e8430a]">Gear</span></span>
                    </div>
                    <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.6); margin-bottom: 40px;">Sewa alat pendakian & outdoor terpercaya. Lengkap, terawat, harga wajar.</p>

                    {{-- Kontak --}}
                    <h4 class="text-base font-bold text-white tracking-wide" style="margin-bottom: 16px;">Kontak</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4 text-sm" style="color: rgba(255,255,255,0.6);">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <span class="pt-1.5 leading-relaxed">Jl. Pendakian No. 1, Indonesia</span>
                        </li>
                        <li class="flex items-center gap-4 text-sm" style="color: rgba(255,255,255,0.6);">
                            <a href="https://wa.me/6281234567890" target="_blank" class="flex items-center gap-4 hover:text-white transition-colors" style="color: inherit; text-decoration: none;">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </div>
                                <span>WhatsApp: +62 812-3456-7890</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-4 text-sm" style="color: rgba(255,255,255,0.6);">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <span>info@summitgear.id</span>
                        </li>
                    </ul>
                </div>

                {{-- Column 2: Menu --}}
                <div>
                    <h4 class="text-base font-bold text-white tracking-wide" style="margin-bottom: 16px;">Menu Utama</h4>
                    <ul class="space-y-4">
                        @foreach(['Katalog' => '#katalog', 'Cara Sewa' => '#cara-sewa', 'Booking' => '/booking', 'FAQ' => '#faq'] as $text => $href)
                        <li>
                            <a href="{{ $href }}" class="group flex items-center gap-3 text-sm transition-colors" style="color: rgba(255,255,255,0.6);" onmouseover="this.style.color='rgba(255,255,255,1)'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white/30 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                {{ $text }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 3: Legal & Social --}}
                <div>
                    <h4 class="text-base font-bold text-white tracking-wide" style="margin-bottom: 16px;">Legal</h4>
                    <ul class="space-y-4 mb-10">
                        @foreach(['Syarat Ketentuan' => '#', 'Privasi' => '#', 'Refund' => '#'] as $text => $href)
                        <li>
                            <a href="{{ $href }}" class="group flex items-center gap-3 text-sm transition-colors" style="color: rgba(255,255,255,0.6);" onmouseover="this.style.color='rgba(255,255,255,1)'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white/30 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                {{ $text }}
                            </a>
                        </li>
                        @endforeach
                    </ul>


                </div>
            </div>

            <div class="pt-8 text-center" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <p class="text-sm" style="color: rgba(255,255,255,0.4);">{{ date('Y') }} SummitGear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    {{-- ===================================================================== --}}
    {{-- FLOATING ACTIVE BOOKING TOAST / CARD (Informative, Non-Intrusive)     --}}
    {{-- ===================================================================== --}}
    <div x-cloak 
         x-show="activeBooking && !dismissedBanner" 
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-5 right-5 z-[90] max-w-sm w-[calc(100vw-40px)] sm:w-96">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border-2 border-orange-500/30 ring-4 ring-orange-500/10 space-y-3">
            <div class="flex items-start justify-between gap-2.5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-[#e8430a] flex items-center justify-center shrink-0 font-bold shadow-2xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">
                                Booking Aktif
                            </span>
                            <span class="text-xs font-mono font-black text-slate-800" x-text="activeBooking?.rental_code"></span>
                        </div>
                        <p class="text-xs font-semibold text-slate-800 mt-1">
                            Jadwal: <span class="font-bold text-emerald-700" x-text="activeBooking?.pickup_formatted"></span>
                        </p>
                        <p class="text-[11px] text-slate-500">
                            Batas toleransi: <span class="font-bold text-amber-700" x-text="activeBooking?.tolerance_formatted"></span>
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="dismissedBanner = true" 
                        class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Tutup banner">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Total Sewa</span>
                    <span class="font-black text-orange-600 text-sm" x-text="activeBooking?.total_formatted"></span>
                </div>
                <button type="button" 
                        @click="openBookingModal(activeBooking)"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-black text-white transition-all hover:brightness-110 shadow-sm cursor-pointer"
                        style="background: #e8430a;">
                    <span>Lihat Rincian</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- MODAL RIWAYAT / TIKET BOOKING (Landing Page)                         --}}
    {{-- ===================================================================== --}}
    <div id="booking-history-modal"
         x-cloak 
         x-show="showHistoryModal" 
         :class="showHistoryModal ? 'flex' : 'hidden'"
         :style="!showHistoryModal ? 'display: none !important;' : 'display: flex !important;'"
         @keydown.escape.window="closeBookingModal()"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
         style="display: none;">
        
        {{-- Backdrop Blur --}}
        <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm cursor-pointer" 
             @click="closeBookingModal()"
             onclick="var m=document.getElementById('booking-history-modal'); if(m) m.style.setProperty('display', 'none', 'important');"></div>

        {{-- Modal Dialog --}}
        <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-7 z-10 text-left my-auto"
             @click.stop>
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-[#e8430a] flex items-center justify-center font-bold shadow-2xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Tiket Booking Peralatan</h3>
                        <p class="text-[11px] text-slate-500">Tersimpan di sesi browser perangkat ini</p>
                    </div>
                </div>
                <button type="button" 
                        @click.stop="closeBookingModal()" 
                        onclick="var m=document.getElementById('booking-history-modal'); if(m) m.style.setProperty('display', 'none', 'important');"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Tutup Modal"
                        aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Multiple Bookings Switcher (If user has multiple bookings) --}}
            <template x-if="bookingHistory.length > 1">
                <div class="py-2.5 border-b border-slate-100 flex items-center gap-2 overflow-x-auto text-xs">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 shrink-0">Pilih Booking:</span>
                    <template x-for="item in bookingHistory" :key="item.rental_code">
                        <button type="button" 
                                @click="selectedBooking = item"
                                class="px-2.5 py-1 rounded-lg font-mono font-bold text-xs shrink-0 transition-all cursor-pointer"
                                :class="selectedBooking?.rental_code === item.rental_code ? 'bg-[#e8430a] text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                x-text="item.rental_code">
                        </button>
                    </template>
                </div>
            </template>

            {{-- Selected Booking Details --}}
            <template x-if="selectedBooking">
                <div class="mt-4 space-y-3.5">
                    
                    {{-- Status Banner --}}
                    <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-900">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-bold">Status:</span>
                            <span class="font-extrabold uppercase" x-text="selectedBooking.status || 'MENUNGGU DIAMBIL'"></span>
                        </div>
                        <span class="text-[11px] text-emerald-700 font-semibold">Bayar di Kasir</span>
                    </div>

                    {{-- Kode Booking Box with Copy Button --}}
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3 shadow-2xs">
                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-200/70">
                            <div>
                                <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block">Kode Booking</span>
                                <span class="text-base font-black font-mono tracking-wider text-[#0f1729]" x-text="selectedBooking.rental_code"></span>
                            </div>
                            <button type="button" 
                                    @click="copyCode(selectedBooking.rental_code)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                    :class="copied ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:border-[#e8430a] hover:text-[#e8430a]'">
                                <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>

                        {{-- Details Grid --}}
                        <div class="grid grid-cols-2 gap-2.5 text-xs">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Nama Pemesan</span>
                                <span class="font-extrabold text-slate-800" x-text="selectedBooking.customer_name"></span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Durasi Sewa</span>
                                <span class="font-extrabold text-emerald-800" x-text="(selectedBooking.duration_days || 1) + ' Hari'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Jadwal Pengambilan</span>
                                <span class="font-extrabold text-slate-800" x-text="selectedBooking.pickup_formatted"></span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Batas Toleransi</span>
                                <span class="font-extrabold text-amber-700" x-text="'Maks. ' + selectedBooking.tolerance_formatted + ' (2 Jam)'"></span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Biaya Estimasi</span>
                                <span class="text-sm font-black text-orange-600" x-text="selectedBooking.total_formatted"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Metode Pembayaran</span>
                                <span class="font-bold text-slate-700">Bayar di Kasir Toko</span>
                            </div>
                        </div>

                        {{-- Items List --}}
                        <template x-if="selectedBooking.items && selectedBooking.items.length > 0">
                            <div class="pt-2 border-t border-slate-200/70">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">
                                    Daftar Perlengkapan (<span x-text="selectedBooking.items.length"></span> Alat)
                                </span>
                                <div class="max-h-28 overflow-y-auto space-y-1 text-xs pr-1">
                                    <template x-for="(item, idx) in selectedBooking.items" :key="idx">
                                        <div class="flex items-center justify-between py-1 px-2 rounded-lg bg-white border border-slate-100 text-slate-700">
                                            <span class="truncate font-medium" x-text="item.name"></span>
                                            <span class="font-black text-slate-900 shrink-0 ml-2" x-text="'x' + item.quantity"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Notice Alert --}}
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs flex items-start gap-2 shadow-2xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="leading-relaxed text-[11px]">
                            Bawa <strong>KTP fisik asli</strong> dan tunjukkan <strong>Kode Booking</strong> di atas kepada kasir saat pengambilan barang di toko SummitGear.
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-2 pt-1">
                        <a :href="'https://wa.me/{{ config('services.whatsapp.admin_phone', '6281234567890') }}?text=' + encodeURIComponent('Halo SummitGear, saya ingin menanyakan booking dengan kode ' + (selectedBooking.rental_code || '') + ' atas nama ' + (selectedBooking.customer_name || ''))"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 transition-colors cursor-pointer">
                            <svg class="h-4 w-4 text-emerald-700" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span>Chat WhatsApp Kasir / Toko</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="removeBooking(selectedBooking.rental_code)"
                                    class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-slate-500 hover:text-red-600 hover:bg-red-50 border border-slate-200 transition-colors cursor-pointer">
                                Hapus Riwayat Ini
                            </button>
                            <button type="button" 
                                    @click.stop="closeBookingModal()"
                                    onclick="var m=document.getElementById('booking-history-modal'); if(m) m.style.setProperty('display', 'none', 'important');"
                                    class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 transition-colors cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var m = document.getElementById('booking-history-modal');
                if (m) m.style.setProperty('display', 'none', 'important');
            }
        });
    </script>

    <style>
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    </style>
</div>
