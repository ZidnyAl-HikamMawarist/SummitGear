<div class="admin-layout" x-data="{ nikVal: @entangle('nik').live }">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar :title="$customerId ? 'Detail / Edit Pelanggan' : 'Daftarkan Pelanggan Baru'" />

        <div class="content-area">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.customers') }}"
                        class="w-10 h-10 rounded-xl bg-white border border-gray-200 hover:border-navy text-navy hover:bg-slate-50 flex items-center justify-center transition shadow-sm shrink-0"
                        title="Kembali ke Daftar Pelanggan">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate uppercase tracking-wider">Pelanggan</span>
                            <span class="text-slate text-xs">/</span>
                            <span
                                class="text-xs font-bold text-coral">{{ $customerId ? 'Edit Data' : 'Registrasi Baru' }}</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            {{ $customerId ? 'Edit Data Pelanggan: ' . $name : 'Daftarkan Pelanggan Baru' }}
                        </h2>
                    </div>
                </div>

                @if($customerId)
                    <div class="flex items-center gap-2">
                        <span class="badge badge-info">ID Pelanggan:
                            #{{ str_pad($customerId, 5, '0', STR_PAD_LEFT) }}</span>
                        @if($has_consent)
                            <span class="badge badge-success">✓ S&K Disetujui</span>
                        @endif
                    </div>
                @endif
            </div>

            @if (session()->has('message'))
                <div
                    class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                    <!-- KARTU 1: DATA IDENTITAS PRIBADI -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-header-left">
                                <div class="form-section-icon" style="background-color: #EFF6FF; color: #1D4ED8;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="form-section-title">Informasi Dasar Pelanggan</h3>
                                    <p class="form-section-subtitle">Data resmi penyewa untuk pencocokan fisik & kontak
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-body space-y-5">
                            <!-- Nama Lengkap Sesuai KTP -->
                            <div class="form-group mb-0">
                                <div class="form-label-row">
                                    <label for="customer_name" class="form-label-text">
                                        Nama Lengkap Sesuai KTP <span class="form-label-required">*</span>
                                    </label>
                                </div>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="customer_name" wire:model="name"
                                        class="form-control input-with-prefix font-medium"
                                        placeholder="Contoh: Budi Santoso" required autofocus>
                                </div>
                                <p class="form-hint">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-slate"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Nama harus sesuai persis dengan kartu identitas KTP/SIM.
                                </p>
                                @error('name') <span
                                    class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- NIK (16 Digit) -->
                            <div class="form-group mb-0">
                                <div class="form-label-row">
                                    <label for="customer_nik" class="form-label-text">
                                        Nomor Induk Kependudukan (NIK) <span class="form-label-required">*</span>
                                    </label>
                                    <span class="digit-badge" :class="{
                                            'digit-badge-valid': (nikVal || '').length === 16,
                                            'digit-badge-pending': (nikVal || '').length > 0 && (nikVal || '').length < 16,
                                            'digit-badge-invalid': (nikVal || '').length > 16
                                        }">
                                        <span x-text="(nikVal || '').length">0</span>/16 Digit
                                    </span>
                                </div>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        </svg>
                                    </span>
                                    <input type="text" id="customer_nik" wire:model="nik"
                                        class="form-control input-with-prefix font-mono tracking-wider font-semibold"
                                        placeholder="Contoh: 3201123456780001" maxlength="16" required
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <p class="form-hint">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-slate"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Hanya 16 angka numerik. Tidak boleh ada spasi atau karakter khusus.
                                </p>
                                @error('nik') <span
                                    class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Nomor Handphone (WA Aktif) -->
                            <div class="form-group mb-0">
                                <div class="form-label-row">
                                    <label for="customer_phone" class="form-label-text">
                                        Nomor WhatsApp / Kontak Aktif
                                    </label>
                                    <span class="badge badge-success text-xs font-bold"
                                        style="padding: 0.15rem 0.5rem;">
                                        WhatsApp Notifikasi
                                    </span>
                                </div>
                                <div class="form-input-group">
                                    <span class="input-icon-prefix" style="color: #10B981;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </span>
                                    <input type="tel" id="customer_phone" wire:model="phone"
                                        class="form-control input-with-prefix font-semibold"
                                        placeholder="Contoh: 081234567890"
                                        oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                                </div>
                                <p class="form-hint">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-slate"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Invoice digital & notifikasi pengingat kembali alat akan dikirimkan ke nomor ini.
                                </p>
                                @error('phone') <span
                                    class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- KARTU 2: DOKUMEN IDENTITAS & PERSETUJUAN -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-header-left">
                                <div class="form-section-icon"
                                    style="background-color: rgba(255, 69, 0, 0.1); color: var(--color-coral);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="form-section-title">Dokumen Identitas & Persetujuan</h3>
                                    <p class="form-section-subtitle">Jaminan sewa & perlindungan kepatuhan privasi (UU
                                        PDP)</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-body space-y-5">
                            <!-- Alamat Lengkap Pelanggan -->
                            <div class="form-group mb-0">
                                <div class="form-label-row">
                                    <label for="customer_address" class="form-label-text">
                                        Alamat Lengkap Domisili
                                    </label>
                                </div>
                                <div class="form-input-group">
                                    <textarea id="customer_address" wire:model="address" rows="3"
                                        class="form-control font-medium p-3"
                                        placeholder="Contoh: Jl. Rinjani No. 45, RT 02/RW 05, Kel. Mendaki, Kota Bandung"></textarea>
                                </div>
                                @error('address') <span
                                    class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Info Kepatuhan UU PDP & Jaminan Identitas Fisik -->
                            <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/60 text-xs text-navy space-y-2">
                                <div class="flex items-center gap-2 font-bold text-blue-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>Kepatuhan Privasi Data (UU PDP) & Verifikasi Jaminan</span>
                                </div>
                                <p class="text-gray-600 leading-relaxed">
                                    Untuk melindungi privasi pelanggan sesuai ketentuan UU PDP, foto dokumen tidak disimpan secara digital di server. Identitas resmi (KTP/SIM asli) diverifikasi dan dititipkan sebagai jaminan fisik saat serah-terima alat (Check-Out) serta dikelola pada modul Deposit Toko.
                                </p>
                            </div>

                            <!-- Persetujuan S&K (Consent Card) -->
                            <div class="consent-card" :class="{ 'checked': $wire.has_consent }"
                                @click="$wire.has_consent = !$wire.has_consent">
                                <div class="consent-checkbox-wrapper" @click.stop>
                                    <input type="checkbox" id="has_consent_checkbox" wire:model.live="has_consent"
                                        class="consent-checkbox">
                                </div>
                                <div>
                                    <label for="has_consent_checkbox" class="consent-title cursor-pointer"
                                        @click.stop="$wire.has_consent = !$wire.has_consent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-coral shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Persetujuan Syarat & Ketentuan Sewa
                                    </label>
                                    <p class="consent-description">
                                        Pelanggan telah membaca, memahami, dan menyetujui seluruh S&K sewa alat
                                        SummitGear, serta memberikan persetujuan legal atas penyimpanan data identitas
                                        KTP sebagai jaminan selama masa sewa aktif berlangsung.
                                    </p>
                                </div>
                            </div>
                            @error('has_consent') <span
                                class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror

                        </div>
                    </div>

                </div>

                <!-- Action Buttons Bar -->
                <div class="form-action-bar">
                    <a href="{{ route('admin.customers') }}" class="btn-form-cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </a>

                    <button type="submit" class="btn-form-save" wire:loading.attr="disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Data Pelanggan</span>
                    </button>
                </div>
            </form>

            @if($customerId && $rentals->count() > 0)
                <!-- Riwayat Transaksi (Hanya Mode Edit) -->
                <div class="form-section-card mt-8">
                    <div class="form-section-header">
                        <div class="form-section-header-left">
                            <div class="form-section-icon" style="background-color: #ECFDF5; color: #059669;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="form-section-title">Riwayat Transaksi Terakhir</h3>
                                <p class="form-section-subtitle">5 transaksi sewa terbaru oleh pelanggan ini</p>
                            </div>
                        </div>
                    </div>

                    <div class="sg-table-container border-0 rounded-none">
                        <table class="sg-table">
                            <thead>
                                <tr>
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal Ambil</th>
                                    <th>Status Sewa</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rentals as $rental)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-navy">{{ $rental->rental_code }}</div>
                                        </td>
                                        <td>
                                            <div class="text-sm text-gray-600">
                                                {{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            @if($rental->status === 'COMPLETED')
                                                <span class="badge badge-success">Selesai</span>
                                            @elseif($rental->status === 'ACTIVE')
                                                <span class="badge badge-warning">Sedang Disewa</span>
                                            @elseif($rental->status === 'BOOKED')
                                                <span class="badge badge-info">Booking</span>
                                            @else
                                                <span class="badge badge-neutral">{{ $rental->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.transactions.invoice', $rental->id) }}"
                                                class="text-xs font-bold text-coral hover:underline inline-flex items-center gap-1">
                                                <span>Buka Invoice</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </main>
</div>