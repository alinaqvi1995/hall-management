@extends('dashboard.includes.partial.base')

@section('title', 'Edit Booking '.$booking->formatted_booking_number)

@section('content')
    <x-page-header :title="'Edit '.$booking->formatted_booking_number"
        :subtitle="($booking->customer->name ?? 'Customer').' · '.($booking->start_datetime?->format('d M Y'))"
        icon="edit_calendar"
        :breadcrumbs="[
            'Bookings' => route('bookings.index'),
            $booking->formatted_booking_number => route('bookings.show', $booking),
            'Edit' => null,
        ]" />

    <form action="{{ route('bookings.update', $booking) }}" method="POST" id="bookingForm">
        @include('bookings._form', ['isEdit' => true])
    </form>
@endsection
