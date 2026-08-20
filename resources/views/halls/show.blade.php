@extends('dashboard.includes.partial.base')

@section('title', $hall->name)

@section('content')
    <x-page-header :title="$hall->name" :subtitle="$hall->hall_type_label ? $hall->hall_type_label.' · '.$hall->full_address : $hall->full_address"
        icon="festival" :breadcrumbs="['Halls' => route('halls.index'), $hall->name => null]">
        <x-slot:actions>
            @can('update', $hall)
                <a href="{{ route('halls.edit', $hall) }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">edit</i> Edit
                </a>
            @endcan
            @can('delete', $hall)
                <form action="{{ route('halls.destroy', $hall) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"
                        data-confirm="Delete {{ $hall->name }}? Its lawns, bookings and ledger go with it.">
                        <i class="material-icons-outlined fs-6 align-middle">delete</i> Delete
                    </button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Bookable spaces" :value="number_format($hall->lawns_count)" icon="grid_view"
                tone="primary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Total capacity" :value="number_format($hall->hall_capacity)" icon="event_seat"
                tone="secondary" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Bookings" :value="number_format($hall->bookings_count)" icon="event_available"
                tone="success" />
        </div>
        <div class="col-xl-3 col-sm-6">
            <x-stat-card label="Team members" :value="number_format($hall->users->count())" icon="people"
                tone="secondary" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            {{-- Venue info --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Venue Information</span>
                    <x-status-badge :label="$hall->status ? 'Active' : 'Inactive'"
                        :tone="$hall->status ? 'success' : 'danger'" />
                </div>
                <div class="card-body">
                    @if ($hall->logo)
                        <img src="{{ asset($hall->logo) }}" alt="{{ $hall->name }}" class="mb-3 rounded"
                            style="max-height:80px">
                    @endif

                    <div class="detail-grid">
                        <div class="detail-item">
                            <p class="detail-item__label">Owner</p>
                            <p class="detail-item__value">{{ $hall->owner_name ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Phone</p>
                            <p class="detail-item__value">
                                @if ($hall->phone)
                                    <a href="tel:{{ $hall->phone }}"
                                        class="text-decoration-none">{{ $hall->phone }}</a>
                                @else — @endif
                            </p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Email</p>
                            <p class="detail-item__value">{{ $hall->email ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Venue Type</p>
                            <p class="detail-item__value">{{ $hall->hall_type_label ?? '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Area</p>
                            <p class="detail-item__value">{{ $hall->area ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Established</p>
                            <p class="detail-item__value">{{ $hall->established_at_formatted }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Registration #</p>
                            <p class="detail-item__value">{{ $hall->registration_number ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">NTN</p>
                            <p class="detail-item__value">{{ $hall->ntn_number ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">GST Number</p>
                            <p class="detail-item__value">{{ $hall->gst_number ?: '—' }}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-item__label">Full Address</p>
                            <p class="detail-item__value">{{ $hall->full_address }}</p>
                        </div>
                    </div>

                    @if ($hall->description)
                        <div class="mt-3 pt-3 border-top">
                            <p class="detail-item__label">Description</p>
                            <p class="mb-0">{{ $hall->description }}</p>
                        </div>
                    @endif

                    @if ($hall->notes)
                        <div class="mt-3 pt-3 border-top">
                            <p class="detail-item__label">Internal Notes</p>
                            <p class="mb-0 text-secondary">{{ $hall->notes }}</p>
                        </div>
                    @endif

                    <div class="mt-3 pt-3 border-top d-flex flex-wrap gap-4 small text-secondary">
                        <span>Created by <strong>{{ $hall->creator_name ?? '—' }}</strong> on
                            {{ $hall->created_at_formatted }}</span>
                        <span>Last updated by <strong>{{ $hall->editor_name ?? '—' }}</strong> on
                            {{ $hall->updated_at_formatted }}</span>
                    </div>
                </div>
            </div>

            {{-- Bookable spaces --}}
            <div class="card mb-3">
                <div class="card-header">Bookable Spaces ({{ $hall->lawns->count() }})</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :sortable="false">
                        <thead>
                            <tr>
                                <th>Space</th>
                                <th class="text-end">Seats</th>
                                <th class="text-end">Upcoming bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hall->lawns as $lawn)
                                <tr>
                                    <td class="fw-medium">{{ $lawn->name }}</td>
                                    <td class="text-end tabular">
                                        {{ $lawn->capacity ? number_format($lawn->capacity) : '—' }}</td>
                                    <td class="text-end tabular">
                                        {{ $lawn->bookings()->active()->where('start_datetime', '>=', now())->count() }}
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="3" icon="grid_view" title="No spaces configured"
                                    message="A hall needs at least one lawn or space before it can be booked.">
                                    @can('update', $hall)
                                        <x-slot:action>
                                            <a href="{{ route('halls.edit', $hall) }}"
                                                class="btn btn-sm btn-primary">Add spaces</a>
                                        </x-slot:action>
                                    @endcan
                                </x-empty-state>
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>

            {{-- Upcoming events --}}
            <div class="card">
                <div class="card-header">Upcoming Events</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :sortable="false">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Space</th>
                                <th>Date</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcoming as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('bookings.show', $booking) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $booking->formatted_booking_number }}
                                        </a>
                                    </td>
                                    <td>{{ $booking->customer->name ?? '—' }}</td>
                                    <td>{{ $booking->lawn->name ?? '—' }}</td>
                                    <td>{{ $booking->start_datetime?->format('d M Y, h:i A') }}</td>
                                    <td class="text-end"><x-money :amount="$booking->total_amount" /></td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="5" icon="event_note" title="No upcoming events"
                                    message="Nothing is scheduled at this venue yet." />
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">Commercial Settings</div>
                <div class="card-body">
                    <div class="totals-row">
                        <span class="totals-row__label">Default per-head rate</span>
                        <span><x-money :amount="$hall->default_per_head_rate" zero="Not set" /></span>
                    </div>
                    <div class="totals-row">
                        <span class="totals-row__label">Advance required</span>
                        <span>{{ $hall->advance_policy_percent }}%</span>
                    </div>
                    <div class="totals-row">
                        <span class="totals-row__label">Cancellation charge</span>
                        <span>{{ $hall->cancellation_charge_percent }}%</span>
                    </div>
                    <div class="totals-row">
                        <span class="totals-row__label">Tax / GST</span>
                        <span>{{ rtrim(rtrim(number_format($hall->tax_percent, 2), '0'), '.') }}%</span>
                    </div>
                </div>
            </div>

            @can('view-packages')
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Packages ({{ $hall->packages->count() }})</span>
                        <a href="{{ route('packages.index') }}"
                            class="btn btn-sm btn-outline-secondary">Manage</a>
                    </div>
                    <div class="card-body p-0">
                        @if ($hall->packages->isEmpty())
                            <x-empty-state icon="restaurant_menu" title="No packages"
                                message="Add menus so bookings can be quoted per head." />
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($hall->packages as $package)
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                        <span class="min-w-0">
                                            <span class="d-block text-truncate">{{ $package->name }}</span>
                                            <small class="text-secondary">{{ $package->type_label }}</small>
                                        </span>
                                        <span class="text-nowrap">
                                            <x-money :amount="$package->per_head_rate" /><small
                                                class="text-secondary">/head</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endcan

            <div class="card">
                <div class="card-header">Team ({{ $hall->users->count() }})</div>
                <div class="card-body p-0">
                    @if ($hall->users->isEmpty())
                        <x-empty-state icon="people" title="No users assigned"
                            message="Assign staff accounts to this hall." />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($hall->users as $user)
                                <li class="list-group-item d-flex align-items-center gap-2">
                                    <span class="avatar-initial">{{ mb_substr($user->name, 0, 1) }}</span>
                                    <span class="flex-grow-1 min-w-0">
                                        @can('view-users')
                                            <a href="{{ route('users.show', $user) }}"
                                                class="d-block text-truncate text-decoration-none">{{ $user->name }}</a>
                                        @else
                                            <span class="d-block text-truncate">{{ $user->name }}</span>
                                        @endcan
                                        <small
                                            class="text-secondary">{{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</small>
                                    </span>
                                    <x-status-badge :label="$user->is_active ? 'Active' : 'Inactive'"
                                        :tone="$user->is_active ? 'success' : 'secondary'" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
