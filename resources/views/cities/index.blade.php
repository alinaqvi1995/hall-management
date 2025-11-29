@extends('dashboard.includes.partial.base')

@section('title', 'Cities')

@section('content')
    <h6 class="mb-0 text-uppercase">Cities</h6>
    <hr>

    {{-- Add City Button --}}
    <div class="mb-3 text-end">
        <button class="btn btn-grd btn-grd-primary" id="addCityBtn">
            <i class="material-icons-outlined">add</i> Add City
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable">
                    <thead>
                        <tr>
                            <th>Sr#</th>
                            <th>City Name</th>
                            <th>State</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cities as $city)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->state->name ?? '-' }}</td>
                                <td>{{ optional($city->creator)->name ?? '-' }}</td>
                                <td>{{ optional($city->updater)->name ?? '-' }}</td>

                                <td>
                                    {{-- Edit Button --}}
                                    <button class="btn btn-sm btn-info editCityBtn"
                                        data-id="{{ $city->id }}"
                                        data-name="{{ $city->name }}"
                                        data-state="{{ $city->state_id }}">
                                        <i class="material-icons-outlined">edit</i>
                                    </button>

                                    {{-- Delete Form --}}
                                    <form action="{{ route('cities.destroy', $city->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="material-icons-outlined">delete</i>
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>


    <!-- ------------------------------------------------------------------------------------ -->
    <!-- ADD CITY MODAL -->
    <!-- ------------------------------------------------------------------------------------ -->
    <div class="modal fade" id="addCityModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form action="{{ route('cities.store') }}" method="POST" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add City</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-control" required>
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save City</button>
                </div>
            </form>
        </div>
    </div>



    <!-- ------------------------------------------------------------------------------------ -->
    <!-- EDIT CITY MODAL -->
    <!-- ------------------------------------------------------------------------------------ -->
    <div class="modal fade" id="editCityModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form method="POST" class="modal-content" id="editCityForm">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit City</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-control" id="edit_state_id" required>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City Name</label>
                        <input type="text" name="name" class="form-control" id="edit_name" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Update City</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('extra_js')
<script>
    // ADD CITY MODAL
    document.getElementById('addCityBtn').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('addCityModal')).show();
    });

    // EDIT CITY MODAL
    document.querySelectorAll('.editCityBtn').forEach(btn => {
        btn.addEventListener('click', function() {

            let id = this.dataset.id;
            let name = this.dataset.name;
            let state = this.dataset.state;

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_state_id').value = state;

            // Set form action dynamically
            document.getElementById('editCityForm').action =
                `/cities/${id}`;

            new bootstrap.Modal(document.getElementById('editCityModal')).show();
        });
    });
</script>
@endsection
