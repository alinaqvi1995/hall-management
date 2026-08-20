@extends('dashboard.includes.partial.base-auth')

@section('title', 'Verify OTP')

@section('content')
    <h1 class="auth-title">Verify your code</h1>
    <p class="auth-subtitle">Enter the 6-digit code we sent to your email address.</p>

    @if (session('success') || session('status'))
        <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
            <i class="material-icons-outlined fs-6">check_circle</i>
            <span>{{ session('success') ?? session('status') }}</span>
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

    <form method="POST" action="{{ route('verify.otp.post') }}">
        @csrf

        <div class="mb-4">
            <label for="otp" class="form-label">Verification Code</label>
            <input id="otp" type="text" name="otp" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                class="form-control otp-input @error('otp') is-invalid @enderror" placeholder="000000" required
                autofocus autocomplete="one-time-code">
            @error('otp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth" data-loading-text="Verifying…">
            Verify Code
        </button>
    </form>

    <div class="auth-links">
        Didn&rsquo;t get the code? <a href="{{ route('resend.otp') }}">Send it again</a>
    </div>
@endsection

@section('extra_js')
    <script>
        // Keep the field numeric and submit automatically once six digits are in,
        // which is what people expect from an OTP box.
        (function () {
            var input = document.getElementById('otp');
            if (!input) return;

            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);

                if (this.value.length === 6) {
                    this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit();
                }
            });
        })();
    </script>
@endsection
