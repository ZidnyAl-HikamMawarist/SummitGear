@props([
    'active' => 'pos', // 'pos' | 'booking'
])

<header class="bg-white px-4 sm:px-6 py-2.5 flex items-center justify-between border-b border-gray-200 shrink-0 shadow-xs z-30 select-none">
    <!-- Brand & Shift Info -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.transactions.create') }}" class="flex items-center gap-2.5 group">
            <x-app-logo size="md" />
            <div>
                <h1 class="text-base font-black text-navy leading-none tracking-tight group-hover:text-orange-600 transition">SummitGear <span class="text-coral">POS</span></h1>
                <p class="text-[10px] font-semibold text-slate-400 mt-0.5">Terminal Kasir & Rental</p>
            </div>
        </a>

        <div class="h-6 bg-gray-200 shrink-0 mx-1 hidden md:block" style="width: 1px;"></div>

        <!-- Shift Status Badge -->
        <div class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Shift Kasir Aktif
        </div>
    </div>

    <!-- Center Navigation Tabs (Kasir POS <-> Booking Masuk) -->
    <div class="flex items-center bg-slate-100 p-1 rounded-2xl border border-slate-200/80 shadow-inner">
        <!-- 1. Tab Kasir POS -->
        <a href="{{ route('admin.transactions.create') }}" 
           class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $active === 'pos' ? 'bg-white text-navy shadow-sm ring-1 ring-slate-200/60' : 'text-slate-600 hover:text-navy hover:bg-white/60' }}"
           title="Terminal Transaksi Kasir POS (F1)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ $active === 'pos' ? 'text-orange-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Kasir POS</span>
            <span class="hidden sm:inline-block px-1.5 py-0.5 rounded bg-slate-100 text-[9px] font-mono text-slate-500 font-bold border border-slate-200">F1</span>
        </a>

        <!-- 2. Tab Booking Masuk (Online) with Realtime Badge Component -->
        <livewire:components.incoming-booking-badge type="tab" :active="$active === 'booking'" />
    </div>

    <!-- Right Actions & Cashier Controls -->
    <div class="flex items-center gap-3">
        @if(auth()->check() && auth()->user()->role === 'admin')
        <!-- Admin Dashboard return button -->
        <a href="{{ route('dashboard') }}" 
           class="btn text-xs font-bold text-[#101F42] hover:text-white bg-blue-50 hover:bg-[#101F42] border border-blue-200 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5" 
           title="Kembali ke Dashboard Utama Admin">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="hidden lg:inline">Dashboard Admin</span>
        </a>
        @endif

        @if(auth()->check() && auth()->user()->role === 'kasir')
        <!-- Lock Screen Button (Kasir only) -->
        <button type="button" x-data x-on:click="$dispatch('lockScreen')" 
                class="text-gray-500 hover:text-[#101F42] hover:bg-gray-100 p-2 rounded-xl border border-transparent hover:border-gray-200 transition cursor-pointer" 
                title="Kunci Layar Kasir">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </button>
        @endif

        <div class="h-6 bg-gray-200 shrink-0 mx-0.5 hidden sm:block" style="width: 1px;"></div>

        <!-- Cashier Identity -->
        @auth
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white shadow-2xs" style="background: #101F42;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="hidden sm:flex flex-col text-left">
                <span class="text-xs font-bold text-[#101F42] leading-tight">{{ auth()->user()->name }}</span>
                <span class="text-[10px] font-bold text-[#FF4500] leading-tight">{{ strtoupper(auth()->user()->role) }}</span>
            </div>
        </div>
        @endauth

        <!-- Logout / Selesai Shift -->
        <button type="button" 
                @click="$dispatch('open-logout-modal')" 
                class="btn text-xs font-bold text-red-600 hover:text-white hover:bg-red-600 bg-red-50 border border-red-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1 cursor-pointer" 
                title="Selesai Shift & Keluar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="hidden md:inline">Keluar</span>
        </button>
    </div>
</header>
