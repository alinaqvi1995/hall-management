@extends('dashboard.includes.partial.base-auth')

@section('content')
    <!--authentication-->
    <div class="auth-basic-wrapper d-flex align-items-center justify-content-center">
        <div class="container-fluid my-5 my-lg-0">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-4 mx-auto">
                    <div class="card rounded-4 mb-0 border-top border-4 border-primary border-gradient-1">
                        <div class="card-body p-5">

                            {{-- <div class="d-flex justify-content-center mb-4">
                            <img src="{{ asset('web-assets/images/logo/logo_001.png') }}" 
                                class="logo-img" 
                                alt="Logo">
                        </div> --}}
                            <div class="d-flex justify-content-center mb-4">
                                <h3 class="mb-0" style="color: #FC5523 !important">Hall Management</h3>
                            </div>
                            <h4 class="fw-bold">Get Started Now</h4>
                            <p class="mb-0">Enter your credentials to login your account</p>

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

                            <div class="form-body my-5">
                                {{-- Laravel login route --}}
                                <form class="row g-3" method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="col-12">
                                        <label for="inputEmailAddress" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="inputEmailAddress" name="email" value="{{ old('email') }}"
                                            placeholder="jhon@example.com" required autofocus>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="inputChoosePassword" class="form-label">Password</label>
                                        <div class="input-group" id="show_hide_password">
                                            <input type="password"
                                                class="form-control border-end-0 @error('password') is-invalid @enderror"
                                                id="inputChoosePassword" name="password" placeholder="Enter Password"
                                                required>
                                            <a href="javascript:;" class="input-group-text bg-transparent">
                                                <i class="bi bi-eye-slash-fill"></i>
                                            </a>
                                            @error('password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="remember"
                                                id="flexSwitchCheckChecked" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                                        </div>
                                    </div>

                                    {{-- <div class="col-md-6 text-end">
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}">Forgot Password ?</a>
                                    @endif
                                </div> --}}

                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-grd btn-grd-primary">Login</button>
                                        </div>
                                    </div>

                                    {{-- <div class="col-12">
                                    <div class="text-start">
                                        <p class="mb-0">
                                            Don't have an account yet? 
                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}">Sign up here</a>
                                            @endif
                                        </p>
                                    </div>
                                </div> --}}
                                </form>
                            </div>

                            {{-- <div class="separator section-padding">
                            <div class="line"></div>
                            <p class="mb-0 fw-bold">OR SIGN IN WITH</p>
                            <div class="line"></div>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="{{ url('/auth/google') }}" class="wh-42 d-flex align-items-center justify-content-center rounded-circle bg-grd-danger">
                                <i class="bi bi-google fs-5 text-white"></i>
                            </a>
                        </div> --}}

                        </div>
                    </div>
                </div>
            </div><!--end row-->
        </div>
    </div>
    <!--authentication-->
@endsection
