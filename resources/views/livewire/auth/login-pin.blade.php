<div class="auth-layout" style="min-height: 100vh; background: radial-gradient(circle at 20% 20%, rgba(255, 69, 0, 0.04) 0%, transparent 40%), radial-gradient(circle at 80% 80%, rgba(16, 31, 66, 0.07) 0%, transparent 50%), #F4F6FA; display: flex; align-items: center; justify-content: center; padding: 1rem;">
    <div class="w-full" style="max-width: 380px; margin: auto;">
        
        <!-- Main Login Card -->
        <div class="card bg-white" style="padding: 1.75rem 1.75rem; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.9); box-shadow: 0 16px 36px -8px rgba(16, 31, 66, 0.08);">
            
            <!-- Logo & Brand Header -->
            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center w-11 h-11 rounded-xl mb-2.5 shadow-md" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%); box-shadow: 0 8px 16px -4px rgba(16, 31, 66, 0.25);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 11c0 3.532-2.1 6.643-5 8.16M12 11c0 3.532 2.1 6.643 5 8.16M12 11V3m0 8h.01M12 21a9 9 0 110-18 9 9 0 010 18z" />
                    </svg>
                </div>
                <h1 class="text-xl font-black tracking-tight mb-0.5" style="color: var(--color-navy); font-size: 1.35rem;">
                    Masuk <span style="color: var(--color-coral);">Kasir</span>
                </h1>
                <p class="text-[11px] text-gray-400 font-medium">Masukkan 6 digit PIN untuk masuk shift</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="$wire.login(pin)" 
                  x-data="{ pin: '' }" 
                  x-init="$watch('pin', value => { if (value && value.length === 6) { $wire.login(value); } })"
                  @pin-error.window="pin = ''"
                  @keydown.window="
                    if ($event.key >= '0' && $event.key <= '9' && pin.length < 6) {
                        pin += $event.key;
                    } else if ($event.key === 'Backspace') {
                        pin = pin.slice(0, -1);
                    } else if ($event.key === 'Enter') {
                        $wire.login(pin);
                    }
                  ">
                
                <!-- PIN Display Boxes -->
                <div class="mb-4 flex justify-center gap-2">
                    @for($i = 0; $i < 6; $i++)
                        <div class="w-10 h-11 rounded-xl border-2 flex items-center justify-center text-lg font-black transition-all shadow-2xs select-none"
                             :class="pin.length > {{ $i }} ? 'border-[#101F42] bg-white text-[#101F42] ring-2 ring-[#101F42]/10 scale-105' : 'border-gray-200 bg-gray-50/80 text-gray-300'" 
                             style="transition-duration: 150ms;">
                            <span x-text="pin[{{ $i }}] || '—'"></span>
                        </div>
                    @endfor
                </div>

                @error('pin') 
                    <div class="mb-3 text-center">
                        <span class="text-[11px] font-bold text-red-600 block bg-red-50 py-2 px-3 rounded-lg border border-red-100 shadow-2xs animate-pulse">{{ $message }}</span>
                    </div>
                @enderror

                <!-- Numpad -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                        <button type="button" 
                                @click="if(pin.length < 6) pin += '{{ $digit }}'" 
                                class="h-11 rounded-xl bg-white border border-gray-200 text-lg font-bold text-slate-800 hover:bg-slate-50 active:bg-slate-100 transition-colors shadow-2xs select-none cursor-pointer">
                            {{ $digit }}
                        </button>
                    @endforeach
                    <button type="button" 
                            @click="pin = ''" 
                            title="Hapus Semua"
                            class="h-11 rounded-xl bg-gray-50 border border-gray-200 text-[10px] font-black text-gray-400 hover:text-gray-600 hover:bg-gray-100 active:bg-gray-200 transition-colors shadow-2xs select-none cursor-pointer flex items-center justify-center">
                        CLEAR
                    </button>
                    <button type="button" 
                            @click="if(pin.length < 6) pin += '0'" 
                            class="h-11 rounded-xl bg-white border border-gray-200 text-lg font-bold text-slate-800 hover:bg-slate-50 active:bg-slate-100 transition-colors shadow-2xs select-none cursor-pointer">
                        0
                    </button>
                    <button type="button" 
                            @click="pin = pin.slice(0, -1)" 
                            title="Hapus Satu Digit"
                            class="h-11 rounded-xl bg-gray-100 border border-gray-200 text-slate-700 hover:bg-gray-200 active:bg-gray-300 transition-colors shadow-2xs flex items-center justify-center select-none cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <flux:button type="submit" 
                             @click.prevent="$wire.login(pin)"
                             variant="primary" 
                             class="w-full h-10 text-xs font-bold mb-3">
                    Masuk ke Sistem
                </flux:button>

                <div class="text-center">
                    <flux:button href="{{ route('login') }}" variant="ghost" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                        Kembali ke Login Email
                    </flux:button>
                </div>
            </form>

        </div>

    </div>
</div>
