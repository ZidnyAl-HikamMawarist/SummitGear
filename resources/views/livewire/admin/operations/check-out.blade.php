<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Check-Out & Serah Terima QC" />

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
                            <span class="text-coral font-bold">Check-Out Serah Terima</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy mt-0.5">
                            Serah Terima Alat (QC Out): {{ $rental->rental_code }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:badge color="sky" size="sm">TRX: {{ $rental->rental_code }}</flux:badge>
                    <flux:badge color="amber" size="sm">Status: {{ $rental->status }}</flux:badge>
                </div>
            </div>

            <!-- Rental Info Card -->
            <flux:card>
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-navy">Informasi Sewa & Pelanggan</h2>
                        <p class="text-xs text-gray-500">Identitas penyewa dan tenggat waktu pengembalian alat</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pelanggan</span>
                        <div class="text-base font-bold text-navy mt-1">{{ $rental->customer->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">WA: {{ $rental->customer->phone }} | NIK: {{ $rental->customer->nik }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Periode Sewa</span>
                        <div class="text-sm font-bold text-navy mt-1">
                            {{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Durasi: {{ \Carbon\Carbon::parse($rental->start_date)->diffInDays(\Carbon\Carbon::parse($rental->end_date)) + 1 }} Hari</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Batas Waktu Pengembalian</span>
                        <div class="text-sm font-bold text-red-600 mt-1">
                            {{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Terlambat dikenakan denda sesuai regulasi</div>
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

            <form wire:submit.prevent="submitCheckOut" class="space-y-6">
                <!-- Items Inspection Checklist -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-5 flex items-center justify-between border-b border-gray-100 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Checklist Kondisi Fisik Unit (QC Out)</h3>
                                <p class="text-xs text-gray-500">Periksa kelengkapan & catat kondisi unit sebelum dibawa oleh penyewa</p>
                            </div>
                        </div>
                        <flux:badge color="sky" size="sm">{{ $rental->details->count() }} Unit Fisik</flux:badge>
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
                                <flux:label class="text-xs mb-1.5 block">Status Fisik Unit:</flux:label>
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
                                <flux:label class="text-xs mb-1.5 block">Catatan Khusus (Opsional):</flux:label>
                                <flux:input type="text" wire:model="checklists.{{ $detail->id }}.notes" placeholder="Contoh: Lecet tipis, tas komplit..." />
                            </div>
                        </div>
                        @endforeach
                    </div>
                </flux:card>

                <!-- Handover & Digital Agreement Box -->
                <flux:card class="space-y-4">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-navy">Konfirmasi & Tanda Terima</h3>
                            <p class="text-xs text-gray-500">Pernyataan penerimaan barang oleh penyewa</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 flex items-start gap-3">
                            <input type="checkbox" id="customerAgreed" wire:model="customerAgreed" class="mt-1 h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy">
                            <div>
                                <label for="customerAgreed" class="font-bold text-sm text-navy cursor-pointer">
                                    Konfirmasi Penerimaan Unit Lengkap & Normal
                                </label>
                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    Pelanggan (<strong>{{ $rental->customer->name }}</strong>) menyatakan seluruh alat di atas telah diterima lengkap, berfungsi normal, dan sanggup mengembalikan tepat waktu sebelum <strong>{{ \Carbon\Carbon::parse($rental->scheduled_return_time)->format('d M Y, H:i') }} WIB</strong>.
                                </p>
                            </div>
                        </div>
                        @error('customerAgreed') <span class="text-xs font-bold text-red-600 block">{{ $message }}</span> @enderror

                        <div>
                            <flux:label class="text-xs mb-1 block">Catatan Tambahan Serah Terima (Opsional):</flux:label>
                            <textarea wire:model="notes" rows="2" placeholder="Catatan khusus kasir / permintaan pelanggan..." class="text-xs p-2.5 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20"></textarea>
                        </div>
                    </div>
                </flux:card>

                <!-- Submit Button Bar -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button href="{{ route('admin.operations.handover') }}" variant="subtle">
                        Batal
                    </flux:button>

                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitCheckOut">Konfirmasi Check-Out & Serahkan Alat</span>
                        <span wire:loading wire:target="submitCheckOut">Memproses Serah Terima...</span>
                    </flux:button>
                </div>
            </form>

        </div>
    </main>
</div>
