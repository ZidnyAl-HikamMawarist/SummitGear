<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Check-In & Verifikasi QC Masuk" />

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
                            <span class="text-coral font-bold">Check-In Pengembalian</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            Pemeriksaan Masuk (QC In): {{ $rental->rental_code }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:badge color="sky" size="sm">TRX: {{ $rental->rental_code }}</flux:badge>
                    @if(now()->greaterThan(\Carbon\Carbon::parse($rental->scheduled_return_time)))
                        <flux:badge color="red" size="sm" class="animate-pulse">⚠️ Terlambat Pengembalian</flux:badge>
                    @else
                        <flux:badge color="emerald" size="sm">✓ Tepat Waktu</flux:badge>
                    @endif
                </div>
            </div>

            <!-- Rental & Deposit Info Card -->
            <flux:card>
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-navy">Detail Pengembalian & Jaminan Deposit</h2>
                        <p class="text-xs text-gray-500">Tinjauan batas waktu sewa dan status dana jaminan pelanggan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pelanggan</span>
                        <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">WA: {{ $rental->customer->phone }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Batas Waktu Pengembalian</span>
                        <div class="text-sm font-bold text-navy mt-1">
                            {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            Dikembalikan: {{ now()->format('d M Y, H:i') }} WIB
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Deposit Yang Ditahan</span>
                        <div class="text-xl font-bold text-coral mt-1">
                            Rp {{ number_format($rental->deposits->where('status', 'HELD')->sum('amount'), 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Siap dikembalikan utuh atau dipotong jika ada denda.</div>
                    </div>
                </div>
            </flux:card>

            @if($errors->has('error'))
                <div class="p-4 text-xs font-bold text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('error') }}</span>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="p-3 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="submitCheckIn" class="space-y-6">
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-5 flex items-center justify-between border-b border-gray-100 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Komparasi Checklist QC Masuk vs Keluar</h3>
                                <p class="text-xs text-gray-500">Bandingkan kondisi awal saat keluar dengan kondisi saat dikembalikan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <button type="button" 
                                    wire:click="markAllAsGood" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all flex items-center gap-1.5 cursor-pointer shadow-xs active:scale-95"
                                    title="Tandai semua unit dalam kondisi Baik">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Semua Unit Baik</span>
                            </button>
                            <flux:badge color="coral" size="sm">{{ $rental->details->count() }} Unit Fisik</flux:badge>
                        </div>
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
                                    <flux:badge color="sky" size="sm">
                                        {{ $initialConditions[$detail->id] ?? 'Baik' }}
                                    </flux:badge>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <!-- Segmented Pill Selector for Condition -->
                                    <div class="flex-1">
                                        <flux:label class="text-xs mb-1.5 block">Kondisi Fisik Saat Kembali:</flux:label>
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
                                        <flux:label class="text-xs mb-1.5 block">Status Fisik:</flux:label>
                                        <select wire:model.live="checkinData.{{ $detail->id }}.return_status" class="text-xs font-semibold px-3 py-2 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                                            <option value="RETURNED">Lengkap (RETURNED)</option>
                                            <option value="DAMAGED">Rusak (DAMAGED)</option>
                                            <option value="LOST">Hilang (LOST)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Catatan Kerusakan / Keterangan -->
                                <div>
                                    <flux:label class="text-xs mb-1.5 block">Catatan Pemeriksaan / Kerusakan (Opsional):</flux:label>
                                    <flux:input type="text" wire:model="checkinData.{{ $detail->id }}.notes" placeholder="Contoh: Frame bengkok 1 ruas, resleting macet, atau bersih sempurna..." />
                                </div>
                            </div>

                            @if(($checkinData[$detail->id]['condition'] ?? '') === 'Rusak' || ($checkinData[$detail->id]['return_status'] ?? '') === 'DAMAGED')
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                        <span class="text-xs font-bold text-amber-900">🛠️ Estimasi Biaya Perbaikan / Denda Kerusakan:</span>
                                        <span class="text-[11px] text-amber-700">Nilai Penggantian 100%: Rp {{ number_format($detail->itemUnit->replacement_value ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                        <div class="w-full sm:w-56">
                                            <flux:input type="number" wire:model.live="checkinData.{{ $detail->id }}.damage_cost" placeholder="0" min="0" step="5000" />
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            Default: 30% nilai unit. Staf QC dapat mengisi estimasi biaya riil (jahit, sparepart frame, laundry).
                                        </div>
                                    </div>
                                </div>
                            @elseif(($checkinData[$detail->id]['condition'] ?? '') === 'Hilang' || ($checkinData[$detail->id]['return_status'] ?? '') === 'LOST')
                                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 flex items-center justify-between font-medium">
                                    <span>✕ <strong>Barang Hilang:</strong> Dikenakan ganti rugi 100% nilai unit pengganti: <strong>Rp {{ number_format($detail->itemUnit->replacement_value ?? 0, 0, ',', '.') }}</strong>.</span>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </flux:card>

                <!-- Submit Bar -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button href="{{ route('admin.operations.handover') }}" variant="subtle">
                        Batal
                    </flux:button>

                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitCheckIn">Selesaikan Check-In & Update Inventaris</span>
                        <span wire:loading wire:target="submitCheckIn">Memproses Check-In...</span>
                    </flux:button>
                </div>
            </form>

        </div>
    </main>
</div>
