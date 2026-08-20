{{--
    Add-on fields shared by the create and edit modals.
    `old()` is not used here: several edit modals coexist on the page, so a
    failed validation must not spray one form's input across all of them.
--}}
<div class="row g-3">
    @if (auth()->user()->isSuperAdmin())
        <div class="col-12">
            <label class="form-label">Hall <span class="required-mark">*</span></label>
            <select name="hall_id" class="form-select" required>
                <option value="">Select hall</option>
                @foreach ($halls as $hall)
                    <option value="{{ $hall->id }}" @selected($addon?->hall_id == $hall->id)>{{ $hall->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="hall_id" value="{{ auth()->user()->hall_id }}">
    @endif

    <div class="col-12">
        <label class="form-label">Service Name <span class="required-mark">*</span></label>
        <input type="text" name="name" value="{{ $addon->name ?? '' }}" class="form-control"
            placeholder="e.g. Stage Decoration" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Price <span class="required-mark">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" min="0" name="price" value="{{ $addon->price ?? '' }}"
                class="form-control" required>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Charged <span class="required-mark">*</span></label>
        <select name="pricing_mode" class="form-select" required>
            @foreach (\App\Models\Addon::PRICING_MODES as $key => $label)
                <option value="{{ $key }}" @selected(($addon->pricing_mode ?? 'fixed') === $key)>{{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Per head multiplies by the guest count.</div>
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <input type="text" name="description" value="{{ $addon->description ?? '' }}" class="form-control">
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                id="addonActive{{ $addon->id ?? 'new' }}" @checked($addon->is_active ?? true)>
            <label class="form-check-label" for="addonActive{{ $addon->id ?? 'new' }}">
                Available for new bookings
            </label>
        </div>
    </div>
</div>
