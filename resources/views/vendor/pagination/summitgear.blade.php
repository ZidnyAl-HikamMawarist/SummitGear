@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-4 border-t border-gray-100 bg-gray-50/50">
        <div class="text-xs text-gray-500 font-medium">
            Menampilkan
            <span class="font-bold text-navy">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-bold text-navy">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-bold text-navy">{{ $paginator->total() }}</span>
            data
        </div>

        <div class="inline-flex items-center gap-1 shadow-sm rounded-lg bg-white p-1 border border-gray-200">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-2.5 py-1 text-xs font-bold text-gray-300 rounded cursor-not-allowed">
                    ‹ Prev
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="px-2.5 py-1 text-xs font-bold text-navy hover:bg-gray-100 rounded transition">
                    ‹ Prev
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-2 py-1 text-xs text-gray-400 font-bold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-1 text-xs font-bold rounded bg-coral text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="px-3 py-1 text-xs font-bold rounded text-gray-600 hover:bg-gray-100 transition">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="px-2.5 py-1 text-xs font-bold text-navy hover:bg-gray-100 rounded transition">
                    Next ›
                </button>
            @else
                <span class="px-2.5 py-1 text-xs font-bold text-gray-300 rounded cursor-not-allowed">
                    Next ›
                </span>
            @endif
        </div>
    </nav>
@endif
