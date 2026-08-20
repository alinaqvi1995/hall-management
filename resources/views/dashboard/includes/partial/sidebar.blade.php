@php
    $counts = $sidebarCounts ?? [];
    $user = auth()->user();

    // Highlight the group a nested page belongs to, so "Add Booking" still
    // marks the Bookings entry as current.
    $is = fn (...$patterns) => request()->routeIs(...$patterns) ? 'active' : '';
@endphp

<!--start sidebar-->
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div class="logo-icon">
            <span class="sidebar-brand-mark">
                <i class="material-icons-outlined">festival</i>
            </span>
        </div>
        <div class="logo-name flex-grow-1">
            <h6 class="mb-0 sidebar-brand-name">Hall Management</h6>
            <small class="text-secondary d-block lh-1">
                {{ $user?->isSuperAdmin() ? 'All venues' : ($user?->hall->name ?? 'No venue linked') }}
            </small>
        </div>
        <div class="sidebar-close">
            <span class="material-icons-outlined">close</span>
        </div>
    </div>

    <div class="sidebar-nav">
        <ul class="metismenu" id="menu">

            @can('view-dashboard')
                <li class="{{ $is('dashboard') }}">
                    <a href="{{ route('dashboard') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">dashboard</i></div>
                        <div class="menu-title">Dashboard</div>
                    </a>
                </li>
            @endcan

            {{-- ─────────────────────────── Operations ─────────────────────────── --}}
            @if ($user?->hasAnyPermission(['view-bookings', 'view-payments', 'view-customers']))
                <li class="menu-label">Operations</li>
            @endif

            @can('view-bookings')
                <li class="{{ $is('bookings.*') }}">
                    <a href="{{ route('bookings.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">event_available</i></div>
                        <div class="menu-title">
                            Bookings
                            @if (($counts['upcoming'] ?? 0) > 0)
                                <span class="badge float-end">{{ $counts['upcoming'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-payments')
                <li class="{{ $is('payments.*') }}">
                    <a href="{{ route('payments.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">payments</i></div>
                        <div class="menu-title">Payments</div>
                    </a>
                </li>
            @endcan

            @can('view-expenses')
                <li class="{{ $is('expenses.*') }}">
                    <a href="{{ route('expenses.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">receipt_long</i></div>
                        <div class="menu-title">Expenses</div>
                    </a>
                </li>
            @endcan

            @can('view-customers')
                <li class="{{ $is('customers.*') }}">
                    <a href="{{ route('customers.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">groups</i></div>
                        <div class="menu-title">
                            Customers
                            @if (($counts['customers'] ?? 0) > 0)
                                <span class="badge float-end">{{ $counts['customers'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endcan

            {{-- ─────────────────────────── Venue setup ────────────────────────── --}}
            @if ($user?->hasAnyPermission(['view-halls', 'view-packages', 'view-addons']))
                <li class="menu-label">Venue</li>
            @endif

            @can('view-halls')
                <li class="{{ $is('halls.*') }}">
                    <a href="{{ route('halls.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">festival</i></div>
                        <div class="menu-title">
                            {{ $user->isSuperAdmin() ? 'Halls' : 'My Hall' }}
                            @if ($user->isSuperAdmin() && ($counts['halls'] ?? 0) > 0)
                                <span class="badge float-end">{{ $counts['halls'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-packages')
                <li class="{{ $is('packages.*') }}">
                    <a href="{{ route('packages.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">restaurant_menu</i></div>
                        <div class="menu-title">Menus & Packages</div>
                    </a>
                </li>
            @endcan

            @can('view-addons')
                <li class="{{ $is('addons.*') }}">
                    <a href="{{ route('addons.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">celebration</i></div>
                        <div class="menu-title">Extra Services</div>
                    </a>
                </li>
            @endcan

            {{-- ────────────────────────────── People ─────────────────────────── --}}
            @if ($user?->hasAnyPermission(['view-staff', 'view-vendors']))
                <li class="menu-label">People</li>
            @endif

            @can('view-staff')
                <li class="{{ $is('staff.*') }}">
                    <a href="{{ route('staff.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">badge</i></div>
                        <div class="menu-title">
                            Staff
                            @if (($counts['staff'] ?? 0) > 0)
                                <span class="badge float-end">{{ $counts['staff'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-vendors')
                <li class="{{ $is('vendors.*') }}">
                    <a href="{{ route('vendors.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">local_shipping</i></div>
                        <div class="menu-title">Vendors</div>
                    </a>
                </li>
            @endcan

            {{-- ────────────────────────────── Reports ────────────────────────── --}}
            @can('view-reports')
                <li class="menu-label">Insights</li>
                <li class="{{ $is('reports.*') }}">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">insights</i></div>
                        <div class="menu-title">Reports</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('reports.index') }}"><i class="material-icons-outlined">bar_chart</i>
                                Business Summary</a></li>
                        <li><a href="{{ route('reports.outstanding') }}"><i
                                    class="material-icons-outlined">account_balance_wallet</i> Outstanding Dues</a></li>
                        <li><a href="{{ route('reports.dailySheet') }}"><i class="material-icons-outlined">today</i>
                                Daily Event Sheet</a></li>
                        <li><a href="{{ route('reports.profitability') }}"><i
                                    class="material-icons-outlined">trending_up</i> Profit per Event</a></li>
                    </ul>
                </li>
            @endcan

            {{-- ───────────────────────── Administration ──────────────────────── --}}
            @if ($user?->hasAnyPermission(['view-users', 'view-roles', 'view-permissions', 'view-states', 'view-cities', 'view-activityLogs']))
                <li class="menu-label">Administration</li>
            @endif

            @can('view-users')
                <li class="{{ $is('dashboard.users.*', 'users.show') }}">
                    <a href="{{ route('dashboard.users.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">people</i></div>
                        <div class="menu-title">
                            Users
                            @if (($counts['users'] ?? 0) > 0)
                                <span class="badge float-end">{{ $counts['users'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endcan

            @if ($user?->hasAnyPermission(['view-roles', 'view-permissions']))
                <li class="{{ $is('roles.*', 'permissions.*') }}">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">admin_panel_settings</i></div>
                        <div class="menu-title">Access Control</div>
                    </a>
                    <ul>
                        @can('view-roles')
                            <li><a href="{{ route('roles.index') }}"><i class="material-icons-outlined">shield</i>
                                    Roles</a></li>
                        @endcan
                        @can('view-permissions')
                            <li><a href="{{ route('permissions.index') }}"><i class="material-icons-outlined">key</i>
                                    Permissions</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            @if ($user?->hasAnyPermission(['view-states', 'view-cities']))
                <li class="{{ $is('states.*', 'cities.*') }}">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">public</i></div>
                        <div class="menu-title">Locations</div>
                    </a>
                    <ul>
                        @can('view-states')
                            <li><a href="{{ route('states.index') }}"><i class="material-icons-outlined">map</i>
                                    Provinces</a></li>
                        @endcan
                        @can('view-cities')
                            <li><a href="{{ route('cities.index') }}"><i
                                        class="material-icons-outlined">location_city</i> Cities</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            @can('view-activityLogs')
                <li class="{{ $is('view.activity_logs') }}">
                    <a href="{{ route('view.activity_logs') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">history</i></div>
                        <div class="menu-title">Activity Logs</div>
                    </a>
                </li>
            @endcan

            @can('view-trustedIps')
                <li class="{{ $is('trusted-ips.*') }}">
                    <a href="{{ route('trusted-ips.index') }}">
                        <div class="parent-icon"><i class="material-icons-outlined">security</i></div>
                        <div class="menu-title">Trusted IPs</div>
                    </a>
                </li>
            @endcan
        </ul>
    </div>
</aside>
<!--end sidebar-->
