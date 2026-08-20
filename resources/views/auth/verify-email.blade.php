@extends('dashboard.includes.partial.base-auth')

@section('title', 'Verify Email')

@section('content')
    <h1 class="auth-title">Verify your email</h1>
    <p class="auth-subtitle">
        Thanks for signing up. Please confirm your email address by clicking the link we just sent you.
        If it did not arrive, we will gladly send another.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">check_circle</i>
            <span>A new verification link has been sent to your email address.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Sending…">
            Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-auth">Log Out</button>
    </form>
@endsection
