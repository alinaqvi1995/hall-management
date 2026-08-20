@php
    $user = auth()->user();
    $counts = $sidebarCounts ?? [];
@endphp

<!--start header-->
<header class="top-header">
    <nav class="navbar navbar-expand align-items-center gap-3">
        <div class="btn-toggle">
            <a href="javascript:;" aria-label="Toggle menu"><i class="material-icons-outlined">menu</i></a>
        </div>

        {{-- Venue context: which hall the numbers on screen belong to. --}}
        <div class="d-none d-md-flex align-items-center gap-2 topbar-context">
            <i class="material-icons-outlined text-primary">place</i>
            <div class="lh-sm">
                <small class="d-block text-secondary">Viewing</small>
                <span class="fw-semibold">
                    {{ $user?->isSuperAdmin() ? 'All venues' : ($user?->hall->name ?? 'No venue linked') }}
                </span>
            </div>
        </div>

        <div class="flex-grow-1"></div>

        <ul class="navbar-nav gap-1 align-items-center">
            {{-- Quick add --}}
            @can('create-bookings')
                <li class="nav-item d-none d-sm-block">
                    <a class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                        href="{{ route('bookings.create') }}">
                        <i class="material-icons-outlined fs-6">add</i>
                        <span>New Booking</span>
                    </a>
                </li>
            @endcan

            {{-- Today's events --}}
            @can('view-bookings')
                <li class="nav-item">
                    <a class="nav-link topbar-icon" href="{{ route('reports.dailySheet') }}"
                        title="Today's events">
                        <i class="material-icons-outlined">today</i>
                    </a>
                </li>
            @endcan

            {{-- Theme toggle: persists the choice, unlike the old customizer. --}}
            <li class="nav-item">
                <button type="button" class="nav-link topbar-icon btn border-0" id="themeToggle"
                    title="Switch light / dark theme" aria-label="Switch theme">
                    <i class="material-icons-outlined" data-theme-icon>dark_mode</i>
                </button>
            </li>

            {{-- User menu --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret d-flex align-items-center gap-2"
                    href="javascript:;" data-bs-toggle="dropdown">
                    <span class="topbar-avatar">{{ mb_substr($user->name ?? '?', 0, 1) }}</span>
                    <span class="d-none d-lg-block lh-sm text-start">
                        <span class="d-block fw-semibold">{{ $user->name ?? 'Account' }}</span>
                        <small class="text-secondary">
                            {{ $user?->roles->pluck('name')->join(', ') ?: 'No role' }}
                        </small>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="px-3 py-2 border-bottom">
                        <p class="mb-0 fw-semibold">{{ $user->name ?? '' }}</p>
                        <small class="text-secondary">{{ $user->email ?? '' }}</small>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                            <i class="material-icons-outlined fs-6">person</i> My Profile
                        </a>
                    </li>
                    @can('view-trustedIps')
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('trusted-ips.index') }}">
                                <i class="material-icons-outlined fs-6">security</i> Trusted IPs
                            </a>
                        </li>
                    @endcan
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                <i class="material-icons-outlined fs-6">logout</i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<!--end header-->
