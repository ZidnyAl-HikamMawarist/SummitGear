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
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-mono font-semibold text-gray-600 bg-slate-100 inline-block px-2 py-0.5 rounded">
                                        SN: {{ $detail->itemUnit->serial_number ?? '-' }}
                                    </span>
                                    <button type="button" wire:click="openSwapModal({{ $detail->id }})"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        <span>Tukar Unit</span>
                                    </button>
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

                @if($rental->balance_due > 0)
                <!-- Pelunasan Sisa Pokok Sewa (Khusus Booking Online DP 30%) -->
                <flux:card class="space-y-4 border-2 border-amber-300 bg-amber-50/30">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-3 border-b border-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Pelunasan Sisa Biaya Sewa (Booking DP)</h3>
                                <p class="text-xs text-amber-900/80">Penyewa baru membayar DP sebesar <strong>Rp {{ number_format($rental->down_payment_amount, 0, ',', '.') }}</strong> dari total <strong>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong>.</p>
                            </div>
                        </div>
                        <div class="bg-amber-100/80 px-3.5 py-1.5 rounded-xl border border-amber-300 text-right">
                            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Wajib Dilunasi</span>
                            <span class="text-lg font-extrabold text-amber-900">Rp {{ number_format($rental->balance_due, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <flux:label class="text-xs">Metode Pembayaran Pelunasan:</flux:label>
                            <select wire:model="balancePaymentMethod" class="text-xs font-semibold px-3 py-2 border border-gray-300 rounded-lg w-full bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20 mt-1">
                                <option value="CASH">Uang Tunai (Cash)</option>
                                <option value="TRANSFER">Transfer Bank</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                            @error('balancePaymentMethod') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <flux:label class="text-xs">Nominal Diterima Kasir (Rp):</flux:label>
                            <flux:input type="number" wire:model="balancePaymentAmount" placeholder="Minimal: {{ $rental->balance_due }}" class="mt-1" />
                            @error('balancePaymentAmount') <span class="text-xs font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </flux:card>
                @endif

                <!-- Verifikasi Jaminan Identitas (Deposit Gate) -->
                @if($hasHeldDeposit)
                    <flux:card class="border border-emerald-200 bg-emerald-50/40">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-emerald-900">Jaminan Identitas Telah Ditahan</h4>
                                    <flux:badge color="emerald" size="sm">Status: HELD</flux:badge>
                                </div>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Jaminan fisik (KTP / Uang Deposit) telah tercatat di sistem dari kasir dan tersimpan aman di loker jaminan.
                                </p>
                            </div>
                        </div>
                    </flux:card>
                @else
                    <flux:card class="space-y-4 border-2 border-indigo-200 bg-indigo-50/20">
                        <div class="flex items-center gap-3 pb-3 border-b border-indigo-100">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-bold shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-navy">Verifikasi & Penahanan Jaminan (Online Booking)</h3>
                                <p class="text-xs text-indigo-900/80">Pelanggan belum menyerahkan jaminan fisik di outlet. Kasir wajib menahan KTP asli sebelum barang diserahkan.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="p-3.5 rounded-xl border border-indigo-200 bg-white flex items-start gap-3">
                                <input type="checkbox" id="is_ktp_verified" wire:model="is_ktp_verified" class="mt-1 h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy">
                                <div>
                                    <label for="is_ktp_verified" class="font-bold text-xs text-navy cursor-pointer">
                                        KTP Asli Penyewa Valid & Fisik KTP Ditahan di Kasir
                                    </label>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Periksa kesesuaian wajah dan data NIK ({{ $rental->customer->nik }}), lalu simpan fisik KTP ke loker kasir.
                                    </p>
                                </div>
                            </div>
                            @error('is_ktp_verified') <span class="text-xs font-bold text-red-600 block">{{ $message }}</span> @enderror

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-indigo-100">
                                <div>
                                    <flux:label class="text-xs">Uang Jaminan Tunai Tambahan (Opsional):</flux:label>
                                    <flux:input type="number" wire:model="counter_deposit_amount" placeholder="0 jika hanya KTP" min="0" step="50000" class="mt-1" />
                                </div>
                                <div class="flex items-center text-xs text-gray-500 pt-5">
                                    <span>Jika penyewa menyewa barang bernilai tinggi atau tidak meninggalkan KTP asli, wajib minta jaminan tunai.</span>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endif

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

            @if($showSwapModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-base text-navy">Tukar Unit Fisik (Unit Swap)</h3>
                        </div>
                        <button type="button" wire:click="closeSwapModal" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">
                            Alat: <strong>{{ $swapItemName }}</strong><br>
                            SN Saat Ini: <span class="font-mono font-bold text-red-600">{{ $swapOldSN }}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            Pilih unit fisik pengganti yang saat ini tersedia (Available) di gudang dan tidak bertabrakan dengan jadwal rental:
                        </p>
                    </div>

                    @error('swapError')
                        <div class="p-3 text-xs font-bold text-red-700 bg-red-50 rounded-xl">{{ $message }}</div>
                    @enderror

                    @if(count($availableUnitsForSwap) > 0)
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @foreach($availableUnitsForSwap as $u)
                                <label class="flex items-center justify-between p-3 rounded-xl border {{ $newUnitId == $u->id ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200 bg-white hover:bg-gray-50' }} cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" wire:model="newUnitId" value="{{ $u->id }}" class="text-blue-600">
                                        <div>
                                            <div class="font-mono font-bold text-xs text-navy">SN: {{ $u->serial_number }}</div>
                                            <div class="text-[11px] text-gray-500">Kondisi: {{ $u->condition_notes ?: 'Siap Pakai (Available)' }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Tersedia</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 font-medium">
                            ⚠️ Tidak ada unit lain yang tersedia (Available) untuk alat ini di gudang pada tanggal sewa tersebut.
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <flux:button type="button" wire:click="closeSwapModal" variant="subtle" size="sm">
                            Batal
                        </flux:button>
                        @if(count($availableUnitsForSwap) > 0)
                            <flux:button type="button" wire:click="executeSwapUnit" variant="primary" size="sm">
                                Terapkan Tukar Unit
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </main>
</div>
