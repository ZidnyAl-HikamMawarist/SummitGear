<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Check-Out & Serah Terima QC" />

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
                            <span class="text-xs font-bold text-coral">Check-Out Serah Terima</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            Serah Terima Alat (QC Out): {{ $rental->rental_code }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-info font-bold">TRX: {{ $rental->rental_code }}</span>
                    <span class="badge badge-warning font-bold">Status: {{ $rental->status }}</span>
                </div>
            </div>

            <!-- Rental Info Card -->
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <div class="form-section-icon" style="background-color: #EFF6FF; color: #1D4ED8;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="form-section-title">Informasi Sewa & Pelanggan</h3>
                            <p class="form-section-subtitle">Identitas penyewa dan tenggat waktu pengembalian alat</p>
                        </div>
                    </div>
                </div>

                <div class="form-section-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Pelanggan</span>
                            <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">WA: {{ $rental->customer->phone }} | NIK: {{ $rental->customer->nik }}</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Periode Sewa</span>
                            <div class="text-sm font-bold text-navy mt-1">
                                {{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">Durasi: {{ \Carbon\Carbon::parse($rental->start_date)->diffInDays(\Carbon\Carbon::parse($rental->end_date)) + 1 }} Hari</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Batas Waktu Pengembalian</span>
                            <div class="text-sm font-bold text-red-600 mt-1">
                                {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">Terlambat dikenakan denda sesuai regulasi</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($errors->has('error'))
                <div class="mb-6 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('error') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="submitCheckOut">
                <!-- Items Inspection Checklist -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="form-section-header-left">
                            <div class="form-section-icon" style="background-color: rgba(255, 69, 0, 0.1); color: var(--color-coral);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="form-section-title">Checklist Kondisi Fisik Unit (QC Out)</h3>
                                <p class="form-section-subtitle">Periksa kelengkapan & catat kondisi unit sebelum dibawa oleh penyewa</p>
                            </div>
                        </div>
                        <span class="badge badge-info">{{ $rental->details->count() }} Unit Fisik</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($rental->details as $detail)
                        <div class="p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-white hover:bg-slate-50 transition">
                            <div class="w-full md:w-1/3">
                                <div class="font-bold text-navy text-sm">{{ $detail->itemUnit->item->name ?? 'Barang Sewa' }}</div>
                                <div class="text-xs font-mono font-semibold text-gray-600 bg-slate-100 inline-block px-2 py-0.5 rounded mt-1">
                                    SN: {{ $detail->itemUnit->serial_number ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Nilai Ganti Rugi: Rp {{ number_format($detail->itemUnit->replacement_value ?? 0, 0, ',', '.') }}
                                </div>
                            </div>

                            <!-- Segmented Pill Selector for Condition -->
                            <div class="w-full md:w-1/3">
                                <label class="form-label-text text-xs mb-1.5 block">Status Fisik Unit:</label>
                                <div class="qc-segmented-group">
                                    <label class="qc-segment-label {{ ($checklists[$detail->id]['condition'] ?? '') === 'Baik' ? 'selected-baik' : '' }}">
                                        <input type="radio" wire:model.live="checklists.{{ $detail->id }}.condition" value="Baik">
                                        <span>✓ Baik</span>
                                    </label>
                                    <label class="qc-segment-label {{ ($checklists[$detail->id]['condition'] ?? '') === 'Cukup' ? 'selected-cukup' : '' }}">
                                        <input type="radio" wire:model.live="checklists.{{ $detail->id }}.condition" value="Cukup">
                                        <span>⚡ Cukup</span>
                                    </label>
                                    <label class="qc-segment-label {{ ($checklists[$detail->id]['condition'] ?? '') === 'Perhatian' ? 'selected-perhatian' : '' }}">
                                        <input type="radio" wire:model.live="checklists.{{ $detail->id }}.condition" value="Perhatian">
                                        <span>⚠️ Perhatian</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="w-full md:w-1/3">
                                <label class="form-label-text text-xs mb-1.5 block">Catatan Khusus (Opsional):</label>
                                <input type="text" wire:model="checklists.{{ $detail->id }}.notes" placeholder="Contoh: Lecet tipis, tas komplit..." class="form-control text-xs">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Handover & Digital Agreement Box -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="form-section-header-left">
                            <div class="form-section-icon" style="background-color: #ECFDF5; color: #059669;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="form-section-title">Konfirmasi & Tanda Terima</h3>
                                <p class="form-section-subtitle">Pernyataan penerimaan barang oleh penyewa</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-body space-y-4">
                        <div class="consent-card">
                            <div class="consent-checkbox-wrapper">
                                <input type="checkbox" id="customerAgreed" wire:model="customerAgreed" class="consent-checkbox">
                            </div>
                            <div>
                                <label for="customerAgreed" class="consent-title cursor-pointer">
                                    Konfirmasi Penerimaan Unit Lengkap & Normal
                                </label>
                                <p class="consent-description">
                                    Pelanggan (<strong>{{ $rental->customer->name }}</strong>) menyatakan seluruh alat di atas telah diterima lengkap, berfungsi normal, dan sanggup mengembalikan tepat waktu sebelum <strong>{{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB</strong>.
                                </p>
                            </div>
                        </div>
                        @error('customerAgreed') <span class="form-error text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror

                        <div class="form-group mb-0">
                            <label class="form-label-text text-xs mb-1">Catatan Tambahan Serah Terima (Opsional):</label>
                            <textarea wire:model="notes" rows="2" placeholder="Catatan khusus kasir / permintaan pelanggan..." class="form-control text-xs" style="height: auto;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="form-action-bar">
                    <a href="{{ route('admin.operations.handover') }}" class="btn-form-cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </a>

                    <button type="submit" class="btn-form-save" style="background: linear-gradient(135deg, var(--color-navy) 0%, #1E3A8A 100%);" wire:loading.attr="disabled">
                        <svg wire:loading.remove wire:target="submitCheckOut" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading.remove wire:target="submitCheckOut">Konfirmasi Check-Out & Serahkan Alat</span>

                        <svg wire:loading wire:target="submitCheckOut" class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading wire:target="submitCheckOut">Memproses Serah Terima...</span>
                    </button>
                </div>
            </form>

        </div>
    </main>
</div>
