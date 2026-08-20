@extends('dashboard.includes.partial.base')

@section('title', 'Profit per Event')

@section('content')
    <x-page-header title="Profit per Event" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')"
        icon="trending_up" :breadcrumbs="['Reports' => null, 'Profit per Event' => null]" />

    <div class="card mb-4">
        <div class="card-body">
            @include('reports._filters', ['route' => route('reports.profitability')])
        </div>
    </div>

    @php
        $totalBilled = $bookings->sum('total_amount');
        $totalCollected = $bookings->sum(fn($b) => $b->amount_paid);
        $totalCosts = $bookings->sum(fn($b) => (float) ($b->expenses_sum_amount ?? 0));
        $totalMargin = $totalCollected - $totalCosts;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Billed" :value="'Rs. '.number_format($totalBilled)" icon="request_quote"
                tone="primary" :hint="$bookings->count().' events'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Collected" :value="'Rs. '.number_format($totalCollected)" icon="payments"
                tone="success" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Direct costs" :value="'Rs. '.number_format($totalCosts)" icon="receipt_long"
                tone="danger" hint="Expenses linked to events" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Margin" :value="'Rs. '.number_format($totalMargin)"
                icon="{{ $totalMargin >= 0 ? 'trending_up' : 'trending_down' }}"
                tone="{{ $totalMargin >= 0 ? 'info' : 'danger' }}"
                :hint="$totalCollected > 0 ? round($totalMargin / $totalCollected * 100, 1).'% of collections' : null" />
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Event Profitability
            <small class="text-secondary fw-normal">
                — margin is money actually collected less the expenses linked to that event
            </small>
        </div>
        <div class="card-body p-0">
            <x-data-table :order="[[2, 'desc']]">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Event date</th>
                        <th>Venue</th>
                        <th class="text-end">Guests</th>
                        <th class="text-end">Billed</th>
                        <th class="text-end">Collected</th>
                        <th class="text-end">Costs</th>
                        <th class="text-end">Margin</th>
                        <th class="text-end">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php
                            $costs = (float) ($booking->expenses_sum_amount ?? 0);
                            $collected = $booking->amount_paid;
                            $margin = $collected - $costs;
                            $marginPercent = $collected > 0 ? round($margin / $collected * 100, 1) : null;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $booking) }}"
                                    class="fw-semibold text-decoration-none">
                                    {{ $booking->formatted_booking_number }}
                                </a>
                            </td>
                            <td>{{ $booking->customer->name ?? '—' }}</td>
                            <td>{{ $booking->start_datetime?->format('d M Y') }}</td>
                            <td>
                                <div>{{ $booking->lawn->name ?? '—' }}</div>
                                <small class="text-secondary">{{ $booking->hall->name ?? '' }}</small>
                            </td>
                            <td class="text-end tabular">{{ number_format($booking->guest_count) }}</td>
                            <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                            <td class="text-end"><x-money :amount="$collected" /></td>
                            <td class="text-end"><x-money :amount="$costs" zero="—" /></td>
                            <td class="text-end fw-semibold">
                                <x-money :amount="$margin" :tone="$margin >= 0 ? 'success' : 'danger'" />
                            </td>
                            <td class="text-end tabular">
                                @if ($marginPercent === null)
                                    <span class="text-secondary">—</span>
                                @else
                                    <span class="text-{{ $marginPercent >= 0 ? 'success' : 'danger' }}">
                                        {{ $marginPercent }}%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="10" icon="trending_up" title="No events in this period"
                            message="Adjust the date range to see profitability." />
                    @endforelse
                </tbody>
                @if ($bookings->isNotEmpty())
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="5" class="text-end">Totals</td>
                            <td class="text-end"><x-money :amount="$totalBilled" /></td>
                            <td class="text-end"><x-money :amount="$totalCollected" /></td>
                            <td class="text-end"><x-money :amount="$totalCosts" /></td>
                            <td class="text-end">
                                <x-money :amount="$totalMargin" :tone="$totalMargin >= 0 ? 'success' : 'danger'" />
                            </td>
                            <td class="text-end tabular">
                                {{ $totalCollected > 0 ? round($totalMargin / $totalCollected * 100, 1).'%' : '—' }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </x-data-table>
        </div>
    </div>

    <div class="alert alert-info app-alert mt-3 d-flex align-items-start gap-2">
        <i class="material-icons-outlined">info</i>
        <div class="small">
            Only expenses linked to a booking count here. Hall overheads such as electricity, salaries and
            taxes are unlinked by design, and appear in the
            <a href="{{ route('reports.index') }}" class="alert-link">Business Summary</a> instead.
        </div>
    </div>
@endsection
