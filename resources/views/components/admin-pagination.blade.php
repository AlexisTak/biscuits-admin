@props(['paginator'])

@if ($paginator->hasPages())
<nav class="pagination-wrapper" role="navigation" aria-label="Pagination">
    <div class="pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-item pagination-disabled">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Pages --}}
        @foreach (range(1, $paginator->lastPage()) as $page)
            @if ($page == $paginator->currentPage())
                <span class="pagination-item pagination-active">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="pagination-item">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="pagination-item pagination-disabled">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif
    </div>

    <div class="pagination-info">
        <p>
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>
    </div>
</nav>
@endif