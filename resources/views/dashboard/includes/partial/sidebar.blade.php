<!--start sidebar-->
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        {{-- <div class="logo-icon">
            <img src="{{ asset('web-assets/images/logo/logo_001.png') }}" class="logo-img w-100" alt="Logo">
        </div> --}}
        <div class="logo-name flex-grow-1">
            <h6 class="mb-0" style="color: #FC5523 !important">Hall Management</h6>
        </div>
        <div class="sidebar-close">
            <span class="material-icons-outlined">close</span>
        </div>
    </div>

    <div class="sidebar-nav">
        @php
            $currentStatus = request()->query('status');
        @endphp
        <ul class="metismenu" id="menu">

            @can('view-dashboard')
                <li>
                    <a href="{{ route('dashboard') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">dashboard</i></div>
                        <div class="menu-title">Dashboard</div>
                    </a>
                </li>
            @endcan

            <!-- Categories -->
            @can('view-categories')
                <li class="menu-label">Categories</li>
                <li>
                    <a href="{{ route('categories.index') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">category</i></div>
                        <div class="menu-title">
                            Categories
                            <span class="badge bg-primary float-end">{{ $categoriesCount ?? 0 }}</span>
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-subcategories')
                <li>
                    <a href="{{ route('subcategories.index') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">subtitles</i></div>
                        <div class="menu-title">
                            Subcategories
                            <span class="badge bg-primary float-end">{{ $subcategoriesCount ?? 0 }}</span>
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-halls')
                <li>
                    <a href="{{ route('halls.index') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">festival</i></div>
                        <div class="menu-title">
                            Halls
                            <span class="badge bg-primary float-end">{{ $hallsCount ?? 0 }}</span>
                        </div>
                    </a>
                </li>
            @endcan

            @can('view-bookings')
                <li>
                    <a href="{{ route('bookings.index') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">event_available</i></div>
                        <div class="menu-title">
                            Bookings
                            <span class="badge bg-primary float-end">{{ $bookingsCount ?? 0 }}</span>
                        </div>
                    </a>
                </li>
            @endcan

            <!-- Users & Roles -->
            @if (auth()->user()->can('view-users') || auth()->user()->can('view-roles'))
                <li class="menu-label">Users & Roles</li>
                @can('view-users')
                    <li>
                        <a href="{{ route('dashboard.users.index') }}">
                            <div class="parent-icon notranslate"><i class="material-icons-outlined">people</i></div>
                            <div class="menu-title">
                                Users
                                <span class="badge bg-primary float-end">{{ $usersCount ?? 0 }}</span>
                            </div>
                        </a>
                    </li>
                @endcan

                @can('view-cities')
                    <li>
                        <a href="{{ route('cities.index') }}">
                            <div class="parent-icon notranslate"><i class="material-icons-outlined">location_city</i>
                            </div>
                            <div class="menu-title">Cities</div>
                        </a>
                    </li>
                @endcan

                @can('view-states')
                    <li>
                        <a href="{{ route('states.index') }}">
                            <div class="parent-icon notranslate"><i class="material-icons-outlined">map</i></div>
                            <div class="menu-title">States</div>
                        </a>
                    </li>
                @endcan

                @can('view-activityLogs')
                    <li>
                        <a href="{{ route('view.activity_logs') }}">
                            <div class="parent-icon notranslate"><i class="material-icons-outlined">people</i></div>
                            <div class="menu-title">Activity Logs</div>
                        </a>
                    </li>
                @endcan

                @can('view-roles')
                    <li>
                        <a href="{{ route('roles.index') }}">
                            <div class="parent-icon notranslate"><i class="material-icons-outlined">admin_panel_settings</i>
                            </div>
                            <div class="menu-title">Roles</div>
                        </a>
                    </li>
                @endcan
            @endif

            {{-- <li>
                    <a href="{{ route('trusted-ips.index') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">security</i></div>
                        <div class="menu-title">Trusted IPs</div>
                    </a>
                </li>

                <li>
                    <a href="{{ route('reports.quotes.histories') }}">
                        <div class="parent-icon notranslate"><i class="material-icons-outlined">security</i></div>
                        <div class="menu-title">Report</div>
                    </a>
                </li> --}}
        </ul>
    </div>
</aside>
<!--end sidebar-->
