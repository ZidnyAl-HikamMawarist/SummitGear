<div class="admin-layout" x-data="{ nikVal: @entangle('nik').live }">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar :title="$customerId ? 'Detail / Edit Pelanggan' : 'Daftarkan Pelanggan Baru'" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('admin.customers') }}" variant="subtle" icon="arrow-left" size="sm">
                        Kembali
                    </flux:button>
                    <div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                            <span>Pelanggan</span>
                            <span>/</span>
                            <span class="text-coral font-bold">{{ $customerId ? 'Edit Data' : 'Registrasi Baru' }}</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            {{ $customerId ? 'Edit Data Pelanggan: ' . $name : 'Daftarkan Pelanggan Baru' }}
                        </h1>
                    </div>
                </div>

                @if($customerId)
                    <div class="flex items-center gap-2">
                        <flux:badge color="sky" size="sm">ID: #{{ str_pad($customerId, 5, '0', STR_PAD_LEFT) }}</flux:badge>
                        @if($has_consent)
                            <flux:badge color="emerald" size="sm">✓ S&K Disetujui</flux:badge>
                        @endif
                    </div>
                @endif
            </div>

            @if (session()->has('message'))
                <div class="p-4 text-xs font-bold text-green-800 bg-green-50 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                    <!-- KARTU 1: DATA IDENTITAS PRIBADI -->
                    <flux:card class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Informasi Dasar Pelanggan</h3>
                                <p class="text-xs text-gray-500">Data resmi penyewa untuk pencocokan fisik & kontak</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Nama Lengkap Sesuai KTP -->
                            <div>
                                <flux:label for="customer_name" class="text-xs font-semibold">
                                    Nama Lengkap Sesuai KTP <span class="text-red-500">*</span>
                                </flux:label>
                                <flux:input type="text" id="customer_name" wire:model="name" class="mt-1" placeholder="Contoh: Budi Santoso" required autofocus />
                                <p class="text-[11px] text-gray-400 mt-1">Nama harus sesuai persis dengan kartu identitas KTP/SIM.</p>
                                @error('name') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- NIK (16 Digit) -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <flux:label for="customer_nik" class="text-xs font-semibold">
                                        Nomor Induk Kependudukan (NIK) <span class="text-red-500">*</span>
                                    </flux:label>
                                    <span class="text-[11px] font-mono font-bold" :class="(nikVal || '').length === 16 ? 'text-emerald-600' : 'text-amber-600'">
                                        <span x-text="(nikVal || '').length">0</span>/16 Digit
                                    </span>
                                </div>
                                <flux:input type="text" id="customer_nik" wire:model="nik" class="font-mono font-semibold" placeholder="Contoh: 3201123456780001" maxlength="16" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                <p class="text-[11px] text-gray-400 mt-1">Hanya 16 angka numerik. Tidak boleh ada spasi atau karakter khusus.</p>
                                @error('nik') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nomor Handphone (WA Aktif) -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <flux:label for="customer_phone" class="text-xs font-semibold">
                                        Nomor WhatsApp / Kontak Aktif
                                    </flux:label>
                                    <flux:badge color="emerald" size="sm">WhatsApp Notifikasi</flux:badge>
                                </div>
                                <flux:input type="tel" id="customer_phone" wire:model="phone" placeholder="Contoh: 081234567890" oninput="this.value = this.value.replace(/[^0-9+]/g, '')" />
                                <p class="text-[11px] text-gray-400 mt-1">Invoice digital & notifikasi pengingat kembali alat akan dikirimkan ke nomor ini.</p>
                                @error('phone') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Alamat Email -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <flux:label for="customer_email" class="text-xs font-semibold">
                                        Alamat Email (Opsional)
                                    </flux:label>
                                    <flux:badge color="sky" size="sm">E-Invoice & Reminder</flux:badge>
                                </div>
                                <flux:input type="email" id="customer_email" wire:model="email" placeholder="Contoh: penyewa@gmail.com" />
                                <p class="text-[11px] text-gray-400 mt-1">Digunakan untuk pengiriman salinan tanda terima dan faktur elektronik resmi.</p>
                                @error('email') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </flux:card>

                    <!-- KARTU 2: DOKUMEN IDENTITAS & PERSETUJUAN -->
                    <flux:card class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Dokumen Identitas & Persetujuan</h3>
                                <p class="text-xs text-gray-500">Jaminan sewa & perlindungan kepatuhan privasi (UU PDP)</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Alamat Lengkap Pelanggan -->
                            <div>
                                <flux:label for="customer_address" class="text-xs font-semibold">
                                    Alamat Lengkap Domisili
                                </flux:label>
                                <textarea id="customer_address" wire:model="address" rows="3" class="mt-1 text-xs font-medium p-3 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20" placeholder="Contoh: Jl. Rinjani No. 45, RT 02/RW 05, Kel. Mendaki, Kota Bandung"></textarea>
                                @error('address') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
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

                            <!-- Persetujuan S&K -->
                            <div class="p-4 rounded-xl border border-coral/30 bg-orange-50/40 flex items-start gap-3">
                                <input type="checkbox" id="has_consent_checkbox" wire:model.live="has_consent" class="mt-1 h-4 w-4 rounded border-gray-300 text-coral focus:ring-coral">
                                <div>
                                    <label for="has_consent_checkbox" class="font-bold text-sm text-navy cursor-pointer">
                                        Persetujuan Syarat & Ketentuan Sewa
                                    </label>
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                        Pelanggan telah membaca, memahami, dan menyetujui seluruh S&K sewa alat SummitGear, serta memberikan persetujuan legal atas penyimpanan data identitas KTP sebagai jaminan selama masa sewa aktif berlangsung.
                                    </p>
                                </div>
                            </div>
                            @error('has_consent') <span class="text-xs font-bold text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </flux:card>

                </div>

                <!-- Action Buttons Bar -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button href="{{ route('admin.customers') }}" variant="subtle">
                        Batal
                    </flux:button>

                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                        Simpan Data Pelanggan
                    </flux:button>
                </div>
            </form>

            @if($customerId && $rentals->count() > 0)
                <!-- Riwayat Transaksi (Hanya Mode Edit) -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-5 flex items-center justify-between border-b border-gray-100 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Riwayat Transaksi Terakhir</h3>
                                <p class="text-xs text-gray-500">5 transaksi sewa terbaru oleh pelanggan ini</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200/80 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-gray-500">
                                    <th class="py-3 px-4">Kode Transaksi</th>
                                    <th class="py-3 px-4">Tanggal Ambil</th>
                                    <th class="py-3 px-4">Status Sewa</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @foreach($rentals as $rental)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-navy">{{ $rental->rental_code }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="text-xs text-gray-600">
                                                {{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($rental->status === 'COMPLETED')
                                                <flux:badge color="emerald" size="sm">Selesai</flux:badge>
                                            @elseif($rental->status === 'ACTIVE')
                                                <flux:badge color="amber" size="sm">Sedang Disewa</flux:badge>
                                            @elseif($rental->status === 'BOOKED')
                                                <flux:badge color="sky" size="sm">Booking</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">{{ $rental->status }}</flux:badge>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <flux:button href="{{ route('admin.transactions.invoice', $rental->id) }}" variant="subtle" size="sm" icon="arrow-top-right-on-square">
                                                Invoice
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </flux:card>
            @endif

        </div>
    </main>
</div>