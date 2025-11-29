@extends('dashboard.includes.partial.base-auth')

@section('content')
    <!--authentication-->
    <div class="auth-basic-wrapper d-flex align-items-center justify-content-center">
        <div class="container-fluid my-5 my-lg-0">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-4 mx-auto">
                    <div class="card rounded-4 mb-0 border-top border-4 border-primary border-gradient-1">
                        <div class="card-body p-5">

                            <div class="d-flex justify-content-center mb-4">
                                <img src="{{ asset('web-assets/images/logo/logo_001.png') }}" class="logo-img" alt="Logo">
                            </div>

                            <h4 class="fw-bold">Verify OTP</h4>
                            <p class="mb-0">Enter the 6-digit OTP sent to your email</p>

                            {{-- Display all errors --}}
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success mt-3">{{ session('success') }}</div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success mt-3">{{ session('status') }}</div>
                            @endif

                            <div class="form-body my-5">
                                {{-- OTP Verification Form --}}
                                <form class="row g-3" method="POST" action="{{ route('verify.otp.post') }}">
                                    @csrf

                                    <div class="col-12">
                                        <label for="otp" class="form-label">OTP Code</label>
                                        <input type="text" maxlength="6"
                                            class="form-control @error('otp') is-invalid @enderror" id="otp"
                                            name="otp" placeholder="Enter 6-digit OTP" required autofocus>
                                        @error('otp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-grd btn-grd-primary">Verify OTP</button>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center">
                                        <p class="mb-0">
                                            Didn't receive the code?
                                            <a href="{{ route('resend.otp') }}">Resend OTP</a>
                                        </p>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!--end row-->
        </div>
    </div>
    <!--authentication-->
@endsection
