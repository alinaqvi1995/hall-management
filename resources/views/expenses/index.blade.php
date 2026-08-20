@extends('dashboard.includes.partial.base')

@section('title', 'Expenses')

@section('content')
    <x-page-header title="Expenses" :subtitle="'Costs from '.$from->format('d M Y').' to '.$to->format('d M Y')"
        icon="receipt_long" :breadcrumbs="['Expenses' => null]">
        <x-slot:actions>
            <button class="btn btn-outline-secondary btn-sm" data-print>
                <i class="material-icons-outlined fs-6 align-middle">print</i> Print
            </button>
            @can('create-expenses')
                <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Record Expense
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @php
        $eventCosts = $expenses->whereNotNull('booking_id')->sum('amount');
        $overheads = $expenses->whereNull('booking_id')->sum('amount');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Total spent" :value="'Rs. '.number_format($total)" icon="payments" tone="danger"
                :hint="$expenses->count().' entries'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Event costs" :value="'Rs. '.number_format($eventCosts)" icon="celebration"
                tone="warning" hint="Linked to a booking" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Overheads" :value="'Rs. '.number_format($overheads)" icon="home_work"
                tone="secondary" hint="Utilities, salaries, rent" />
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="p-3 pb-0 no-print">
                <form method="GET" class="row g-2 align-items-end" data-no-guard>
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
                    <div class="col-sm-4">
                        <label class="form-label">Category</label>
                        <select name="expense_category_id" class="form-select form-select-sm">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('expense_category_id') == $category->id)>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2 d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    </div>
                </form>
            </div>

            <hr class="my-3">

            <x-data-table :order="[[0, 'desc']]">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Booking</th>
                        <th>Vendor</th>
                        <th>Method</th>
                        <th>Recorded by</th>
                        <th class="text-end">Amount</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->spent_on?->format('d M Y') }}</td>
                            <td>
                                <div class="fw-medium">{{ $expense->title }}</div>
                                @if ($expense->reference)
                                    <small class="text-secondary">Ref: {{ $expense->reference }}</small>
                                @endif
                            </td>
                            <td>{{ $expense->category->name ?? '—' }}</td>
                            <td>
                                @if ($expense->booking)
                                    <a href="{{ route('bookings.show', $expense->booking) }}"
                                        class="text-decoration-none">
                                        {{ $expense->booking->formatted_booking_number }}
                                    </a>
                                @else
                                    <span class="chip">Overhead</span>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $expense->vendor->name ?? '—' }}</td>
                            <td>{{ $expense->method_label }}</td>
                            <td class="text-secondary">{{ $expense->creator->name ?? '—' }}</td>
                            <td class="text-end"><x-money :amount="$expense->amount" /></td>
                            <td class="text-end no-print">
                                @can('edit-expenses')
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </a>
                                @endcan
                                @can('delete-expenses')
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Delete this expense entry?">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="9" icon="receipt_long" title="No expenses in this period"
                            message="Record catering, decor, utilities and wages to see true profit per event.">
                            @can('create-expenses')
                                <x-slot:action>
                                    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                                        <i class="material-icons-outlined fs-6 align-middle">add</i> Record Expense
                                    </a>
                                </x-slot:action>
                            @endcan
                        </x-empty-state>
                    @endforelse
                </tbody>
                @if ($expenses->isNotEmpty())
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="7" class="text-end">Total</td>
                            <td class="text-end"><x-money :amount="$total" /></td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </x-data-table>
        </div>
    </div>
@endsection
