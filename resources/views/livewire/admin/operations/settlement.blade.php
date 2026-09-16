<div class="admin-layout" x-data="{ showOverrideModal: false, selectedPenaltyId: null, currentAmount: 0, overrideAmount: 0, reasonText: '' }">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Penyelesaian Denda & Jaminan" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('admin.operations.handover') }}" variant="subtle" icon="arrow-left" size="sm">
                        Kembali
                    </flux:button>
                    <div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                            <span>Operasional QC</span>
                            <span>/</span>
                            <span class="text-coral font-bold">Penyelesaian Sengketa</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            Penyelesaian Deposit & Denda: {{ $rental->rental_code }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:badge color="sky" size="sm">TRX: {{ $rental->rental_code }}</flux:badge>
                    <flux:badge color="zinc" size="sm">Pelanggan: {{ $rental->customer->name }}</flux:badge>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="p-4 text-xs font-bold text-green-800 bg-green-50 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if($errors->has('settleError'))
                <div class="p-4 text-xs font-bold text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('settleError') }}</span>
                </div>
            @endif

            <!-- Customer & TRX Card -->
            <flux:card>
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-navy">Ringkasan Finansial Penyelesaian</h2>
                        <p class="text-xs text-gray-500">Komparasi deposit ditahan dengan akumulasi tagihan denda keterlambatan / kerusakan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Identitas Pelanggan</span>
                        <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Kontak WA: {{ $rental->customer->phone }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jaminan Deposit Ditahan</span>
                        <div class="text-2xl font-bold text-coral mt-1">
                            Rp {{ number_format($this->heldDepositAmount, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Total deposit tunai saat booking</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Denda / Ganti Rugi</span>
                        <div class="text-2xl font-bold text-red-600 mt-1">
                            Rp {{ number_format($this->totalPenalty, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Dari {{ $this->pendingPenalties->count() }} item sengketa aktif</div>
                    </div>
                </div>
            </flux:card>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- List Denda -->
                <div class="lg:col-span-2 space-y-4">
                    <flux:card class="p-0 overflow-hidden">
                        <div class="p-5 flex items-center justify-between border-b border-gray-100 bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-navy">Rincian Denda & Kerusakan</h3>
                                    <p class="text-xs text-gray-500">Daftar penalti yang belum terselesaikan</p>
                                </div>
                            </div>
                            <flux:badge color="red" size="sm">{{ $this->pendingPenalties->count() }} Tagihan Aktif</flux:badge>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($this->pendingPenalties as $penalty)
                            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white hover:bg-slate-50 transition">
                                <div class="flex-1">
                                    <div class="font-bold text-navy text-sm">{{ $penalty->reason }}</div>
                                    @if($penalty->is_override)
                                        <div class="text-xs text-purple-700 font-semibold mt-1 flex items-center gap-1">
                                            <span>✓ Telah di-override: {{ $penalty->override_reason }}</span>
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-400 mt-0.5">Dicatat: {{ $penalty->created_at->format('d M Y, H:i') }}</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-base font-bold text-red-600">Rp {{ number_format($penalty->amount, 0, ',', '.') }}</div>
                                    </div>
                                    <flux:button size="sm" variant="filled"
                                        x-on:click="selectedPenaltyId = {{ $penalty->id }}; currentAmount = {{ $penalty->amount }}; overrideAmount = {{ $penalty->amount }}; reasonText = ''; showOverrideModal = true;">
                                        Diskon / Override (PIN)
                                    </flux:button>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-gray-400 text-xs">
                                Tidak ada denda atau sengketa kerusakan pada transaksi ini. Seluruh alat kembali dengan baik.
                            </div>
                            @endforelse
                        </div>
                    </flux:card>
                </div>

                <!-- Kalkulasi Penyelesaian & Action -->
                <div class="space-y-6">
                    <flux:card class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-navy">Kalkulasi Akhir</h3>
                                <p class="text-xs text-gray-500">Penyelesaian kas & deposit</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            @if($this->balanceDue > 0)
                            <div class="flex justify-between pb-2 border-b border-gray-100">
                                <span class="text-amber-700 font-medium">Sisa Pokok Sewa (DP):</span>
                                <span class="font-bold text-amber-600">Rp {{ number_format($this->balanceDue, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between pb-2 border-b border-gray-100">
                                <span class="text-gray-500 font-medium">Total Denda / Sengketa:</span>
                                <span class="font-bold text-red-600">Rp {{ number_format($this->totalPenalty, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b border-gray-100">
                                <span class="text-gray-500 font-medium">Deposit Tersedia:</span>
                                <span class="font-bold text-gray-900">- Rp {{ number_format($this->heldDepositAmount, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="pt-2">
                                @if($this->netBalance <= 0)
                                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                                        <div class="text-xs font-bold text-green-700 uppercase tracking-wider">Uang Kembali Ke Pelanggan:</div>
                                        <div class="text-2xl font-bold text-green-700 mt-1">
                                            Rp {{ number_format(abs($this->netBalance), 0, ',', '.') }}
                                        </div>
                                        <p class="text-xs text-green-600 mt-1 mb-0">Deposit cukup menutup denda. Kembalikan sisa dana ini ke pelanggan.</p>
                                    </div>
                                @else
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center mb-4">
                                        <div class="text-xs font-bold text-red-700 uppercase tracking-wider">Kekurangan Yang Wajib Dibayar:</div>
                                        <div class="text-2xl font-bold text-red-700 mt-1">
                                            Rp {{ number_format($this->netBalance, 0, ',', '.') }}
                                        </div>
                                        <p class="text-xs text-red-600 mt-1 mb-0">Denda melebihi deposit. Tagihkan kekurangan ini ke penyewa.</p>
                                    </div>

                                    <!-- Form Bayar Kekurangan -->
                                    <div class="space-y-3 pt-2">
                                        <div>
                                            <flux:label class="text-xs">Metode Bayar Sisa Denda:</flux:label>
                                            <select wire:model="additionalPaymentMethod" class="text-xs font-semibold px-3 py-2 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20 mt-1">
                                                <option value="CASH">Uang Tunai (Cash)</option>
                                                <option value="TRANSFER">Transfer Bank</option>
                                                <option value="QRIS">QRIS</option>
                                            </select>
                                        </div>
                                        <div>
                                            <flux:label class="text-xs">Nominal Diterima Kasir (Rp):</flux:label>
                                            <flux:input type="number" wire:model="amountPaid" placeholder="Minimal: {{ $this->netBalance }}" class="mt-1" />
                                            @error('amountPaid') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <flux:button 
                                wire:click="processSettlement" 
                                variant="primary" 
                                icon="check" 
                                class="w-full justify-center mt-4">
                                Tutup & Selesaikan Transaksi
                            </flux:button>
                        </div>
                    </flux:card>
                </div>

            </div>

            <!-- Override Penalty Modal -->
            <div x-show="showOverrideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm">
                <flux:card class="w-full max-w-md space-y-4 shadow-2xl" @click.away="showOverrideModal = false">
                    <h3 class="text-lg font-bold text-navy">Otorisasi Diskon / Override Denda</h3>
                    <p class="text-xs text-gray-500">Setiap perubahan denda wajib disertai alasan dan akan memerlukan input PIN Admin.</p>

                    <div>
                        <flux:label class="text-xs">Nominal Baru (Rp):</flux:label>
                        <flux:input type="number" x-model="overrideAmount" class="mt-1 font-mono font-bold" />
                    </div>

                    <div>
                        <flux:label class="text-xs">Alasan Kebijakan / Diskon:</flux:label>
                        <textarea x-model="reasonText" rows="3" placeholder="Contoh: Kesepakatan kekeluargaan, diskon loyalitas pelanggan..." class="text-xs p-2.5 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20 mt-1"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button variant="subtle" size="sm" @click="showOverrideModal = false">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" size="sm" 
                            @click="$wire.set('newAmount', overrideAmount); $wire.set('overrideReason', reasonText); $wire.requestOverride(selectedPenaltyId); showOverrideModal = false;">
                            Lanjut ke Verifikasi PIN
                        </flux:button>
                    </div>
                </flux:card>
            </div>

        </div>
    </main>
</div>
