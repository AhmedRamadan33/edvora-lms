@props(['paginator'])

@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="ed-table-pagination mt-3">
        <div class="small text-muted">
            {{ __('Showing :from–:to of :total records', [
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ]) }}
            · {{ __('Page :current of :total', ['current' => $paginator->currentPage(), 'total' => $paginator->lastPage()]) }}
        </div>
        <div>{{ $paginator->onEachSide(1)->links() }}</div>
    </div>
@endif
