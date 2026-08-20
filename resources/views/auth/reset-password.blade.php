@extends('dashboard.includes.partial.base-auth')

@section('title', 'Reset Password')

@section('content')
    <h1 class="auth-title">Choose a new password</h1>
    <p class="auth-subtitle">Pick something you have not used before.</p>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="material-icons-outlined fs-6">error_outline</i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                class="form-control @error('email') is-invalid @enderror" required autofocus
                autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="auth-password">
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required
                    autocomplete="new-password" minlength="8">
                <button type="button" class="btn-eye" data-toggle-password="password" aria-label="Show password">
                    <i class="material-icons-outlined fs-6">visibility</i>
                </button>
            </div>
            <div class="form-text">At least 8 characters.</div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <div class="auth-password">
                <input id="password_confirmation" type="password" name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror" required
                    autocomplete="new-password">
                <button type="button" class="btn-eye" data-toggle-password="password_confirmation"
                    aria-label="Show password">
                    <i class="material-icons-outlined fs-6">visibility</i>
                </button>
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Saving…">
            Reset Password
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to sign in</a>
    </div>
@endsection
