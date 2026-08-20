@extends('dashboard.includes.partial.base')

@section('title', 'Vendors')

@section('content')
    <x-page-header title="Vendors" subtitle="Caterers, decorators and other suppliers" icon="local_shipping"
        :breadcrumbs="['Vendors' => null]">
        <x-slot:actions>
            @can('create-vendors')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Vendor
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Service</th>
                        @if (auth()->user()->isSuperAdmin())
                            <th>Hall</th>
                        @endif
                        <th>Contact</th>
                        <th class="text-end">Total paid</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $vendor->name }}</div>
                                @if ($vendor->company && $vendor->company !== $vendor->name)
                                    <small class="text-secondary">{{ $vendor->company }}</small>
                                @endif
                            </td>
                            <td>{{ $vendor->service_type ?: '—' }}</td>
                            @if (auth()->user()->isSuperAdmin())
                                <td class="text-secondary">{{ $vendor->hall->name ?? '—' }}</td>
                            @endif
                            <td>
                                @if ($vendor->phone)
                                    <a href="tel:{{ $vendor->phone }}"
                                        class="text-decoration-none">{{ $vendor->phone }}</a>
                                @else — @endif
                                @if ($vendor->email)
                                    <small class="text-secondary d-block">{{ $vendor->email }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <x-money :amount="$vendor->expenses_sum_amount ?? 0" zero="—" />
                            </td>
                            <td>
                                <x-status-badge :label="$vendor->is_active ? 'Active' : 'Inactive'"
                                    :tone="$vendor->is_active ? 'success' : 'secondary'" />
                            </td>
                            <td class="text-end">
                                @can('edit-vendors')
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#editVendor{{ $vendor->id }}" title="Edit">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </button>
                                @endcan
                                @can('delete-vendors')
                                    <form action="{{ route('vendors.destroy', $vendor) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Remove"
                                            data-confirm="Remove {{ $vendor->name }}? Past expense records are kept.">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="auth()->user()->isSuperAdmin() ? 7 : 6" icon="local_shipping"
                            title="No vendors yet"
                            message="Add your suppliers so expenses can be attributed to them.">
                            @can('create-vendors')
                                <x-slot:action>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addVendorModal">
                                        <i class="material-icons-outlined fs-6 align-middle">add</i> Add Vendor
                                    </button>
                                </x-slot:action>
                            @endcan
                        </x-empty-state>
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>

    @can('create-vendors')
        <div class="modal fade" id="addVendorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" method="POST" action="{{ route('vendors.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('vendors._fields', ['vendor' => null, 'halls' => $halls])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Saving…">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @can('edit-vendors')
        @foreach ($vendors as $vendor)
            <div class="modal fade" id="editVendor{{ $vendor->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('vendors.update', $vendor) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit {{ $vendor->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('vendors._fields', ['vendor' => $vendor, 'halls' => $halls])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" data-loading-text="Updating…">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
@endsection
