<div class="h-screen max-h-screen w-full flex flex-col lg:flex-row bg-slate-950 font-sans text-slate-900 antialiased overflow-hidden selection:bg-orange-500 selection:text-white">
    
    <!-- LEFT SIDE: Professional Mountain Brand Hero -->
    <div class="relative hidden lg:flex lg:w-1/2 xl:w-[56%] h-full max-h-screen flex-col justify-between p-8 xl:p-12 overflow-hidden bg-slate-950 select-none">
        
        <!-- Single Background Illustration -->
        <div class="absolute inset-0 z-0">
            <img 
                src="{{ asset('images/summit_illustration.jpg') }}" 
                alt="SummitGear Mountain Illustration" 
                class="w-full h-full object-cover object-top brightness-[0.85] contrast-[1.08] filter scale-100 transition-transform duration-700 hover:scale-105"
            >
            <!-- Premium Gradient Overlays & Atmospheric Glow -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/25 to-transparent"></div>
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-orange-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Top Left Brand Badge -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3 bg-slate-900/90 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-white/15 shadow-2xl">
                <x-app-logo size="sm" />
                <div class="flex flex-col">
                    <span class="text-xs font-black tracking-wider text-white uppercase">SummitGear</span>
                    <span class="text-[10px] font-semibold text-slate-400">Enterprise POS & Rental</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 backdrop-blur-md shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Aktif v2.5
                </span>
            </div>
        </div>

        <!-- Middle Content & Glassmorphic Trust Card -->
        <div class="relative z-10 max-w-xl my-auto py-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-orange-500/20 border border-orange-500/30 text-orange-400 text-xs font-black uppercase tracking-wider mb-4 backdrop-blur-md shadow-sm">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Professional Outdoor POS
            </div>

            <h1 class="text-3xl xl:text-4xl font-black text-white leading-tight tracking-tight mb-3 drop-shadow-md">
                Solusi Terpadu <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-emerald-400">
                    Operasional & Rental Outdoor.
                </span>
            </h1>

            <p class="text-slate-200 text-xs xl:text-sm leading-relaxed font-normal mb-5 max-w-md drop-shadow-xs">
                Kelola stok perlengkapan mendaki, kalender reservasi, transaksi kasir cepat, hingga analisis pendapatan dalam satu platform modern berkecepatan tinggi.
            </p>

            <!-- Glassmorphic Trust Card -->
            <div class="p-4 rounded-2xl bg-slate-900/75 border border-white/15 backdrop-blur-md shadow-xl max-w-md">
                <div class="flex items-start gap-3.5">
                    <div class="p-2.5 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 text-white shrink-0 shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-white mb-0.5">Keamanan & Kecepatan Terjamin</h4>
                        <p class="text-[11px] text-slate-300 leading-relaxed">
                            Enkripsi data transaksi, verifikasi 2FA Google Authenticator, serta otorisasi PIN kasir bertingkat.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Footer Info -->
        <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-4 text-xs text-slate-400">
            <div>
                &copy; {{ date('Y') }} <span class="text-slate-200 font-semibold">SummitGear Outdoor System</span>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <span class="text-slate-300 font-medium">Enterprise Edition</span>
                <span>•</span>
                <span class="text-slate-300 font-medium">Monitoring Ready</span>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: Auth Container Fitted Exactly to 100vh Without Overflow -->
    <div class="w-full lg:w-1/2 xl:w-[44%] h-full max-h-screen flex flex-col justify-between items-center p-6 sm:p-8 lg:p-10 xl:p-12 bg-white overflow-y-auto lg:overflow-hidden">
        
        <!-- Mobile Header (Visible only on small screens) -->
        <div class="w-full lg:hidden flex items-center justify-between mb-4">
            <x-app-logo size="md" variant="full" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistem Online
            </span>
        </div>

        <!-- Central Form Wrapper -->
        <div class="w-full my-auto max-w-[420px]">
            
            @if(!$requires2fa)
                <!-- Form Header -->
                <div class="mb-5">
                    <div class="inline-flex p-2.5 rounded-2xl bg-slate-900 text-white mb-3 shadow-md ring-4 ring-slate-100">
                        <x-app-logo size="md" />
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                        Selamat Datang Kembali
                    </h2>
                    <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed">
                        Masukkan akun email dan kata sandi Anda untuk mengakses portal kasir & rental.
                    </p>
                </div>

                <!-- Main Login Form -->
                <form wire:submit.prevent="login" class="space-y-4" novalidate>
                    
                    <!-- Email Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="email" class="block text-[11px] font-bold text-slate-800 uppercase tracking-wider">
                                Alamat Email Perusahaan <span class="text-orange-600 font-black">*</span>
                            </label>
                        </div>

                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            
                            <input 
                                id="email"
                                type="email" 
                                wire:model="email" 
                                placeholder="nama@summitgear.com" 
                                required 
                                autofocus 
                                autocomplete="username"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 @error('email') border-red-500 bg-red-50/40 text-red-950 focus:ring-red-500/20 focus:border-red-500 @else border-slate-300 text-slate-900 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15 focus:bg-white @enderror rounded-xl text-sm placeholder-slate-400 focus:outline-none transition-all font-medium"
                                aria-describedby="@error('email') email-error @else email-helper @enderror"
                            />
                        </div>

                        @error('email')
                            <div id="email-error" class="flex items-center gap-1.5 mt-1.5 text-red-600 text-[11px] font-bold" role="alert">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @else
                            <p id="email-helper" class="text-[10px] text-slate-500 mt-1 font-medium">
                                Gunakan email akun yang terdaftar di sistem SummitGear.
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-[11px] font-bold text-slate-800 uppercase tracking-wider">
                                Kata Sandi <span class="text-orange-600 font-black">*</span>
                            </label>
                            
                            <!-- Help / Forgot hint -->
                            <div x-data="{ tooltip: false }" class="relative">
                                <button 
                                    type="button" 
                                    @mouseenter="tooltip = true" 
                                    @mouseleave="tooltip = false"
                                    @click="tooltip = !tooltip"
                                    class="text-[11px] text-orange-600 hover:text-orange-700 font-bold transition focus:outline-none">
                                    Lupa Sandi?
                                </button>
                                <div 
                                    x-show="tooltip" 
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute right-0 bottom-full mb-1.5 w-52 p-2 bg-slate-900 text-white text-[10px] rounded-xl shadow-xl z-20 leading-relaxed text-center pointer-events-none">
                                    Hubungi Superadmin untuk reset kata sandi akun Anda.
                                </div>
                            </div>
                        </div>

                        <div class="relative rounded-xl shadow-xs" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>

                            <input 
                                id="password"
                                :type="show ? 'text' : 'password'" 
                                wire:model="password" 
                                placeholder="Masukkan minimal 8 karakter" 
                                required 
                                autocomplete="current-password"
                                class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border-2 @error('password') border-red-500 bg-red-50/40 text-red-950 focus:ring-red-500/20 focus:border-red-500 @else border-slate-300 text-slate-900 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15 focus:bg-white @enderror rounded-xl text-sm placeholder-slate-400 focus:outline-none transition-all font-medium"
                                aria-describedby="@error('password') password-error @enderror"
                            />

                            <!-- Toggle Visibility Button -->
                            <button 
                                type="button" 
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 pl-2 flex items-center text-slate-500 hover:text-slate-800 transition focus:outline-none focus:text-orange-600 cursor-pointer min-w-[40px] justify-center"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.97 8.97 0 013.682-.793c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div id="password-error" class="flex items-center gap-1.5 mt-1.5 text-red-600 text-[11px] font-bold" role="alert">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Action Submit Button -->
                    <div class="pt-1">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-6 bg-slate-900 hover:bg-orange-600 active:bg-orange-700 text-white text-sm font-extrabold rounded-xl shadow-md hover:shadow-orange-600/25 transition-all duration-200 flex flex-row items-center justify-center group cursor-pointer focus:outline-none focus:ring-4 focus:ring-orange-500/25"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="login" class="flex flex-row items-center justify-center gap-2" style="display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 0.5rem;">
                                <span class="whitespace-nowrap">Masuk ke Sistem POS</span>
                                <svg class="w-4 h-4 shrink-0 inline-block transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </span>
                            <span wire:loading.inline-flex wire:target="login" class="flex flex-row items-center justify-center gap-2 text-white" style="display: none; flex-direction: row; align-items: center; justify-content: center; gap: 0.5rem;">
                                <svg class="animate-spin w-4 h-4 shrink-0 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="whitespace-nowrap">Memverifikasi Kredensial...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Prominent Kasir PIN Section -->
                <div class="mt-5 pt-4 border-t border-slate-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Akses Cepat Shift Kasir</span>
                        <span class="px-2 py-0.5 rounded-md bg-orange-100 text-orange-800 text-[9px] font-extrabold uppercase tracking-wider">
                            PIN Auth
                        </span>
                    </div>

                    <a href="{{ route('login.pin') }}" 
                       class="w-full py-2.5 px-3.5 bg-slate-50 hover:bg-orange-50/60 text-slate-800 hover:text-orange-950 text-xs font-bold rounded-xl border-2 border-slate-200 hover:border-orange-400 flex items-center justify-between transition-all duration-200 shadow-xs group focus:outline-none focus:ring-4 focus:ring-orange-500/15">
                        <div class="flex items-center gap-2.5">
                            <div class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 shadow-xs group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="font-extrabold text-slate-900 text-xs group-hover:text-orange-900">Masuk sebagai Kasir (PIN)</div>
                                <div class="text-[10px] text-slate-500 font-medium">Buka kasir dengan 4-6 digit PIN shift</div>
                            </div>
                        </div>
                        
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-600 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

            @else
                <!-- STEP 2: 2FA Verification Form -->
                <div class="text-center mb-5">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl mb-3 shadow-lg bg-slate-900 text-white ring-4 ring-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900">
                        Verifikasi 2FA Admin
                    </h2>
                    <p class="text-xs text-slate-600 mt-1 font-medium">
                        Masukkan kode keamanan dari aplikasi Google Authenticator Anda.
                    </p>
                </div>

                <!-- Account Badge -->
                <div class="mb-4 p-3 bg-slate-50 rounded-2xl border-2 border-slate-200 flex items-center justify-between shadow-xs">
                    <div class="min-w-0 pr-2">
                        <span class="text-[9px] text-slate-500 font-bold block uppercase tracking-wider">Login Sebagai</span>
                        <span class="text-xs font-bold text-slate-900 truncate block">{{ $email }}</span>
                    </div>
                    <span class="px-2 py-0.5 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider">
                        Admin
                    </span>
                </div>

                <!-- 2FA Form -->
                <form wire:submit.prevent="verify2fa" class="space-y-3.5" novalidate>
                    @if(!$useRecoveryCode)
                        <div>
                            <label for="twoFactorCode" class="text-[11px] font-bold text-center block mb-1.5 text-slate-800 uppercase tracking-wider">
                                Masukkan 6 Digit Kode Otentikasi
                            </label>
                            <input 
                                id="twoFactorCode"
                                type="text" 
                                wire:model="twoFactorCode" 
                                maxlength="6" 
                                autofocus 
                                placeholder="000000"
                                class="w-full text-center tracking-[0.4em] font-mono text-xl font-black py-2.5 px-4 border-2 @error('twoFactorCode') border-red-500 bg-red-50/40 text-red-950 focus:ring-red-500/20 @else border-slate-300 text-slate-900 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15 focus:bg-white @enderror rounded-xl bg-slate-50 shadow-inner outline-none transition-all"
                            />
                            @error('twoFactorCode')
                                <div class="flex items-center justify-center gap-1.5 mt-1.5 text-red-600 text-[11px] font-bold" role="alert">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                            <p class="text-[10px] text-slate-500 mt-1.5 text-center font-medium">
                                Kode diperbarui setiap 30 detik pada aplikasi di HP Anda.
                            </p>
                        </div>
                    @else
                        <div>
                            <label for="recoveryCode" class="text-[11px] font-bold mb-1.5 block text-slate-800 uppercase tracking-wider">
                                Kode Pemulihan Cadangan (Recovery Code)
                            </label>
                            <input 
                                id="recoveryCode"
                                type="text" 
                                wire:model="recoveryCode" 
                                autofocus 
                                placeholder="XXXXX-XXXXX"
                                class="w-full text-center uppercase tracking-wider font-mono text-xs font-bold py-2.5 px-4 border-2 @error('recoveryCode') border-red-500 bg-red-50/40 text-red-950 @else border-slate-300 text-slate-900 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15 focus:bg-white @enderror rounded-xl bg-slate-50 outline-none transition-all"
                            />
                            @error('recoveryCode')
                                <div class="flex items-center gap-1.5 mt-1.5 text-red-600 text-[11px] font-bold" role="alert">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                            <p class="text-[10px] text-slate-500 mt-1.5 text-center font-medium">
                                Masukkan salah satu kode darurat yang disimpan saat aktivasi.
                            </p>
                        </div>
                    @endif

                    <div class="pt-1">
                        <button 
                            type="submit" 
                            class="w-full py-2.5 px-6 bg-slate-900 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-md transition-all duration-200 cursor-pointer focus:outline-none focus:ring-4 focus:ring-orange-500/25 flex items-center justify-center gap-2"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="verify2fa">Verifikasi & Masuk Dashboard</span>
                            <span wire:loading.inline-flex wire:target="verify2fa" class="inline-flex items-center justify-center gap-2 text-white">
                                <svg class="animate-spin w-3.5 h-3.5 shrink-0 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="whitespace-nowrap">Memvalidasi Kode...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Toggle Recovery Code & Cancel -->
                <div class="mt-4 pt-3 border-t border-slate-200 flex flex-col items-center gap-2 text-center">
                    <button type="button" 
                            wire:click="toggleRecoveryCode" 
                            class="text-[11px] text-slate-600 hover:text-slate-900 font-semibold transition cursor-pointer">
                        {{ $useRecoveryCode ? '← Masukkan kode 6 digit Google Authenticator' : 'Kehilangan ponsel? Gunakan Recovery Code' }}
                    </button>

                    <button type="button" 
                            wire:click="cancel2fa" 
                            class="text-[11px] text-red-600 hover:text-red-700 font-bold transition cursor-pointer">
                        Batal & Kembali
                    </button>
                </div>
            @endif

        </div>

        <!-- Footer -->
        <p class="text-center text-[11px] text-slate-400 mt-4 font-medium">
            &copy; {{ date('Y') }} SummitGear Outdoor Rental System.
        </p>
    </div>
</div>
