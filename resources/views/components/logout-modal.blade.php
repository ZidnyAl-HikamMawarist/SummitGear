@auth
<div x-data="{ show: false }" 
     x-on:open-logout-modal.window="show = true"
     x-on:keydown.escape.window="show = false"
     x-show="show" 
     class="relative z-[100]" 
     style="display: none;"
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop with blur -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.outside="show = false"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                
                <div class="p-6">
                    <!-- Top Icon Header -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600 shadow-2xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Konfirmasi Keluar (Logout)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Validasi pengakhiran sesi akun pengguna</p>
                        </div>
                    </div>

                    <!-- User Account Card -->
                    <div class="mt-4 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#101F42] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-white text-slate-700 border border-slate-200 shrink-0">
                            {{ auth()->user()->role }}
                        </span>
                    </div>

                    <!-- Warning Note -->
                    <div class="mt-4 flex items-start gap-2.5 text-xs text-slate-600 bg-amber-50/70 border border-amber-200/60 p-3 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Apakah Anda yakin ingin keluar? Anda harus memasukkan kredensial / PIN kembali untuk masuk ke sistem SummitGear.</span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="show = false" 
                                class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                            @csrf
                            <button type="submit" 
                                    class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Ya, Keluar Akun
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth
