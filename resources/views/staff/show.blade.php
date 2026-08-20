@extends('dashboard.includes.partial.base')

@section('title', $member->name)

@section('content')
    <x-page-header :title="$member->name" :subtitle="$member->designation ?: 'Staff member'" icon="badge"
        :breadcrumbs="['Staff' => route('staff.index'), $member->name => null]">
        <x-slot:actions>
            @can('edit-staff')
                <a href="{{ route('staff.edit', $member) }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">edit</i> Edit
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar-initial mx-auto mb-3"
                        style="width:72px;height:72px;font-size:1.8rem">{{ mb_substr($member->name, 0, 1) }}</span>
                    <h5 class="mb-1">{{ $member->name }}</h5>
                    <p class="text-secondary mb-2">{{ $member->designation ?: '—' }}</p>
                    <x-status-badge :label="$member->is_active ? 'Currently employed' : 'No longer employed'"
                        :tone="$member->is_active ? 'success' : 'secondary'" />
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Hall</p>
                        <p class="detail-item__value">{{ $member->hall->name ?? '—' }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Mobile</p>
                        <p class="detail-item__value">
                            @if ($member->phone)
                                <a href="tel:{{ $member->phone }}"
                                    class="text-decoration-none">{{ $member->phone }}</a>
                            @else — @endif
                        </p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">CNIC</p>
                        <p class="detail-item__value">{{ $member->cnic ?: '—' }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Employment</p>
                        <p class="detail-item__value">{{ $member->employment_type_label }}</p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Monthly Salary</p>
                        <p class="detail-item__value">
                            <x-money :amount="$member->monthly_salary" zero="Daily wage" />
                        </p>
                    </div>
                    <div class="detail-item mb-3">
                        <p class="detail-item__label">Joined</p>
                        <p class="detail-item__value">
                            {{ $member->joined_on?->format('d M Y') ?? '—' }}
                            @if ($member->joined_on)
                                <span
                                    class="text-secondary d-block small">{{ $member->joined_on->diffForHumans(null, true) }}
                                    of service</span>
                            @endif
                        </p>
                    </div>
                    <div class="detail-item mb-0">
                        <p class="detail-item__label">Address</p>
                        <p class="detail-item__value">{{ $member->address ?: '—' }}</p>
                    </div>

                    @if ($member->notes)
                        <div class="mt-3 pt-3 border-top">
                            <p class="detail-item__label">Notes</p>
                            <p class="mb-0 text-secondary">{{ $member->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">Event Assignments</div>
                <div class="card-body p-0">
                    <x-data-table :searchable="false" :sortable="false">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Space</th>
                                <th>Date</th>
                                <th>Role</th>
                                <th class="text-end">Wage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignments as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('bookings.show', $booking) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $booking->formatted_booking_number }}
                                        </a>
                                    </td>
                                    <td>{{ $booking->customer->name ?? '—' }}</td>
                                    <td>{{ $booking->lawn->name ?? '—' }}</td>
                                    <td>{{ $booking->start_datetime?->format('d M Y') }}</td>
                                    <td>{{ $booking->pivot->role ?: '—' }}</td>
                                    <td class="text-end"><x-money :amount="$booking->pivot->wage" zero="—" /></td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" icon="event_note" title="No assignments yet"
                                    message="Events this staff member is rostered onto appear here." />
                            @endforelse
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>
@endsection
