<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4" style="background: radial-gradient(circle at 20% 20%, rgba(255, 69, 0, 0.04) 0%, transparent 40%), radial-gradient(circle at 80% 80%, rgba(16, 31, 66, 0.07) 0%, transparent 50%), #F4F6FA;">
    <div class="w-full max-w-md">
        
        <!-- Main Login Card with Flux -->
        <flux:card class="shadow-xl bg-white border border-slate-200/90 text-slate-900">
            
            @if(!$requires2fa)
                <!-- Logo & Brand Header -->
                <div class="text-center mb-6">
                    <x-app-logo size="lg" class="mb-3 justify-center" />
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">
                        SummitGear <span class="text-coral">POS</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Sistem Kasir & Manajemen Rental Alat Pendakian</p>
                </div>

                <!-- Regular Login Form -->
                <form wire:submit.prevent="login" class="space-y-4">
                    <flux:input 
                        label="Alamat Email" 
                        type="email" 
                        wire:model="email" 
                        placeholder="nama@summitgear.com" 
                        icon="envelope" 
                        required 
                        autofocus 
                    />

                    <flux:input 
                        label="Kata Sandi" 
                        type="password" 
                        wire:model="password" 
                        placeholder="••••••••" 
                        icon="key" 
                        viewable 
                        required 
                    />

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary" class="w-full h-11 text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm">
                            Masuk ke Akun
                        </flux:button>
                    </div>
                </form>

                <!-- Login Kasir (PIN) Button -->
                <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500 mb-3 font-medium">Bekerja di shift kasir?</p>
                    <flux:button href="{{ route('login.pin') }}" variant="filled" class="w-full h-11 text-sm font-bold border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-800" icon="identification">
                        Masuk sebagai Kasir (PIN)
                    </flux:button>
                </div>

            @else
                <!-- STEP 2: Google Authenticator (2FA) Verification Card -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3 shadow-md bg-navy text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold tracking-tight text-slate-900">
                        Verifikasi 2FA Admin
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Lapisan keamanan tambahan Google Authenticator
                    </p>
                </div>

                <!-- Account Identity Badge -->
                <div class="mb-5 p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Login Sebagai</span>
                        <span class="text-xs font-bold text-slate-900 truncate block">{{ $email }}</span>
                    </div>
                    <flux:badge color="zinc" size="sm">Admin</flux:badge>
                </div>

                <!-- 2FA Form -->
                <form wire:submit.prevent="verify2fa" class="space-y-4">
                    @if(!$useRecoveryCode)
                        <div>
                            <flux:label class="text-xs font-bold text-center block mb-1.5 text-slate-900">
                                Masukkan 6 Digit Kode Google Authenticator
                            </flux:label>
                            <input 
                                type="text" 
                                wire:model="twoFactorCode" 
                                maxlength="6" 
                                autofocus 
                                placeholder="000000"
                                class="w-full text-center tracking-[0.45em] font-mono text-2xl font-black py-3 px-4 border border-slate-300 rounded-xl bg-white shadow-inner focus:ring-2 focus:ring-slate-900 focus:border-transparent outline-none transition-all text-slate-900"
                            />
                            @error('twoFactorCode')
                                <span class="text-red-600 text-xs font-bold mt-1.5 text-center block">{{ $message }}</span>
                            @enderror
                            <p class="text-[11px] text-slate-400 mt-1.5 text-center">
                                Kode diperbarui setiap 30 detik pada aplikasi di HP Anda.
                            </p>
                        </div>
                    @else
                        <div>
                            <flux:label class="text-xs font-bold mb-1.5 block text-slate-900">
                                Kode Pemulihan Cadangan (Recovery Code)
                            </flux:label>
                            <input 
                                type="text" 
                                wire:model="recoveryCode" 
                                autofocus 
                                placeholder="XXXXX-XXXXX"
                                class="w-full text-center uppercase tracking-wider font-mono text-sm font-bold py-3 px-4 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent outline-none text-slate-900"
                            />
                            @error('recoveryCode')
                                <span class="text-red-600 text-xs font-bold mt-1.5 text-center block">{{ $message }}</span>
                            @enderror
                            <p class="text-[11px] text-slate-400 mt-1.5 text-center">
                                Masukkan salah satu kode darurat yang disimpan saat aktivasi.
                            </p>
                        </div>
                    @endif

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary" class="w-full h-11 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="verify2fa">Verifikasi & Masuk Dashboard</span>
                            <span wire:loading wire:target="verify2fa">Memvalidasi Kode...</span>
                        </flux:button>
                    </div>
                </form>

                <!-- Toggle Recovery Code & Cancel -->
                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col items-center gap-2.5 text-center">
                    <button type="button" 
                            wire:click="toggleRecoveryCode" 
                            class="text-xs text-slate-500 hover:text-slate-900 font-semibold transition cursor-pointer">
                        {{ $useRecoveryCode ? '← Masukkan kode 6 digit Google Authenticator' : 'Kehilangan ponsel? Gunakan Recovery Code' }}
                    </button>

                    <button type="button" 
                            wire:click="cancel2fa" 
                            class="text-xs text-red-600 hover:text-red-700 font-bold transition cursor-pointer">
                        Batal
                    </button>
                </div>
            @endif

        </flux:card>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; 2026 SummitGear Outdoor Rental System.
        </p>

    </div>
</div>
