@extends('dashboard.includes.partial.base')

@section('title', 'Outstanding Dues')

@section('content')
    <x-page-header title="Outstanding Dues" subtitle="Bookings with money still to collect, soonest event first"
        icon="account_balance_wallet" :breadcrumbs="['Reports' => null, 'Outstanding Dues' => null]" />

    <div class="card mb-4">
        <div class="card-body">
            @include('reports._filters', ['route' => route('reports.outstanding')])
        </div>
    </div>

    @php
        $overdue = $bookings->filter(fn($b) => $b->start_datetime && $b->start_datetime->isPast());
        $advanceShort = $bookings->filter(fn($b) => $b->amount_paid < $b->required_advance);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Total outstanding" :value="'Rs. '.number_format($totalOutstanding)"
                icon="account_balance_wallet" tone="warning" :hint="$bookings->count().' booking(s)'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Past events unpaid" :value="number_format($overdue->count())" icon="warning"
                tone="danger" :hint="'Rs. '.number_format($overdue->sum(fn($b) => max($b->balance_due, 0))).' overdue'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Advance not met" :value="number_format($advanceShort->count())" icon="pending_actions"
                tone="secondary" hint="Below the hall's advance policy" />
        </div>
    </div>

    <div class="card">
        <div class="card-header">Follow-up List</div>
        <div class="card-body p-0">
            <x-data-table :order="[[3, 'asc']]">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Event date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Received</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php $isOverdue = $booking->start_datetime && $booking->start_datetime->isPast(); @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('bookings.show', $booking) }}"
                                    class="fw-semibold text-decoration-none">
                                    {{ $booking->formatted_booking_number }}
                                </a>
                                <small class="d-block text-secondary">{{ $booking->hall->name ?? '' }}</small>
                            </td>
                            <td>{{ $booking->customer->name ?? '—' }}</td>
                            <td>
                                @if ($booking->customer?->phone)
                                    <a href="tel:{{ $booking->customer->phone }}"
                                        class="text-decoration-none">{{ $booking->customer->phone }}</a>
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $booking->start_datetime?->format('d M Y') }}
                                @if ($isOverdue)
                                    <span class="badge text-bg-danger ms-1">Overdue</span>
                                @endif
                            </td>
                            <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                            <td class="text-end"><x-money :amount="$booking->amount_paid" /></td>
                            <td class="text-end fw-semibold">
                                <x-money :amount="$booking->balance_due" tone="danger" />
                            </td>
                            <td>
                                <x-status-badge :label="$booking->payment_status_label"
                                    :tone="$booking->payment_status_colour" />
                            </td>
                            <td class="text-end no-print">
                                <a href="{{ route('bookings.show', $booking) }}"
                                    class="btn btn-sm btn-outline-secondary" title="Open booking">
                                    <i class="material-icons-outlined fs-6">open_in_new</i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="9" icon="task_alt" title="Nothing outstanding"
                            message="Every booking in scope is fully paid." />
                    @endforelse
                </tbody>
                @if ($bookings->isNotEmpty())
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="6" class="text-end">Total outstanding</td>
                            <td class="text-end"><x-money :amount="$totalOutstanding" tone="danger" /></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </x-data-table>
        </div>
    </div>
@endsection
