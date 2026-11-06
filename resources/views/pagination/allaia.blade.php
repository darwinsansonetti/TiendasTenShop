@if ($paginator->hasPages())
<ul class="pagination">
    <li>
        @if ($paginator->onFirstPage())
            <a href="#0" class="prev" title="Anterior" style="opacity:.4;pointer-events:none">&#10094;</a>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="prev" title="Anterior">&#10094;</a>
        @endif
    </li>

    @foreach ($elements as $element)
        @if (is_string($element))
            <li><a href="#0" style="pointer-events:none">{{ $element }}</a></li>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                <li>
                    <a href="{{ $url }}" class="{{ $page == $paginator->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                </li>
            @endforeach
        @endif
    @endforeach

    <li>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="next" title="Siguiente">&#10095;</a>
        @else
            <a href="#0" class="next" title="Siguiente" style="opacity:.4;pointer-events:none">&#10095;</a>
        @endif
    </li>
</ul>
@endif