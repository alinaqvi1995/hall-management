@extends('layouts.guest')
@section('title', 'Thank You - Service Site')
@section('meta_description', 'Your quote request has been submitted successfully. Our team will contact you shortly.')
@section('meta_keywords', 'thank you, quote submitted, success')

@section('content')
    <!--========== breadcrumb Start ==============-->
    <section class="breadcrumb-wrapper" data-bg-image="{{ asset('web-assets/images/banner/cta-bg.webp') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-content">
                        <h1 class="breadcrumb-title text-center mb-5">Thank You</h1>
                        <div class="breadcrumb-link">
                            <span>
                                <a href="{{ route('home') }}">
                                    <span>Home</span>
                                </a>
                            </span>
                            >
                            <span>
                                <span>Thank You</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--========== breadcrumb End ==============-->

    <!--========== Thank You Section Start ==============-->
    <section class="tj-about-section-three py-5">
        <div class="container">
            <div class="row align-items-center">
                <!-- Left Content -->
                <div class="col-lg-12" data-sal="slide-left" data-sal-duration="800">
                    <div class="about-content-three">
                        <div class="tj-section-heading text-center">
                            <span class="sub-title active-shape">We Appreciate Your Trust</span>
                            <h2 class="title">Thank You for Your Request</h2>
                            <p class="desc">
                                Your quote request has been submitted successfully. Our team is reviewing your details and will contact you shortly with the best possible solution.
                            </p>
                        </div>
                        <div class="content-box d-flex align-items-center justify-content-around">
                            <div class="tj-icon-box">
                                <div class="ab-text d-flex align-items-center">
                                    <div class="ab-icon">
                                        <img src="{{ asset('web-assets/images/icon/global.svg') }}" alt="Support Icon" />
                                    </div>
                                    <div class="ab-title">
                                        <h5 class="title">What’s Next?</h5>
                                    </div>
                                </div>
                                <p class="desc">
                                    One of our representatives will reach out to confirm the details and guide you through the next steps.
                                </p>
                            </div>
                            <div class="tj-icon-box">
                                <div class="ab-text d-flex align-items-center">
                                    <div class="ab-icon">
                                        <img src="{{ asset('web-assets/images/icon/winner.svg') }}" alt="Commitment Icon" />
                                    </div>
                                    <div class="ab-title">
                                        <h5 class="title">Our Commitment</h5>
                                    </div>
                                </div>
                                <p class="desc">
                                    We aim to deliver quick responses, transparent communication, and reliable transport services tailored to your needs.
                                </p>
                            </div>
                            <div class="tj-icon-box">
                                <div class="ab-text d-flex align-items-center">
                                    <div class="ab-icon">
                                        <img src="{{ asset('web-assets/images/icon/global.svg') }}" alt="Support Icon" />
                                    </div>
                                    <div class="ab-title">
                                        <h5 class="title">What’s Next?</h5>
                                    </div>
                                </div>
                                <p class="desc">
                                    One of our representatives will reach out to confirm the details and guide you through the next steps.
                                </p>
                            </div>
                        </div>
                        <div class="tj-theme-button mt-4  text-center">
                            <a class="tj-primary-btn" href="{{ route('home') }}">
                                Back to Home <i class="flaticon-right-1"></i>
                            </a>
                            <a class="tj-transparent-btn ms-3" href="{{ route('all_services.index') }}">
                                Explore Services <i class="flaticon-right-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--========== Thank You Section End ==============-->


@endsection
