@extends('dashboard.includes.partial.base')

@section('title', 'Trusted IPs')

@section('content')
    <h6 class="mb-0 text-uppercase">Trusted IPs</h6>
    <hr>

    <div class="mb-3 text-end">
        <button class="btn btn-grd btn-grd-primary" id="addIpBtn">
            <i class="material-icons-outlined">add</i> Add Trusted IP
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ips as $ip)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $ip->user->name ?? 'N/A' }}</td>
                                <td>{{ $ip->ip_address }}</td>
                                <td>{{ $ip->created_at_formatted }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editIpBtn" data-id="{{ $ip->id }}"
                                        data-user_id="{{ $ip->user_id }}" data-ip="{{ $ip->ip_address }}">
                                        <i class="material-icons-outlined">edit</i>
                                    </button>
                                    <form action="{{ route('trusted-ips.destroy', $ip->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

    <!-- Add/Edit IP Modal -->
    <div class="modal fade" id="ipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="ipForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ipModalLabel">Add Trusted IP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="userSelect" class="form-label">Select User</label>
                            <select name="user_id" id="userSelect" class="form-select" required>
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ipAddress" class="form-label">IP Address</label>
                            <input type="text" name="ip_address" id="ipAddress" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-grd btn-grd-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            // Add IP button
            $('#addIpBtn').click(function() {
                $('#ipForm').attr('action', "{{ route('trusted-ips.store') }}");
                $('#formMethod').val('POST');
                $('#ipModalLabel').text('Add Trusted IP');
                $('#ipForm')[0].reset();
                let modal = new bootstrap.Modal(document.getElementById('ipModal'));
                modal.show();
            });

            // Edit IP button
            $('.editIpBtn').click(function() {
                const id = $(this).data('id');
                const userId = $(this).data('user_id');
                const ip = $(this).data('ip');

                $('#ipForm').attr('action', '/trusted-ips/' + id);
                $('#formMethod').val('PUT');
                $('#ipModalLabel').text('Edit Trusted IP');
                $('#userSelect').val(userId);
                $('#ipAddress').val(ip);

                let modal = new bootstrap.Modal(document.getElementById('ipModal'));
                modal.show();
            });
        });
    </script>
@endsection
