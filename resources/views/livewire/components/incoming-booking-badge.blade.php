<div wire:poll.5s class="{{ $type === 'sidebar' ? 'w-full' : 'inline-flex items-center' }}">
    @if($type === 'sidebar')
        <a href="{{ route('admin.operations.incoming_booking') }}" 
           class="sidebar-link {{ request()->routeIs('admin.operations.incoming_booking') ? 'active' : '' }} flex items-center justify-between w-full"
           title="Daftar Booking Online Masuk">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Booking Masuk</span>
            </div>
            @if($count > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-black text-white bg-red-600 rounded-full shadow-sm animate-pulse">
                    {{ $count }}
                </span>
            @endif
        </a>
    @else
        <a href="{{ route('admin.operations.incoming_booking') }}" 
           class="btn text-xs font-bold {{ $count > 0 ? 'text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 shadow-xs' : 'text-gray-700 hover:text-[#101F42] bg-gray-100 hover:bg-gray-200/80' }} px-3.5 py-2 rounded-xl transition flex items-center gap-2 relative" 
           title="{{ $count > 0 ? $count . ' Booking Online Masuk Menunggu Diproses' : 'Daftar Booking Online Masuk' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $count > 0 ? 'text-red-600' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span>Booking Masuk</span>
            @if($count > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-black text-white bg-red-600 rounded-full shadow-sm animate-pulse">
                    {{ $count }}
                </span>
            @endif
        </a>
    @endif
</div>
