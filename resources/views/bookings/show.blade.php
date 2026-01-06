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
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Booking Details</h5>
                    <div class="btn-group">
                        @can('edit-bookings')
                            <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-light btn-sm">
                                <i class="material-icons-outlined">edit</i> Edit
                            </a>
                        @endcan
                        <a href="{{ route('bookings.invoice', $booking->id) }}" class="btn btn-light btn-sm"
                            target="_blank">
                            <i class="material-icons-outlined">print</i> Invoice
                        </a>
                        <a href="{{ route('bookings.index') }}" class="btn btn-light btn-sm">
                            <i class="material-icons-outlined">arrow_back</i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-12 col-lg-8">
                            <!-- Booking Information -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3 fw-bold text-primary">Booking Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">Booking Number</small>
                                            <strong>{{ $booking->booking_number }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">Status</small>
                                            <span
                                                class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : ($booking->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">Hall / Lawn</small>
                                            <strong>{{ $booking->hall->name ?? '-' }} /
                                                {{ $booking->lawn->name ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">Guests</small>
                                            <strong>{{ $booking->capacity }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">Start Date & Time</small>
                                            <strong>{{ $booking->start_datetime?->format('d M Y h:i A') }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-1">End Date & Time</small>
                                            <strong>{{ $booking->end_datetime?->format('d M Y h:i A') }}</strong>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">Facilities</small>
                                            @if (!empty($booking->facilities))
                                                @foreach ($booking->facilities as $facility)
                                                    <span
                                                        class="badge bg-secondary me-1">{{ ucwords(str_replace('_', ' ', $facility)) }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">Notes</small>
                                            <p class="mb-0">{{ $booking->notes ?? 'No notes provided.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-12 col-lg-4">
                            <!-- Customer Details -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3 fw-bold text-primary">Customer Details</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Name:</small>
                                        <strong>{{ $booking->customer->name ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Phone:</small>
                                        <strong>{{ $booking->customer->phone ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">CNIC:</small>
                                        <strong>{{ $booking->customer->cnic ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Email:</small>
                                        <strong class="text-end"
                                            style="max-width: 60%;">{{ $booking->customer->email ?? '-' }}</strong>
                                    </div>
                                    <div class="border-top pt-2 mt-2">
                                        <small class="text-muted d-block mb-1">Address:</small>
                                        <p class="mb-0">{{ $booking->customer->address ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Summary -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3 fw-bold text-primary">Financial Summary</h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Booking Price:</small>
                                        <h6 class="mb-0">Rs {{ number_format($booking->booking_price ?? 0, 2) }}</h6>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Advance Paid:</small>
                                        <h6 class="mb-0 text-success">Rs
                                            {{ number_format($booking->advance_paid ?? 0, 2) }}</h6>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                                        <small class="text-muted fw-bold">Balance Due:</small>
                                        <h5 class="mb-0 text-danger fw-bold">Rs
                                            {{ number_format(($booking->booking_price ?? 0) - ($booking->advance_paid ?? 0), 2) }}
                                        </h5>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Payment Status:</small>
                                        <span
                                            class="badge {{ $booking->payment_status === 'paid' ? 'bg-success' : ($booking->payment_status === 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
