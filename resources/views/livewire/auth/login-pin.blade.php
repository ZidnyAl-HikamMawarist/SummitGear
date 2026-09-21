<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4" style="background: radial-gradient(circle at 20% 20%, rgba(255, 69, 0, 0.04) 0%, transparent 40%), radial-gradient(circle at 80% 80%, rgba(16, 31, 66, 0.07) 0%, transparent 50%), #F4F6FA;">
    <div class="w-full max-w-sm">
        
        <!-- Main Login Card with Flux -->
        <flux:card class="shadow-xl bg-white border border-slate-200/90 text-slate-900">
            
            <!-- Logo & Brand Header -->
            <div class="text-center mb-6">
                <x-app-logo size="lg" class="mb-3 justify-center" />
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Masuk <span class="text-coral">Kasir</span>
                </h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Masukkan 6 digit PIN untuk masuk shift</p>
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
                  "
                  class="space-y-4">
                
                <!-- PIN Display Boxes -->
                <div class="flex justify-center gap-2">
                    @for($i = 0; $i < 6; $i++)
                        <div class="w-10 h-11 rounded-xl border-2 flex items-center justify-center text-lg font-black transition-all shadow-2xs select-none"
                             :class="pin.length > {{ $i }} ? 'border-slate-900 bg-white text-slate-900 ring-2 ring-slate-900/10 scale-105' : 'border-slate-200 bg-slate-50/80 text-slate-400'" 
                             style="transition-duration: 150ms;">
                            <span x-text="pin[{{ $i }}] || '—'"></span>
                        </div>
                    @endfor
                </div>

                @error('pin') 
                    <div class="text-center">
                        <span class="text-xs font-bold text-red-600 block bg-red-50 py-2 px-3 rounded-lg border border-red-100 shadow-2xs">{{ $message }}</span>
                    </div>
                @enderror

                <!-- Numpad -->
                <div class="grid grid-cols-3 gap-2 pt-2">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                        <button type="button" 
                                @click="if(pin.length < 6) pin += '{{ $digit }}'" 
                                class="h-11 rounded-xl bg-white border border-slate-200 text-lg font-bold text-slate-800 hover:bg-slate-50 active:bg-slate-100 transition-colors shadow-2xs select-none cursor-pointer">
                            {{ $digit }}
                        </button>
                    @endforeach
                    <button type="button" 
                            @click="pin = ''" 
                            title="Hapus Semua"
                            class="h-11 rounded-xl bg-slate-50 border border-slate-200 text-[10px] font-black text-slate-400 hover:text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors shadow-2xs select-none cursor-pointer flex items-center justify-center">
                        CLEAR
                    </button>
                    <button type="button" 
                            @click="if(pin.length < 6) pin += '0'" 
                            class="h-11 rounded-xl bg-white border border-slate-200 text-lg font-bold text-slate-800 hover:bg-slate-50 active:bg-slate-100 transition-colors shadow-2xs select-none cursor-pointer">
                        0
                    </button>
                    <button type="button" 
                            @click="pin = pin.slice(0, -1)" 
                            title="Hapus Satu Digit"
                            class="h-11 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 active:bg-slate-300 transition-colors shadow-2xs flex items-center justify-center select-none cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <flux:button type="submit" 
                             variant="primary" 
                             class="w-full h-11 text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm">
                    Masuk ke Sistem
                </flux:button>

                <div class="text-center pt-2">
                    <flux:button href="{{ route('login') }}" variant="ghost" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                        Kembali ke Login Email
                    </flux:button>
                </div>
            </form>

        </flux:card>

    </div>
</div>
