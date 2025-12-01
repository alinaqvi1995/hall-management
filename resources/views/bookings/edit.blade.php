@extends('dashboard.includes.partial.base')
@section('title', 'Edit Booking')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Booking</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('bookings.update', $booking->id) }}" method="POST" id="bookingForm">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary">
                        <h5 class="mb-0 text-white">Edit Booking</h5>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">save</i> Save
                            </button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">cancel</i> Cancel
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="mb-3">Customer Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" value="{{ $booking->customer->name }}"
                                    class="form-control" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" value="{{ $booking->customer->phone }}"
                                    class="form-control" required>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                <input type="text" name="customer_cnic" value="{{ $booking->customer->cnic }}"
                                    class="form-control" required>
                                @error('customer_cnic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" value="{{ $booking->customer->email }}"
                                    class="form-control">
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="customer_address" class="form-control">{{ $booking->customer->address }}</textarea>
                                @error('customer_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Booking Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_datetime"
                                    value="{{ $booking->start_datetime->format('Y-m-d\TH:i') }}" class="form-control"
                                    required>
                                @error('start_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_datetime"
                                    value="{{ $booking->end_datetime->format('Y-m-d\TH:i') }}" class="form-control"
                                    required>
                                @error('end_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hall <span class="text-danger">*</span></label>
                                {{-- @if (auth()->user()->isHallAdmin()) --}}
                                <input type="text" class="form-control" value="{{ $booking->hall->name }}" disabled>
                                <input type="hidden" name="hall_id" value="{{ $booking->hall_id }}">
                                {{-- @else
                                    <select name="hall_id" class="form-select" required>
                                        <option value="">Select Hall</option>
                                        @foreach ($halls as $hall)
                                            <option value="{{ $hall->id }}"
                                                {{ $booking->hall_id == $hall->id ? 'selected' : '' }}>{{ $hall->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif --}}
                                @error('hall_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Lawn <span class="text-danger">*</span></label>
                                <select name="lawn_id" id="lawnSelect" class="form-select" required>
                                    <option value="">Select Lawn</option>
                                    @foreach ($lawns as $lawn)
                                        <option value="{{ $lawn->id }}" @if ($booking->lawn_id == $lawn->id) selected @endif>
                                            {{ $lawn->name }} ({{ $lawn->capacity }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('lawn_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" value="{{ $booking->capacity }}" class="form-control"
                                    required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Owner Quote Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="quote_price"
                                    value="{{ $booking->quote_price }}" class="form-control" required>
                                @error('quote_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Final Booking Price</label>
                                <input type="number" step="0.01" name="booking_price"
                                    value="{{ $booking->booking_price }}" class="form-control">
                                @error('booking_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Advance Paid</label>
                                <input type="number" step="0.01" name="advance_paid"
                                    value="{{ $booking->advance_paid }}" class="form-control">
                                @error('advance_paid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="partial" {{ $booking->payment_status == 'partial' ? 'selected' : '' }}>
                                        Partial</option>
                                    <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                </select>
                                @error('payment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Booking Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control">{{ $booking->notes }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Facilities</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $facilityList = [
                                            'cake',
                                            'catering',
                                            'special_entry',
                                            'decoration',
                                            'photography',
                                            'music',
                                            'lightings',
                                        ];
                                        $bookingFacilities = $booking->facilities ?? [];
                                    @endphp
                                    @foreach ($facilityList as $facility)
                                        <label class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="facilities[]"
                                                value="{{ $facility }}"
                                                {{ in_array($facility, $bookingFacilities) ? 'checked' : '' }}>
                                            <span
                                                class="form-check-label">{{ ucwords(str_replace('_', ' ', $facility)) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('facilities')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="material-icons-outlined">save</i> Save
                        </button>
                        <a href="{{ route('bookings.index') }}" class="btn btn-secondary px-4">
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
        $(document).ready(function() {
            const startInput = $('[name="start_datetime"]');
            const endInput = $('[name="end_datetime"]');
            const cnicInput = $('[name="customer_cnic"]');
            const capacityInput = $('[name="capacity"]');
            const quotePriceInput = $('[name="quote_price"]');
            const bookingPriceInput = $('[name="booking_price"]');
            const advanceInput = $('[name="advance_paid"]');

            function formatDateTimeLocal(date) {
                const pad = num => num.toString().padStart(2, '0');
                return date.getFullYear() + '-' +
                    pad(date.getMonth() + 1) + '-' +
                    pad(date.getDate()) + 'T' +
                    pad(date.getHours()) + ':' +
                    pad(date.getMinutes());
            }

            const now = new Date();
            startInput.attr('min', formatDateTimeLocal(now));

            startInput.on('change', function() {
                const startVal = new Date($(this).val());
                if (!isNaN(startVal)) {
                    endInput.attr('min', formatDateTimeLocal(startVal));
                    const currentEnd = new Date(endInput.val());
                    if (currentEnd < startVal) {
                        endInput.val($(this).val());
                    }
                }
            });

            $('#bookingForm').on('submit', function(e) {
                let valid = true;
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback.d-block').remove();

                const startDate = new Date(startInput.val());
                const endDate = new Date(endInput.val());

                function showError(input, message) {
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback d-block">' + message + '</div>');
                }

                const today = new Date();
                today.setSeconds(0, 0);
                if (startDate < today) {
                    valid = false;
                    showError(startInput, 'Start datetime must be today or later.');
                }

                if (endDate <= startDate) {
                    valid = false;
                    showError(endInput, 'End datetime must be after start datetime.');
                }

                const cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
                if (!cnicPattern.test(cnicInput.val().trim())) {
                    valid = false;
                    showError(cnicInput, 'CNIC format is invalid. Example: 12345-1234567-1');
                }

                [capacityInput, quotePriceInput, bookingPriceInput, advanceInput].forEach(function(input) {
                    const val = parseFloat(input.val());
                    if (!isNaN(val) && val < 0) {
                        valid = false;
                        showError(input, 'Value cannot be negative.');
                    }
                });

                if (!valid) e.preventDefault();
            });
        });
    </script>
@endsection
