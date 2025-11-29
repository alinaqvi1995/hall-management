@extends('dashboard.includes.partial.base')

@section('title', 'Users')

@section('content')
    <h6 class="mb-0 text-uppercase">All Users</h6>
    <hr>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <h5 class="mb-0">Users List</h5>
                </div>

                @can('update', $users)
                    <a href="{{ route('dashboard.users.create') }}" class="btn btn-grd btn-grd-primary">
                        <i class="material-icons-outlined">add</i> Add New User
                    </a>
                @endcan
            </div>

            {{-- <div class="row mb-3">
                <div class="col-md-4">
                    <select id="columnSearchSelect" class="form-select">
                        <option value="">Search All Columns</option>
                        <option value="0">Name</option>
                        <option value="1">Email</option>
                    </select>
                </div>
                <div class="col-md-8 position-relative">
                    <input class="form-control rounded-5 px-5" type="text" placeholder="Search" id="columnSearchInput">
                    <span
                        class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
                </div>
            </div> --}}

            <div class="table-responsive">
                <table class="table align-middle datatable" id="usersTable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Name / Email</th>
                            <th>Hall Name</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Timestamps</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $user->name }} <br>
                                    {{ $user->email }}
                                </td>
                                <td>{{ $user->hall?->name ?? 'No Hall' }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>

                                <td>{!! $user->status_label !!}</td>

                                <td>
                                    <small>Created: {{ $user->created_at_formatted }}</small><br>
                                    <small>Updated: {{ $user->updated_at_formatted }}</small>
                                </td>

                                <td>
                                    @if (!$user->hasRole('super_admin'))
                                        <div class="dropdown d-inline">
                                            <a href="javascript:;" class="dropdown-toggle btn btn-sm btn-secondary"
                                                data-bs-toggle="dropdown">
                                                View Options
                                            </a>
                                            <ul class="dropdown-menu">
                                                {{-- @can('edit-users') --}}
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('users.show', $user->id) }}">
                                                        <i class="material-icons-outlined me-1">edit</i> Edit
                                                    </a>
                                                </li>
                                                {{-- @endcan --}}
                                                @can('delete-users')
                                                    <li>
                                                        <form action="{{ route('dashboard.users.destroy', $user->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item"
                                                                onclick="return confirm('Are you sure?')">
                                                                <i class="material-icons-outlined me-1">delete</i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                                <li>
                                                    <form action="{{ route('users.toggleActive', $user->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i
                                                                class="material-icons-outlined me-1">{{ $user->is_active ? 'lock' : 'lock_open' }}</i>
                                                            {{ $user->is_active ? 'Freeze' : 'Activate' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('users.forceLogout', $user->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="material-icons-outlined me-1">logout</i> Force Logout
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="ms-2">Admin - no actions</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="small text-muted">
                </div>
                <div>
                    {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            // Column-specific search
            $('#columnSearchInput').on('keyup', function() {
                var colIndex = $('#columnSearchSelect').val();
                var searchTerm = this.value;
                if (colIndex === "") {
                    table.search(searchTerm).draw();
                } else {
                    table.column(colIndex).search(searchTerm).draw();
                }
            });

            $('#columnSearchSelect').on('change', function() {
                $('#columnSearchInput').trigger('keyup');
            });
        });
    </script>
@endsection
