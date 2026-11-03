@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        <div>
            @if ($paginator->previousPageUrl())
                <a href="{{ $paginator->previousPageUrl() }}">← Sebelumnya</a>
            @endif
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" @if ($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Berikutnya →</a>
            @endif
        </div>
    </nav>
@endif
