<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Pengaturan Sistem" />

        <div class="content-area">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-navy">Pengaturan Sistem</h2>
                <p class="text-sm text-gray-500 mt-0.5">Konfigurasi parameter inti yang memengaruhi batasan booking, durasi hold, dan denda keterlambatan.</p>
            </div>

            @if (session()->has('message'))
                <div class="mb-6 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="sg-card p-0 overflow-hidden">
                    <!-- Section 1: Aturan Reservasi -->
                    <div class="p-5 border-b border-gray-100 bg-slate-50 flex items-center gap-2.5">
                        <div class="stat-icon-circle" style="background-color: rgba(255,69,0,0.1); color: var(--color-coral); width: 36px; height: 36px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Aturan Reservasi & Booking</h3>
                            <p class="text-xs text-gray-400">Batasan jendela pemesanan di muka dan durasi penahanan stok</p>
                        </div>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="form-label">Maksimal Jendela Booking (Hari)</label>
                            <div class="relative">
                                <input type="number" wire:model="booking_window_days" class="form-control pr-14 text-base font-bold" min="1" max="20">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-400">
                                    Hari
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Rentang maksimal pelanggan dapat memesan tanggal sewa ke depan (Maksimal 20 Hari).</p>
                            @error('booking_window_days') <span class="text-danger text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Batas Waktu Tunggu / Hold Booking (Jam)</label>
                            <div class="relative">
                                <input type="number" wire:model="hold_duration_hours" class="form-control pr-14 text-base font-bold" min="1" max="48">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-400">
                                    Jam
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Durasi sistem menahan unit sebelum booking otomatis dibatalkan jika DP/pelunasan belum selesai.</p>
                            @error('hold_duration_hours') <span class="text-danger text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Batas Kadaluarsa Booking Online (Jam)</label>
                            <div class="relative">
                                <input type="number" wire:model="online_booking_expire_hours" class="form-control pr-14 text-base font-bold" min="1" max="168">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-400">
                                    Jam
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Durasi kadaluarsa otomatis untuk Booking Online masuk yang tidak diproses.</p>
                            @error('online_booking_expire_hours') <span class="text-danger text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Section 2: Aturan Denda & Keterlambatan -->
                    <div class="p-5 border-t border-b border-gray-100 bg-slate-50 flex items-center gap-2.5">
                        <div class="stat-icon-circle" style="background-color: #FEF2F2; color: #DC2626; width: 36px; height: 36px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Aturan Denda & Kompensasi</h3>
                            <p class="text-xs text-gray-400">Besaran denda keterlambatan per jam otomatis</p>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Tarif Denda Keterlambatan / Jam</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-400">
                                    Rp
                                </div>
                                <input type="number" wire:model="late_fee_per_hour" class="form-control pl-11 text-base font-bold">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Nominal denda per jam yang otomatis ditagihkan saat status rental beralih ke OVERDUE.</p>
                            @error('late_fee_per_hour') <span class="text-danger text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="p-5 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn text-xs font-bold text-white shadow-md transition flex items-center gap-2" style="background-color: var(--color-navy); border-radius: 10px; padding: 0.75rem 1.75rem;">
                            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Simpan Perubahan Pengaturan
                        </button>
                    </div>
                </div>
            </form>

            <!-- Section 3: Keamanan Akun & Google Authenticator (2FA) -->
            <div class="mt-8 sg-card p-0 overflow-hidden border border-slate-200">
                <div class="p-5 border-b border-gray-100 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-sm" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Keamanan 2FA (Google Authenticator)</h3>
                            <p class="text-xs text-gray-500">Perlindungan ganda untuk akun Administrator menggunakan kode OTP berbasis waktu</p>
                        </div>
                    </div>
                    @if($twoFactorEnabled)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            2FA Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            2FA Nonaktif
                        </span>
                    @endif
                </div>

                @if(session()->has('message_2fa'))
                    <div class="m-6 p-4 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('message_2fa') }}</span>
                    </div>
                @endif

                <div class="p-6">
                    @if(!$twoFactorEnabled && !$showingQrCode)
                        <!-- Penjelasan & Tombol Aktivasi -->
                        <div class="max-w-2xl">
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Autentikasi Dua Faktor (2FA) menambahkan lapisan keamanan ekstra pada proses login Admin. Saat fitur ini diaktifkan, Anda akan diminta memasukkan kata sandi serta 6 digit kode unik dari aplikasi <strong>Google Authenticator</strong> di ponsel Anda.
                            </p>
                            <div class="mt-4 flex items-center gap-3">
                                <button type="button" 
                                        wire:click="initiateTwoFactor" 
                                        class="btn text-xs font-bold text-white shadow-sm hover:shadow transition flex items-center gap-2 px-4 py-2.5 rounded-xl cursor-pointer" 
                                        style="background: #101F42;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Aktifkan Google Authenticator (2FA)
                                </button>
                            </div>
                        </div>

                    @elseif($showingQrCode)
                        <!-- Form Setup 2FA: Tampilkan QR Code Besar & Input Kode Verifikasi di Bawahnya -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-8 sm:p-10">
                            <div class="max-w-sm mx-auto flex flex-col items-center text-center">

                                <!-- Judul & Instruksi -->
                                <p class="text-sm font-bold text-slate-800">Pindai dengan Google Authenticator</p>
                                <p class="text-xs text-slate-500 mt-2 max-w-xs leading-relaxed">
                                    Buka aplikasi <strong>Google Authenticator</strong> di smartphone Anda, klik tanda <strong>(+)</strong> dan pilih <strong>"Pindai kode QR"</strong>.
                                </p>

                                <!-- Box QR Code Ukuran Besar & Leluasa -->
                                <div class="qr-code-box p-5 bg-white rounded-3xl shadow-sm border border-slate-200 w-[270px] h-[270px] sm:w-[290px] sm:h-[290px] flex items-center justify-center mt-6 mb-6 transition-transform hover:scale-[1.01]">
                                    <div class="w-full h-full flex items-center justify-center [&_svg]:!w-full [&_svg]:!h-full [&_svg]:!max-w-none [&_svg]:!max-h-none [&_svg]:block">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                </div>

                                <!-- Kunci Penyiapan Manual -->
                                <div class="w-full p-4 bg-white rounded-xl border border-slate-200 shadow-2xs text-center">
                                    <span class="text-[10px] text-slate-400 block font-semibold uppercase tracking-wider mb-2">Kunci Penyiapan Manual</span>
                                    <div class="flex items-center justify-between gap-2 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                        <code class="text-xs font-mono font-bold text-slate-800 tracking-wider truncate select-all">{{ $secretKey }}</code>
                                        <button type="button"
                                                x-data="{ copied: false }"
                                                @click="navigator.clipboard.writeText('{{ $secretKey }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                class="shrink-0 px-2.5 py-1 text-[10px] font-bold rounded-md border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition cursor-pointer">
                                            <span x-show="!copied">Salin</span>
                                            <span x-show="copied" class="text-emerald-600 font-bold" style="display: none;">Tersalin!</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Garis Pemisah -->
                                <div class="w-full border-t border-slate-200/80 mt-8 mb-6 relative">
                                    <span class="absolute left-1/2 -top-2.5 -translate-x-1/2 bg-slate-50 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Langkah Konfirmasi
                                    </span>
                                </div>

                                <!-- Input Konfirmasi Kode 6 Digit -->
                                <div class="w-full">
                                    <h4 class="text-sm font-bold text-navy mb-1">Konfirmasi Kode 6 Digit</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed mb-5">
                                        Masukkan 6 digit angka yang muncul di Google Authenticator untuk memverifikasi pemasangan:
                                    </p>

                                    <div class="mb-6">
                                        <input type="text"
                                               wire:model="confirmationCode"
                                               maxlength="6"
                                               placeholder="000000"
                                               autocomplete="off"
                                               class="w-full text-center tracking-[0.5em] font-mono text-2xl font-bold py-4 px-4 border border-slate-200 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-navy focus:border-transparent outline-none">
                                        @error('confirmationCode')
                                            <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-center gap-3">
                                        <button type="button"
                                                wire:click="cancelInitiateTwoFactor"
                                                class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer shadow-2xs">
                                            Batal
                                        </button>
                                        <button type="button"
                                                wire:click="confirmTwoFactor"
                                                wire:loading.attr="disabled"
                                                class="px-6 py-2.5 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                                style="background: #101F42;">
                                            <span wire:loading.remove wire:target="confirmTwoFactor">Konfirmasi & Aktifkan 2FA</span>
                                            <span wire:loading wire:target="confirmTwoFactor">Memverifikasi...</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                    @else
                        <!-- Status 2FA Aktif: Kelola & Nonaktifkan -->
                        <div class="space-y-4">
                            <div class="p-4 bg-emerald-50/70 border border-emerald-200/80 rounded-xl flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <div class="text-xs text-slate-700 space-y-1">
                                    <p class="font-bold text-emerald-900">Akun Anda Dilindungi Google Authenticator 2FA</p>
                                    <p class="text-slate-600">Setiap login dengan email admin akan mewajibkan verifikasi 6 digit kode dari aplikasi Google Authenticator Anda.</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <button type="button" 
                                        wire:click="$set('showRecoveryCodesModal', true)" 
                                        class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    Lihat Kode Pemulihan Cadangan (Recovery Codes)
                                </button>

                                <button type="button" 
                                        wire:click="disableTwoFactor" 
                                        class="px-4 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100/80 border border-rose-200 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Nonaktifkan 2FA
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal Recovery Codes -->
            @if($showRecoveryCodesModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeRecoveryModal"></div>
                
                <div class="relative bg-white rounded-2xl text-left shadow-2xl transform transition-all w-full max-w-md border border-slate-100 p-6 z-10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center shrink-0 text-amber-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-slate-900">Kode Pemulihan Cadangan (Recovery Codes)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Simpan kode cadangan ini di tempat yang aman. Kode ini dapat digunakan untuk login jika Anda kehilangan akses ke smartphone Anda.</p>
                        </div>
                    </div>

                    <!-- Grid Recovery Codes -->
                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200/80 rounded-xl">
                        <div class="grid grid-cols-2 gap-2 font-mono text-xs font-bold text-slate-800 text-center">
                            @forelse($recoveryCodes as $code)
                                <div class="p-2 bg-white rounded border border-slate-200 select-all">{{ $code }}</div>
                            @empty
                                <div class="col-span-2 text-slate-400 text-xs py-2">Belum ada kode pemulihan.</div>
                            @endforelse
                        </div>
                    </div>

                    <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/60 mt-3">
                        Setiap kode hanya dapat digunakan satu kali.
                    </p>

                    <div class="mt-6 flex justify-end">
                        <button type="button"
                                wire:click="closeRecoveryModal"
                                class="px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-sm transition cursor-pointer"
                                style="background: #101F42;">
                            Saya Telah Menyimpan Kode Ini
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </main>
</div>
