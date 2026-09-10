<div class="admin-layout" x-data="{ showOverrideModal: false, selectedPenaltyId: null, currentAmount: 0, overrideAmount: 0, reasonText: '' }">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Penyelesaian Denda & Jaminan" />

        <div class="content-area">
            <!-- Header Nav & Title -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.operations.handover') }}" class="btn-form-cancel text-xs font-bold py-2 px-3.5" title="Kembali">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate uppercase tracking-wider">Operasional QC</span>
                            <span class="text-slate text-xs">/</span>
                            <span class="text-xs font-bold text-coral">Penyelesaian Sengketa</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            Penyelesaian Deposit & Denda: {{ $rental->rental_code }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-info font-bold">TRX: {{ $rental->rental_code }}</span>
                    <span class="badge badge-neutral font-bold">Pelanggan: {{ $rental->customer->name }}</span>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if($errors->has('settleError'))
                <div class="mb-6 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('settleError') }}</span>
                </div>
            @endif

            <!-- Customer & TRX Card -->
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <div class="form-section-icon" style="background-color: rgba(255, 69, 0, 0.1); color: var(--color-coral);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="form-section-title">Ringkasan Finansial Penyelesaian</h3>
                            <p class="form-section-subtitle">Komparasi deposit ditahan dengan akumulasi tagihan denda keterlambatan / kerusakan</p>
                        </div>
                    </div>
                </div>

                <div class="form-section-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Identitas Pelanggan</span>
                            <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Kontak WA: {{ $rental->customer->phone }}</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Jaminan Deposit Ditahan</span>
                            <div class="text-2xl font-bold text-coral mt-1">
                                Rp {{ number_format($this->heldDepositAmount, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">Total deposit tunai saat booking</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Total Denda / Ganti Rugi</span>
                            <div class="text-2xl font-bold text-red-600 mt-1">
                                Rp {{ number_format($this->totalPenalty, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">Dari {{ $this->pendingPenalties->count() }} item sengketa aktif</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-start">
                
                <!-- List Denda -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-header-left">
                                <div class="form-section-icon" style="background-color: #FEF2F2; color: #DC2626;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="form-section-title">Rincian Denda & Kerusakan</h3>
                                    <p class="form-section-subtitle">Daftar penalti yang belum terselesaikan</p>
                                </div>
                            </div>
                            <span class="badge badge-danger">{{ $this->pendingPenalties->count() }} Tagihan Aktif</span>
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
                                        <div class="text-base font-black text-red-600">Rp {{ number_format($penalty->amount, 0, ',', '.') }}</div>
                                    </div>
                                    <button type="button" 
                                        x-on:click="selectedPenaltyId = {{ $penalty->id }}; currentAmount = {{ $penalty->amount }}; overrideAmount = {{ $penalty->amount }}; reasonText = ''; showOverrideModal = true;"
                                        class="btn text-xs font-bold text-navy bg-slate-100 hover:bg-slate-200 rounded-lg py-1.5 px-3 border border-gray-200 transition">
                                        Diskon / Override (PIN)
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-gray-400 text-xs">
                                Tidak ada denda atau sengketa kerusakan pada transaksi ini. Seluruh alat kembali dengan baik.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Kalkulasi Penyelesaian & Action -->
                <div class="space-y-6">
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-header-left">
                                <div class="form-section-icon" style="background-color: #EFF6FF; color: #1D4ED8;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="form-section-title">Kalkulasi Akhir</h3>
                                    <p class="form-section-subtitle">Penyelesaian kas & deposit</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-body space-y-4 text-sm">
                            <div class="flex justify-between pb-2 border-b border-gray-100">
                                <span class="text-gray-500 font-medium">Total Denda:</span>
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
                                        <div class="text-2xl font-black text-green-700 mt-1">
                                            Rp {{ number_format(abs($this->netBalance), 0, ',', '.') }}
                                        </div>
                                        <p class="text-xs text-green-600 mt-1 mb-0">Deposit cukup menutup denda. Kembalikan sisa dana ini ke pelanggan.</p>
                                    </div>
                                @else
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center mb-4">
                                        <div class="text-xs font-bold text-red-700 uppercase tracking-wider">Kekurangan Yang Wajib Dibayar:</div>
                                        <div class="text-2xl font-black text-red-700 mt-1">
                                            Rp {{ number_format($this->netBalance, 0, ',', '.') }}
                                        </div>
                                        <p class="text-xs text-red-600 mt-1 mb-0">Denda melebihi deposit. Tagihkan kekurangan ini ke penyewa.</p>
                                    </div>

                                    <!-- Form Bayar Kekurangan -->
                                    <div class="space-y-3 pt-2">
                                        <div>
                                            <label class="form-label-text text-xs mb-1">Metode Bayar Sisa Denda:</label>
                                            <select wire:model="additionalPaymentMethod" class="form-control text-xs font-semibold">
                                                <option value="CASH">Uang Tunai (Cash)</option>
                                                <option value="TRANSFER">Transfer Bank</option>
                                                <option value="QRIS">QRIS</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label-text text-xs mb-1">Nominal Diterima Kasir (Rp):</label>
                                            <input type="number" wire:model="amountPaid" placeholder="Minimal: {{ $this->netBalance }}" class="form-control text-sm font-bold text-right font-mono">
                                            @error('amountPaid') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
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
                    </div>
                </div>

            </div>

            <!-- Override Penalty Modal -->
            <div x-show="showOverrideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md border border-gray-100" @click.away="showOverrideModal = false">
                    <h3 class="text-lg font-bold text-navy mb-1">Otorisasi Diskon / Override Denda</h3>
                    <p class="text-xs text-gray-500 mb-4">Setiap perubahan denda wajib disertai alasan dan akan memerlukan input PIN Admin.</p>

                    <div class="mb-4">
                        <label class="form-label-text text-xs mb-1">Nominal Baru (Rp):</label>
                        <input type="number" x-model="overrideAmount" class="form-control text-base font-bold text-right font-mono">
                    </div>

                    <div class="mb-6">
                        <label class="form-label-text text-xs mb-1">Alasan Kebijakan / Diskon:</label>
                        <textarea x-model="reasonText" rows="3" placeholder="Contoh: Kesepakatan kekeluargaan, diskon loyalitas pelanggan..." class="form-control text-xs" style="height: auto;"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showOverrideModal = false" class="btn-form-cancel text-xs py-2 px-3.5">
                            Batal
                        </button>
                        <button type="button" 
                            @click="$wire.set('newAmount', overrideAmount); $wire.set('overrideReason', reasonText); $wire.requestOverride(selectedPenaltyId); showOverrideModal = false;"
                            class="btn text-xs font-bold text-white bg-navy hover:bg-blue-900 rounded-xl px-4 py-2 shadow">
                            Lanjut ke Verifikasi PIN
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
