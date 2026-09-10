<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Check-In & Verifikasi QC Masuk" />

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
                            <span class="text-xs font-bold text-coral">Check-In Pengembalian</span>
                        </div>
                        <h2 class="text-2xl font-bold text-navy mb-0 mt-0.5">
                            Pemeriksaan Masuk (QC In): {{ $rental->rental_code }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-info font-bold">TRX: {{ $rental->rental_code }}</span>
                    @if(now()->greaterThan(\Carbon\Carbon::parse($rental->scheduled_return_time)))
                        <span class="badge badge-danger font-bold animate-pulse">⚠️ Terlambat Pengembalian</span>
                    @else
                        <span class="badge badge-success font-bold">✓ Tepat Waktu</span>
                    @endif
                </div>
            </div>

            <!-- Rental & Deposit Info Card -->
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <div class="form-section-icon" style="background-color: rgba(255, 69, 0, 0.1); color: var(--color-coral);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="form-section-title">Detail Pengembalian & Jaminan Deposit</h3>
                            <p class="form-section-subtitle">Tinjauan batas waktu sewa dan status dana jaminan pelanggan</p>
                        </div>
                    </div>
                </div>

                <div class="form-section-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Pelanggan</span>
                            <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">WA: {{ $rental->customer->phone }}</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Batas Waktu Pengembalian</span>
                            <div class="text-sm font-bold text-navy mt-1">
                                {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                Dikembalikan: {{ now()->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate uppercase tracking-wider">Deposit Yang Ditahan</span>
                            <div class="text-lg font-bold text-coral mt-1">
                                Rp {{ number_format($rental->deposits->where('status', 'HELD')->sum('amount'), 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">Siap dikembalikan utuh atau dipotong jika ada denda.</div>
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

            <form wire:submit.prevent="submitCheckIn">
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="form-section-header-left">
                            <div class="form-section-icon" style="background-color: #EFF6FF; color: #1D4ED8;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="form-section-title">Komparasi Checklist QC Masuk vs Keluar</h3>
                                <p class="form-section-subtitle">Bandingkan kondisi awal saat keluar dengan kondisi saat dikembalikan</p>
                            </div>
                        </div>
                        <span class="badge badge-coral">{{ $rental->details->count() }} Unit Fisik</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($rental->details as $detail)
                        <div class="p-5 space-y-3 bg-white hover:bg-slate-50 transition">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
                                <div>
                                    <div class="font-bold text-navy text-sm">{{ $detail->itemUnit->item->name ?? 'Barang Sewa' }}</div>
                                    <div class="text-xs font-mono font-semibold text-gray-500">SN: {{ $detail->itemUnit->serial_number ?? '-' }}</div>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-gray-500 font-medium">Kondisi saat Check-Out:</span>
                                    <span class="badge badge-info">
                                        {{ $initialConditions[$detail->id] ?? 'Baik' }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <!-- Segmented Pill Selector for Condition -->
                                    <div class="flex-1">
                                        <label class="form-label-text text-xs mb-1.5 block">Kondisi Fisik Saat Kembali:</label>
                                        <div class="qc-segmented-group qc-segmented-5">
                                            <label class="qc-segment-label {{ ($checkinData[$detail->id]['condition'] ?? '') === 'Baik' ? 'selected-baik' : '' }}">
                                                <input type="radio" wire:model.live="checkinData.{{ $detail->id }}.condition" value="Baik">
                                                <span>✓ Baik</span>
                                            </label>
                                            <label class="qc-segment-label {{ ($checkinData[$detail->id]['condition'] ?? '') === 'Cukup' ? 'selected-cukup' : '' }}">
                                                <input type="radio" wire:model.live="checkinData.{{ $detail->id }}.condition" value="Cukup">
                                                <span>⚡ Cukup</span>
                                            </label>
                                            <label class="qc-segment-label {{ ($checkinData[$detail->id]['condition'] ?? '') === 'Perhatian' ? 'selected-perhatian' : '' }}">
                                                <input type="radio" wire:model.live="checkinData.{{ $detail->id }}.condition" value="Perhatian">
                                                <span>⚠️ Kotor</span>
                                            </label>
                                            <label class="qc-segment-label {{ ($checkinData[$detail->id]['condition'] ?? '') === 'Rusak' ? 'selected-rusak' : '' }}">
                                                <input type="radio" wire:model.live="checkinData.{{ $detail->id }}.condition" value="Rusak">
                                                <span>🛠️ Rusak</span>
                                            </label>
                                            <label class="qc-segment-label {{ ($checkinData[$detail->id]['condition'] ?? '') === 'Hilang' ? 'selected-hilang' : '' }}">
                                                <input type="radio" wire:model.live="checkinData.{{ $detail->id }}.condition" value="Hilang">
                                                <span>✕ Hilang</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Status Pengembalian -->
                                    <div class="w-full lg:w-48">
                                        <label class="form-label-text text-xs mb-1.5 block">Status Fisik:</label>
                                        <select wire:model.live="checkinData.{{ $detail->id }}.return_status" class="form-control text-xs font-semibold">
                                            <option value="RETURNED">Lengkap (RETURNED)</option>
                                            <option value="DAMAGED">Rusak (DAMAGED)</option>
                                            <option value="LOST">Hilang (LOST)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Catatan Kerusakan / Keterangan -->
                                <div>
                                    <label class="form-label-text text-xs mb-1.5 block">Catatan Pemeriksaan / Kerusakan (Opsional):</label>
                                    <input type="text" wire:model="checkinData.{{ $detail->id }}.notes" placeholder="Contoh: Frame bengkok 1 ruas, resleting macet, atau bersih sempurna..." class="form-control text-xs">
                                </div>
                            </div>

                            @if(in_array($checkinData[$detail->id]['condition'] ?? '', ['Rusak', 'Hilang']) || in_array($checkinData[$detail->id]['return_status'] ?? '', ['DAMAGED', 'LOST']))
                                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 flex items-center justify-between font-medium">
                                    <span>⚠️ <strong>Denda Dikenakan:</strong> Unit ini ditandai bermasalah. Sistem akan mencatat denda kerusakan / ganti rugi (Nilai unit: Rp {{ number_format($detail->itemUnit->replacement_value ?? 0, 0, ',', '.') }}).</span>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Bar -->
                <div class="form-action-bar">
                    <a href="{{ route('admin.operations.handover') }}" class="btn-form-cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </a>

                    <button type="submit" class="btn-form-save" wire:loading.attr="disabled">
                        <svg wire:loading.remove wire:target="submitCheckIn" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading.remove wire:target="submitCheckIn">Selesaikan Check-In & Update Inventaris</span>

                        <svg wire:loading wire:target="submitCheckIn" class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading wire:target="submitCheckIn">Memproses Check-In...</span>
                    </button>
                </div>
            </form>

        </div>
    </main>
</div>
