<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Tracking Unit Fisik" />

        <div class="content-area">
            <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <flux:button href="{{ route('admin.inventory.items') }}" variant="ghost" icon="arrow-left" />
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md border border-gray-200">{{ $item->sku }}</span>
                                <h2 class="text-2xl font-bold text-navy">{{ $item->name }}</h2>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">Tracking serial number unit fisik, status kesiapan, dan nilai ganti rugi.</p>
                        </div>
                    </div>
                    
                    <flux:button type="button" wire:click="openGenerateModal" variant="primary" icon="plus">
                        Generate Unit Baru
                    </flux:button>
                </div>

                @if (session()->has('message'))
                    <div class="p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Search & Filter Card -->
                <flux:card class="p-4">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <div class="flex-1">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari Serial Number unit..." icon="magnifying-glass" />
                        </div>
                        
                        <div class="w-full sm:w-64">
                            <flux:select wire:model.live="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="Available">Available (Tersedia)</option>
                                <option value="Rented">Rented (Sedang Sewa)</option>
                                <option value="Cleaning">Cleaning (Cuci)</option>
                                <option value="Maintenance">Maintenance (Servis)</option>
                                <option value="Lost">Lost (Hilang)</option>
                                <option value="Damaged">Damaged (Rusak)</option>
                            </flux:select>
                        </div>
                    </div>
                </flux:card>

                <!-- Table Card -->
                <flux:card class="p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-5">Serial Number</th>
                                    <th class="py-3 px-5">Status Fisik</th>
                                    <th class="py-3 px-5">Kondisi (Catatan)</th>
                                    <th class="py-3 px-5 text-right">Nilai Ganti Rugi</th>
                                    <th class="py-3 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($units as $unit)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <span class="font-mono font-bold text-navy text-sm">{{ $unit->serial_number }}</span>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        @if($unit->status === 'Available')
                                            <flux:badge size="sm" color="emerald">Available</flux:badge>
                                        @elseif($unit->status === 'Rented')
                                            <flux:badge size="sm" color="blue">Rented</flux:badge>
                                        @elseif($unit->status === 'Cleaning')
                                            <flux:badge size="sm" color="purple">Cleaning</flux:badge>
                                        @elseif($unit->status === 'Maintenance')
                                            <flux:badge size="sm" color="amber">Maintenance</flux:badge>
                                        @elseif(in_array($unit->status, ['Lost', 'Damaged']))
                                            <flux:badge size="sm" color="red">{{ $unit->status }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc">{{ $unit->status }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <span class="text-xs text-slate-600">{{ $unit->condition_notes ?? '-' }}</span>
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        <span class="font-mono font-bold text-navy text-xs">
                                            Rp {{ number_format($unit->replacement_value, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        <x-kebab-menu>
                                            <button type="button" wire:click="editUnit({{ $unit->id }})" class="kebab-item">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit Unit
                                            </button>
                                            @if($unit->status !== 'Rented')
                                            <button type="button" wire:click="confirmDeleteUnit({{ $unit->id }})" class="kebab-item danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Hapus Unit
                                            </button>
                                            @endif
                                        </x-kebab-menu>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-slate-400 text-xs">
                                        Belum ada unit fisik terdaftar untuk barang ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex justify-between items-center bg-white">
                        {{ $units->links('vendor.pagination.summitgear') }}
                    </div>
                </flux:card>
            </div>

            <!-- Modal Generate Unit with Flux -->
            <flux:modal wire:model="isGenerateModalOpen" class="max-w-md">
                <form wire:submit.prevent="submitGenerateUnits" class="space-y-4">
                    <div>
                        <flux:heading size="lg">Generate Unit Baru</flux:heading>
                        <flux:subheading class="text-xs">Sistem akan membuat Serial Number otomatis secara berurutan.</flux:subheading>
                    </div>

                    <flux:input label="Jumlah Unit yang Dibuat" type="number" wire:model="generateQty" min="1" max="100" required />

                    <flux:input label="Nilai Ganti Rugi Default (Rp) per Unit" type="number" wire:model="generateReplacementValue" min="0" required />

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-zinc-700">
                        <flux:button type="button" wire:click="$set('isGenerateModalOpen', false)" variant="ghost">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Generate Sekarang
                        </flux:button>
                    </div>
                </form>
            </flux:modal>

            <!-- Modal Edit Unit with Flux -->
            <flux:modal wire:model="isEditModalOpen" class="max-w-md">
                <form wire:submit.prevent="updateUnit" class="space-y-4">
                    <div>
                        <flux:heading size="lg">Edit Data Unit Fisik</flux:heading>
                        <flux:subheading class="text-xs">Perbarui status fisik, catatan, dan nilai ganti rugi unit.</flux:subheading>
                    </div>

                    <flux:select label="Status Fisik" wire:model="editStatus">
                        <option value="Available">Available (Tersedia)</option>
                        <option value="Rented">Rented (Sedang Sewa)</option>
                        <option value="Cleaning">Cleaning (Cuci)</option>
                        <option value="Maintenance">Maintenance (Servis)</option>
                        <option value="Lost">Lost (Hilang)</option>
                        <option value="Damaged">Damaged (Rusak)</option>
                    </flux:select>

                    <flux:input label="Kondisi (Catatan)" wire:model="editCondition" placeholder="Contoh: Kondisi baik, resleting diganti" />

                    <flux:input label="Nilai Ganti Rugi (Rp)" type="number" wire:model="editReplacementValue" min="0" />

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-zinc-700">
                        <flux:button type="button" wire:click="$set('isEditModalOpen', false)" variant="ghost">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Simpan Perubahan
                        </flux:button>
                    </div>
                </form>
            </flux:modal>

            <!-- Modal Konfirmasi Hapus Unit Fisik (Centered Flux Modal) -->
            <flux:modal wire:model="showDeleteModal" class="max-w-lg">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <flux:heading size="lg">Konfirmasi Hapus Unit Fisik</flux:heading>
                            <flux:subheading size="sm">Periksa kembali detail unit fisik berikut sebelum menghapusnya.</flux:subheading>
                        </div>
                    </div>

                    <!-- Detail Unit Fisik yang akan dihapus -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Nomor Seri (SN):</span>
                            <span class="font-mono font-bold text-slate-900 text-sm bg-white px-2 py-0.5 rounded border border-slate-200">{{ $unitToDeleteSn }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Model Master Barang:</span>
                            <span class="font-bold text-slate-800 text-right">{{ $item->name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Status Saat Ini:</span>
                            <span class="font-bold text-slate-800">{{ $unitToDeleteStatus }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Kondisi / Catatan:</span>
                            <span class="text-slate-700">{{ $unitToDeleteCondition }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-500">Nilai Penggantian:</span>
                            <span class="font-bold font-mono text-slate-900">Rp {{ number_format($unitToDeleteValue, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($unitToDeleteIsRented || $unitToDeleteHasActiveRentals)
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <strong class="font-bold block">Tidak Dapat Dihapus</strong>
                            <span>{{ $unitToDeleteBlockerReason ?: 'Unit ini sedang disewa atau terikat pada jadwal transaksi mendatang.' }}</span>
                        </div>
                    </div>
                    @else
                    <div class="p-3 rounded-xl bg-rose-50/60 border border-rose-200/60 text-xs text-rose-700 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Tindakan ini akan menghapus aset unit fisik ini secara permanen dari inventaris.</span>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <flux:button type="button" wire:click="cancelDeleteUnit" variant="ghost">
                            Batal
                        </flux:button>
                        @if(!$unitToDeleteIsRented && !$unitToDeleteHasActiveRentals)
                        <flux:button type="button" wire:click="executeDeleteUnit" variant="danger" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="executeDeleteUnit">Ya, Hapus Unit</span>
                            <span wire:loading wire:target="executeDeleteUnit">Menghapus...</span>
                        </flux:button>
                        @endif
                    </div>
                </div>
            </flux:modal>

        </div>
    </main>
</div>
