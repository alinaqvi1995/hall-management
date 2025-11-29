@extends('emails.layouts.app')

@section('title', "Quote Update - Quote #{$quote->id}")
@section('header', 'Your Quote Has Been Updated')

@section('content')
    <!-- Greeting -->
    <p style="font-size:16px; color:#333; margin-bottom:15px;">
        Hello <strong>{{ $quote->customer_name }}</strong>,
    </p>

    <!-- Update Message -->
    <p style="font-size:15px; color:#555; line-height:22px; margin-bottom:20px;">
        We wanted to let you know that your quote 
        <strong style="color:#1a73e8;">#{{ $quote->id }}</strong> has been updated.
    </p>

    <!-- Current Status Highlight -->
    <p style="font-size:15px; margin:20px 0; padding:12px 18px; background:#f4f8ff; 
              border-left:4px solid #1a73e8; color:#333; border-radius:4px;">
        <strong>Current Status:</strong> 
        <span style="font-weight:600; color:#1a73e8;">{{ $quote->status }}</span>
    </p>

    <!-- Conditional Messages -->
    @if (in_array($quote->status, ['Booked', 'Dispatch', 'Pickup', 'Delivery']))
        <p style="font-size:14px; color:#555; line-height:22px;">
            Our team will keep you informed as your order progresses.
        </p>
    @elseif($quote->status === 'Completed')
        <p style="font-size:14px; color:#555; line-height:22px;">
            ✅ Thank you for choosing us! Your order has been successfully completed.
        </p>
    @elseif($quote->status === 'Cancelled')
        <p style="font-size:14px; color:#b00020; line-height:22px;">
            ❌ Your order has been cancelled. If this is unexpected, please contact us immediately.
        </p>
    @else
        <p style="font-size:14px; color:#555; line-height:22px;">
            We’ll keep you updated on the next steps.
        </p>
    @endif

    <hr style="border:none; border-top:1px solid #e5e5e5; margin:35px 0;">

    <!-- Quote Summary -->
    <h3 style="margin-bottom:12px; font-size:18px; font-weight:600; color:#1a73e8;">
        Quote Summary
    </h3>
    <p style="font-size:14px; color:#444; margin-bottom:20px; line-height:22px;">
        <strong>Email:</strong> {{ $quote->customer_email }}<br>
        <strong>Phone:</strong> {{ $quote->customer_phone }}
    </p>

    <!-- Vehicles -->
    <h4 style="margin:20px 0 10px; font-size:16px; font-weight:600; color:#333;">
        Vehicles
    </h4>
    <ul style="margin:0; padding-left:20px; font-size:14px; color:#555; line-height:22px;">
        @foreach ($quote->vehicles as $vehicle)
            <li>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</li>
        @endforeach
    </ul>

    <!-- Pickup -->
    <h4 style="margin:25px 0 10px; font-size:16px; font-weight:600; color:#333;">
        Pickup
    </h4>
    <p style="font-size:14px; color:#555; margin:0; line-height:22px;">
        {{ $quote->pickupLocation->full_location }} <br>
        <span style="color:#1a73e8;">{{ $quote->pickup_date_formatted }}</span>
    </p>

    <!-- Delivery -->
    <h4 style="margin:25px 0 10px; font-size:16px; font-weight:600; color:#333;">
        Delivery
    </h4>
    <p style="font-size:14px; color:#555; margin:0; line-height:22px;">
        {{ $quote->deliveryLocation->full_location }} <br>
        <span style="color:#1a73e8;">{{ $quote->delivery_date_formatted }}</span>
    </p>

    <!-- Footer Note -->
    <p style="margin-top:35px; font-size:13px; color:#777; line-height:20px; text-align:center;">
        If you have any questions, just reply to this email or call our support team. <br>
        We’re here to help anytime! 🤝
    </p>
@endsection
