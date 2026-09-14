<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Kalender Visual Ketersediaan Alat" />

        <div class="mx-auto w-full max-w-7xl px-6 py-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-navy">Kalender Visual Ketersediaan Alat</h1>
                    <p class="text-sm text-gray-500 mt-1">Monitoring matriks booking unit fisik secara real-time untuk mencegah jadwal ganda (double-booking).</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge color="zinc" size="sm">
                        Jendela Booking: <strong class="text-navy ml-1">{{ $bookingWindowDays }} Hari</strong>
                    </flux:badge>
                    @if(auth()->user()->role === 'kasir')
                    <flux:button href="{{ route('admin.transactions.create') }}" variant="primary" icon="plus">
                        Buat Booking Baru
                    </flux:button>
                    @endif
                </div>
            </div>

            <!-- Controls Card -->
            <flux:card>
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <!-- Navigation & Jump to Date -->
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <flux:button size="sm" variant="filled" wire:click="prevPeriod">
                            ‹ 7 Hari Lalu
                        </flux:button>
                        <flux:button size="sm" variant="primary" wire:click="todayPeriod">
                            Hari Ini
                        </flux:button>
                        <flux:button size="sm" variant="filled" wire:click="nextPeriod">
                            7 Hari Kedepan ›
                        </flux:button>

                        <div class="flex items-center gap-2 ml-2">
                            <span class="text-xs font-semibold text-gray-500">Mulai:</span>
                            <input type="date" wire:model.live="startDate" class="text-xs font-semibold px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                        </div>

                        <span class="text-xs font-bold text-gray-700 ml-1 px-3 py-1.5 bg-gray-100 rounded-lg">
                            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($startDate)->addDays($daysToShow - 1)->format('d M Y') }}
                        </span>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori:</span>
                        <select wire:model.live="categoryFilter" class="text-xs font-semibold px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-navy/20">
                            <option value="ALL">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </flux:card>

            <!-- Legend Badges Bar -->
            <flux:card class="py-3 px-4 flex flex-wrap gap-3 items-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
                <span class="matrix-legend-badge legend-ready"><span class="legend-dot"></span> Tersedia (Ready)</span>
                <span class="matrix-legend-badge legend-booked"><span class="legend-dot"></span> Booked (Dipesan)</span>
                <span class="matrix-legend-badge legend-rented"><span class="legend-dot"></span> Rented Out (Keluar)</span>
                <span class="matrix-legend-badge legend-cleaning"><span class="legend-dot"></span> Cleaning (Cuci)</span>
                <span class="matrix-legend-badge legend-maintenance"><span class="legend-dot"></span> Maintenance / Hilang</span>
            </flux:card>

            <!-- Interactive Grid Table -->
            <flux:card class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="booking-matrix-table">
                        <thead>
                            <tr>
                                <th class="matrix-th-unit">
                                    <div class="text-xs font-bold text-navy uppercase tracking-wider">Unit Fisik / SN</div>
                                </th>
                                @foreach($dates as $date)
                                <th class="matrix-th-date {{ $date->isToday() ? 'is-today' : '' }}">
                                    <div class="matrix-day-name">{{ $date->format('D') }}</div>
                                    <div class="matrix-day-num">{{ $date->format('d/m') }}</div>
                                    @if($date->isToday())
                                        <span class="matrix-today-indicator">HARI INI</span>
                                    @endif
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                            <tr class="matrix-row">
                                <td class="matrix-td-unit">
                                    <div class="matrix-unit-name" title="{{ $unit->item->name }}">{{ $unit->item->name }}</div>
                                    <div class="matrix-unit-sn">{{ $unit->serial_number }}</div>
                                </td>
                                @foreach($dates as $date)
                                    @php
                                        $dStr = $date->format('Y-m-d');
                                        $slot = $matrix[$unit->id][$dStr] ?? ['status' => 'AVAILABLE', 'label' => 'Ready'];
                                        $st = $slot['status'];
                                    @endphp
                                    <td class="matrix-td-slot {{ $date->isToday() ? 'is-today-col' : '' }}">
                                        @if($st === 'AVAILABLE')
                                            <span class="matrix-slot slot-ready" title="Tersedia (Ready)">Ready</span>
                                        @elseif($st === 'BOOKED')
                                            <a href="{{ isset($slot['rental_id']) ? route('admin.transactions.invoice', $slot['rental_id']) : '#' }}" class="matrix-slot slot-booked" title="Dipesan: {{ $slot['label'] }}">
                                                Booked
                                            </a>
                                        @elseif(in_array($st, ['RENTED_OUT', 'OVERDUE']))
                                            <a href="{{ isset($slot['rental_id']) ? route('admin.transactions.invoice', $slot['rental_id']) : '#' }}" class="matrix-slot slot-rented" title="Sewa: {{ $slot['label'] }}">
                                                Sewa
                                            </a>
                                        @elseif($st === 'CLEANING')
                                            <span class="matrix-slot slot-cleaning" title="Dalam Pembersihan">Cuci</span>
                                        @else
                                            <span class="matrix-slot slot-maintenance" title="{{ $slot['label'] }}">Servis</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($dates) + 1 }}" class="p-10 text-center text-gray-400 text-xs">
                                    Tidak ada unit fisik terdaftar pada filter kategori ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100 bg-white">
                    {{ $units->links('vendor.pagination.summitgear') }}
                </div>
            </flux:card>
        </div>
    </main>
</div>
