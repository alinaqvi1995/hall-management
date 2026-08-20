@extends('dashboard.includes.partial.base')

@php
    $isEdit = $package->exists;
    $items = old('items', $package->items ?? []);
@endphp

@section('title', $isEdit ? 'Edit '.$package->name : 'Add Package')

@section('content')
    <x-page-header :title="$isEdit ? 'Edit '.$package->name : 'Add Package'"
        subtitle="A named menu with a per-head rate" icon="restaurant_menu"
        :breadcrumbs="['Packages' => route('packages.index'), $isEdit ? 'Edit' : 'Add Package' => null]" />

    <form method="POST"
        action="{{ $isEdit ? route('packages.update', $package) : route('packages.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">Package Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label">Hall <span class="required-mark">*</span></label>
                                    <select name="hall_id"
                                        class="form-select @error('hall_id') is-invalid @enderror" required>
                                        <option value="">Select hall</option>
                                        @foreach ($halls as $hall)
                                            <option value="{{ $hall->id }}" @selected(old('hall_id', $package->hall_id) == $hall->id)>
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
                                <label class="form-label">Package Name <span class="required-mark">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $package->name) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. Premium Buffet" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Type <span class="required-mark">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror"
                                    required>
                                    @foreach (\App\Models\Package::TYPES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('type', $package->type) === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Per-Head Rate <span class="required-mark">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="per_head_rate"
                                        value="{{ old('per_head_rate', $package->per_head_rate) }}"
                                        class="form-control @error('per_head_rate') is-invalid @enderror" required>
                                </div>
                                @error('per_head_rate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Minimum Guests</label>
                                <input type="number" min="0" name="min_guests"
                                    value="{{ old('min_guests', $package->min_guests) }}"
                                    class="form-control @error('min_guests') is-invalid @enderror">
                                <div class="form-text">Bookings below this are rejected.</div>
                                @error('min_guests')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="2"
                                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $package->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Menu Items</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                            <i class="material-icons-outlined fs-6 align-middle">add</i>
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-secondary small">Listed on the invoice and the booking page.</p>
                        <div id="itemRows">
                            @foreach ($items as $item)
                                <div class="input-group input-group-sm mb-2 item-row">
                                    <input type="text" name="items[]" value="{{ $item }}" class="form-control"
                                        placeholder="e.g. Chicken Karahi">
                                    <button type="button" class="btn btn-outline-danger remove-item">
                                        <i class="material-icons-outlined fs-6">close</i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="isActive" @checked(old('is_active', $package->is_active ?? true))>
                            <label class="form-check-label" for="isActive">Available for new bookings</label>
                        </div>
                        <div class="form-text">
                            Turn this off to retire a package without affecting existing bookings.
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"
                            data-loading-text="{{ $isEdit ? 'Updating…' : 'Saving…' }}">
                            <i class="material-icons-outlined fs-6 align-middle">save</i>
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var rows = document.getElementById('itemRows');

            function addRow() {
                rows.insertAdjacentHTML('beforeend',
                    '<div class="input-group input-group-sm mb-2 item-row">'
                    + '<input type="text" name="items[]" class="form-control" placeholder="e.g. Chicken Karahi">'
                    + '<button type="button" class="btn btn-outline-danger remove-item">'
                    + '<i class="material-icons-outlined fs-6">close</i></button></div>');
                // Focus the new field so several items can be typed in a row.
                rows.lastElementChild.querySelector('input').focus();
            }

            document.getElementById('addItem').addEventListener('click', addRow);

            rows.addEventListener('click', function (event) {
                var button = event.target.closest('.remove-item');
                if (button) button.closest('.item-row').remove();
            });

            // Enter adds the next item instead of submitting the whole form.
            rows.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && event.target.matches('input[name="items[]"]')) {
                    event.preventDefault();
                    addRow();
                }
            });

            if (!rows.querySelector('.item-row')) {
                addRow();
            }
        })();
    </script>
@endpush
