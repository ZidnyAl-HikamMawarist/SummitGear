@props(['title' => 'SummitGear POS', 'subtitle' => null])

<header class="topbar">
    <div class="flex items-center gap-2">
        {{-- Area kiri topbar bersih tanpa duplikasi judul halaman --}}
    </div>
    <div class="flex items-center gap-3 sm:gap-4 ml-auto">
        @if(auth()->check() && auth()->user()->role === 'kasir')
        <!-- Lock Screen Button (Hanya untuk Kasir) -->
        <button type="button" 
                x-data 
                x-on:click="$dispatch('lockScreen')" 
                class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 hover:border-slate-300 flex items-center justify-center text-slate-500 hover:text-navy transition shadow-2xs cursor-pointer" 
                title="Kunci Layar (Kasir)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </button>
        @endif
        
        <!-- Notification Button -->
        <button type="button" 
                class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 hover:border-slate-300 flex items-center justify-center text-slate-500 hover:text-navy transition shadow-2xs cursor-pointer relative" 
                title="Notifikasi Sistem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500"></span>
        </button>

        @auth
        <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
            <div class="relative w-9 h-9 rounded-xl flex items-center justify-center font-black text-white text-xs shadow-2xs shrink-0" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white"></span>
            </div>
            <div class="flex flex-col text-left">
                <span class="text-xs font-bold text-navy leading-tight">{{ auth()->user()->name }}</span>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-coral leading-tight mt-0.5">{{ strtoupper(auth()->user()->role) }}</span>
            </div>
        </div>
        @endauth
    </div>
</header>

