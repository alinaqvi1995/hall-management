{{-- Vendor fields shared by the create and edit modals. --}}
<div class="row g-3">
    @if (auth()->user()->isSuperAdmin())
        <div class="col-md-6">
            <label class="form-label">Hall <span class="required-mark">*</span></label>
            <select name="hall_id" class="form-select" required>
                <option value="">Select hall</option>
                @foreach ($halls as $hall)
                    <option value="{{ $hall->id }}" @selected($vendor?->hall_id == $hall->id)>{{ $hall->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="hall_id" value="{{ auth()->user()->hall_id }}">
    @endif

    <div class="col-md-6">
        <label class="form-label">Contact Name <span class="required-mark">*</span></label>
        <input type="text" name="name" value="{{ $vendor->name ?? '' }}" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Company</label>
        <input type="text" name="company" value="{{ $vendor->company ?? '' }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Service Type</label>
        <input type="text" name="service_type" value="{{ $vendor->service_type ?? '' }}" class="form-control"
            placeholder="e.g. Catering, Decoration, Sound">
    </div>

    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ $vendor->phone ?? '' }}" class="form-control"
            placeholder="0300-1234567">
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ $vendor->email ?? '' }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">CNIC / NTN</label>
        <input type="text" name="cnic" value="{{ $vendor->cnic ?? '' }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" rows="2" class="form-control">{{ $vendor->address ?? '' }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="2" class="form-control">{{ $vendor->notes ?? '' }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                id="vendorActive{{ $vendor->id ?? 'new' }}" @checked($vendor->is_active ?? true)>
            <label class="form-check-label" for="vendorActive{{ $vendor->id ?? 'new' }}">
                Currently working with us
            </label>
        </div>
    </div>
</div>
