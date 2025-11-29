@extends('dashboard.includes.partial.base-auth')

@section('content')
<div class="bg-register">
    <!--authentication-->
    <div class="container-fluid my-5">
        <div class="row">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-5 mx-auto">
                <div class="card rounded-4 mb-0 border-top border-4 border-primary border-gradient-1">
                    <div class="card-body p-5">
                        {{-- <img src="{{ asset(config('app.logo', 'admin/images/logo1.png')) }}" class="mb-4" width="145" alt="Logo"> --}}
                        <h4 class="fw-bold">Get Started Now</h4>
                        <p class="mb-0">Enter your credentials to create your account</p>

                        <div class="form-body my-4">
                            <form method="POST" action="{{ route('register') }}" class="row g-3">
                                @csrf
                                <div class="col-12">
                                    <label for="inputUsername" class="form-label">Username</label>
                                    <input type="text" name="name" class="form-control" id="inputUsername" value="{{ old('name') }}" placeholder="John" required>
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="inputEmailAddress" class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" id="inputEmailAddress" value="{{ old('email') }}" placeholder="example@user.com" required>
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="inputChoosePassword" class="form-label">Password</label>
                                    <div class="input-group" id="show_hide_password">
                                        <input type="password" name="password" class="form-control border-end-0" id="inputChoosePassword" placeholder="Enter Password" required>
                                        <a href="javascript:;" class="input-group-text bg-transparent"><i class="bi bi-eye-slash-fill"></i></a>
                                    </div>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm Password" required>
                                </div>
                                <div class="col-12">
                                    <label for="inputSelectCountry" class="form-label">Country</label>
                                    <select name="country" class="form-select" id="inputSelectCountry" aria-label="Default select example" required>
                                        <option value="">Select Country</option>
                                        <option value="India">India</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="USA">America</option>
                                        <option value="Dubai">Dubai</option>
                                    </select>
                                    @error('country') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="terms" id="flexSwitchCheckChecked" required>
                                        <label class="form-check-label" for="flexSwitchCheckChecked">
                                            I read and agree to Terms &amp; Conditions
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-grd-danger">Register</button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-start">
                                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="separator section-padding">
                            <div class="line"></div>
                            <p class="mb-0 fw-bold">OR</p>
                            <div class="line"></div>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="" class="wh-42 d-flex align-items-center justify-content-center rounded-circle bg-grd-danger">
                                <i class="bi bi-google fs-5 text-white"></i>
                            </a>
                            {{-- <a href="" class="wh-42 d-flex align-items-center justify-content-center rounded-circle bg-grd-deep-blue">
                                <i class="bi bi-facebook fs-5 text-white"></i>
                            </a>
                            <a href="" class="wh-42 d-flex align-items-center justify-content-center rounded-circle bg-grd-info">
                                <i class="bi bi-linkedin fs-5 text-white"></i>
                            </a>
                            <a href="" class="wh-42 d-flex align-items-center justify-content-center rounded-circle bg-grd-royal">
                                <i class="bi bi-github fs-5 text-white"></i>
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end row-->
    </div>
</div>
<!--authentication-->
@endsection
