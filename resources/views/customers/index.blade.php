@extends('dashboard.includes.partial.base')

@section('title', 'Customers')

@section('content')
    <x-page-header title="Customers" subtitle="Everyone who has booked with you" icon="groups"
        :breadcrumbs="['Customers' => null]" />

    <div class="card">
        <div class="card-body p-0">
            <div class="p-3 pb-0">
                <form method="GET" class="row g-2 align-items-end" data-no-guard>
                    <div class="col-sm-5">
                        <label class="form-label">Search</label>
                        <input type="search" name="q" value="{{ request('q') }}"
                            class="form-control form-control-sm" placeholder="Name, mobile or CNIC">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">Show</label>
                        <select name="filter" class="form-select form-select-sm">
                            <option value="">All customers</option>
                            <option value="blacklisted" @selected(request('filter') === 'blacklisted')>Blacklisted only
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-4 d-flex gap-2">
                        <button class="btn btn-sm btn-primary">Search</button>
                        @if (request()->hasAny(['q', 'filter']))
                            <a href="{{ route('customers.index') }}"
                                class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <hr class="my-3">

            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>CNIC</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th class="text-end">Bookings</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-initial">{{ mb_substr($customer->name, 0, 1) }}</span>
                                    <a href="{{ route('customers.show', $customer) }}"
                                        class="fw-semibold text-decoration-none">{{ $customer->name }}</a>
                                </div>
                            </td>
                            <td class="tabular">{{ $customer->formatted_cnic ?: '—' }}</td>
                            <td>
                                @if ($customer->phone)
                                    <a href="tel:{{ $customer->phone }}"
                                        class="text-decoration-none">{{ $customer->phone }}</a>
                                @else — @endif
                            </td>
                            <td class="text-secondary">{{ $customer->email ?: '—' }}</td>
                            <td class="text-end tabular">{{ number_format($customer->bookings_count) }}</td>
                            <td>
                                @if ($customer->is_blacklisted)
                                    <x-status-badge label="Blacklisted" tone="danger" icon="block" />
                                @else
                                    <x-status-badge label="Good standing" tone="success" />
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('customers.show', $customer) }}"
                                    class="btn btn-sm btn-outline-secondary" title="View history">
                                    <i class="material-icons-outlined fs-6">visibility</i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="7" icon="groups" title="No customers found"
                            message="Customers are created automatically when you take a booking." />
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>
@endsection
