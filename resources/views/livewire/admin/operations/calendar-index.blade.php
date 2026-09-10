<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="Kalender Visual Ketersediaan Alat" />

        <div class="content-area">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-navy">Kalender Visual Ketersediaan Alat (Grid)</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Monitoring matriks booking unit fisik secara real-time untuk mencegah jadwal ganda (double-booking).</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-neutral font-semibold">
                        Jendela Booking: <strong class="text-navy ml-1">{{ $bookingWindowDays }} Hari</strong>
                    </span>
                    @if(auth()->user()->role === 'kasir')
                    <a href="{{ route('admin.transactions.create') }}" class="btn text-xs font-bold text-white shadow-md transition" style="background-color: var(--color-coral); border-radius: 10px; padding: 0.65rem 1.25rem;">
                        + Buat Booking Baru
                    </a>
                    @endif
                </div>
            </div>

            <!-- Controls Card -->
            <div class="sg-card mb-4 p-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <!-- Navigation & Jump to Date -->
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <button type="button" wire:click="prevPeriod" class="btn text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-lg" style="padding: 0.5rem 0.85rem;">
                            ‹ 7 Hari Lalu
                        </button>
                        <button type="button" wire:click="todayPeriod" class="btn text-xs font-bold text-white shadow-sm rounded-lg" style="background-color: var(--color-navy); padding: 0.5rem 0.85rem;">
                            Hari Ini
                        </button>
                        <button type="button" wire:click="nextPeriod" class="btn text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-lg" style="padding: 0.5rem 0.85rem;">
                            7 Hari Kedepan ›
                        </button>

                        <div class="flex items-center gap-1.5 ml-2">
                            <span class="text-xs font-bold text-gray-500">Mulai:</span>
                            <input type="date" wire:model.live="startDate" class="form-control text-xs font-semibold p-1.5" style="width: auto;">
                        </div>

                        <span class="text-xs font-bold text-gray-700 ml-1 px-3 py-1.5 bg-gray-100 rounded-lg">
                            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($startDate)->addDays($daysToShow - 1)->format('d M Y') }}
                        </span>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori:</span>
                        <select wire:model.live="categoryFilter" class="form-control text-xs font-semibold w-auto">
                            <option value="ALL">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Legend Badges Bar -->
            <div class="sg-card mb-4 p-3 flex flex-wrap gap-2.5 items-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
                <span class="matrix-legend-badge legend-ready"><span class="legend-dot"></span> Tersedia (Ready)</span>
                <span class="matrix-legend-badge legend-booked"><span class="legend-dot"></span> Booked (Dipesan)</span>
                <span class="matrix-legend-badge legend-rented"><span class="legend-dot"></span> Rented Out (Keluar)</span>
                <span class="matrix-legend-badge legend-cleaning"><span class="legend-dot"></span> Cleaning (Cuci)</span>
                <span class="matrix-legend-badge legend-maintenance"><span class="legend-dot"></span> Maintenance / Hilang</span>
            </div>

            <!-- Interactive Grid Table -->
            <div class="sg-card p-0 overflow-hidden shadow-sm" style="border: 1px solid #E2E8F0; border-radius: 16px;">
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
            </div>
        </div>
    </main>
</div>
