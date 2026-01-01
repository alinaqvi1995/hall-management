@extends('dashboard.includes.partial.base')
@section('title', 'Edit Hall')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('halls.index') }}">Halls</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Hall</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('halls.update', $hall->id) }}" method="POST" enctype="multipart/form-data" id="hallForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-12">
                <div class="card">

                    <!-- Top Buttons -->
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary">
                        <h5 class="mb-0 text-white">Edit Hall</h5>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">save</i> Update
                            </button>
                            <a href="{{ route('halls.show', $hall->id) }}" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">cancel</i> Cancel
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Hall Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $hall->name) }}"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Hall Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                @if ($hall->logo)
                                    <div class="mt-2">
                                        <img src="{{ asset($hall->logo) }}" alt="Hall Logo" class="img-thumbnail"
                                            style="height: 50px;">
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name', $hall->owner_name) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $hall->phone) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $hall->email) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" value="{{ old('city', $hall->city) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <input type="text" name="state" value="{{ old('state', $hall->state) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $hall->country) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Zipcode</label>
                                <input type="text" name="zipcode" value="{{ old('zipcode', $hall->zipcode) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <input type="text" name="area" value="{{ old('area', $hall->area) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Hall Capacity</label>
                                <input type="number" name="hall_capacity"
                                    value="{{ old('hall_capacity', $hall->hall_capacity) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Hall Types</label>
                                <input type="text" name="hall_types"
                                    value="{{ old('hall_types', $hall->hall_types) }}" class="form-control"
                                    placeholder="e.g. Banquet, Conference">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="registration_number"
                                    value="{{ old('registration_number', $hall->registration_number) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Established At</label>
                                <input type="date" name="established_at"
                                    value="{{ old('established_at', $hall->established_at ? \Carbon\Carbon::parse($hall->established_at)->format('Y-m-d') : '') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $hall->description) }}</textarea>
                            </div>

                            {{-- ─────────────────────────────── --}}
                            {{--   SUPER ADMIN ONLY – STATUS     --}}
                            {{-- ─────────────────────────────── --}}
                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" {{ old('status', $hall->status) == 1 ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ old('status', $hall->status) == 0 ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="status" value="{{ $hall->status }}">
                            @endif

                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $hall->notes) }}</textarea>
                            </div>

                            {{-- ========================= --}}
                            {{-- LAWNS SECTION --}}
                            {{-- ========================= --}}
                            <h5 class="mt-4 mb-3">Lawns</h5>
                            <div id="lawnWrapper">
                                @foreach (old('lawns', $hall->lawns->toArray()) as $index => $lawn)
                                    <div class="row g-3 lawn-item border rounded p-3 mb-3">
                                        <input type="hidden" name="lawns[{{ $index }}][id]"
                                            value="{{ $lawn['id'] ?? '' }}">
                                        <div class="col-md-5">
                                            <label class="form-label">Lawn Name</label>
                                            <input type="text" name="lawns[{{ $index }}][name]"
                                                value="{{ $lawn['name'] ?? '' }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Capacity</label>
                                            <input type="number" name="lawns[{{ $index }}][capacity]"
                                                value="{{ $lawn['capacity'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger removeLawn w-100">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="addLawn" class="btn btn-success btn-sm mt-2">+ Add Lawn</button>

                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="material-icons-outlined">save</i> Update
                        </button>
                        <a href="{{ route('halls.show', $hall->id) }}" class="btn btn-secondary px-4">
                            <i class="material-icons-outlined">cancel</i> Cancel
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </form>
@endsection

@section('extra_js')
    <script>
        let lawnCount = {{ count(old('lawns', $hall->lawns)) }};
        const maxLawns = 10; // adjust as needed

        $('#addLawn').click(function() {
            if (lawnCount >= maxLawns) {
                alert('Maximum ' + maxLawns + ' lawns allowed.');
                return;
            }

            let html = `
        <div class="row g-3 lawn-item border rounded p-3 mb-3">
            <div class="col-md-5">
                <label class="form-label">Lawn Name</label>
                <input type="text" name="lawns[${lawnCount}][name]" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Capacity</label>
                <input type="number" name="lawns[${lawnCount}][capacity]" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger removeLawn w-100">Remove</button>
            </div>
        </div>
        `;
            $('#lawnWrapper').append(html);
            lawnCount++;
        });

        $(document).on('click', '.removeLawn', function() {
            $(this).closest('.lawn-item').remove();
            lawnCount--;
        });
    </script>
@endsection
