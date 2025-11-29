@extends('dashboard.includes.partial.base')

@section('title', 'Edit Profile')

@section('content')
    <div class="container">
        <h6 class="mb-4">Edit Profile</h6>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success">Profile updated successfully.</div>
        @endif

        <div class="row">
            <!-- Profile Info Card -->
            <div class="col-12 col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Your Profile</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $user->detail?->profile_image ? asset($user->detail->profile_image) : asset('admin/images/avatar.png') }}"
                                alt="User Avatar" class="rounded-circle mb-2" width="120" height="120">

                            <label for="profile_image"
                                class="btn btn-primary position-absolute bottom-0 end-0 translate-middle p-2"
                                style="border-radius: 50%; cursor: pointer;">
                                <i class="bi bi-pencil"></i>
                            </label>
                        </div>

                        <form id="profile-image-form" action="{{ route('profile.update') }}" method="POST"
                            enctype="multipart/form-data" class="d-none">
                            @csrf
                            @method('PUT')
                            <input type="file" name="profile_image" id="profile_image">
                            <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                            <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
                        </form>

                        <h5 class="card-title mt-2">{{ $user->name }}</h5>
                        <p class="text-muted mb-1">{{ $user->email }}</p>
                        <hr>

                        <h6 class="mb-1">Roles</h6>
                        @forelse($user->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse

                        <h6 class="mt-3 mb-1">Permissions</h6>
                        @forelse($user->allPermissions() as $perm)
                            <span class="badge bg-success">{{ $perm->name }}</span>
                        @empty
                            <span class="text-muted">No permissions</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="col-12 col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Edit Your Details</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone_1" class="form-label">Phone</label>
                                    <input type="text" name="phone_1" id="phone_1" class="form-control"
                                        value="{{ old('phone_1', $user->detail->phone_1 ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                                        value="{{ old('date_of_birth', optional($user->detail?->date_of_birth)?->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male"
                                        {{ old('gender', $user->detail->gender ?? '') == 'male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="female"
                                        {{ old('gender', $user->detail->gender ?? '') == 'female' ? 'selected' : '' }}>
                                        Female</option>
                                    <option value="other"
                                        {{ old('gender', $user->detail->gender ?? '') == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>

                            <hr>
                            <h6 class="mb-3">Address</h6>
                            <div class="mb-3">
                                <label for="address_1" class="form-label">Address</label>
                                <input type="text" name="address_1" id="address_1" class="form-control"
                                    value="{{ old('address_1', $user->detail->address_1 ?? '') }}">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" id="city" class="form-control"
                                        value="{{ old('city', $user->detail->city ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" name="state" id="state" class="form-control"
                                        value="{{ old('state', $user->detail->state ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" name="country" id="country" class="form-control"
                                        value="{{ old('country', $user->detail->country ?? '') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control"
                                    value="{{ old('postal_code', $user->detail->postal_code ?? '') }}">
                            </div>

                            <hr>
                            <h6 class="mb-3">Emergency Contact</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="emergency_contact_name" class="form-label">Name</label>
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                                        class="form-control"
                                        value="{{ old('emergency_contact_name', $user->detail->emergency_contact_name ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="emergency_contact_relation" class="form-label">Relation</label>
                                    <input type="text" name="emergency_contact_relation"
                                        id="emergency_contact_relation" class="form-control"
                                        value="{{ old('emergency_contact_relation', $user->detail->emergency_contact_relation ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Phone</label>
                                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone"
                                        class="form-control"
                                        value="{{ old('emergency_contact_phone', $user->detail->emergency_contact_phone ?? '') }}">
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">Employment Info</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" name="department" id="department" class="form-control"
                                        value="{{ old('department', $user->detail->department ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" name="designation" id="designation" class="form-control"
                                        value="{{ old('designation', $user->detail->designation ?? '') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="shift_timings" class="form-label">Shift Timings</label>
                                <select name="shift_timings" id="shift_timings" class="form-control">
                                    <option value="">Select Shift</option>
                                    <option value="9am-6pm"
                                        {{ old('shift_timings', $user->detail->shift_timings ?? '') == '9am-6pm' ? 'selected' : '' }}>
                                        9am – 6pm PKT</option>
                                    <option value="10am-7pm"
                                        {{ old('shift_timings', $user->detail->shift_timings ?? '') == '10am-7pm' ? 'selected' : '' }}>
                                        10am – 7pm PKT</option>
                                    <option value="8am-5pm"
                                        {{ old('shift_timings', $user->detail->shift_timings ?? '') == '8am-5pm' ? 'selected' : '' }}>
                                        8am – 5pm PKT</option>
                                </select>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">New Password <small>(leave blank to keep
                                            current)</small></label>
                                    <input type="password" name="password" id="password" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-grd btn-grd-primary mt-2">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            $('#profile_image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('.position-relative img').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);

                    // Submit form after preview
                    $('#profile-image-form').submit();
                }
            });
        });
    </script>
@endsection
