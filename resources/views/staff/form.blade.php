@extends('dashboard.includes.partial.base')

@php $isEdit = $member->exists; @endphp

@section('title', $isEdit ? 'Edit '.$member->name : 'Add Staff')

@section('content')
    <x-page-header :title="$isEdit ? 'Edit '.$member->name : 'Add Staff Member'"
        subtitle="Employment details and contact information" icon="badge"
        :breadcrumbs="['Staff' => route('staff.index'), $isEdit ? 'Edit' : 'Add Staff' => null]" />

    <form method="POST" action="{{ $isEdit ? route('staff.update', $member) : route('staff.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">Staff Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label">Hall <span class="required-mark">*</span></label>
                                    <select name="hall_id"
                                        class="form-select @error('hall_id') is-invalid @enderror" required>
                                        <option value="">Select hall</option>
                                        @foreach ($halls as $hall)
                                            <option value="{{ $hall->id }}" @selected(old('hall_id', $member->hall_id) == $hall->id)>
                                                {{ $hall->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('hall_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="hall_id" value="{{ auth()->user()->hall_id }}">
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $member->name) }}"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation"
                                    value="{{ old('designation', $member->designation) }}"
                                    class="form-control @error('designation') is-invalid @enderror"
                                    placeholder="e.g. Head Waiter, Chef, Security Guard">
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="phone" value="{{ old('phone', $member->phone) }}"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    placeholder="0300-1234567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">CNIC</label>
                                <input type="text" name="cnic" value="{{ old('cnic', $member->cnic) }}"
                                    class="form-control @error('cnic') is-invalid @enderror"
                                    placeholder="35201-1234567-1">
                                @error('cnic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Employment Type <span class="required-mark">*</span></label>
                                <select name="employment_type"
                                    class="form-select @error('employment_type') is-invalid @enderror" required>
                                    @foreach (\App\Models\Staff::EMPLOYMENT_TYPES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('employment_type', $member->employment_type ?? 'permanent') === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('employment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Monthly Salary</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="monthly_salary"
                                        value="{{ old('monthly_salary', $member->monthly_salary) }}"
                                        class="form-control @error('monthly_salary') is-invalid @enderror">
                                </div>
                                <div class="form-text">Leave blank for daily-wage staff.</div>
                                @error('monthly_salary')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Joined On</label>
                                <input type="date" name="joined_on" max="{{ now()->toDateString() }}"
                                    value="{{ old('joined_on', $member->joined_on?->format('Y-m-d')) }}"
                                    class="form-control @error('joined_on') is-invalid @enderror">
                                @error('joined_on')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" rows="2"
                                    class="form-control @error('address') is-invalid @enderror">{{ old('address', $member->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="2"
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $member->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="isActive" @checked(old('is_active', $member->is_active ?? true))>
                                    <label class="form-check-label" for="isActive">Currently employed</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="submit" class="btn btn-primary"
                            data-loading-text="{{ $isEdit ? 'Updating…' : 'Saving…' }}">
                            <i class="material-icons-outlined fs-6 align-middle">save</i>
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
