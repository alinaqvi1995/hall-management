@extends('dashboard.includes.partial.base')

@section('title', $customer->name)

@section('content')
    <x-page-header :title="$customer->name" :subtitle="$customer->formatted_cnic ? 'CNIC '.$customer->formatted_cnic : 'Customer'"
        icon="person" :breadcrumbs="['Customers' => route('customers.index'), $customer->name => null]">
        <x-slot:actions>
            @can('edit-customers')
                <button class="btn btn-sm {{ $customer->is_blacklisted ? 'btn-outline-success' : 'btn-outline-danger' }}"
                    data-bs-toggle="modal" data-bs-target="#blacklistModal">
                    <i class="material-icons-outlined fs-6 align-middle">
                        {{ $customer->is_blacklisted ? 'check_circle' : 'block' }}
                    </i>
                    {{ $customer->is_blacklisted ? 'Remove from Blacklist' : 'Blacklist' }}
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($customer->is_blacklisted)
        <div class="alert alert-danger app-alert d-flex align-items-start gap-2">
            <i class="material-icons-outlined">block</i>
            <div>
                <strong>This customer is blacklisted.</strong>
                <p class="mb-0 small">{{ $customer->blacklist_reason ?: 'No reason recorded.' }}</p>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Total bookings" :value="number_format($bookings->count())" icon="event_available"
                tone="primary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Lifetime value" :value="'Rs. '.number_format($lifetimeValue)" icon="paid"
                tone="success" hint="Excludes cancelled" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Outstanding" :value="'Rs. '.number_format($outstanding)"
                icon="account_balance_wallet" :tone="$outstanding > 0 ? 'warning' : 'secondary'" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Cancelled" :value="number_format($bookings->where('status', 'cancelled')->count())"
                icon="event_busy" tone="secondary" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">Contact Details</div>
                <div class="card-body">
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Name</p>
                        <p class="detail-item__value">{{ $customer->name }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">CNIC</p>
                        <p class="detail-item__value tabular">{{ $customer->formatted_cnic ?: '—' }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Mobile</p>
                        <p class="detail-item__value">
                            @if ($customer->phone)
                                <a href="tel:{{ $customer->phone }}"
                                    class="text-decoration-none">{{ $customer->phone }}</a>
                            @else — @endif
                        </p>
                    </div>
                    @if ($customer->secondary_phone)
                        <div class="detail-item mb-3">
                            <p class="detail-item__label">Alternate Mobile</p>
                            <p class="detail-item__value">{{ $customer->secondary_phone }}</p>
                        </div>
                    @endif
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Email</p>
                        <p class="detail-item__value">{{ $customer->email ?: '—' }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Address</p>
                        <p class="detail-item__value">{{ $customer->address ?: '—' }}</p>
                    </div>
                    <div class="detail-item mb-0">
                        <p class="detail-item__label">First booked</p>
                        <p class="detail-item__value">{{ $customer->created_at?->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">Booking History</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :order="[[2, 'desc']]">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Venue</th>
                                <th>Event date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="{{ $booking->isCancelled() ? 'opacity-75' : '' }}">
                                    <td>
                                        <a href="{{ route('bookings.show', $booking) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $booking->formatted_booking_number }}
                                        </a>
                                        <small
                                            class="d-block text-secondary">{{ $booking->event_type_label ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $booking->lawn->name ?? '—' }}</div>
                                        <small class="text-secondary">{{ $booking->hall->name ?? '' }}</small>
                                    </td>
                                    <td>{{ $booking->start_datetime?->format('d M Y') }}</td>
                                    <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                                    <td class="text-end"><x-money :amount="$booking->amount_paid" /></td>
                                    <td class="text-end">
                                        @if ($booking->balance_due > 0)
                                            <x-money :amount="$booking->balance_due" tone="danger" />
                                        @else
                                            <span class="text-success small fw-semibold">Settled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-status-badge :label="$booking->status_label"
                                            :tone="$booking->status_colour" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" icon="event_note" title="No bookings"
                                    message="This customer has no bookings at your venue." />
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────── Blacklist modal ────────────────────── --}}
    @can('edit-customers')
        <div class="modal fade" id="blacklistModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('customers.blacklist', $customer) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $customer->is_blacklisted ? 'Remove from Blacklist' : 'Blacklist Customer' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($customer->is_blacklisted)
                            <p class="mb-0">
                                Restore {{ $customer->name }} to good standing? Staff will no longer be warned
                                when taking a booking from them.
                            </p>
                        @else
                            <p class="small text-secondary">
                                Staff are warned about blacklisted customers when a booking is being created.
                                Existing bookings are not affected.
                            </p>
                            <label class="form-label">Reason <span class="required-mark">*</span></label>
                            <textarea name="blacklist_reason" rows="3" class="form-control" required
                                placeholder="e.g. Repeated non-payment, damage to property"></textarea>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn {{ $customer->is_blacklisted ? 'btn-success' : 'btn-danger' }}"
                            data-loading-text="Saving…">
                            {{ $customer->is_blacklisted ? 'Remove from Blacklist' : 'Blacklist Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
