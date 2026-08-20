@extends('dashboard.includes.partial.base')

@section('title', 'Halls')

@section('content')
    <x-page-header :title="auth()->user()->isSuperAdmin() ? 'Halls' : 'My Hall'"
        subtitle="Venues, their spaces and their commercial settings" icon="festival"
        :breadcrumbs="['Halls' => null]">
        <x-slot:actions>
            @can('create-halls')
                <a href="{{ route('halls.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Hall
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[1, 'asc']]">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hall</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th class="text-end">Spaces</th>
                        <th class="text-end">Capacity</th>
                        <th class="text-end">Bookings</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($halls as $hall)
                        <tr>
                            <td class="text-secondary">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($hall->logo)
                                        <img src="{{ asset($hall->logo) }}" alt=""
                                            style="width:36px;height:36px;object-fit:cover;border-radius:8px">
                                    @else
                                        <span class="avatar-initial">{{ mb_substr($hall->name, 0, 1) }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('halls.show', $hall) }}"
                                            class="fw-semibold text-decoration-none d-block text-truncate">
                                            {{ $hall->name }}
                                        </a>
                                        <small class="text-secondary">{{ $hall->hall_type_label ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $hall->owner_name ?: '—' }}</div>
                                <small class="text-secondary">{{ $hall->phone ?: '' }}</small>
                            </td>
                            <td>
                                <div>{{ $hall->cityRelation->name ?? $hall->city ?? '—' }}</div>
                                <small class="text-secondary">{{ $hall->area ?: '' }}</small>
                            </td>
                            <td class="text-end tabular">{{ number_format($hall->lawns_count) }}</td>
                            <td class="text-end tabular">{{ number_format($hall->hall_capacity) }}</td>
                            <td class="text-end tabular">{{ number_format($hall->bookings_count) }}</td>
                            <td>
                                <x-status-badge :label="$hall->status ? 'Active' : 'Inactive'"
                                    :tone="$hall->status ? 'success' : 'danger'" />
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('halls.show', $hall) }}">
                                                <i class="material-icons-outlined fs-6">visibility</i>View</a>
                                        </li>
                                        @can('edit-halls')
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ route('halls.edit', $hall) }}">
                                                    <i class="material-icons-outlined fs-6">edit</i>Edit</a>
                                            </li>
                                        @endcan
                                        @can('delete-halls')
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="{{ route('halls.destroy', $hall) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                        data-confirm="Delete {{ $hall->name }}? Its lawns, bookings and ledger go with it.">
                                                        <i class="material-icons-outlined fs-6">delete</i>Delete</button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="9" icon="festival" title="No halls yet"
                            message="Add your first venue to start taking bookings.">
                            @can('create-halls')
                                <x-slot:action>
                                    <a href="{{ route('halls.create') }}" class="btn btn-primary btn-sm">
                                        <i class="material-icons-outlined fs-6 align-middle">add</i> Add Hall
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
