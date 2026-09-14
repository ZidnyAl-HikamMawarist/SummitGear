@auth
<flux:modal name="logout-modal" class="max-w-md" x-on:open-logout-modal.window="$flux.modal('logout-modal').show()">
    <div class="space-y-6">
        <!-- Top Icon Header -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600 shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div>
                <flux:heading size="lg">Konfirmasi Keluar (Logout)</flux:heading>
                <flux:subheading size="sm">Validasi pengakhiran sesi akun pengguna</flux:subheading>
            </div>
        </div>

        <!-- User Account Card -->
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full bg-[#101F42] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <flux:badge size="sm" color="zinc">{{ strtoupper(auth()->user()->role) }}</flux:badge>
        </div>

        <!-- Warning Note -->
        <div class="flex items-start gap-2.5 text-xs text-slate-600 bg-amber-50/70 border border-amber-200/60 p-3 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Apakah Anda yakin ingin keluar? Anda harus memasukkan kredensial / PIN kembali untuk masuk ke sistem SummitGear.</span>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                @csrf
                <flux:button type="submit" variant="danger">
                    Ya, Keluar Akun
                </flux:button>
            </form>
        </div>
    </div>
</flux:modal>
@endauth
