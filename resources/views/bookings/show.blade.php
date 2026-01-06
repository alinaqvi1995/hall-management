@extends('dashboard.includes.partial.base')
@section('title', 'Booking Details')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Booking #{{ $booking->booking_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <a href="{{ route('bookings.invoice', $booking->id) }}" class="btn btn-grd btn-grd-info" target="_blank">
                    <i class="material-icons-outlined">receipt</i> Invoice
                </a>
                <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-grd btn-grd-warning">
                    <i class="material-icons-outlined">edit</i> Edit
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Booking Details -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Booking Information</h5>
                    <span class="badge bg-light text-primary text-uppercase">{{ $booking->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Booking Number</label>
                            <p>{{ $booking->booking_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hall / Lawn</label>
                            <p>{{ $booking->hall->name ?? '-' }} / {{ $booking->lawn->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date & Time</label>
                            <p>{{ $booking->start_datetime?->format('d M Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date & Time</label>
                            <p>{{ $booking->end_datetime?->format('d M Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Capacity / Guests</label>
                            <p>{{ $booking->capacity }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status</label>
                            <p>
                                <span
                                    class="badge {{ $booking->payment_status === 'paid' ? 'bg-success' : ($booking->payment_status === 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Facilities</label>
                            <div>
                                @if (!empty($booking->facilities))
                                    @foreach ($booking->facilities as $facility)
                                        <span
                                            class="badge bg-secondary me-1">{{ ucwords(str_replace('_', ' ', $facility)) }}</span>
                                    @endforeach
                                @else
                                    <p>No facilities specified.</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <p class="text-muted">{{ $booking->notes ?? 'No notes provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Financials Sidebar -->
        <div class="col-12 col-lg-4">
            <!-- Customer Card -->
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0 text-white">Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <label class="fw-bold">Name:</label>
                            <span>{{ $booking->customer->name ?? '-' }}</span>
                        </div>
                        <div>
                            <label class="fw-bold">Phone:</label>
                            <span>{{ $booking->customer->phone ?? '-' }}</span>
                        </div>
                        <div>
                            <label class="fw-bold">CNIC:</label>
                            <span>{{ $booking->customer->cnic ?? '-' }}</span>
                        </div>
                        <div>
                            <label class="fw-bold">Email:</label>
                            <span>{{ $booking->customer->email ?? '-' }}</span>
                        </div>
                        <div>
                            <label class="fw-bold">Address:</label>
                            <p class="mb-0 small">{{ $booking->customer->address ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financials Card -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 text-white">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td>Booking Price:</td>
                                    <td class="text-end fw-bold">Rs {{ number_format($booking->booking_price ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Advance Paid:</td>
                                    <td class="text-end text-success fw-bold">- Rs
                                        {{ number_format($booking->advance_paid ?? 0, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Balance Due:</td>
                                    <td class="text-end text-danger fw-bold fs-5">Rs
                                        {{ number_format(($booking->booking_price ?? 0) - ($booking->advance_paid ?? 0), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
