@extends('dashboard.includes.partial.base')

@section('title', 'Bookings')

@section('content')
    <x-page-header title="Bookings" subtitle="Calendar and register of every event" icon="event_available"
        :breadcrumbs="['Bookings' => null]">
        <x-slot:actions>
            @can('view-reports')
                <a href="{{ route('reports.dailySheet') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">today</i> Daily Sheet
                </a>
            @endcan
            @can('create-bookings')
                <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> New Booking
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @php
        $totals = [
            'count' => $bookings->count(),
            'billed' => $bookings->where('status', '!=', 'cancelled')->sum('total_amount'),
            'received' => $bookings->sum(fn($b) => $b->amount_paid),
        ];
        $totals['due'] = max($totals['billed'] - $totals['received'], 0);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Bookings" :value="number_format($totals['count'])" icon="event" tone="primary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Billed" :value="'Rs. '.number_format($totals['billed'])" icon="request_quote"
                tone="secondary" hint="Excludes cancelled" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Received" :value="'Rs. '.number_format($totals['received'])" icon="payments"
                tone="success" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Balance due" :value="'Rs. '.number_format($totals['due'])"
                icon="account_balance_wallet" tone="warning" />
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-1" data-bs-toggle="tab"
                data-bs-target="#calendarView" type="button" role="tab">
                <i class="material-icons-outlined fs-6">calendar_month</i> Calendar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-1" data-bs-toggle="tab" data-bs-target="#listView"
                type="button" role="tab">
                <i class="material-icons-outlined fs-6">list_alt</i> Register
                <span class="badge text-bg-secondary ms-1">{{ $totals['count'] }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ─────────────────────────────── Calendar ─────────────────────── --}}
        <div class="tab-pane fade show active" id="calendarView" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                    <div class="calendar-legend mt-3 pt-3 border-top">
                        <span><span class="dot" style="background:#d97706"></span>Pending</span>
                        <span><span class="dot" style="background:#16a34a"></span>Confirmed</span>
                        <span><span class="dot" style="background:#2563eb"></span>Completed</span>
                        <span><span class="dot" style="background:#dc2626"></span>Cancelled</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────── Register ─────────────────────── --}}
        <div class="tab-pane fade" id="listView" role="tabpanel">
            <div class="card">
                <div class="card-body p-0">
                    <div class="p-3 pb-0">
                        <form method="GET" class="row g-2 align-items-end" data-no-guard>
                            <div class="col-sm-3">
                                <label class="form-label">Booking status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    @foreach (\App\Models\Booking::STATUSES as $key => $label)
                                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">Payment status</label>
                                <select name="payment_status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    @foreach (\App\Models\Booking::PAYMENT_STATUSES as $key => $label)
                                        <option value="{{ $key }}" @selected(request('payment_status') === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (auth()->user()->isSuperAdmin() && $halls->count() > 1)
                                <div class="col-sm-3">
                                    <label class="form-label">Hall</label>
                                    <select name="hall_id" class="form-select form-select-sm">
                                        <option value="">All halls</option>
                                        @foreach ($halls as $h)
                                            <option value="{{ $h->id }}" @selected(request('hall_id') == $h->id)>
                                                {{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-sm-3 d-flex gap-2">
                                <button class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                                @if (request()->hasAny(['status', 'payment_status', 'hall_id']))
                                    <a href="{{ route('bookings.index') }}"
                                        class="btn btn-sm btn-outline-secondary">Clear</a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <hr class="my-3">

                    <x-data-table :order="[[3, 'desc']]">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th>Venue</th>
                                <th class="text-end">Guests</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="no-sort text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="{{ $booking->isCancelled() ? 'opacity-75' : '' }}">
                                    <td class="text-secondary">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('bookings.show', $booking) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $booking->formatted_booking_number }}
                                        </a>
                                        <div><small class="text-secondary">
                                                {{ $booking->created_at?->format('d M Y') }}</small></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $booking->customer->name ?? '—' }}</div>
                                        <small class="text-secondary">{{ $booking->customer->phone ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $booking->event_type_label ?? '—' }}</div>
                                        <small class="text-secondary">
                                            {{ $booking->start_datetime?->format('d M Y, h:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ $booking->lawn->name ?? '—' }}</div>
                                        <small class="text-secondary">{{ $booking->hall->name ?? '' }}</small>
                                    </td>
                                    <td class="text-end tabular">{{ number_format($booking->guest_count) }}</td>
                                    <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                                    <td class="text-end">
                                        @if ($booking->balance_due > 0)
                                            <x-money :amount="$booking->balance_due" tone="danger" />
                                        @else
                                            <span class="text-success small fw-semibold">Settled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 align-items-start">
                                            <x-status-badge :label="$booking->status_label"
                                                :tone="$booking->status_colour" />
                                            <x-status-badge :label="$booking->payment_status_label"
                                                :tone="$booking->payment_status_colour" />
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                                        href="{{ route('bookings.show', $booking) }}">
                                                        <i
                                                            class="material-icons-outlined fs-6">visibility</i>View</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                                        href="{{ route('bookings.invoice', $booking) }}"
                                                        target="_blank">
                                                        <i class="material-icons-outlined fs-6">receipt</i>Invoice</a>
                                                </li>
                                                @can('edit-bookings')
                                                    @if ($booking->isEditable())
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                                href="{{ route('bookings.edit', $booking) }}">
                                                                <i
                                                                    class="material-icons-outlined fs-6">edit</i>Edit</a>
                                                        </li>
                                                    @endif
                                                @endcan
                                                @can('delete-bookings')
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('bookings.destroy', $booking) }}"
                                                            method="POST">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                                data-confirm="Delete booking {{ $booking->formatted_booking_number }}? Cancelling is usually the better option.">
                                                                <i
                                                                    class="material-icons-outlined fs-6">delete</i>Delete</button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="10" icon="event_busy" title="No bookings yet"
                                    message="Create the first booking to see it here and on the calendar.">
                                    @can('create-bookings')
                                        <x-slot:action>
                                            <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                                                <i class="material-icons-outlined fs-6 align-middle">add</i> New Booking
                                            </a>
                                        </x-slot:action>
                                    @endcan
                                </x-empty-state>
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/fullcalendar/css/main.min.css') }}">
@endpush

@push('vendor_scripts')
    <script src="{{ asset('admin/plugins/fullcalendar/js/main.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('calendar');
            if (!el || typeof FullCalendar === 'undefined') return;

            var calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                height: 720,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                // Events are fetched per visible window rather than dumping every
                // booking into the page.
                events: '{{ route('bookings.events') }}',
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                dayMaxEvents: 3,
                nowIndicator: true,
                eventDidMount: function (info) {
                    var p = info.event.extendedProps;
                    info.el.title = info.event.title
                        + '\n' + (p.status || '')
                        + ' · ' + (p.paymentStatus || '')
                        + (p.guests ? '\n' + p.guests + ' guests' : '');
                }
            });

            calendar.render();

            // The calendar starts inside a hidden tab, so it must re-measure
            // once its pane becomes visible or it renders zero-width.
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tab) {
                tab.addEventListener('shown.bs.tab', function () {
                    calendar.updateSize();
                });
            });
        });
    </script>
@endpush
