@extends('dashboard.includes.partial.base')

@section('title', 'Booking '.$booking->formatted_booking_number)

@section('content')
    <x-page-header :title="$booking->formatted_booking_number"
        :subtitle="($booking->event_type_label ?? 'Event').' · '.($booking->customer->name ?? 'Customer')"
        icon="event_available"
        :breadcrumbs="['Bookings' => route('bookings.index'), $booking->formatted_booking_number => null]">
        <x-slot:actions>
            <a href="{{ route('bookings.invoice', $booking) }}" target="_blank"
                class="btn btn-outline-secondary btn-sm">
                <i class="material-icons-outlined fs-6 align-middle">receipt_long</i> Invoice
            </a>
            @if ($whatsappUrl)
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">chat</i> WhatsApp
                </a>
            @endif
            @if ($gmailUrl)
                <a href="{{ $gmailUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">mail</i> Email
                </a>
            @endif
            @can('edit-bookings')
                @if ($booking->isEditable())
                    <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-primary btn-sm">
                        <i class="material-icons-outlined fs-6 align-middle">edit</i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                        data-bs-target="#cancelBookingModal">
                        <i class="material-icons-outlined fs-6 align-middle">event_busy</i> Cancel
                    </button>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($booking->isCancelled())
        <div class="alert alert-danger app-alert d-flex align-items-start gap-2">
            <i class="material-icons-outlined">event_busy</i>
            <div>
                <strong>Cancelled
                    {{ $booking->cancelled_at ? 'on '.$booking->cancelled_at->format('d M Y') : '' }}</strong>
                @if ($booking->canceller)
                    <span class="text-secondary">by {{ $booking->canceller->name }}</span>
                @endif
                <p class="mb-0 small">{{ $booking->cancellation_reason }}</p>
                @if ($booking->cancellation_charge > 0)
                    <p class="mb-0 small">Cancellation charge retained:
                        <strong><x-money :amount="$booking->cancellation_charge" /></strong>
                    </p>
                @endif
            </div>
        </div>
    @endif

    @if ($booking->customer?->is_blacklisted)
        <div class="alert alert-warning app-alert d-flex align-items-start gap-2">
            <i class="material-icons-outlined">block</i>
            <div>
                <strong>This customer is blacklisted.</strong>
                <p class="mb-0 small">{{ $booking->customer->blacklist_reason }}</p>
            </div>
        </div>
    @endif

    {{-- ────────────────────────────── Money strip ───────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Total bill" :value="'Rs. '.number_format($booking->total_amount)"
                icon="request_quote" tone="primary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Received" :value="'Rs. '.number_format($booking->amount_paid)" icon="payments"
                tone="success" :hint="$booking->payments->where('direction', 'in')->count().' receipt(s)'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Balance due" :value="'Rs. '.number_format(max($booking->balance_due, 0))"
                icon="account_balance_wallet" :tone="$booking->balance_due > 0 ? 'warning' : 'success'"
                :hint="$booking->is_fully_paid ? 'Fully settled' : 'Advance expected Rs. '.number_format($booking->required_advance)" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Event costs" :value="'Rs. '.number_format($booking->expenses->sum('amount'))"
                icon="receipt_long" tone="secondary"
                :hint="'Margin Rs. '.number_format($booking->amount_paid - $booking->expenses->sum('amount'))" />
        </div>
    </div>

    <div class="row g-3">
        {{-- ───────────────────────── Left: details ─────────────────────── --}}
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Event Details</span>
                    <div class="d-flex gap-1">
                        <x-status-badge :label="$booking->status_label" :tone="$booking->status_colour" />
                        <x-status-badge :label="$booking->payment_status_label"
                            :tone="$booking->payment_status_colour" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <p class="detail-item__label">Event Type</p>
                            <p class="detail-item__value">{{ $booking->event_type_label ?? '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Venue</p>
                            <p class="detail-item__value">
                                {{ $booking->hall->name ?? '—' }}
                                @if ($booking->lawn)<span class="text-secondary">·
                                        {{ $booking->lawn->name }}</span>@endif
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Starts</p>
                            <p class="detail-item__value">{{ $booking->start_datetime?->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Ends</p>
                            <p class="detail-item__value">{{ $booking->end_datetime?->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Guests</p>
                            <p class="detail-item__value">{{ number_format($booking->guest_count) }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Package</p>
                            <p class="detail-item__value">
                                @if ($booking->package)
                                    {{ $booking->package->name }}
                                @elseif ($booking->menu_amount > 0)
                                    Custom rate
                                @else
                                    <span class="text-secondary">Customer&rsquo;s own caterer</span>
                                @endif
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Created by</p>
                            <p class="detail-item__value">
                                {{ $booking->creator->name ?? '—' }}
                                <span
                                    class="text-secondary d-block small">{{ $booking->created_at?->format('d M Y, h:i A') }}</span>
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Last updated by</p>
                            <p class="detail-item__value">
                                {{ $booking->updater->name ?? '—' }}
                                <span
                                    class="text-secondary d-block small">{{ $booking->updated_at?->format('d M Y, h:i A') }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($booking->notes)
                        <div class="mt-3 pt-3 border-top">
                            <p class="detail-item__label">Notes</p>
                            <p class="mb-0">{{ $booking->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bill breakdown --}}
            <div class="card mb-3">
                <div class="card-header">Bill Breakdown</div>
                <div class="card-body">
                    @if ($booking->hall_rent > 0)
                        <div class="totals-row">
                            <span class="totals-row__label">Hall / lawn rent</span>
                            <span><x-money :amount="$booking->hall_rent" /></span>
                        </div>
                    @endif

                    {{-- Only shown when the hall is providing the food. --}}
                    @if ($booking->menu_amount > 0)
                        <div class="totals-row">
                            <span class="totals-row__label">
                                Catering
                                <small class="text-secondary">({{ number_format($booking->guest_count) }} ×
                                    Rs. {{ number_format($booking->per_head_rate) }})</small>
                            </span>
                            <span><x-money :amount="$booking->menu_amount" /></span>
                        </div>
                    @endif

                    @foreach ($booking->addons as $addon)
                        <div class="totals-row">
                            <span class="totals-row__label ps-3">
                                {{ $addon->name }}
                                <small class="text-secondary">
                                    ({{ $addon->pivot->quantity }} ×
                                    Rs. {{ number_format($addon->pivot->unit_price) }}{{ $addon->pricing_mode === 'per_head' ? '/head' : '' }})
                                </small>
                            </span>
                            <span><x-money :amount="$addon->pivot->line_total" /></span>
                        </div>
                    @endforeach


                    @if ($booking->discount > 0)
                        <div class="totals-row">
                            <span class="totals-row__label">Discount</span>
                            <span class="text-danger">-<x-money :amount="$booking->discount" /></span>
                        </div>
                    @endif

                    @if ($booking->tax_amount > 0)
                        <div class="totals-row">
                            <span class="totals-row__label">Tax / GST
                                ({{ rtrim(rtrim(number_format($booking->tax_percent, 2), '0'), '.') }}%)</span>
                            <span><x-money :amount="$booking->tax_amount" /></span>
                        </div>
                    @endif

                    <div class="totals-row totals-row--grand">
                        <span class="totals-row__label">Total payable</span>
                        <span><x-money :amount="$booking->total_amount" /></span>
                    </div>
                </div>
            </div>

            {{-- Payment ledger --}}
            @can('view-payments')
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Payment Ledger</span>
                        @can('create-payments')
                            @if (! $booking->isCancelled() && $booking->balance_due > 0)
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addPaymentModal">
                                    <i class="material-icons-outlined fs-6 align-middle">add</i> Record Payment
                                </button>
                            @endif
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <x-data-table :searchable="false" :sortable="false">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Received by</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($booking->payments->sortBy('paid_on') as $payment)
                                    <tr>
                                        <td class="fw-semibold">{{ $payment->receipt_number }}</td>
                                        <td>{{ $payment->paid_on?->format('d M Y') }}</td>
                                        <td>{{ $payment->method_label }}</td>
                                        <td class="text-secondary small">{{ $payment->reference ?: '—' }}</td>
                                        <td>{{ $payment->receiver->name ?? '—' }}</td>
                                        <td class="text-end">
                                            @if ($payment->direction === 'refund')
                                                <span class="text-danger">
                                                    -<x-money :amount="$payment->amount" />
                                                    <span class="badge text-bg-secondary ms-1">Refund</span>
                                                </span>
                                            @else
                                                <x-money :amount="$payment->amount" tone="success" />
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Print receipt">
                                                <i class="material-icons-outlined fs-6">print</i>
                                            </a>
                                            @can('delete-payments')
                                                <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        data-confirm="Remove receipt {{ $payment->receipt_number }}? Balances will be recalculated."
                                                        title="Delete">
                                                        <i class="material-icons-outlined fs-6">delete</i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state :colspan="7" icon="payments" title="No payments recorded"
                                        message="Record the booking advance to start the ledger." />
                                @endforelse
                            </tbody>
                            @if ($booking->payments->isNotEmpty())
                                <tfoot>
                                    <tr class="fw-semibold">
                                        <td colspan="5" class="text-end">Net received</td>
                                        <td class="text-end"><x-money :amount="$booking->amount_paid" /></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </x-data-table>
                    </div>
                </div>
            @endcan

            {{-- Event costs --}}
            @can('view-expenses')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Event Costs</span>
                        @can('create-expenses')
                            <a href="{{ route('expenses.create', ['booking_id' => $booking->id]) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="material-icons-outlined fs-6 align-middle">add</i> Add Expense
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <x-data-table :searchable="false" :sortable="false">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($booking->expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->title }}</td>
                                        <td class="text-secondary">{{ $expense->category->name ?? '—' }}</td>
                                        <td>{{ $expense->spent_on?->format('d M Y') }}</td>
                                        <td class="text-end"><x-money :amount="$expense->amount" /></td>
                                    </tr>
                                @empty
                                    <x-empty-state :colspan="4" icon="receipt_long" title="No costs recorded"
                                        message="Link catering, decor or staff costs to see the margin on this event." />
                                @endforelse
                            </tbody>
                        </x-data-table>
                    </div>
                </div>
            @endcan
        </div>

        {{-- ───────────────────────── Right: customer ───────────────────── --}}
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">Customer</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar-initial"
                            style="width:48px;height:48px;font-size:1.1rem">{{ mb_substr($booking->customer->name ?? '?', 0, 1) }}</span>
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-truncate">{{ $booking->customer->name ?? '—' }}</p>
                            @can('view-customers')
                                <a href="{{ route('customers.show', $booking->customer) }}"
                                    class="small text-decoration-none">View history</a>
                            @endcan
                        </div>
                    </div>

                    <div class="detail-item mb-2">
                        <p class="detail-item__label">CNIC</p>
                        <p class="detail-item__value">{{ $booking->customer->formatted_cnic ?? '—' }}</p>
                    </div>
                    <div class="detail-item mb-2">
                        <p class="detail-item__label">Mobile</p>
                        <p class="detail-item__value">
                            @if ($booking->customer?->phone)
                                <a href="tel:{{ $booking->customer->phone }}"
                                    class="text-decoration-none">{{ $booking->customer->phone }}</a>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    @if ($booking->customer?->secondary_phone)
                        <div class="detail-item mb-2">
                            <p class="detail-item__label">Alternate</p>
                            <p class="detail-item__value">{{ $booking->customer->secondary_phone }}</p>
                        </div>
                    @endif
                    <div class="detail-item mb-2">
                        <p class="detail-item__label">Email</p>
                        <p class="detail-item__value">{{ $booking->customer->email ?: '—' }}</p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-item__label">Address</p>
                        <p class="detail-item__value">{{ $booking->customer->address ?: '—' }}</p>
                    </div>
                </div>
            </div>

            @if ($booking->package && $booking->package->items)
                <div class="card mb-3">
                    <div class="card-header">{{ $booking->package->name }}</div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($booking->package->items as $item)
                                <li class="d-flex align-items-center gap-2 mb-1">
                                    <i class="material-icons-outlined fs-6 text-success">check</i>{{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if ($booking->staff->isNotEmpty())
                <div class="card">
                    <div class="card-header">Assigned Staff</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($booking->staff as $member)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $member->name }}
                                        <small
                                            class="text-secondary d-block">{{ $member->pivot->role ?? $member->designation }}</small></span>
                                    @if ($member->pivot->wage > 0)
                                        <x-money :amount="$member->pivot->wage" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ─────────────────────────── Payment modal ───────────────────────── --}}
    @can('create-payments')
        @if (! $booking->isCancelled())
            <div class="modal fade" id="addPaymentModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('payments.store') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Record Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info app-alert py-2 small d-flex gap-2">
                                <i class="material-icons-outlined fs-6">info</i>
                                <span>Outstanding balance:
                                    <strong>Rs. {{ number_format(max($booking->balance_due, 0), 2) }}</strong></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Amount <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" step="0.01" min="0.01" name="amount"
                                            class="form-control" required
                                            value="{{ max($booking->balance_due, 0) > 0 ? number_format(max($booking->balance_due, 0), 2, '.', '') : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="required-mark">*</span></label>
                                    <input type="date" name="paid_on" class="form-control"
                                        value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Method <span class="required-mark">*</span></label>
                                    <select name="method" class="form-select" required>
                                        @foreach (\App\Models\Payment::METHODS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type <span class="required-mark">*</span></label>
                                    <select name="direction" class="form-select" required>
                                        <option value="in">Payment received</option>
                                        <option value="refund">Refund to customer</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control"
                                        placeholder="Cheque no. / transaction id">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
                                Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    {{-- ────────────────────────── Cancellation modal ───────────────────── --}}
    @can('edit-bookings')
        @if ($booking->isEditable())
            <div class="modal fade" id="cancelBookingModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('bookings.cancel', $booking) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Cancel Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-secondary">
                                Cancelling frees the lawn for other bookings and keeps the record for your
                                audit trail. It does not delete anything.
                            </p>
                            <div class="mb-3">
                                <label class="form-label">Reason <span class="required-mark">*</span></label>
                                <textarea name="cancellation_reason" rows="3" class="form-control" required
                                    minlength="5" placeholder="Why is this booking being cancelled?"></textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Cancellation charge to retain</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="cancellation_charge"
                                        class="form-control"
                                        placeholder="{{ number_format($booking->total_amount * ($booking->hall->cancellation_charge_percent ?? 0) / 100, 2, '.', '') }}">
                                </div>
                                <div class="form-text">
                                    Leave blank to apply the hall policy
                                    ({{ $booking->hall->cancellation_charge_percent ?? 0 }}% of the bill).
                                    Capped at the amount already received
                                    (Rs. {{ number_format($booking->amount_paid, 2) }}).
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep
                                Booking</button>
                            <button type="submit" class="btn btn-danger" data-loading-text="Cancelling…">
                                Cancel Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan
@endsection
