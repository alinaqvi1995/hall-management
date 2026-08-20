@props(['route', 'hallId' => null, 'halls' => null, 'from' => null, 'to' => null, 'day' => null])

{{--
    Shared report filter bar. Only renders the hall selector for a super admin,
    since everybody else is pinned to a single venue anyway.
--}}
<form method="GET" action="{{ $route }}" class="row g-2 align-items-end no-print" data-no-guard>
    @if ($day)
        <div class="col-sm-3 col-6">
            <label class="form-label">Date</label>
            <input type="date" name="day" value="{{ $day->toDateString() }}"
                class="form-control form-control-sm">
        </div>
    @endif

    @if ($from)
        <div class="col-sm-3 col-6">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-6">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                class="form-control form-control-sm">
        </div>
    @endif

    @if (auth()->user()->isSuperAdmin() && $halls && $halls->count() > 1)
        <div class="col-sm-3 col-6">
            <label class="form-label">Hall</label>
            <select name="hall_id" class="form-select form-select-sm">
                <option value="">All halls</option>
                @foreach ($halls as $hall)
                    <option value="{{ $hall->id }}" @selected($hallId == $hall->id)>{{ $hall->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="col-sm-3 d-flex gap-2">
        <button class="btn btn-sm btn-primary flex-grow-1">
            <i class="material-icons-outlined fs-6 align-middle">filter_alt</i> Apply
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-print>
            <i class="material-icons-outlined fs-6 align-middle">print</i>
        </button>
    </div>
</form>
