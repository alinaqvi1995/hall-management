@extends('dashboard.includes.partial.base')

@section('title', 'Business Summary')

@section('content')
    <x-page-header title="Business Summary" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')"
        icon="insights" :breadcrumbs="['Reports' => null, 'Business Summary' => null]" />

    <div class="card mb-4">
        <div class="card-body">
            @include('reports._filters', ['route' => route('reports.index')])
        </div>
    </div>

    {{-- ────────────────────────────── Money ───────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Billed" :value="'Rs. '.number_format($summary['billed'])" icon="request_quote"
                tone="primary" :hint="$summary['bookings_total'].' bookings'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Collected" :value="'Rs. '.number_format($summary['collected'])" icon="payments"
                tone="success" hint="Receipts less refunds" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Outstanding" :value="'Rs. '.number_format($summary['outstanding'])"
                icon="account_balance_wallet" tone="warning" :href="route('reports.outstanding')" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Expenses" :value="'Rs. '.number_format($summary['expenses'])" icon="receipt_long"
                tone="danger" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Net profit" :value="'Rs. '.number_format($summary['profit'])"
                icon="{{ $summary['profit'] >= 0 ? 'trending_up' : 'trending_down' }}"
                tone="{{ $summary['profit'] >= 0 ? 'info' : 'danger' }}" hint="Collected less expenses" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Occupancy" :value="$occupancy['percent'].'%'" icon="event_seat" tone="secondary"
                :hint="$occupancy['booked_slots'].' of '.$occupancy['capacity_slots'].' lawn-days'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Guests hosted" :value="number_format($summary['guests'])" icon="groups"
                tone="secondary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Cancelled" :value="number_format($summary['bookings_cancelled'])" icon="event_busy"
                tone="secondary"
                :hint="$summary['bookings_total'] > 0 ? round($summary['bookings_cancelled'] / $summary['bookings_total'] * 100).'% of bookings' : null" />
        </div>
    </div>

    {{-- ────────────────────────────── Charts ──────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">Collections &amp; bookings over time</div>
                <div class="card-body">
                    <div id="trendChart" style="min-height:320px"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">Where the money goes</div>
                <div class="card-body">
                    @if ($expenseBreakdown->isEmpty())
                        <x-empty-state icon="pie_chart" title="No expenses"
                            message="Record expenses to see the cost split." />
                    @else
                        <div id="expenseChart" style="min-height:320px"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────── Breakdowns ───────────────────────── --}}
    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">Event Types</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :sortable="false">
                        <thead>
                            <tr>
                                <th>Event Type</th>
                                <th class="text-end">Bookings</th>
                                <th class="text-end">Billed</th>
                                <th class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $eventTotal = $eventTypes->sum('total'); @endphp
                            @forelse ($eventTypes as $row)
                                <tr>
                                    <td class="fw-medium">{{ $row['label'] }}</td>
                                    <td class="text-end tabular">{{ number_format($row['total']) }}</td>
                                    <td class="text-end"><x-money :amount="$row['amount']" /></td>
                                    <td class="text-end tabular">
                                        {{ $eventTotal ? round($row['total'] / $eventTotal * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="4" icon="celebration" title="No events in this period" />
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">Expense Categories</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :sortable="false">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $expenseTotal = $expenseBreakdown->sum('amount'); @endphp
                            @forelse ($expenseBreakdown as $row)
                                <tr>
                                    <td class="fw-medium">{{ $row['label'] }}</td>
                                    <td class="text-end"><x-money :amount="$row['amount']" /></td>
                                    <td class="text-end tabular">
                                        {{ $expenseTotal ? round($row['amount'] / $expenseTotal * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="3" icon="receipt_long"
                                    title="No expenses in this period" />
                            @endforelse
                        </tbody>
                        @if ($expenseBreakdown->isNotEmpty())
                            <tfoot>
                                <tr class="fw-semibold">
                                    <td class="text-end">Total</td>
                                    <td class="text-end"><x-money :amount="$expenseTotal" /></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </x-data-table>
                </div>
            </div>
        </div>
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

            new ApexCharts(document.querySelector('#trendChart'), {
                chart: { height: 320, type: 'line', toolbar: { show: false }, fontFamily: 'Noto Sans, sans-serif' },
                series: [
                    { name: 'Collected (Rs.)', type: 'area', data: @json($revenueByDay->values()) },
                    { name: 'Bookings', type: 'column', data: @json($bookingsByDay->values()) }
                ],
                stroke: { width: [2, 0], curve: 'smooth' },
                fill: { type: ['gradient', 'solid'], opacity: [0.25, 0.85] },
                colors: ['#16a34a', '#fc5523'],
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($revenueByDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
                    labels: { style: { colors: textColour }, rotate: -45, hideOverlappingLabels: true }
                },
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

            var expenseEl = document.querySelector('#expenseChart');
            if (expenseEl) {
                new ApexCharts(expenseEl, {
                    chart: { height: 320, type: 'donut', fontFamily: 'Noto Sans, sans-serif' },
                    series: @json($expenseBreakdown->pluck('amount')),
                    labels: @json($expenseBreakdown->pluck('label')),
                    colors: ['#dc2626', '#fc5523', '#d97706', '#7c3aed', '#0891b2', '#16a34a', '#2563eb', '#65a30d'],
                    legend: { position: 'bottom', labels: { colors: textColour } },
                    dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: { formatter: function (v) { return 'Rs. ' + Number(v).toLocaleString('en-PK'); } }
                    },
                    stroke: { width: 0 }
                }).render();
            }
        })();
    </script>
@endpush
