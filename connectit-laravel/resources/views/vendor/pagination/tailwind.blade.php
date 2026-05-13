@if ($paginator->hasPages())
<nav class="flex items-center justify-between" aria-label="Pagination">
    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    <div class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg bg-white/3 text-gray-600 text-xs font-bold cursor-not-allowed">← Prev</span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 text-xs font-bold hover:bg-white/10 transition-colors">← Prev</a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
            <span class="px-2 py-1.5 text-gray-600 text-xs">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                    <span class="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 text-xs font-bold hover:bg-white/10 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 text-xs font-bold hover:bg-white/10 transition-colors">Next →</a>
        @else
        <span class="px-3 py-1.5 rounded-lg bg-white/3 text-gray-600 text-xs font-bold cursor-not-allowed">Next →</span>
        @endif
    </div>
</nav>
@endif
