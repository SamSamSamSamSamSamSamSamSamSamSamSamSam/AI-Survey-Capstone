@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Pagination Navigation') }}"
        class="pagination-wrapper"
    >
        <ul class="pagination">

            {{-- ── Previous Page ──────────────────────────────────────────── --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-compact-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item" aria-label="@lang('pagination.previous')">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-compact-left"></i>
                    </a>
                </li>
            @endif

            {{-- ── Page Numbers & Ellipsis ────────────────────────────────── --}}
            @foreach ($elements as $element)

                {{-- Three-dots separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true">{{ $element }}</span>
                    </li>
                @endif

                {{-- Numbered page links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif

            @endforeach

            {{-- ── Next Page ──────────────────────────────────────────────── --}}
            @if ($paginator->hasMorePages())
                <li class="page-item" aria-label="@lang('pagination.next')">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class="bi bi-chevron-compact-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-compact-right"></i>
                    </span>
                </li>
            @endif

        </ul>
    </nav>
@endif