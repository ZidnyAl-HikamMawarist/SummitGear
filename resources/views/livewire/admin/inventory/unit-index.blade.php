<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Tracking Unit Fisik" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.inventory.items') }}" class="btn text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-xl p-2.5 shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md border border-gray-200">{{ $item->sku }}</span>
                            <h2 class="text-2xl font-bold text-navy">{{ $item->name }}</h2>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">Tracking serial number unit fisik, status kesiapan, dan nilai ganti rugi.</p>
                    </div>
                </div>
                
                <button type="button" wire:click="openGenerateModal" class="btn text-xs font-bold text-white shadow-md transition flex items-center gap-1.5" style="background-color: var(--color-coral); border-radius: 10px; padding: 0.65rem 1.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Generate Unit Baru
                </button>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs font-bold text-green-800 bg-green-100 border border-green-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-5 p-4 text-xs font-bold text-red-800 bg-red-100 border border-red-200 rounded-xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search & Filter Card -->
            <div class="sg-card mb-5 p-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Serial Number unit..." class="form-control text-xs" style="padding-left: 2.4rem;">
                        <div style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94A3B8; pointer-events:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    
                    <select wire:model.live="statusFilter" class="form-control text-xs font-semibold w-auto">
                        <option value="">Semua Status</option>
                        <option value="Available">Available (Tersedia)</option>
                        <option value="Rented">Rented (Sedang Sewa)</option>
                        <option value="Cleaning">Cleaning (Cuci)</option>
                        <option value="Maintenance">Maintenance (Servis)</option>
                        <option value="Lost">Lost (Hilang)</option>
                        <option value="Damaged">Damaged (Rusak)</option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div class="sg-table-container">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Status Fisik</th>
                            <th>Kondisi (Catatan)</th>
                            <th class="text-right">Nilai Ganti Rugi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td>
                                <span class="font-mono font-bold text-navy text-sm">{{ $unit->serial_number }}</span>
                            </td>
                            <td>
                                @if($unit->status === 'Available')
                                    <span class="badge badge-success">Available</span>
                                @elseif($unit->status === 'Rented')
                                    <span class="badge badge-info">Rented</span>
                                @elseif($unit->status === 'Cleaning')
                                    <span class="badge badge-purple">Cleaning</span>
                                @elseif($unit->status === 'Maintenance')
                                    <span class="badge badge-warning">Maintenance</span>
                                @elseif(in_array($unit->status, ['Lost', 'Damaged']))
                                    <span class="badge badge-danger">{{ $unit->status }}</span>
                                @else
                                    <span class="badge badge-neutral">{{ $unit->status }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">{{ $unit->condition_notes ?? '-' }}</span>
                            </td>
                            <td class="text-right">
                                <span class="font-mono font-bold text-navy text-xs">
                                    Rp {{ number_format($unit->replacement_value, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <x-kebab-menu>
                                    <button type="button" wire:click="editUnit({{ $unit->id }})" class="kebab-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        Edit Unit
                                    </button>
                                    @if($unit->status !== 'Rented')
                                    <button type="button" wire:click="confirmDeleteUnit({{ $unit->id }})" class="kebab-item danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        Hapus Unit
                                    </button>
                                    @endif
                                </x-kebab-menu>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400 text-xs">
                                Belum ada unit fisik terdaftar untuk barang ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t border-slate-100 flex justify-between items-center bg-white">
                    {{ $units->links('vendor.pagination.summitgear') }}
                </div>
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

    <!-- Modal Konfirmasi Hapus Unit Fisik -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="cancelDeleteUnit"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Konfirmasi Hapus Unit Fisik</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Periksa kembali detail unit fisik berikut sebelum menghapusnya.</p>
                    </div>
                </div>

                <!-- Detail Unit Fisik yang akan dihapus -->
                <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
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

                @if($unitToDeleteIsRented)
                <div class="mt-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong class="font-bold block">Tidak Dapat Dihapus</strong>
                        <span>Unit ini sedang dalam status disewa (Rented). Pengembalian sewa harus diproses terlebih dahulu.</span>
                    </div>
                </div>
                @else
                <div class="mt-4 p-3 rounded-xl bg-rose-50/60 border border-rose-200/60 text-xs text-rose-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Tindakan ini akan menghapus aset unit fisik ini secara permanen dari inventaris.</span>
                </div>
                @endif

                <!-- Actions -->
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" 
                            wire:click="cancelDeleteUnit" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    @if(!$unitToDeleteIsRented)
                    <button type="button" 
                            wire:click="executeDeleteUnit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="executeDeleteUnit">Ya, Hapus Unit</span>
                        <span wire:loading wire:target="executeDeleteUnit">Menghapus...</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    </main>
</div>

