<div class="auth-layout" style="min-height: 100vh; background: radial-gradient(circle at 20% 20%, rgba(255, 69, 0, 0.04) 0%, transparent 40%), radial-gradient(circle at 80% 80%, rgba(16, 31, 66, 0.07) 0%, transparent 50%), #F4F6FA; display: flex; align-items: center; justify-content: center; padding: 1rem;">
    <div class="w-full" style="max-width: 400px; margin: auto;">
        
        <!-- Main Login Card -->
        <div class="card bg-white" style="padding: 2.25rem 1.85rem; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.9); box-shadow: 0 16px 36px -8px rgba(16, 31, 66, 0.08);">
            
            @if(!$requires2fa)
                <!-- Logo & Brand Header -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-3 shadow-md" style="background: linear-gradient(135deg, #FF4500 0%, #E03E00 100%); box-shadow: 0 8px 16px -4px rgba(255, 69, 0, 0.3);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m12 3-9 17h18Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m12 3 3 8-6 4" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-black tracking-tight mb-0.5" style="color: var(--color-navy); font-size: 1.45rem;">
                        SummitGear <span style="color: var(--color-coral);">POS</span>
                    </h1>
                    <p class="text-[11px] text-gray-400 font-medium">Sistem Kasir & Manajemen Rental Alat Pendakian</p>
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
                        <flux:button type="submit" variant="primary" class="w-full h-11 text-sm font-bold">
                            Masuk ke Akun
                        </flux:button>
                    </div>
                </form>

                <!-- Login Kasir (PIN) Button -->
                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500 mb-3 font-medium">Bekerja di shift kasir?</p>
                    <flux:button href="{{ route('login.pin') }}" variant="outline" class="w-full h-11 text-sm font-bold" icon="identification">
                        Masuk sebagai Kasir (PIN)
                    </flux:button>
                </div>

            @else
                <!-- STEP 2: Google Authenticator (2FA) Verification Card -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3 shadow-md" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-black tracking-tight" style="color: var(--color-navy);">
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
                        <span class="text-xs font-bold text-slate-800 truncate block">{{ $email }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-white text-navy border border-slate-200">
                        Admin
                    </span>
                </div>

                <!-- 2FA Form -->
                <form wire:submit.prevent="verify2fa" class="space-y-4">
                    @if(!$useRecoveryCode)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 text-center">
                                Masukkan 6 Digit Kode Google Authenticator
                            </label>
                            <input 
                                type="text" 
                                wire:model="twoFactorCode" 
                                maxlength="6" 
                                autofocus 
                                placeholder="000000"
                                class="w-full text-center tracking-[0.45em] font-mono text-2xl font-black py-3 px-4 border border-slate-200 rounded-xl bg-slate-50/50 shadow-inner focus:bg-white focus:ring-2 focus:ring-navy focus:border-transparent outline-none transition-all"
                            />
                            @error('twoFactorCode')
                                <span class="text-rose-600 text-xs font-bold mt-1.5 text-center block">{{ $message }}</span>
                            @enderror
                            <p class="text-[11px] text-slate-400 mt-1.5 text-center">
                                Kode diperbarui setiap 30 detik pada aplikasi di HP Anda.
                            </p>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kode Pemulihan Cadangan (Recovery Code)
                            </label>
                            <input 
                                type="text" 
                                wire:model="recoveryCode" 
                                autofocus 
                                placeholder="XXXXX-XXXXX"
                                class="w-full text-center uppercase tracking-wider font-mono text-sm font-bold py-3 px-4 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-navy focus:border-transparent outline-none"
                            />
                            @error('recoveryCode')
                                <span class="text-rose-600 text-xs font-bold mt-1.5 text-center block">{{ $message }}</span>
                            @enderror
                            <p class="text-[11px] text-slate-400 mt-1.5 text-center">
                                Masukkan salah satu kode darurat yang disimpan saat aktivasi.
                            </p>
                        </div>
                    @endif

                    <div class="pt-2">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full h-11 rounded-xl text-xs font-bold text-white shadow-md transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                                style="background: #101F42;">
                            <span wire:loading.remove wire:target="verify2fa">Verifikasi & Masuk Dashboard</span>
                            <span wire:loading wire:target="verify2fa">Memvalidasi Kode...</span>
                        </button>
                    </div>
                </form>

                <!-- Toggle Recovery Code & Cancel -->
                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col items-center gap-2.5 text-center">
                    <button type="button" 
                            wire:click="toggleRecoveryCode" 
                            class="text-xs text-slate-500 hover:text-navy font-semibold transition cursor-pointer">
                        {{ $useRecoveryCode ? '← Masukkan kode 6 digit Google Authenticator' : 'Kehilangan ponsel? Gunakan Recovery Code' }}
                    </button>

                    <button type="button" 
                            wire:click="cancel2fa" 
                            class="text-xs text-rose-600 hover:text-rose-700 font-bold transition cursor-pointer">
                        Batal & Kembali ke Login
                    </button>
                </div>
            @endif

        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; 2026 SummitGear Outdoor Rental System.
        </p>

    </div>
</div>
