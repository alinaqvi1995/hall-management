@extends('dashboard.includes.partial.base')

@section('title', 'Daily Event Sheet')

@section('content')
    <x-page-header title="Daily Event Sheet" :subtitle="$day->format('l, d F Y')" icon="today"
        :breadcrumbs="['Reports' => null, 'Daily Event Sheet' => null]" />

    <div class="card mb-4 no-print">
        <div class="card-body">
            @include('reports._filters', ['route' => route('reports.dailySheet'), 'from' => null, 'to' => null])

            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                <a href="{{ route('reports.dailySheet', ['day' => $day->copy()->subDay()->toDateString(), 'hall_id' => request('hall_id')]) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="material-icons-outlined fs-6 align-middle">chevron_left</i> Previous day
                </a>
                <a href="{{ route('reports.dailySheet', ['hall_id' => request('hall_id')]) }}"
                    class="btn btn-sm btn-outline-primary">Today</a>
                <a href="{{ route('reports.dailySheet', ['day' => $day->copy()->addDay()->toDateString(), 'hall_id' => request('hall_id')]) }}"
                    class="btn btn-sm btn-outline-secondary">
                    Next day <i class="material-icons-outlined fs-6 align-middle">chevron_right</i>
                </a>
            </div>
        </div>
    </div>

    {{-- Printed header, since the page furniture is hidden when printing. --}}
    <div class="print-only mb-3">
        <h3 style="margin:0">Daily Event Sheet</h3>
        <p style="margin:0">{{ $day->format('l, d F Y') }}</p>
    </div>

    @if ($bookings->isEmpty())
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="event_busy" title="No events on this day"
                    message="Nothing is scheduled for {{ $day->format('d M Y') }}." />
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-stat-card label="Events today" :value="number_format($bookings->count())" icon="event"
                    tone="primary" />
            </div>
            <div class="col-xl-3 col-sm-6">
                <x-stat-card label="Total guests" :value="number_format($bookings->sum('guest_count'))" icon="groups"
                    tone="secondary" />
            </div>
            <div class="col-xl-3 col-sm-6">
                <x-stat-card label="Billed" :value="'Rs. '.number_format($bookings->sum('total_amount'))"
                    icon="request_quote" tone="success" />
            </div>
            <div class="col-xl-3 col-sm-6">
                <x-stat-card label="To collect"
                    :value="'Rs. '.number_format($bookings->sum(fn($b) => max($b->balance_due, 0)))"
                    icon="account_balance_wallet" tone="warning" />
            </div>
        </div>

        {{-- One card per event: the operations team works down this list. --}}
        @foreach ($bookings as $booking)
            <div class="card mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="d-flex align-items-center gap-2">
                        <strong>{{ $booking->start_datetime?->format('h:i A') }} –
                            {{ $booking->end_datetime?->format('h:i A') }}</strong>
                        <span class="text-secondary">·</span>
                        <span>{{ $booking->lawn->name ?? ($booking->hall->name ?? '—') }}</span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <x-status-badge :label="$booking->status_label" :tone="$booking->status_colour" />
                        <x-status-badge :label="$booking->payment_status_label"
                            :tone="$booking->payment_status_colour" />
                        <a href="{{ route('bookings.show', $booking) }}"
                            class="btn btn-sm btn-outline-secondary no-print">
                            {{ $booking->formatted_booking_number }}
                        </a>
                    </span>
                </div>
                <div class="card-body">
                    <div class="detail-grid mb-3">
                        <div class="detail-item">
                            <p class="detail-item__label">Customer</p>
                            <p class="detail-item__value">
                                {{ $booking->customer->name ?? '—' }}
                                @if ($booking->customer?->phone)
                                    <span class="d-block text-secondary small">{{ $booking->customer->phone }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Event</p>
                            <p class="detail-item__value">{{ $booking->event_type_label ?? '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Guests</p>
                            <p class="detail-item__value">{{ number_format($booking->guest_count) }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Catering</p>
                            <p class="detail-item__value">
                                @if ($booking->menu_amount > 0)
                                    {{ $booking->package->name ?? 'Custom' }}
                                    <span class="d-block text-secondary small">
                                        Rs. {{ number_format($booking->per_head_rate) }}/head
                                    </span>
                                @else
                                    {{-- Kitchen has nothing to prepare for this event. --}}
                                    <span class="badge text-bg-light">Own caterer</span>
                                    <span class="d-block text-secondary small">Venue only</span>
                                @endif
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Balance to collect</p>
                            <p class="detail-item__value">
                                @if ($booking->balance_due > 0)
                                    <x-money :amount="$booking->balance_due" tone="danger" />
                                @else
                                    <span class="text-success">Fully paid</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row g-3">
                        @if ($booking->addons->isNotEmpty())
                            <div class="col-md-6">
                                <p class="detail-item__label">Extra services</p>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach ($booking->addons as $addon)
                                        <li class="d-flex align-items-center gap-2">
                                            <i
                                                class="material-icons-outlined fs-6 text-success">check</i>{{ $addon->name }}
                                            @if ($addon->pivot->quantity > 1)
                                                <span class="text-secondary">×{{ $addon->pivot->quantity }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($booking->package && $booking->package->items)
                            <div class="col-md-6">
                                <p class="detail-item__label">Menu items</p>
                                <p class="mb-0 small">{{ collect($booking->package->items)->join(', ') }}</p>
                            </div>
                        @endif

                        @if ($booking->staff->isNotEmpty())
                            <div class="col-md-6">
                                <p class="detail-item__label">Staff assigned</p>
                                <p class="mb-0 small">{{ $booking->staff->pluck('name')->join(', ') }}</p>
                            </div>
                        @endif

                        @if ($booking->notes)
                            <div class="col-12">
                                <p class="detail-item__label">Notes</p>
                                <p class="mb-0 small">{{ $booking->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
