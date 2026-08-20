@extends('dashboard.includes.partial.base')

@section('title', 'Menus & Packages')

@section('content')
    <x-page-header title="Menus &amp; Packages" subtitle="Per-head catering rates offered at your venue"
        icon="restaurant_menu" :breadcrumbs="['Packages' => null]">
        <x-slot:actions>
            @can('create-packages')
                <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Package
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Type</th>
                        @if (auth()->user()->isSuperAdmin())
                            <th>Hall</th>
                        @endif
                        <th class="text-end">Per-head rate</th>
                        <th class="text-end">Min guests</th>
                        <th class="text-end">Used by</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $package->name }}</div>
                                @if ($package->items)
                                    <small class="text-secondary">
                                        {{ collect($package->items)->take(4)->join(', ') }}
                                        @if (count($package->items) > 4)
                                            +{{ count($package->items) - 4 }} more
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>{{ $package->type_label }}</td>
                            @if (auth()->user()->isSuperAdmin())
                                <td class="text-secondary">{{ $package->hall->name ?? '—' }}</td>
                            @endif
                            <td class="text-end"><x-money :amount="$package->per_head_rate" /></td>
                            <td class="text-end tabular">
                                {{ $package->min_guests ? number_format($package->min_guests) : '—' }}</td>
                            <td class="text-end tabular">{{ number_format($package->bookings_count) }}</td>
                            <td>
                                <x-status-badge :label="$package->is_active ? 'Active' : 'Inactive'"
                                    :tone="$package->is_active ? 'success' : 'secondary'" />
                            </td>
                            <td class="text-end">
                                @can('edit-packages')
                                    <a href="{{ route('packages.edit', $package) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </a>
                                @endcan
                                @can('delete-packages')
                                    <form action="{{ route('packages.destroy', $package) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Delete {{ $package->name }}? Existing bookings keep their agreed price.">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="auth()->user()->isSuperAdmin() ? 8 : 7" icon="restaurant_menu"
                            title="No packages yet"
                            message="Add your menus so bookings can be quoted per head instead of by hand.">
                            @can('create-packages')
                                <x-slot:action>
                                    <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm">
                                        <i class="material-icons-outlined fs-6 align-middle">add</i> Add Package
                                    </a>
                                </x-slot:action>
                            @endcan
                        </x-empty-state>
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>
@endsection
