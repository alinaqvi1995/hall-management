@extends('dashboard.includes.partial.base')

@section('title', 'Roles')

@section('content')
    <h6 class="mb-0 text-uppercase">Roles</h6>
    <hr>

    @can('create-roles')
        <div class="mb-3 text-end">
            <button class="btn btn-grd btn-grd-primary" data-bs-toggle="modal" data-bs-target="#roleModal" id="addRoleBtn">
                <i class="material-icons-outlined">add</i> Add Role
            </button>
        </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable" id="roleTable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            @continue($role->slug == 'super_admin')
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->slug }}</td>
                                <td>
                                    @foreach ($role->permissions as $perm)
                                        <span class="badge bg-primary me-1 mb-1">{{ $perm->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @can('edit-roles')
                                        <button class="btn btn-sm btn-info editRoleBtn" data-id="{{ $role->id }}"
                                            data-name="{{ $role->name }}" data-slug="{{ $role->slug }}"
                                            data-permissions='@json($role->permissions->pluck('id'))'>
                                            <i class="material-icons-outlined">edit</i>
                                        </button>
                                    @endcan
                                    @can('delete-roles')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="material-icons-outlined">delete</i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="roleForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalLabel">Add Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="roleName" class="form-label">Name</label>
                            <input type="text" name="name" id="roleName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="roleSlug" class="form-label">Slug</label>
                            <input type="text" name="slug" id="roleSlug" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="rolePermissions" class="form-label">Permissions</label>
                            <select name="permissions[]" id="rolePermissions" class="select2 form-control" multiple>
                                @foreach (\App\Models\Permission::all() as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
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
            $('#rolePermissions').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                dropdownParent: $('#roleModal')
            });

            // Add Role
            $('#addRoleBtn').click(function() {
                $('#roleForm').attr('action', "{{ route('roles.store') }}");
                $('#formMethod').val('POST');
                $('#roleModalLabel').text('Add Role');
                $('#roleForm')[0].reset();
                $('#rolePermissions').val(null).trigger('change');
            });

            // Edit Role
            $('.editRoleBtn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const slug = $(this).data('slug');
                const permissions = $(this).data('permissions');

                $('#roleForm').attr('action', "{{ url('roles') }}/" + id);
                $('#formMethod').val('PUT');
                $('#roleModalLabel').text('Edit Role');
                $('#roleName').val(name);
                $('#roleSlug').val(slug);

                $('#roleModal').data('permissions', permissions).modal('show');
            });

            // Set permissions when modal is shown
            $('#roleModal').on('shown.bs.modal', function() {
                const permissions = $(this).data('permissions');
                if (permissions) {
                    $('#rolePermissions').val(permissions).trigger('change');
                    $(this).removeData('permissions');
                }
            });

        });
    </script>
@endsection
