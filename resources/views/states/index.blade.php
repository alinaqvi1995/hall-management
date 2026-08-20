@extends('dashboard.includes.partial.base')

@section('title', 'Provinces')

@section('content')
    <x-page-header title="Provinces" subtitle="Provinces and territories of Pakistan" icon="map"
        :breadcrumbs="['Locations' => null, 'Provinces' => null]">
        <x-slot:actions>
            @can('create-states')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStateModal">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Province
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Province</th>
                        <th class="text-end">Cities</th>
                        <th>Created by</th>
                        <th>Updated by</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($states as $state)
                        <tr>
                            <td class="fw-semibold">{{ $state->name }}</td>
                            <td class="text-end tabular">{{ number_format($state->cities_count) }}</td>
                            <td class="text-secondary">{{ $state->creator->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $state->updater->name ?? '—' }}</td>
                            <td class="text-end">
                                @can('edit-states')
                                    <button class="btn btn-sm btn-outline-secondary js-edit-state" title="Edit"
                                        data-name="{{ $state->name }}"
                                        data-action="{{ route('states.update', $state) }}">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </button>
                                @endcan
                                @can('delete-states')
                                    <form action="{{ route('states.destroy', $state) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"
                                            data-confirm="Delete {{ $state->name }}? Its cities are deleted too.">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="5" icon="map" title="No provinces"
                            message="Add provinces so halls can record their location." />
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>

    @can('create-states')
        <div class="modal fade" id="addStateModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('states.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Province</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Province Name <span class="required-mark">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="e.g. Punjab">
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

    @can('edit-states')
        <div class="modal fade" id="editStateModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" id="editStateForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Province</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Province Name <span class="required-mark">*</span></label>
                        <input type="text" name="name" id="editStateName" class="form-control" required>
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
            var button = event.target.closest('.js-edit-state');
            if (!button) return;

            document.getElementById('editStateForm').action = button.dataset.action;
            document.getElementById('editStateName').value = button.dataset.name;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('editStateModal')).show();
        });
    </script>
@endpush
