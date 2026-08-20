@props([
    'id' => null,
    'searchable' => true,
    'sortable' => true,
    'pageLength' => 15,
    'order' => null,
])

{{--
    Table shell with a horizontal scroll container, so wide tables scroll inside
    the card instead of forcing the whole page sideways on a phone.
--}}
<div class="table-responsive">
    <table
        @if ($id) id="{{ $id }}" @endif
        class="table table-hover align-middle mb-0 app-table {{ $searchable || $sortable ? 'datatable' : '' }}"
        data-page-length="{{ $pageLength }}"
        data-searching="{{ $searchable ? 'true' : 'false' }}"
        data-ordering="{{ $sortable ? 'true' : 'false' }}"
        @if ($order !== null) data-order='@json($order)' @endif>
        {{ $slot }}
    </table>
</div>
