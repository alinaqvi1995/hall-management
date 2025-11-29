<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta content="Admin & Dashboard" name="description">
        <meta content="Dashboard" name="author">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- App favicon -->
        <link rel="shortcut icon" href="admin-assets/images/favicon.ico">
        
        <!-- Fonts css load -->
        <link rel="preconnect" href="https://fonts.googleapis.com/">
        <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
        <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">    <!--Swiper slider css-->
        <link href="admin-assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

        <!-- Layout config Js -->
        <script src="admin-assets/js/layout.js"></script>
        <!-- Bootstrap Css -->
        <link href="admin-assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <!-- Icons Css -->
        <link href="admin-assets/css/icons.min.css" rel="stylesheet" type="text/css">
        <!-- App Css-->
        <link href="admin-assets/css/app.min.css" rel="stylesheet" type="text/css">
        <!-- custom Css-->
        <link href="admin-assets/css/custom.min.css" rel="stylesheet" type="text/css">

    </head>
    <body>

        <!-- Begin page -->
        <div id="layout-wrapper">
                @include('partials.admin.header')
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
            <div class="main-content">
                        {{-- {{ $slot }} --}}
                    @yield('content')
                @include('partials.admin.footer')
            </div>  
        </div>
        @include('partials.admin.extra')
                <!-- JAVASCRIPT -->
        <script src="admin-assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="admin-assets/libs/simplebar/dist/simplebar.min.js"></script>
        <script src="admin-assets/js/plugins.js"></script>    
        <script src="admin-assets/libs/list.js/dist/list.min.js"></script>
        <!--Swiper slider js-->
        <script src="admin-assets/libs/swiper/swiper-bundle.min.js"></script>
        <!-- apexcharts -->
        <script src="admin-assets/libs/apexcharts/dist/apexcharts.min.js"></script>
        <!--dashboard doctor init js-->
        <script src="admin-assets/js/pages/dashboard-doctor.init.js"></script>
        <!-- App js -->
        <script src="admin-assets/js/app.js"></script>
    </body>
</html>