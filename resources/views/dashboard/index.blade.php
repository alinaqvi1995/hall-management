@extends('dashboard.includes.partial.base')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard"
        :subtitle="$hall ? $hall->name.' · '.now()->format('F Y') : 'All venues · '.now()->format('F Y')"
        icon="dashboard">
        <x-slot:actions>
            @can('view-reports')
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">insights</i> Reports
                </a>
            @endcan
            @can('create-bookings')
                <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> New Booking
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- A user with no hall attached would previously hit a fatal error here. --}}
    @if ($needsHallLink)
        <div class="alert alert-warning app-alert d-flex align-items-start gap-2">
            <i class="material-icons-outlined">link_off</i>
            <div>
                <strong>Your account is not linked to a venue.</strong>
                <p class="mb-0 small">Ask a super admin to assign you to a hall before taking bookings.</p>
            </div>
        </div>
    @endif

    {{-- ─────────────────────────── Money this month ─────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Billed this month" :value="'Rs. '.number_format($summary['billed'])" icon="request_quote"
                tone="primary" :hint="$summary['bookings_total'].' bookings'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Collected" :value="'Rs. '.number_format($summary['collected'])" icon="payments"
                tone="success" hint="Receipts less refunds" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Outstanding" :value="'Rs. '.number_format($summary['outstanding'])"
                icon="account_balance_wallet" tone="warning"
                :href="Route::has('reports.outstanding') && auth()->user()->can('view-reports') ? route('reports.outstanding') : null"
                hint="Still to be recovered" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Profit" :value="'Rs. '.number_format($summary['profit'])"
                icon="{{ $summary['profit'] >= 0 ? 'trending_up' : 'trending_down' }}"
                tone="{{ $summary['profit'] >= 0 ? 'info' : 'danger' }}"
                :hint="'Expenses Rs. '.number_format($summary['expenses'])" />
        </div>
    </div>

    {{-- ───────────────────────────── Operations ─────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Occupancy" :value="$occupancy['percent'].'%'" icon="event_seat" tone="secondary"
                :hint="$occupancy['booked_slots'].' of '.$occupancy['capacity_slots'].' lawn-days'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Guests hosted" :value="number_format($summary['guests'])" icon="groups"
                tone="secondary" hint="This month" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="{{ $isSuperAdmin ? 'Venues' : 'Lawns / Halls' }}"
                :value="$isSuperAdmin ? number_format($hallCount) : number_format($lawnCount)" icon="festival"
                tone="secondary" :hint="$isSuperAdmin ? number_format($lawnCount).' bookable spaces' : 'Bookable spaces'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Team members" :value="number_format($userCount)" icon="people" tone="secondary"
                :href="auth()->user()->can('view-users') ? route('dashboard.users.index') : null" hint="System users" />
        </div>
    </div>

    {{-- ─────────────────────── Booking status snapshot ──────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-4 align-items-center">
                        <span class="form-section-title mb-0" style="flex:0 0 auto">Booking pipeline</span>
                        @foreach ([['Pending', 'pending', 'warning'], ['Confirmed', 'confirmed', 'success'], ['Completed', 'completed', 'primary'], ['Cancelled', 'cancelled', 'danger']] as [$label, $key, $tone])
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill text-bg-{{ $tone }}">
                                    {{ number_format($statusCounts[$key]) }}
                                </span>
                                <span class="small text-secondary">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────────── Charts ───────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Collections &amp; bookings — last 30 days</span>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="min-height:300px"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">Event types this month</div>
                <div class="card-body">
                    @if ($eventTypes->isEmpty())
                        <x-empty-state icon="pie_chart" title="No events yet"
                            message="Event mix appears once bookings are made." />
                    @else
                        <div id="eventTypeChart" style="min-height:300px"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────────────── Work lists ────────────────────────── --}}
    <div class="row g-3">
        {{-- Today --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Today&rsquo;s events</span>
                    @can('view-reports')
                        <a href="{{ route('reports.dailySheet') }}" class="btn btn-sm btn-outline-secondary">Full
                            sheet</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($todayBookings->isEmpty())
                        <x-empty-state icon="event_busy" title="Nothing scheduled today"
                            message="Enjoy the quiet day." />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($todayBookings as $booking)
                                <a href="{{ route('bookings.show', $booking) }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                    <span class="avatar-initial">
                                        {{ mb_substr($booking->customer->name ?? '?', 0, 1) }}
                                    </span>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-0 fw-semibold text-truncate">
                                            {{ $booking->customer->name ?? 'Customer' }}
                                        </p>
                                        <small class="text-secondary">
                                            {{ $booking->lawn->name ?? '—' }} ·
                                            {{ $booking->start_datetime->format('h:i A') }} ·
                                            {{ number_format($booking->guest_count) }} guests
                                        </small>
                                    </div>
                                    <x-status-badge :label="$booking->status_label" :tone="$booking->status_colour" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upcoming --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Upcoming events</span>
                    @can('view-bookings')
                        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">All
                            bookings</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($upcoming->isEmpty())
                        <x-empty-state icon="event_note" title="No upcoming events"
                            message="Bookings for the next 30 days appear here." />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($upcoming as $booking)
                                <a href="{{ route('bookings.show', $booking) }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                    <div class="text-center" style="min-width:46px">
                                        <div class="fw-bold lh-1">{{ $booking->start_datetime->format('d') }}</div>
                                        <small class="text-secondary text-uppercase">
                                            {{ $booking->start_datetime->format('M') }}
                                        </small>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-0 fw-semibold text-truncate">
                                            {{ $booking->customer->name ?? 'Customer' }}
                                        </p>
                                        <small class="text-secondary">
                                            {{ $booking->event_type_label ?? 'Event' }} ·
                                            {{ $booking->lawn->name ?? '—' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold">
                                            <x-money :amount="$booking->total_amount" />
                                        </div>
                                        <small class="text-{{ $booking->payment_status_colour }}">
                                            {{ $booking->payment_status_label }}
                                        </small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recovery list --}}
        @can('view-payments')
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Payments to follow up</span>
                        @can('view-reports')
                            <a href="{{ route('reports.outstanding') }}"
                                class="btn btn-sm btn-outline-secondary">Full list</a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <x-data-table :searchable="false" :sortable="false">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Event date</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Received</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outstanding as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking) }}"
                                                class="fw-semibold text-decoration-none">
                                                {{ $booking->formatted_booking_number }}
                                            </a>
                                        </td>
                                        <td>{{ $booking->customer->name ?? '—' }}</td>
                                        <td>{{ $booking->start_datetime?->format('d M Y') }}</td>
                                        <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                                        <td class="text-end"><x-money :amount="$booking->amount_paid" /></td>
                                        <td class="text-end fw-semibold">
                                            <x-money :amount="$booking->balance_due" tone="danger" />
                                        </td>
                                        <td>
                                            <x-status-badge :label="$booking->payment_status_label"
                                                :tone="$booking->payment_status_colour" />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state :colspan="7" icon="task_alt" title="Everything is settled"
                                        message="No booking has an outstanding balance." />
                                @endforelse
                            </tbody>
                        </x-data-table>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('vendor_scripts')
    <script src="{{ asset('admin/plugins/apexchart/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        (function () {
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            var textColour = isDark ? '#adb5bd' : '#6c757d';
            var gridColour = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';

            var revenue = @json($revenueByDay->values());
            var bookings = @json($bookingsByDay->values());
            var days = @json($revenueByDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));

            new ApexCharts(document.querySelector('#revenueChart'), {
                chart: { height: 300, type: 'line', toolbar: { show: false }, fontFamily: 'Noto Sans, sans-serif' },
                // Collections are money and bookings are counts, so they need
                // separate axes or the smaller series flattens to zero.
                series: [
                    { name: 'Collected (Rs.)', type: 'area', data: revenue },
                    { name: 'Bookings', type: 'column', data: bookings }
                ],
                stroke: { width: [2, 0], curve: 'smooth' },
                fill: { type: ['gradient', 'solid'], opacity: [0.25, 0.85] },
                colors: ['#16a34a', '#fc5523'],
                dataLabels: { enabled: false },
                xaxis: { categories: days, labels: { style: { colors: textColour }, rotate: -45, hideOverlappingLabels: true } },
                yaxis: [
                    {
                        seriesName: 'Collected (Rs.)',
                        labels: {
                            style: { colors: textColour },
                            formatter: function (v) { return 'Rs. ' + Math.round(v / 1000) + 'k'; }
                        }
                    },
                    {
                        seriesName: 'Bookings', opposite: true,
                        labels: { style: { colors: textColour }, formatter: function (v) { return Math.round(v); } }
                    }
                ],
                grid: { borderColor: gridColour, strokeDashArray: 4 },
                legend: { labels: { colors: textColour } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            var eventEl = document.querySelector('#eventTypeChart');
            if (eventEl) {
                new ApexCharts(eventEl, {
                    chart: { height: 300, type: 'donut', fontFamily: 'Noto Sans, sans-serif' },
                    series: @json($eventTypes->pluck('total')),
                    labels: @json($eventTypes->pluck('label')),
                    colors: ['#fc5523', '#16a34a', '#2563eb', '#d97706', '#7c3aed', '#0891b2', '#dc2626', '#65a30d'],
                    legend: { position: 'bottom', labels: { colors: textColour } },
                    dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
                    tooltip: { theme: isDark ? 'dark' : 'light' },
                    stroke: { width: 0 }
                }).render();
            }
        })();
    </script>
@endpush
