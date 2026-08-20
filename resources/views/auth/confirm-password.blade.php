@extends('dashboard.includes.partial.base-auth')

@section('title', 'Confirm Password')

@section('content')
    <h1 class="auth-title">Confirm your password</h1>
    <p class="auth-subtitle">
        This is a secure area. Please re-enter your password to continue.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">error_outline</i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="auth-password">
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required
                    autocomplete="current-password" autofocus>
                <button type="button" class="btn-eye" data-toggle-password="password" aria-label="Show password">
                    <i class="material-icons-outlined fs-6">visibility</i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Confirming…">
            Confirm
        </button>
    </form>
@endsection
