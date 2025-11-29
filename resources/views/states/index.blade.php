@extends('dashboard.includes.partial.base')

@section('title', 'States')

@section('content')
    <h6 class="mb-0 text-uppercase">States</h6>
    <hr>

    <div class="mb-3 text-end">
        <button class="btn btn-grd btn-grd-primary" id="addStateBtn">
            <i class="material-icons-outlined">add</i> Add State
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable">
                    <thead>
                        <tr>
                            <th>Sr#</th>
                            <th>State</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($states as $state)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $state->name }}</td>
                                <td>{{ $state->creator->name ?? '-' }}</td>
                                <td>{{ $state->updater->name ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editStateBtn" data-id="{{ $state->id }}"
                                        data-name="{{ $state->name }}">
                                        <i class="material-icons-outlined">edit</i>
                                    </button>

                                    <form action="{{ route('states.destroy', $state->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this state?')">
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

    {{-- Modal --}}
    <div class="modal fade" id="stateModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="stateForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">State</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">State Name</label>
                            <input type="text" name="name" id="stateName" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-grd btn-grd-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('extra_js')
    <script>
        const modal = new bootstrap.Modal('#stateModal');

        // ADD
        document.getElementById('addStateBtn').addEventListener('click', function() {
            document.getElementById('stateForm').reset();
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('stateForm').action = "{{ route('states.store') }}";
            modal.show();
        });

        // EDIT
        document.querySelectorAll('.editStateBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                document.getElementById('stateName').value = name;

                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('stateForm').action =
                    "/states/" + id;

                modal.show();
            });
        });
    </script>
@endsection
