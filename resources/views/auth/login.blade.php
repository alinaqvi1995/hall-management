@extends('dashboard.includes.partial.base-auth')

@section('title', 'Sign In')

@section('content')
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to manage your bookings and payments.</p>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">check_circle</i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required
                autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="auth-password">
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password"
                    required autocomplete="current-password">
                <button type="button" class="btn-eye" data-toggle-password="password" aria-label="Show password">
                    <i class="material-icons-outlined fs-6">visibility</i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Signing in…">
            Sign In
        </button>
    </form>

    @if (Route::has('register'))
        <div class="auth-links">
            Don&rsquo;t have an account? <a href="{{ route('register') }}">Create one</a>
        </div>
    @endif
@endsection
