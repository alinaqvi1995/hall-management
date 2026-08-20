<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') · {{ config('app.name', 'Hall Management') }}</title>
    <link rel="icon" href="{{ asset('admin/images/favicon-32x32.png') }}" type="image/png">

    <script>
        // Match the theme the user picked inside the app.
        (function () {
            try {
                var saved = localStorage.getItem('hm-theme');
                if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
            } catch (e) {}
        })();
    </script>

    <link href="{{ asset('admin/css/pace.min.css') }}" rel="stylesheet">
    <script src="{{ asset('admin/js/pace.min.js') }}"></script>

    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined&display=swap"
        rel="stylesheet">

    <link href="{{ asset('admin/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/main.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/sass/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/auth.css') }}" rel="stylesheet">

    @yield('extra_css')
</head>

<body class="auth-body">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-brand__mark"><i class="material-icons-outlined">festival</i></span>
                <div>
                    <p class="auth-brand__name">{{ config('app.name', 'Hall Management') }}</p>
                    <p class="auth-brand__tag">Marquee &amp; banquet management</p>
                </div>
            </div>

            @yield('content')
        </div>

        <p class="auth-footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Hall Management') }} · Pakistan
        </p>
    </div>

    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery.min.js') }}"></script>
    <script>
        // Password visibility toggles. Scoped per field so a page with several
        // password inputs works, unlike the previous global selector.
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                var input = document.getElementById(button.dataset.togglePassword);
                if (!input) return;

                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.querySelector('i').textContent = show ? 'visibility_off' : 'visibility';
            });
        });
    </script>

    @yield('extra_js')
</body>

</html>
