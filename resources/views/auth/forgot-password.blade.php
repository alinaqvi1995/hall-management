@extends('dashboard.includes.partial.base-auth')

@section('title', 'Forgot Password')

@section('content')
    <h1 class="auth-title">Forgot your password?</h1>
    <p class="auth-subtitle">
        Enter the email address on your account and we will send you a link to choose a new password.
    </p>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">check_circle</i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">error_outline</i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror" required autofocus
                autocomplete="username" placeholder="you@example.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Sending…">
            Email Password Reset Link
        </button>
    </form>

    <div class="auth-links">
        Remembered it? <a href="{{ route('login') }}">Back to sign in</a>
    </div>
@endsection
