@extends('dashboard.includes.partial.base')

@section('title', 'Cities')

@section('content')
    <x-page-header title="Cities" subtitle="Cities available when recording a venue's location" icon="location_city"
        :breadcrumbs="['Locations' => null, 'Cities' => null]">
        <x-slot:actions>
            @can('create-cities')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCityModal">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add City
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[0, 'asc']]" :page-length="25">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Province</th>
                        <th>Created by</th>
                        <th>Updated by</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $city)
                        <tr>
                            <td class="fw-semibold">{{ $city->name }}</td>
                            <td>{{ $city->state->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $city->creator->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $city->updater->name ?? '—' }}</td>
                            <td class="text-end">
                                @can('edit-cities')
                                    <button class="btn btn-sm btn-outline-secondary js-edit-city" title="Edit"
                                        data-id="{{ $city->id }}" data-name="{{ $city->name }}"
                                        data-state="{{ $city->state_id }}"
                                        data-action="{{ route('cities.update', $city) }}">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </button>
                                @endcan
                                @can('delete-cities')
                                    <form action="{{ route('cities.destroy', $city) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Delete {{ $city->name }}?">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="5" icon="location_city" title="No cities"
                            message="Add cities so halls can record where they are." />
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>

    @can('create-cities')
        <div class="modal fade" id="addCityModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('cities.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add City</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Province <span class="required-mark">*</span></label>
                            <select name="state_id" class="form-select" required>
                                <option value="">Select province</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">City Name <span class="required-mark">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Lahore">
                        </div>
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

    {{-- A single edit modal, populated from the row's data attributes. Rendering
         one modal per city previously produced hundreds of KB of markup. --}}
    @can('edit-cities')
        <div class="modal fade" id="editCityModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" id="editCityForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit City</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Province <span class="required-mark">*</span></label>
                            <select name="state_id" id="editCityState" class="form-select" required>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">City Name <span class="required-mark">*</span></label>
                            <input type="text" name="name" id="editCityName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Updating…">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.js-edit-city');
            if (!button) return;

            var form = document.getElementById('editCityForm');
            form.action = button.dataset.action;
            document.getElementById('editCityName').value = button.dataset.name;
            document.getElementById('editCityState').value = button.dataset.state;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('editCityModal')).show();
        });
    </script>
@endpush
