@extends('dashboard.includes.partial.base')

@section('title', 'Payments')

@section('content')
    <x-page-header title="Payments" subtitle="Every receipt and refund across your bookings" icon="payments"
        :breadcrumbs="['Payments' => null]">
        <x-slot:actions>
            <button class="btn btn-outline-secondary btn-sm" data-print>
                <i class="material-icons-outlined fs-6 align-middle">print</i> Print
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Received" :value="'Rs. '.number_format($totalIn)" icon="south_west" tone="success"
                :hint="$payments->where('direction', 'in')->count().' receipt(s)'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Refunded" :value="'Rs. '.number_format($totalRefund)" icon="north_east" tone="danger"
                :hint="$payments->where('direction', 'refund')->count().' refund(s)'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Net collected" :value="'Rs. '.number_format($totalIn - $totalRefund)"
                icon="account_balance" tone="primary" />
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="p-3 pb-0 no-print">
                <form method="GET" class="row g-2 align-items-end" data-no-guard>
                    <div class="col-sm-3 col-6">
                        <label class="form-label">From</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-3 col-6">
                        <label class="form-label">To</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-2 col-6">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach (\App\Models\Payment::METHODS as $key => $label)
                                <option value="{{ $key }}" @selected(request('method') === $key)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2 col-6">
                        <label class="form-label">Type</label>
                        <select name="direction" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="in" @selected(request('direction') === 'in')>Receipts</option>
                            <option value="refund" @selected(request('direction') === 'refund')>Refunds</option>
                        </select>
                    </div>
                    <div class="col-sm-2 d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                        @if (request()->hasAny(['from', 'to', 'method', 'direction']))
                            <a href="{{ route('payments.index') }}"
                                class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <hr class="my-3">

            <x-data-table :order="[[1, 'desc']]">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Date</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Received by</th>
                        <th class="text-end">Amount</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="fw-semibold">{{ $payment->receipt_number }}</td>
                            <td>{{ $payment->paid_on?->format('d M Y') }}</td>
                            <td>
                                @if ($payment->booking)
                                    <a href="{{ route('bookings.show', $payment->booking) }}"
                                        class="text-decoration-none">
                                        {{ $payment->booking->formatted_booking_number }}
                                    </a>
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>{{ $payment->booking->customer->name ?? '—' }}</td>
                            <td>{{ $payment->method_label }}</td>
                            <td class="text-secondary small">{{ $payment->reference ?: '—' }}</td>
                            <td>{{ $payment->receiver->name ?? '—' }}</td>
                            <td class="text-end">
                                @if ($payment->direction === 'refund')
                                    <span class="text-danger">-<x-money :amount="$payment->amount" /></span>
                                    <span class="badge text-bg-secondary ms-1">Refund</span>
                                @else
                                    <x-money :amount="$payment->amount" tone="success" />
                                @endif
                            </td>
                            <td class="text-end no-print">
                                <a href="{{ route('payments.receipt', $payment) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Print receipt">
                                    <i class="material-icons-outlined fs-6">print</i>
                                </a>
                                @can('delete-payments')
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Remove receipt {{ $payment->receipt_number }}? Booking balances will be recalculated.">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="9" icon="payments" title="No payments in this period"
                            message="Payments are recorded from a booking's page." />
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>
@endsection
