@extends('dashboard.includes.partial.base')

@section('title', 'Extra Services')

@section('content')
    <x-page-header title="Extra Services" subtitle="Priced add-ons offered alongside a booking" icon="celebration"
        :breadcrumbs="['Extra Services' => null]">
        <x-slot:actions>
            @can('create-addons')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddonModal">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Service
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Service</th>
                        @if (auth()->user()->isSuperAdmin())
                            <th>Hall</th>
                        @endif
                        <th class="text-end">Price</th>
                        <th>Charged</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addons as $addon)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $addon->name }}</div>
                                @if ($addon->description)
                                    <small class="text-secondary">{{ $addon->description }}</small>
                                @endif
                            </td>
                            @if (auth()->user()->isSuperAdmin())
                                <td class="text-secondary">{{ $addon->hall->name ?? '—' }}</td>
                            @endif
                            <td class="text-end"><x-money :amount="$addon->price" /></td>
                            <td>
                                <span class="badge text-bg-light">{{ $addon->pricing_mode_label }}</span>
                            </td>
                            <td>
                                <x-status-badge :label="$addon->is_active ? 'Active' : 'Inactive'"
                                    :tone="$addon->is_active ? 'success' : 'secondary'" />
                            </td>
                            <td class="text-end">
                                @can('edit-addons')
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#editAddon{{ $addon->id }}" title="Edit">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </button>
                                @endcan
                                @can('delete-addons')
                                    <form action="{{ route('addons.destroy', $addon) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Remove {{ $addon->name }}? Bookings that already include it keep their agreed price.">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="auth()->user()->isSuperAdmin() ? 6 : 5" icon="celebration"
                            title="No extra services yet"
                            message="Add priced extras such as stage decor, DJ or photography so they land on the bill automatically.">
                            @can('create-addons')
                                <x-slot:action>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addAddonModal">
                                        <i class="material-icons-outlined fs-6 align-middle">add</i> Add Service
                                    </button>
                                </x-slot:action>
                            @endcan
                        </x-empty-state>
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>

    {{-- ────────────────────────────── Create modal ─────────────────────── --}}
    @can('create-addons')
        <div class="modal fade" id="addAddonModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('addons.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('addons._fields', ['addon' => null, 'halls' => $halls])
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

    {{-- ────────────────────────────── Edit modals ──────────────────────── --}}
    @can('edit-addons')
        @foreach ($addons as $addon)
            <div class="modal fade" id="editAddon{{ $addon->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('addons.update', $addon) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit {{ $addon->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('addons._fields', ['addon' => $addon, 'halls' => $halls])
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
