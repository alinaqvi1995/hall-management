@extends('dashboard.includes.partial.base')
@section('title', 'Booking Created Successfully')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Booking Success</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Success Message Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="material-icons-outlined text-success" style="font-size: 80px;">check_circle</i>
                    </div>
                    <h3 class="mb-3 fw-bold text-success">Booking Created Successfully!</h3>
                    <p class="text-muted mb-4">
                        Booking #<strong>{{ $booking->formatted_booking_number }}</strong> has been created for
                        <strong>{{ $booking->customer->name }}</strong>
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-info border-0 mb-0">
                                <i class="material-icons-outlined align-middle me-2">info</i>
                                You can now send the invoice to the customer via WhatsApp or Email
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Booking Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="material-icons-outlined text-primary me-2">event</i>
                                <div>
                                    <small class="text-muted d-block">Event Date & Time</small>
                                    <strong>{{ $booking->start_datetime->format('d M, Y') }}</strong><br>
                                    <small>{{ $booking->start_datetime->format('h:i A') }} -
                                        {{ $booking->end_datetime->format('h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="material-icons-outlined text-primary me-2">place</i>
                                <div>
                                    <small class="text-muted d-block">Venue</small>
                                    <strong>{{ $booking->hall->name }}</strong><br>
                                    <small>{{ $booking->lawn->name }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="material-icons-outlined text-primary me-2">person</i>
                                <div>
                                    <small class="text-muted d-block">Customer</small>
                                    <strong>{{ $booking->customer->name }}</strong><br>
                                    <small>{{ $booking->customer->phone }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="material-icons-outlined text-primary me-2">payments</i>
                                <div>
                                    <small class="text-muted d-block">Payment</small>
                                    <strong class="text-success">Rs
                                        {{ number_format($booking->booking_price, 2) }}</strong><br>
                                    <small>Advance: Rs {{ number_format($booking->advance_paid, 2) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Send Invoice to Customer (Optional)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <i class="material-icons-outlined align-middle me-1" style="font-size: 18px;">info</i>
                        Choose how you'd like to share the invoice with your customer. Both options are optional.
                    </p>

                    <div class="row g-3 mb-4">
                        <!-- WhatsApp Option -->
                        <div class="col-md-6">
                            <div class="card border h-100 hover-shadow" style="transition: all 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bx bxl-whatsapp text-success" style="font-size: 48px;"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">Send via WhatsApp</h6>
                                    <p class="text-muted small mb-3">
                                        Opens WhatsApp Web with a pre-filled message and invoice link
                                    </p>
                                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success w-100">
                                        <i class="bx bxl-whatsapp me-2"></i>Open WhatsApp
                                    </a>
                                    <small class="text-muted d-block mt-2">
                                        <i class="material-icons-outlined" style="font-size: 14px;">phone</i>
                                        {{ $booking->customer->phone }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Gmail Option -->
                        <div class="col-md-6">
                            <div class="card border h-100 hover-shadow" style="transition: all 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bx bxl-gmail text-danger" style="font-size: 48px;"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">Send via Gmail</h6>
                                    <p class="text-muted small mb-3">
                                        Opens Gmail in Chrome with a pre-filled message and invoice link
                                    </p>
                                    <a href="{{ $gmailUrl }}" target="_blank" class="btn btn-danger w-100">
                                        <i class="bx bxl-gmail me-2"></i>Open Gmail
                                    </a>
                                    <small class="text-muted d-block mt-2">
                                        <i class="material-icons-outlined" style="font-size: 14px;">alternate_email</i>
                                        {{ $booking->customer->email ?? 'No email provided' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Download -->
                    <div class="alert alert-light border d-flex align-items-center mb-0">
                        <i class="material-icons-outlined text-danger me-3" style="font-size: 32px;">picture_as_pdf</i>
                        <div class="flex-grow-1">
                            <strong class="d-block">Invoice PDF</strong>
                            <small class="text-muted">Download or share the invoice manually</small>
                        </div>
                        <a href="{{ asset($invoiceUrl) }}" target="_blank" class="btn btn-outline-danger">
                            <i class="material-icons-outlined me-1" style="font-size: 18px;">download</i>Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-lg btn-dark">
                            <i class="material-icons-outlined me-2">visibility</i>View Full Booking Details
                        </a>
                        <a href="{{ route('bookings.index') }}" class="btn btn-lg btn-outline-secondary">
                            <i class="material-icons-outlined me-2">list</i>Back to Bookings
                        </a>
                        @can('create-bookings')
                            <a href="{{ route('bookings.create') }}" class="btn btn-lg btn-outline-primary">
                                <i class="material-icons-outlined me-2">add</i>Create Another Booking
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }
    </style>
@endsection
