@extends('dashboard.includes.partial.base')

@section('title', 'Create User')

@section('content')
    <style>
        /* scrollable dropdown */
        .select2-dropdown.bigdrop {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
    <div class="card">
        <div class="card-header bg-light">
            <h6>Create New User</h6>
        </div>

        <div class="card-body">
            <form action="{{ route('dashboard.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ================= USERS TABLE (core) ================= --}}
                <h5 class="mt-1 mb-3">Account</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control">
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">Active?</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label">User is active</label>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Force Logout (flag)</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="force_logout" value="1"
                                {{ old('force_logout') ? 'checked' : '' }}>
                            <label class="form-check-label">Mark to force logout</label>
                        </div>
                    </div>
                </div>

                {{-- Roles / Direct Permissions --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Hall *</label>
                        <select name="hall_id" class="form-control" required>
                            <option value="">Please Select</option>
                            @foreach ($halls as $hall)
                                <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                            @endforeach
                        </select>
                        @error('halls')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Roles *</label>
                        <select name="roles[]" class="select2-checkbox form-control" multiple
                            data-placeholder="Select roles">
                            @foreach ($roles->where('slug', '!=', 'admin') as $role)
                                <option value="{{ $role->id }}"
                                    {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Direct User Permissions</label>
                        <select name="permissions[]" class="select2-checkbox form-control" multiple
                            data-placeholder="Select permissions">
                            @foreach ($permissions as $permission)
                                <option value="{{ $permission->id }}"
                                    {{ in_array($permission->id, old('permissions', [])) ? 'selected' : '' }}>
                                    {{ $permission->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ================= USER_DETAILS (contact) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Contact</h5>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Phone 1</label><input type="text" name="phone_1"
                            class="form-control" value="{{ old('phone_1') }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone 2</label><input type="text" name="phone_2"
                            class="form-control" value="{{ old('phone_2') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Address 1</label><input type="text"
                            name="address_1" class="form-control" value="{{ old('address_1') }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Address 2</label><input type="text"
                            name="address_2" class="form-control" value="{{ old('address_2') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Country</label><input type="text" readonly
                            value="Pakistan" name="country" class="form-control" value="{{ old('country') }}"></div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">State</label>
                        <select name="state" id="state" class="form-control select2">
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" {{ old('state') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">City</label>
                        <select name="city" id="city" class="form-control select2">
                            <option value="">Select City</option>
                            @foreach ($cities as $city)
                                @if (old('state') == $city->state_id)
                                    <option value="{{ $city->id }}" {{ old('city') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3"><label class="form-label">Postal Code</label><input type="text"
                            name="postal_code" class="form-control" value="{{ old('postal_code') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Emergency Contact Name</label><input
                            type="text" name="emergency_contact_name" class="form-control"
                            value="{{ old('emergency_contact_name') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Emergency Contact Phone</label><input
                            type="text" name="emergency_contact_phone" class="form-control"
                            value="{{ old('emergency_contact_phone') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Relation</label><input type="text"
                            name="emergency_contact_relation" class="form-control"
                            value="{{ old('emergency_contact_relation') }}"></div>
                </div>

                {{-- ================= USER_DETAILS (personal) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Personal</h5>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Father Name</label><input type="text"
                            name="father_name" class="form-control" value="{{ old('father_name') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Mother Name</label><input type="text"
                            name="mother_name" class="form-control" value="{{ old('mother_name') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Spouse Name</label><input type="text"
                            name="spouse_name" class="form-control" value="{{ old('spouse_name') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Gender</label><input type="text"
                            name="gender" class="form-control" value="{{ old('gender') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Date of Birth</label><input type="date"
                            name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Marital Status</label><input type="text"
                            name="marital_status" class="form-control" value="{{ old('marital_status') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Religion</label><input type="text"
                            name="religion" class="form-control" value="{{ old('religion') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">CNIC</label><input type="text"
                            name="cnic" class="form-control" value="{{ old('cnic') }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Nationality</label><input type="text"
                            name="nationality" class="form-control" value="{{ old('nationality') }}"></div>
                </div>

                {{-- ================= USER_DETAILS (education & skills) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Education & Skills</h5>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Highest Qualification</label><input
                            type="text" name="highest_qualification" class="form-control"
                            value="{{ old('highest_qualification') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Previous Company</label><input type="text"
                            name="previous_company" class="form-control" value="{{ old('previous_company') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Previous Designation</label><input
                            type="text" name="previous_designation" class="form-control"
                            value="{{ old('previous_designation') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Experience (years)</label><input type="number"
                            name="experience_years" class="form-control" value="{{ old('experience_years') }}"></div>
                    <div class="col-md-8 mb-3"><label class="form-label">Skills (comma/line separated)</label>
                        <textarea name="skills" rows="2" class="form-control">{{ old('skills') }}</textarea>
                    </div>
                </div>

                {{-- ================= USER_DETAILS (employment) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Employment</h5>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Employee Code</label><input type="text"
                            name="employee_code" class="form-control" value="{{ old('employee_code') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Department</label><input type="text"
                            name="department" class="form-control" value="{{ old('department') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Designation</label><input type="text"
                            name="designation" class="form-control" value="{{ old('designation') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Joining Date</label><input type="date"
                            name="date_of_joining" class="form-control" value="{{ old('date_of_joining') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Resignation Date</label><input type="date"
                            name="date_of_resignation" class="form-control" value="{{ old('date_of_resignation') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Employment Status</label>
                        <select name="employment_status" class="form-select">
                            @php $es = old('employment_status', 'Active'); @endphp
                            <option value="Active" {{ $es == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $es == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Leave" {{ $es == 'Leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="Terminated" {{ $es == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                            <option value="Probation" {{ $es == 'Probation' ? 'selected' : '' }}>Probation</option>
                        </select>
                    </div>
                </div>

                {{-- ================= USER_DETAILS (financial) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Financial</h5>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Basic Salary</label><input type="number"
                            step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary') }}">
                    </div>
                    <div class="col-md-3 mb-3"><label class="form-label">Allowance</label><input type="number"
                            step="0.01" name="allowance" class="form-control" value="{{ old('allowance') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Bonus</label><input type="number"
                            step="0.01" name="bonus" class="form-control" value="{{ old('bonus') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Commission (%)</label><input type="number"
                            step="0.01" name="commission" class="form-control" value="{{ old('commission', 0) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Margin (%)</label><input type="number"
                            step="0.01" name="margin" class="form-control" value="{{ old('margin', 0) }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Discount (%)</label><input type="number"
                            step="0.01" name="discount" class="form-control" value="{{ old('discount', 0) }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Bank Name</label><input type="text"
                            name="bank_name" class="form-control" value="{{ old('bank_name') }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Bank Account Number</label><input type="text"
                            name="bank_account_number" class="form-control" value="{{ old('bank_account_number') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">IBAN</label><input type="text"
                            name="iban" class="form-control" value="{{ old('iban') }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Tax Number</label><input type="text"
                            name="tax_number" class="form-control" value="{{ old('tax_number') }}"></div>
                </div>

                {{-- ================= USER_DETAILS (referral) ================= --}}
                {{-- <hr>
                <h5 class="mt-3 mb-3">Referral</h5>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Referral Code</label><input type="text"
                            name="referral_code" class="form-control" value="{{ old('referral_code') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Referred By (User ID)</label><input
                            type="number" name="referred_by" class="form-control" value="{{ old('referred_by') }}">
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label">Referral Bonus</label><input type="number"
                            step="0.01" name="referral_bonus" class="form-control"
                            value="{{ old('referral_bonus', 0) }}"></div>
                </div> --}}

                {{-- ================= USER_DETAILS (documents) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Documents</h5>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Profile Image</label><input type="file"
                            name="profile_image" class="form-control" accept="image/*"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Resume</label><input type="file"
                            name="resume_path" class="form-control" accept=".pdf,.doc,.docx"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">CNIC Front</label><input type="file"
                            name="cnic_front_path" class="form-control" accept="image/*,application/pdf"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">CNIC Back</label><input type="file"
                            name="cnic_back_path" class="form-control" accept="image/*,application/pdf"></div>
                </div>

                {{-- ================= USER_DETAILS (misc + flags) ================= --}}
                <hr>
                <h5 class="mt-3 mb-3">Miscellaneous</h5>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Blood Group</label><input type="text"
                            name="blood_group" class="form-control" value="{{ old('blood_group') }}"></div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">Remote?</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_remote" value="1"
                                {{ old('is_remote') ? 'checked' : '' }}>
                            <label class="form-check-label">Works remotely</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Shift Timings</label>
                        <select name="shift_timings" class="form-control">
                            <option value="" disabled selected>Select Shift</option>
                            <option value="9am-6pm" {{ old('shift_timings') == '9am-6pm' ? 'selected' : '' }}>9am – 6pm
                            </option>
                            <option value="10am-7pm" {{ old('shift_timings') == '10am-7pm' ? 'selected' : '' }}>10am – 7pm
                            </option>
                            <option value="8am-5pm" {{ old('shift_timings') == '8am-5pm' ? 'selected' : '' }}>8am – 5pm
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3"><label class="form-label">Working Hours / Week</label><input
                            type="number" name="working_hours_per_week" class="form-control"
                            value="{{ old('working_hours_per_week') }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Leave Balance (days)</label><input
                            type="number" name="leave_balance" class="form-control"
                            value="{{ old('leave_balance', 0) }}"></div>
                    {{-- <div class="col-md-3 mb-3">
                        <label class="form-label d-block">Can Login?</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="can_login" value="1"
                                {{ old('can_login', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Allow system login</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">2FA Enabled?</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1"
                                {{ old('two_factor_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label">Require OTP/2FA</label>
                        </div>
                    </div> --}}
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-grd btn-grd-primary">Create User</button>
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: function() {
                    $(this).data('placeholder');
                }
            });

            $('.select2-checkbox').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Select options",
                allowClear: true,
                closeOnSelect: false, // keep dropdown open for multiple selection
                maximumSelectionLength: 50, // optional limit
                dropdownCssClass: "bigdrop", // add custom height via CSS
            });
        });

        // city / state
        const citiesByState = @json($cities->groupBy('state_id'));

        $('#state').on('change', function() {
            const stateId = $(this).val();
            const $citySelect = $('#city');

            $citySelect.empty().append('<option value="">Select City</option>');

            if (citiesByState[stateId]) {
                citiesByState[stateId]
                    .sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(city => {
                        $citySelect.append(new Option(city.name, city.id));
                    });
            }

            $citySelect.trigger('change'); // refresh select2
        });

        // $(document).ready(function() {
        //     $('.select2').select2({
        //         width: '100%',
        //         placeholder: 'Select an option',
        //         allowClear: true
        //     });
        // });
    </script>

@endsection
