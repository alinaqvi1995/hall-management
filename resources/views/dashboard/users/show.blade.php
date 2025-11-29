@extends('dashboard.includes.partial.base')
@section('title', 'User Details')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @php $currentUser = auth()->user(); @endphp

    <div class="row">
        <!-- User Info Panel -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">User Information</h5>
                    @if ($currentUser->isSuperAdmin() || ($currentUser->isHallAdmin() && $currentUser->hall_id == $user->hall->id))
                        <div class="btn-group" role="group">
                            <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">edit</i> Edit
                            </a>
                            @if ($currentUser->isSuperAdmin())
                                <form action="{{ route('dashboard.users.update', $user->id) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="material-icons-outlined">delete</i> Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Name:</strong> {{ $user->name }}</div>
                        <div class="col-md-6"><strong>Email:</strong> {{ $user->email }}</div>
                        <div class="col-md-6"><strong>Status:</strong> {!! $user->status_label !!}</div>
                        <div class="col-md-6"><strong>Created At:</strong> {{ $user->created_at_formatted }}</div>
                        <div class="col-md-6"><strong>Updated At:</strong> {{ $user->updated_at_formatted }}</div>
                        @if ($user->hall)
                            <div class="col-md-6"><strong>Hall:</strong>
                                <a href="{{ route('halls.show', $user->hall->id) }}">{{ $user->hall->name }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- User Detail Panel -->
        @if ($user->detail)
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header bg-secondary">
                        <h5 class="mb-0 text-white">Additional Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><strong>Phone:</strong> {{ $user->detail->phone_1 ?? '-' }}</div>
                            <div class="col-md-6"><strong>Address:</strong> {{ $user->detail->full_address ?? '-' }}</div>
                            <div class="col-md-6"><strong>Gender:</strong> {{ $user->detail->gender ?? '-' }}</div>
                            <div class="col-md-6"><strong>Date of Birth:</strong>
                                {{ $user->detail->date_of_birth ? $user->detail->date_of_birth->format('d M, Y') : '-' }}
                            </div>
                            <div class="col-md-6"><strong>Department:</strong> {{ $user->detail->department ?? '-' }}</div>
                            <div class="col-md-6"><strong>Designation:</strong> {{ $user->detail->designation ?? '-' }}
                            </div>
                            <div class="col-md-6"><strong>Date of Joining:</strong>
                                {{ $user->detail->date_of_joining ? $user->detail->date_of_joining->format('d M, Y') : '-' }}
                            </div>
                            <div class="col-md-6"><strong>Referred By:</strong>
                                {{ $user->detail->referredBy->name ?? '-' }}
                            </div>
                            <div class="col-md-12"><strong>Notes:</strong> {{ $user->detail->notes ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Trusted IPs Subpanel -->
        {{-- <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-secondary">
                    <h5 class="mb-0 text-white">Trusted IPs ({{ $user->trustedIps->count() }})</h5>
                </div>
                <div class="card-body">
                    @if ($user->trustedIps->count())
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->trustedIps as $ip)
                                    <tr>
                                        <td>{{ $ip->ip_address }}</td>
                                        <td>{{ $ip->created_at->format('d M, Y h:ia') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="mb-0">No trusted IPs.</p>
                    @endif
                </div>
            </div>
        </div> --}}
    </div>
@endsection
