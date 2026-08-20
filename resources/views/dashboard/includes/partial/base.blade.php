<!doctype html>
{{--
    The theme is applied before first paint by the inline script in <head>, so
    a saved dark theme does not flash white on load.
--}}
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Hall Management') }}</title>
    <link rel="icon" href="{{ asset('admin/images/favicon-32x32.png') }}" type="image/png">

    <script>
        // Restore the saved theme before the first paint to avoid a flash.
        (function () {
            try {
                var saved = localStorage.getItem('hm-theme');
                if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
            } catch (e) {}
        })();
    </script>

    <link href="{{ asset('admin/css/pace.min.css') }}" rel="stylesheet">
    <script src="{{ asset('admin/js/pace.min.js') }}"></script>

    <!--plugins-->
    <link href="{{ asset('admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/plugins/metismenu/metisMenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/metismenu/mm-vertical.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/simplebar/css/simplebar.css') }}">

    <!--bootstrap-->
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- Icon font is required for the UI glyphs. The body font falls back to the
         system stack in app.css, so no webfont blocks the first paint. --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('admin/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <!--theme-->
    <link href="{{ asset('admin/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/main.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/responsive.css') }}" rel="stylesheet">

    <!--app overrides: must load last so it wins over the vendor theme-->
    <link href="{{ asset('admin/css/app.css') }}" rel="stylesheet">

    @yield('extra_css')
    @stack('styles')
</head>

<body>
    @include('dashboard.includes.partial.nav')
    @include('dashboard.includes.partial.sidebar')

    <main class="main-wrapper">
        <div class="main-content">
            @include('dashboard.includes.partial.flash')

            @yield('content')
        </div>

        @include('dashboard.includes.partial.footer')
    </main>

    <div class="overlay btn-toggle"></div>

    <!-- jQuery must load before every plugin that extends it -->
    <script src="{{ asset('admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('admin/plugins/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatable/js/datatables.core.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>
    <script src="{{ asset('admin/js/app.js') }}"></script>

    @stack('vendor_scripts')

    @yield('extra_js')
    @stack('scripts')
</body>

</html>
