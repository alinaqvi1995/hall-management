@php
    /**
     * Shared hall form for create and edit.
     * $hall (may be null on create), $states, $lawns, $isEdit
     */
    $lawnRows = old('lawns', $isEdit ? $lawns->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'capacity' => $l->capacity])->values()->all() : []);
    $selectedState = old('state_id', $hall->state_id ?? null);
    $selectedCity = old('city_id', $hall->city_id ?? null);
@endphp

@csrf
@if ($isEdit)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-xl-8">
        {{-- ──────────────────────── Identity ──────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">festival</i> Venue Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Hall Name <span class="required-mark">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $hall->name ?? '') }}"
                            class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Venue Type</label>
                        <select name="hall_types" class="form-select @error('hall_types') is-invalid @enderror">
                            <option value="">Select type</option>
                            @foreach (\App\Models\Hall::HALL_TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('hall_types', $hall->hall_types ?? '') === $key)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hall_types')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $hall->owner_name ?? '') }}"
                            class="form-control @error('owner_name') is-invalid @enderror">
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $hall->phone ?? '') }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="0300-1234567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $hall->email ?? '') }}"
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Facilities, parking, air conditioning…">{{ old('description', $hall->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────────────── Location ──────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">place</i> Location
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="address" value="{{ old('address', $hall->address ?? '') }}"
                            class="form-control @error('address') is-invalid @enderror">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Province</label>
                        <select name="state_id" id="stateSelect"
                            class="form-select @error('state_id') is-invalid @enderror">
                            <option value="">Select province</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected($selectedState == $state->id)>
                                    {{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <select name="city_id" id="citySelect"
                            class="form-select @error('city_id') is-invalid @enderror"
                            data-selected="{{ $selectedCity }}">
                            <option value="">Select province first</option>
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Area / Sector</label>
                        <input type="text" name="area" value="{{ old('area', $hall->area ?? '') }}"
                            class="form-control @error('area') is-invalid @enderror"
                            placeholder="e.g. Johar Town, DHA Phase 5">
                        @error('area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="zipcode" value="{{ old('zipcode', $hall->zipcode ?? '') }}"
                            class="form-control @error('zipcode') is-invalid @enderror">
                        @error('zipcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country"
                            value="{{ old('country', $hall->country ?? 'Pakistan') }}"
                            class="form-control @error('country') is-invalid @enderror">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────── Lawns / bookable spaces ─────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <i class="material-icons-outlined fs-6">grid_view</i> Bookable Spaces
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addLawnRow">
                    <i class="material-icons-outlined fs-6 align-middle">add</i> Add Space
                </button>
            </div>
            <div class="card-body">
                <p class="text-secondary small">
                    Each lawn, hall or marquee inside this venue that can be booked independently.
                    Availability is checked per space, so two events can run at once in different spaces.
                </p>

                @error('lawns')
                    <div class="alert alert-danger app-alert py-2 small">{{ $message }}</div>
                @enderror

                <div id="lawnRows">
                    @forelse ($lawnRows as $i => $row)
                        <div class="row g-2 align-items-end lawn-row mb-2">
                            <input type="hidden" name="lawns[{{ $i }}][id]"
                                value="{{ $row['id'] ?? '' }}">
                            <div class="col-md-7">
                                <label class="form-label">Space Name <span class="required-mark">*</span></label>
                                <input type="text" name="lawns[{{ $i }}][name]"
                                    value="{{ $row['name'] ?? '' }}"
                                    class="form-control @error("lawns.$i.name") is-invalid @enderror" required>
                                @error("lawns.$i.name")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Seats</label>
                                <input type="number" min="0" name="lawns[{{ $i }}][capacity]"
                                    value="{{ $row['capacity'] ?? '' }}" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-lawn"
                                    title="Remove">
                                    <i class="material-icons-outlined fs-6">delete</i>
                                </button>
                            </div>
                        </div>
                    @empty
                        {{-- Rendered by JS below so create starts with one blank row. --}}
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────────── Sidebar ───────────────────────── --}}
    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">image</i> Logo
            </div>
            <div class="card-body text-center">
                <img id="logoPreview" src="{{ $hall?->logo ? asset($hall->logo) : '' }}" alt=""
                    class="mb-3 rounded"
                    style="max-height:110px;max-width:100%;{{ $hall?->logo ? '' : 'display:none' }}">
                <input type="file" name="logo" id="logoInput"
                    class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                <div class="form-text">JPG, PNG, WEBP or SVG. Max 2 MB. Appears on invoices.</div>
                @error('logo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">payments</i> Commercial Defaults
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Total Capacity</label>
                    <input type="number" min="0" name="hall_capacity"
                        value="{{ old('hall_capacity', $hall->hall_capacity ?? 0) }}"
                        class="form-control @error('hall_capacity') is-invalid @enderror">
                    @error('hall_capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Default Per-Head Rate</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" min="0" name="default_per_head_rate"
                            value="{{ old('default_per_head_rate', $hall->default_per_head_rate ?? '') }}"
                            class="form-control @error('default_per_head_rate') is-invalid @enderror">
                    </div>
                    <div class="form-text">Prefills new bookings when no package is chosen.</div>
                    @error('default_per_head_rate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Advance Required</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" name="advance_policy_percent"
                            value="{{ old('advance_policy_percent', $hall->advance_policy_percent ?? 25) }}"
                            class="form-control @error('advance_policy_percent') is-invalid @enderror">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('advance_policy_percent')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Cancellation Charge</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" name="cancellation_charge_percent"
                            value="{{ old('cancellation_charge_percent', $hall->cancellation_charge_percent ?? 0) }}"
                            class="form-control @error('cancellation_charge_percent') is-invalid @enderror">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Retained when a booking is cancelled.</div>
                    @error('cancellation_charge_percent')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label">Default Tax / GST</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" name="tax_percent"
                            value="{{ old('tax_percent', $hall->tax_percent ?? 0) }}"
                            class="form-control @error('tax_percent') is-invalid @enderror">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('tax_percent')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">gavel</i> Registration
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number"
                        value="{{ old('registration_number', $hall->registration_number ?? '') }}"
                        class="form-control @error('registration_number') is-invalid @enderror">
                    @error('registration_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">NTN</label>
                    <input type="text" name="ntn_number"
                        value="{{ old('ntn_number', $hall->ntn_number ?? '') }}"
                        class="form-control @error('ntn_number') is-invalid @enderror"
                        placeholder="1234567-8">
                    <div class="form-text">Printed on invoices.</div>
                    @error('ntn_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">GST / Sales Tax Number</label>
                    <input type="text" name="gst_number"
                        value="{{ old('gst_number', $hall->gst_number ?? '') }}"
                        class="form-control @error('gst_number') is-invalid @enderror">
                    @error('gst_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Established</label>
                    <input type="date" name="established_at" max="{{ now()->toDateString() }}"
                        value="{{ old('established_at', $hall?->established_at?->format('Y-m-d')) }}"
                        class="form-control @error('established_at') is-invalid @enderror">
                    @error('established_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status <span class="required-mark">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" @selected(old('status', $hall->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', $hall->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="notes" rows="2"
                        class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $hall->notes ?? '') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"
                    data-loading-text="{{ $isEdit ? 'Updating…' : 'Saving…' }}">
                    <i class="material-icons-outlined fs-6 align-middle">save</i>
                    {{ $isEdit ? 'Update Hall' : 'Create Hall' }}
                </button>
                <a href="{{ $isEdit ? route('halls.show', $hall) : route('halls.index') }}"
                    class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            /* ------------------------------------------ lawn repeater rows */

            var container = document.getElementById('lawnRows');
            // Continue numbering from the rows already rendered so indexes stay unique.
            var index = container.querySelectorAll('.lawn-row').length;

            function rowMarkup(i) {
                return '<div class="row g-2 align-items-end lawn-row mb-2">'
                    + '<input type="hidden" name="lawns[' + i + '][id]" value="">'
                    + '<div class="col-md-7">'
                    + '<label class="form-label">Space Name <span class="required-mark">*</span></label>'
                    + '<input type="text" name="lawns[' + i + '][name]" class="form-control" required '
                    + 'placeholder="e.g. Main Hall, Rose Lawn">'
                    + '</div>'
                    + '<div class="col-md-4">'
                    + '<label class="form-label">Seats</label>'
                    + '<input type="number" min="0" name="lawns[' + i + '][capacity]" class="form-control">'
                    + '</div>'
                    + '<div class="col-md-1">'
                    + '<button type="button" class="btn btn-outline-danger w-100 remove-lawn" title="Remove">'
                    + '<i class="material-icons-outlined fs-6">delete</i></button>'
                    + '</div></div>';
            }

            function addRow() {
                container.insertAdjacentHTML('beforeend', rowMarkup(index));
                index++;
            }

            document.getElementById('addLawnRow').addEventListener('click', addRow);

            container.addEventListener('click', function (event) {
                var button = event.target.closest('.remove-lawn');
                if (!button) return;

                var row = button.closest('.lawn-row');
                var hidden = row.querySelector('input[name*="[id]"]');

                // Removing a saved lawn is what tells the server to delete it, so
                // warn before dropping one that already exists.
                if (hidden && hidden.value &&
                    !confirm('Remove this space? It is deleted when you save, and that is blocked if it still has upcoming bookings.')) {
                    return;
                }

                row.remove();
            });

            // A venue with no bookable space cannot take bookings; start with one.
            if (index === 0) {
                addRow();
            }

            /* ---------------------------------------------- city cascade */

            var cities = @json(\App\Models\City::orderBy('name')->get(['id', 'name', 'state_id']));
            var stateSelect = document.getElementById('stateSelect');
            var citySelect = document.getElementById('citySelect');

            function fillCities() {
                var stateId = stateSelect.value;
                var wanted = citySelect.dataset.selected;

                if (!stateId) {
                    citySelect.innerHTML = '<option value="">Select province first</option>';
                    return;
                }

                var html = '<option value="">Select city</option>';
                cities.filter(function (c) { return String(c.state_id) === String(stateId); })
                    .forEach(function (c) {
                        html += '<option value="' + c.id + '"'
                            + (String(wanted) === String(c.id) ? ' selected' : '') + '>' + c.name + '</option>';
                    });

                citySelect.innerHTML = html;
            }

            stateSelect.addEventListener('change', function () {
                // A new province invalidates the previously chosen city.
                citySelect.dataset.selected = '';
                fillCities();
            });
            citySelect.addEventListener('change', function () {
                citySelect.dataset.selected = citySelect.value;
            });

            fillCities();

            /* -------------------------------------------- logo preview */

            var logoInput = document.getElementById('logoInput');
            var logoPreview = document.getElementById('logoPreview');

            logoInput.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;

                logoPreview.src = URL.createObjectURL(file);
                logoPreview.style.display = 'inline-block';
            });
        })();
    </script>
@endpush
