@extends('dashboard.includes.partial.base')
@section('title', 'Hall Details')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('halls.index') }}">Halls</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $hall->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Hall Info Panel -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Hall Information</h5>
                    @php $currentUser = auth()->user(); @endphp
                    @if ($currentUser->isSuperAdmin() || ($currentUser->isHallAdmin() && $currentUser->hall_id == $hall->id))
                        <div class="btn-group" role="group">
                            <a href="{{ route('halls.edit', $hall->id) }}" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">edit</i> Edit
                            </a>
                            @if ($currentUser->isSuperAdmin())
                                <form action="{{ route('halls.destroy', $hall->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this hall?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="material-icons-outlined">delete</i>
                                        Delete</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Name:</strong> {{ $hall->name }}</div>
                        <div class="col-md-6"><strong>Owner:</strong> {{ $hall->owner_name ?? '-' }}</div>
                        <div class="col-md-6"><strong>Phone:</strong> {{ $hall->phone ?? '-' }}</div>
                        <div class="col-md-6"><strong>Email:</strong> {{ $hall->email ?? '-' }}</div>
                        <div class="col-md-12"><strong>Address:</strong>
                            {{ implode(', ', array_filter([$hall->address, $hall->city, $hall->state, $hall->country, $hall->zipcode])) }}
                        </div>
                        <div class="col-md-6"><strong>Area:</strong> {{ $hall->area ?? '-' }}</div>
                        <div class="col-md-6"><strong>Established:</strong>
                            {{ $hall->established_at ? \Carbon\Carbon::parse($hall->established_at)->format('d M, Y') : '-' }}
                        </div>
                        <div class="col-md-6"><strong>Registration #:</strong> {{ $hall->registration_number ?? '-' }}
                        </div>
                        <div class="col-md-6"><strong>Status:</strong> {!! $hall->status
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-danger">Inactive</span>' !!}
                        </div>
                        <div class="col-12"><strong>Notes:</strong> {{ $hall->notes ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Subpanel -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Users ({{ $hall->users()->count() }})</h5>
                </div>
                <div class="card-body">
                    @if ($hall->users->count())
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hall->users as $user)
                                    <tr>
                                        <td><a href="{{ route('users.show', $user->id) }}">{{ $user->name }}</a></td>
                                        <td>{{ $user->email ?? '-' }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="mb-0">No users assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
