<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Manajemen Akun Pengguna" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Manajemen Akun Pengguna</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola data login staf (Admin, Kasir, Gudang) dan konfigurasi PIN otorisasi.</p>
                </div>
                <flux:button type="button" wire:click="create" variant="primary" icon="plus">
                    Tambah Pengguna Baru
                </flux:button>
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

            <!-- Table Card -->
            <div class="sg-table-container">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th>Nama & Email</th>
                            <th>Hak Akses (Role)</th>
                            <th>No. WhatsApp</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-sm" style="background-color: var(--color-navy);">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-navy text-sm">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-purple">SUPER ADMIN</span>
                                @elseif($user->role === 'kasir')
                                    <span class="badge badge-coral">KASIR TOKO</span>
                                @else
                                    <span class="badge badge-info">STAF GUDANG</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs font-mono text-gray-600">{{ $user->phone ?? '-' }}</span>
                            </td>
                            <td class="text-right">
                                <!-- Kebab 3-Dots Action Menu -->
                                <x-kebab-menu>
                                    <button type="button" wire:click="edit({{ $user->id }})" class="kebab-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        Edit Akun
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <button type="button" wire:click="promptDelete({{ $user->id }})" class="kebab-item danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Hapus Akun
                                        </button>
                                    @endif
                                </x-kebab-menu>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                {{ $users->links('vendor.pagination.summitgear') }}
            </div>

            <!-- Modal Form with Flux -->
            <flux:modal wire:model="isModalOpen" class="max-w-lg">
                <form wire:submit.prevent="store" class="space-y-4">
                    <div>
                        <flux:heading size="lg">
                            {{ $userId ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}
                        </flux:heading>
                        <flux:subheading class="text-xs">Kelola data kredensial dan hak akses staf</flux:subheading>
                    </div>
                    
                    <flux:input label="Nama Lengkap" wire:model="name" placeholder="Contoh: Budi Santoso" icon="user" required />

                    <flux:input label="Alamat Email" type="email" wire:model="email" placeholder="email@summitgear.com" icon="envelope" required />

                    <flux:input label="Password {{ $userId ? '(Kosongkan jika tidak diubah)' : '' }}" type="password" wire:model="password" placeholder="Minimal 8 karakter" icon="key" viewable />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:select label="Role Hak Akses" wire:model="role">
                            <option value="kasir">Kasir</option>
                            <option value="gudang">Staf Gudang</option>
                            <option value="admin">Admin</option>
                        </flux:select>

                        <flux:input label="PIN Approval (6 Digit)" type="password" wire:model="pin" placeholder="123456" maxlength="6" />
                    </div>
                    
                    <flux:input label="No. WhatsApp" wire:model="phone" placeholder="08xxxxxxxxxx" icon="phone" />

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-zinc-700">
                        <flux:button type="button" wire:click="closeModal" variant="ghost">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Simpan Data
                        </flux:button>
                    </div>
                </form>
            </flux:modal>

            <!-- Modal Konfirmasi Hapus Akun Pengguna -->
            @if($showDeleteModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="cancelDeleteUser"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-slate-900" id="modal-title">Konfirmasi Hapus Akun Pengguna</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Periksa kembali detail akun pengguna berikut sebelum menghapusnya dari sistem.</p>
                            </div>
                        </div>

                        <!-- Detail Pengguna yang akan dihapus -->
                        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Nama Pengguna:</span>
                                <span class="font-bold text-slate-900 text-sm">{{ $userToDeleteName }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Email Akun:</span>
                                <span class="font-bold text-slate-800">{{ $userToDeleteEmail }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Role / Hak Akses:</span>
                                <span class="font-black px-2 py-0.5 rounded text-[11px] bg-white border border-slate-200 text-slate-800 tracking-wider">{{ $userToDeleteRole }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-500">No. WhatsApp / HP:</span>
                                <span class="font-mono text-slate-700">{{ $userToDeletePhone }}</span>
                            </div>
                        </div>

                        <!-- Warning PIN Required -->
                        <div class="mt-4 p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Tindakan ini permanen. Setelah menekan lanjutkan, Anda akan diminta memasukkan PIN Otorisasi Admin Super untuk mengonfirmasi penghapusan.</span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" 
                                    wire:click="cancelDeleteUser" 
                                    class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="button" 
                                    wire:click="proceedDeleteUser" 
                                    class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Lanjutkan ke Otorisasi PIN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </main>
</div>
