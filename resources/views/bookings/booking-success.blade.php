@extends('dashboard.includes.partial.base')

@section('title', 'Booking Confirmed')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card text-center mb-4">
                <div class="card-body py-5">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width:72px;height:72px;background:var(--bs-success-bg-subtle)">
                        <i class="material-icons-outlined text-success" style="font-size:2.4rem">check_circle</i>
                    </span>
                    <h4 class="fw-semibold mb-1">Booking Saved</h4>
                    <p class="text-secondary mb-3">
                        Booking <strong>{{ $booking->formatted_booking_number }}</strong> has been created for
                        {{ $booking->customer->name ?? 'the customer' }}.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary">
                            <i class="material-icons-outlined fs-6 align-middle">visibility</i> View Booking
                        </a>
                        <a href="{{ route('bookings.invoice', $booking) }}" target="_blank"
                            class="btn btn-outline-secondary">
                            <i class="material-icons-outlined fs-6 align-middle">receipt_long</i> Invoice
                        </a>
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                                class="btn btn-outline-success">
                                <i class="material-icons-outlined fs-6 align-middle">chat</i> Send on WhatsApp
                            </a>
                        @endif
                        @if ($gmailUrl)
                            <a href="{{ $gmailUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                                <i class="material-icons-outlined fs-6 align-middle">mail</i> Email Invoice
                            </a>
                        @endif
                    </div>

                    {{-- Share links need contact details on the customer record. --}}
                    @if (! $whatsappUrl || ! $gmailUrl)
                        <p class="text-secondary small mt-3 mb-0">
                            @if (! $whatsappUrl && ! $gmailUrl)
                                Add a phone number or email to the customer to share the invoice directly.
                            @elseif (! $whatsappUrl)
                                Add a mobile number to share on WhatsApp.
                            @else
                                Add an email address to send the invoice by email.
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">Summary</div>
                <div class="card-body">
                    <div class="detail-grid mb-3">
                        <div class="detail-item">
                            <p class="detail-item__label">Event</p>
                            <p class="detail-item__value">{{ $booking->event_type_label ?? '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Venue</p>
                            <p class="detail-item__value">
                                {{ $booking->hall->name ?? '' }} · {{ $booking->lawn->name ?? '' }}
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Date &amp; Time</p>
                            <p class="detail-item__value">
                                {{ $booking->start_datetime?->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Guests</p>
                            <p class="detail-item__value">{{ number_format($booking->guest_count) }}</p>
                        </div>
                    </div>

                    <div class="totals-row">
                        <span class="totals-row__label">Total bill</span>
                        <span><x-money :amount="$booking->total_amount" /></span>
                    </div>
                    <div class="totals-row">
                        <span class="totals-row__label">Advance expected
                            ({{ $booking->hall->advance_policy_percent ?? 0 }}%)</span>
                        <span><x-money :amount="$booking->required_advance" /></span>
                    </div>
                    @if ($booking->amount_paid > 0)
                        <div class="totals-row">
                            <span class="totals-row__label">Received</span>
                            <span class="text-success"><x-money :amount="$booking->amount_paid" /></span>
                        </div>
                    @endif

                    <div class="totals-row totals-row--grand">
                        <span class="totals-row__label">Balance due</span>
                        <span><x-money :amount="$booking->balance_due" /></span>
                    </div>

                    @can('create-payments')
                        @php $receipt = $booking->payments->firstWhere('direction', 'in'); @endphp

                        @if ($receipt)
                            <div class="alert alert-success app-alert mt-3 mb-0 py-2 small d-flex gap-2">
                                <i class="material-icons-outlined fs-6">check_circle</i>
                                <span class="flex-grow-1">
                                    Advance of <strong>Rs. {{ number_format($receipt->amount, 2) }}</strong>
                                    recorded &mdash; receipt {{ $receipt->receipt_number }}.
                                </span>
                                <a href="{{ route('payments.receipt', $receipt) }}" target="_blank"
                                    class="text-nowrap">Print receipt</a>
                            </div>
                        @else
                            <div class="alert alert-info app-alert mt-3 mb-0 py-2 small d-flex gap-2">
                                <i class="material-icons-outlined fs-6">info</i>
                                <span>No payment recorded yet. Open the booking to add a receipt.</span>
                            </div>
                        @endif
                    @endcan
                </div>
                <div class="card-footer d-flex flex-wrap gap-2 justify-content-between">
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="material-icons-outlined fs-6 align-middle">list</i> All Bookings
                    </a>
                    @can('create-bookings')
                        <a href="{{ route('bookings.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="material-icons-outlined fs-6 align-middle">add</i> Another Booking
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
