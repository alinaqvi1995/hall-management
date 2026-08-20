@extends('dashboard.includes.partial.base')

@section('title', 'New Booking')

@section('content')
    <x-page-header title="New Booking" subtitle="Reserve a lawn and quote the event" icon="event_available"
        :breadcrumbs="['Bookings' => route('bookings.index'), 'New Booking' => null]" />

    @if ($halls->isEmpty())
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="festival" title="No hall available"
                    message="Your account is not linked to an active hall, so bookings cannot be created yet." />
            </div>
        </div>
    @else
        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
            @include('bookings._form', ['isEdit' => false])
        </form>
    @endif
@endsection
