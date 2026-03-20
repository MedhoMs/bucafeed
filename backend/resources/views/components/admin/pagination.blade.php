@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center gap-1 sm:gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="p-2 text-white/20 cursor-default" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <x-admin.constants.icons name="chevron-left" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="p-2 text-white/60 hover:text-white transition-colors" data-load="section" aria-label="{{ __('pagination.previous') }}">
                <x-admin.constants.icons name="chevron-left" />
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-3 py-2 text-white/20 select-none">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="z-10 bg-[var(--admin-primary)]/80 text-white px-3.5 py-2 text-sm font-black rounded-xl border border-[var(--admin-primary)]/30 shadow-[0_0_15px_var(--admin-primary-glow)]">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="px-3.5 py-2 text-sm font-bold text-white/40 hover:text-white hover:bg-white/5 rounded-xl transition-all" data-load="section" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="p-2 text-white/60 hover:text-white transition-colors" data-load="section" aria-label="{{ __('pagination.next') }}">
                <x-admin.constants.icons name="chevron-right" />
            </a>
        @else
            <span class="p-2 text-white/20 cursor-default" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <x-admin.constants.icons name="chevron-right" />
            </span>
        @endif
    </nav>
@endif
