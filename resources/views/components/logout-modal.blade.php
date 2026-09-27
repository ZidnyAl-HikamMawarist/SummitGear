@auth
<div x-data="{ open: false }" 
     @open-logout-modal.window="open = true" 
     @keydown.escape.window="open = false" 
     x-cloak>
    
    <!-- Modal Backdrop & Dialog -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
         
        <div x-show="open"
             @click.away="open = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden p-6 space-y-5 select-none">
             
            <!-- Top Icon Header -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Konfirmasi Keluar (Logout)</h3>
                    <p class="text-xs text-slate-500 font-medium">Validasi pengakhiran sesi akun pengguna</p>
                </div>
            </div>

            <!-- User Account Card -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-[#101F42] text-white flex items-center justify-center font-black text-xs shadow-2xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500 font-medium truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-800 text-[10px] font-black uppercase tracking-wider">
                    {{ strtoupper(auth()->user()->role) }}
                </span>
            </div>

            <!-- Warning Note -->
            <div class="flex items-start gap-2.5 text-xs text-slate-600 bg-amber-50/80 border border-amber-200/70 p-3.5 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Apakah Anda yakin ingin keluar? Anda harus memasukkan kredensial / PIN kembali untuk masuk ke sistem SummitGear.</span>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="open = false" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                
                <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20 transition cursor-pointer flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Ya, Keluar Akun</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endauth
