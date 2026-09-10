<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Booking Masuk (Online)" />

        <div class="content-area">
            <!-- Header Bar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-5">
                <div>
                    <h2 class="text-2xl font-black text-navy">Booking Online Masuk</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Pantau jadwal kedatangan penyewa dan validasi pembatalan jika barang tidak diambil.</p>
                </div>
            </div>

            <!-- Search & Filter Card -->
            <div class="sg-card mb-6 p-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-md">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode sewa, nama pelanggan, NIK, atau WhatsApp..." class="form-control text-xs" style="padding-left: 2.5rem; height: 42px;">
                        <div style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94A3B8; pointer-events:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <!-- Status Filter Tabs -->
                    <div class="analytics-period-tabs shrink-0 flex items-center">
                        <button type="button" wire:click="$set('filterStatus', 'all')" class="period-tab {{ $filterStatus === 'all' ? 'active' : '' }}">Semua</button>
                        <button type="button" wire:click="$set('filterStatus', 'expired')" class="period-tab {{ $filterStatus === 'expired' ? 'active' : '' }}" style="{{ $filterStatus === 'expired' ? 'color:#DC2626;' : '' }}">Lewat Waktu</button>
                        <button type="button" wire:click="$set('filterStatus', 'active')" class="period-tab {{ $filterStatus === 'active' ? 'active' : '' }}" style="{{ $filterStatus === 'active' ? 'color:#059669;' : '' }}">Menunggu Diambil</button>
                    </div>
                </div>
            </div>

            <!-- Flash Notifications -->
            @if(session()->has('message'))
                <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold rounded-2xl flex items-center gap-2.5 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if(session()->has('error'))
                <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-800 text-sm font-bold rounded-2xl flex items-center gap-2.5 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($bookings as $booking)
                    <div class="bg-white rounded-2xl border {{ $booking->is_expired ? 'border-rose-200 shadow-sm ring-1 ring-rose-100' : 'border-slate-200/90 shadow-xs hover:shadow-md' }} overflow-hidden flex flex-col group relative transition-all duration-200" x-data="{ expanded: false }">
                        
                        <!-- Card Header -->
                        <div class="p-5 border-b border-slate-100 {{ $booking->is_expired ? 'bg-rose-50/20' : 'bg-slate-50/40' }}">
                            <!-- Top Meta Row -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg font-mono border border-slate-200/80">
                                        {{ $booking->rental_code }}
                                    </span>
                                    @if($booking->is_expired)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200/80 text-[11px] font-black rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                            LEWAT BATAS WAKTU
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200/80 text-[11px] font-bold rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Menunggu Diambil
                                        </span>
                                    @endif
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 border border-amber-200/80 text-amber-800 rounded-lg text-xs font-bold shrink-0" title="Total {{ $booking->details->count() }} Unit Barang">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span>{{ $booking->details->count() }} Unit</span>
                                </div>
                            </div>

                            <!-- Customer Profile Info -->
                            @php
                                $words = explode(' ', trim($booking->customer->name));
                                $initials = count($words) >= 2 
                                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                    : strtoupper(substr($booking->customer->name, 0, 2));
                            @endphp
                            <div class="flex items-center gap-3 pt-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 font-black text-xs shrink-0 shadow-2xs">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-black text-slate-900 leading-snug truncate" title="{{ $booking->customer->name }}">
                                        {{ $booking->customer->name }}
                                    </h3>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->customer->phone) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline font-mono text-[11px]" title="Chat WhatsApp Pelanggan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ $booking->customer->phone }}</span>
                                        </a>
                                        <span class="text-slate-300 text-[10px]">•</span>
                                        <div class="inline-flex items-center gap-1 text-slate-500 font-mono text-[11px]">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 font-sans">NIK:</span>
                                            <span>{{ $booking->customer->nik }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unified Schedule & Tolerance Block -->
                            <div class="mt-4 rounded-xl border {{ $booking->is_expired ? 'border-rose-200/90 bg-rose-50/40' : 'border-slate-200/80 bg-slate-50/70' }} overflow-hidden">
                                <div class="grid grid-cols-2 divide-x {{ $booking->is_expired ? 'divide-rose-200/80' : 'divide-slate-200/80' }} p-3">
                                    <!-- Jadwal Ambil -->
                                    <div class="pr-2.5">
                                        <div class="flex items-center gap-1.5 text-[10px] uppercase font-black text-slate-400 tracking-wider mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>Jadwal Ambil</span>
                                        </div>
                                        <p class="text-xs font-black text-slate-800 leading-tight">{{ $booking->pickup_date_formatted }}</p>
                                        <p class="text-xs font-bold text-emerald-600 font-mono mt-0.5">{{ $booking->pickup_clock_formatted }}</p>
                                    </div>

                                    <!-- Batas Toleransi -->
                                    <div class="pl-2.5">
                                        <div class="flex items-center gap-1.5 text-[10px] uppercase font-black {{ $booking->is_expired ? 'text-rose-500' : 'text-slate-400' }} tracking-wider mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 {{ $booking->is_expired ? 'text-rose-500' : 'text-amber-500' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Batas Toleransi</span>
                                        </div>
                                        <p class="text-xs font-black {{ $booking->is_expired ? 'text-rose-800' : 'text-slate-800' }} leading-tight">{{ $booking->deadline_date_formatted }}</p>
                                        <p class="text-xs font-bold {{ $booking->is_expired ? 'text-rose-600' : 'text-amber-600' }} font-mono mt-0.5">{{ $booking->deadline_clock_formatted }}</p>
                                    </div>
                                </div>

                                <!-- Alert Strip Attached Directly at Bottom of Schedule Box -->
                                @if($booking->is_expired)
                                    <div class="px-3 py-2 bg-rose-100/90 border-t border-rose-200/90 text-rose-900 text-xs flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="min-w-0 flex-1 leading-tight">
                                            <span class="font-black text-rose-900">Terlambat {{ $booking->delay_for_humans }}</span>
                                            <span class="text-[11px] text-rose-700 block mt-0.5 truncate">Barang belum diambil di outlet melewati batas waktu!</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="px-3 py-2 bg-emerald-100/70 border-t border-emerald-200/80 text-emerald-900 text-xs flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="min-w-0 flex-1 leading-tight text-xs">
                                            <span class="text-slate-600">Sisa waktu pengambilan:</span>
                                            <span class="font-black text-emerald-800 ml-1">{{ $booking->remaining_time_for_humans }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Total & Item Details Toggle -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 block">Total Biaya</span>
                                    <span class="text-sm sm:text-base font-black text-slate-900 tracking-tight">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <button type="button" @click="expanded = !expanded" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors cursor-pointer select-none">
                                    <span x-show="!expanded">Lihat {{ $booking->details->count() }} Alat</span>
                                    <span x-show="expanded">Tutup Detail</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transform transition-transform duration-200 text-slate-500" :class="{'rotate-180': expanded}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Card Body (Expandable List of Items) -->
                        <div x-show="expanded" x-collapse class="p-4 bg-slate-50/50 border-b border-gray-100">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2.5">Daftar Peralatan & Unit Terbooking</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                @foreach($booking->details as $detail)
                                    @php
                                        $itemObj = $detail->itemUnit?->item ?? $detail->inventoryItem;
                                        $unit = $detail->itemUnit;
                                    @endphp
                                    <li class="flex items-center gap-3 p-2 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                                        <div class="w-9 h-9 bg-slate-100 rounded-lg overflow-hidden shrink-0 flex items-center justify-center border border-slate-200/60">
                                            @if($itemObj?->photo_url)
                                                <img src="{{ asset('storage/' . $itemObj->photo_url) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $itemObj?->name ?? 'Barang Sewa' }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">SN: {{ $unit?->serial_number ?? '-' }} <span class="text-slate-300">#{{ $unit?->id }}</span></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-bold text-emerald-600">Rp {{ number_format($detail->price_per_day, 0, ',', '.') }}</span>
                                            <span class="text-[9px] text-slate-400 block">/hari</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Card Footer (Kasir Actions) -->
                        <div class="p-4 bg-slate-50/70 border-t border-slate-100 mt-auto">
                            @if($booking->is_expired)
                                <div class="flex flex-col gap-2">
                                    <flux:button 
                                        type="button" 
                                        wire:click="confirmCancelBooking({{ $booking->id }}, 'expired')" 
                                        variant="danger" 
                                        icon="x-circle" 
                                        class="w-full justify-center font-bold text-xs">
                                        Validasi Batal & Kembalikan Stok
                                    </flux:button>

                                    <flux:button 
                                        type="button" 
                                        wire:click="processBooking({{ $booking->id }})" 
                                        variant="outline" 
                                        icon="clipboard-document-check" 
                                        class="w-full justify-center font-bold text-xs text-slate-700 bg-white">
                                        Tetap Proses / Bayar (Terlambat)
                                    </flux:button>
                                </div>
                            @else
                                <div class="flex items-center gap-2.5">
                                    <flux:button 
                                        type="button" 
                                        wire:click="confirmCancelBooking({{ $booking->id }}, 'normal')" 
                                        variant="outline" 
                                        class="text-xs font-bold text-slate-600 hover:text-red-600 hover:border-red-200 bg-white">
                                        Batalkan
                                    </flux:button>

                                    <flux:button 
                                        type="button" 
                                        wire:click="processBooking({{ $booking->id }})" 
                                        variant="primary" 
                                        color="emerald" 
                                        icon="check" 
                                        class="flex-1 justify-center font-bold text-xs">
                                        Proses / Bayar
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-2xl border border-dashed border-gray-300">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-navy">Tidak Ada Booking Ditemukan</h3>
                        <p class="text-xs text-gray-400 mt-1">Belum ada booking masuk atau coba sesuaikan kata kunci pencarian / filter.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Modal Konfirmasi Pembatalan Booking -->
        @if($showCancelModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeCancelModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-slate-900" id="modal-title">Konfirmasi Pembatalan Booking</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $cancelType === 'expired' ? 'Validasi pembatalan booking yang telah melewati batas waktu toleransi pengambilan.' : 'Validasi pembatalan reservasi booking online pelanggan.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Detail Booking yang dibatalkan -->
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Kode Booking:</span>
                            <span class="font-mono font-bold text-slate-900 text-sm bg-white px-2 py-0.5 rounded border border-slate-200">{{ $cancelBookingCode }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Nama Pelanggan:</span>
                            <span class="font-bold text-slate-800 text-right">{{ $cancelCustomerName }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">No. WhatsApp:</span>
                            <span class="font-mono text-slate-700">{{ $cancelCustomerPhone }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Batas Waktu Ambil:</span>
                            <span class="font-semibold text-slate-700">{{ $cancelDeadline }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-500">Unit yang Direservasi:</span>
                            <span class="font-bold text-emerald-600">{{ $cancelUnitCount }} Unit akan dikembalikan ke gudang</span>
                        </div>
                    </div>

                    <!-- Warning note -->
                    <div class="mt-4 p-3 rounded-xl bg-rose-50/60 border border-rose-200/60 text-xs text-rose-700 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Status booking akan diubah menjadi <strong>BATAL (CANCELLED)</strong> dan seluruh unit fisik terkait akan otomatis kembali ke status <strong>Siap Sewa (Available)</strong> di gudang.</span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" 
                                wire:click="closeCancelModal" 
                                class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                            Kembali
                        </button>
                        <button type="button" 
                                wire:click="executeCancellation" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="executeCancellation">Ya, Batalkan & Kembalikan Stok</span>
                            <span wire:loading wire:target="executeCancellation">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </main>
</div>
