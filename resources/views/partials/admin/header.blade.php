<!-- ========== App Menu ========== -->
    <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box">
            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="admin-assets/images/logo-sm.png" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="admin-assets/images/logo-dark.png" alt="" height="22">
                </span>
            </a>
            <a href="{{ route('dashboard') }}" class="logo logo-light">
                <span class="logo-sm">
                    <img src="admin-assets/images/logo-sm.png" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="admin-assets/images/logo-light.png" alt="" height="22">
                </span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
            <div class="vertical-menu-btn-wrapper header-item vertical-icon">
                <button type="button" class="btn btn-sm px-0 fs-xl vertical-menu-btn topnav-hamburger shadow hamburger-icon" id="topnav-hamburger-icon">
                    <i class='bx bx-chevrons-right'></i>
                    <i class='bx bx-chevrons-left'></i>
                </button>
            </div>
        </div>
        <div id="scrollbar">
            <div class="container-fluid">
                <div id="two-column-menu">
                </div>
                <ul class="navbar-nav" id="navbar-nav">

                    <li class="nav-item">
                        <a href="#sidebarHospital" class="nav-link menu-link  collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarHospital">
                            <i class=" bx bx-buildings"></i> <span data-key="t-hospital">Hospital</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarHospital">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('dashboard') }}" class="nav-link" data-key="t-dashboard">Dashboard</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarEcommerce" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce">
                            <i class="bx bx-cart-alt"></i> <span data-key="t-ecommerce">Ecommerce</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarEcommerce">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="dashboard-ecommerce.html" class="nav-link" data-key="t-dashboard"> Dashboard </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a href="apps-calendar.html" class="nav-link menu-link"> <i class="bx bx-calendar"></i> <span data-key="t-calendar">Calendar</span> </a>
                    </li>

                    <li class="nav-item">
                        <a href="apps-chat.html" class="nav-link menu-link"> <i class="bx bx-chat"></i> <span data-key="t-chat">Chat</span> </a>
                    </li>

                    <li class="nav-item">
                        <a href="apps-email.html" class="nav-link menu-link"> <i class="bx bx-envelope"></i> <span data-key="t-email">Email</span> </a>
                    </li>

                    <li class="nav-item">
                        <a href="#sidebarInvoices" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInvoices">
                            <i class="bx bx-file"></i> <span data-key="t-invoices">Invoices</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarInvoices">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="apps-invoices-list.html" class="nav-link" data-key="t-list-view">List View</a>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-invoices-overview.html" class="nav-link" data-key="t-overview">Overview</a>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-invoices-create.html" class="nav-link" data-key="t-create-invoice">Create Invoice</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </div>
            <!-- Sidebar -->
        </div>
<div class="dropdown sidebar-user mt-4">
    <button type="button" class="btn sidebar-user-button shadow-none w-100" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="d-flex align-items-center overflow-hidden">
            <img class="rounded-circle header-profile-user" src="{{ asset('admin-assets/images/users/32/avatar-1.jpg') }}" alt="Header Avatar">
            <span class="text-start ms-xl-2 overflow-hidden flex-grow-1 sideba-user-content">
                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text text-truncate mb-0">
                    {{ Auth::user()->name }}
                </span>
                <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text">
                    {{ Auth::user()->role ?? 'User' }}
                </span>
            </span>
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <!-- Welcome -->
        <h6 class="dropdown-header">Welcome {{ Auth::user()->name }}!</h6>

        <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Profile</span>
        </a>

        <a class="dropdown-item" href="#">
            <i class="mdi mdi-message-text-outline text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Messages</span>
        </a>

        <a class="dropdown-item" href="#">
            <i class="mdi mdi-calendar-check-outline text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Taskboard</span>
        </a>

        <a class="dropdown-item" href="#">
            <i class="mdi mdi-lifebuoy text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Help</span>
        </a>

        <div class="dropdown-divider"></div>

        <a class="dropdown-item" href="#">
            <i class="mdi mdi-wallet text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Balance: <b>${{ number_format(Auth::user()->balance ?? 0, 2) }}</b></span>
        </a>

        <a class="dropdown-item" href="#">
            <span class="badge bg-success-subtle text-success mt-1 float-end">New</span>
            <i class="mdi mdi-cog-outline text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Settings</span>
        </a>

        <a class="dropdown-item" href="#">
            <i class="mdi mdi-lock text-muted fs-lg align-middle me-1"></i>
            <span class="align-middle">Lock screen</span>
        </a>

        <!-- Logout POST method -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item">
                <i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i>
                <span class="align-middle">Logout</span>
            </button>
        </form>
    </div>
</div>

        <div class="sidebar-background"></div>
    </div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
    <div class="topbar-wrapper shadow">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="admin-assets/images/logo-sm.png" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="admin-assets/images/logo-dark.png" alt="" height="22">
                                </span>
                            </a>
        
                            <a href="{{ route('dashboard') }}" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="admin-assets/images/logo-sm.png" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="admin-assets/images/logo-light.png" alt="" height="22">
                                </span>
                            </a>
                        </div>
        
                        <div class="header-item flex-shrink-0 me-3 vertical-btn-wrapper">
                            <button type="button" class="btn btn-sm px-0 fs-xl vertical-menu-btn topnav-hamburger border hamburger-icon" id="topnav-hamburger-icon">
                                <i class='bx bx-chevrons-right arrow-right'></i>
                                <i class='bx bx-chevrons-left arrow-left'></i>
                            </button>
                        </div>
        
                        <h4 class="mb-sm-0 header-item page-title lh-base">Dashboard</h4>
                    </div>
        
                    <div class="d-flex align-items-center">
                        <div class="dropdown d-none d-md-flex topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-search fs-3xl"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                                <form class="p-3">
                                    <div class="form-group m-0">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                            <button class="btn btn-grd btn-grd-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
        
                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" data-toggle="fullscreen">
                                <i class='bx bx-fullscreen fs-3xl'></i>
                            </button>
                        </div>
        
                        <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle mode-layout" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-sun align-middle fs-3xl"></i>
                            </button>
                            <div class="dropdown-menu p-2 dropdown-menu-end" id="light-dark-mode">
                                <a href="#!" class="dropdown-item" data-mode="light"><i class="bx bx-sun align-middle me-2"></i> Default (light mode)</a>
                                <a href="#!" class="dropdown-item" data-mode="dark"><i class="bx bx-moon align-middle me-2"></i> Dark</a>
                                <a href="#!" class="dropdown-item" data-mode="auto"><i class="bx bx-desktop align-middle me-2"></i> Auto (system default)</a>
                            </div>
                        </div>
        
                        <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-notification fs-3xl'></i>
                                <span class="position-absolute topbar-badge fs-3xs translate-middle badge rounded-pill bg-danger"><span class="notification-badge">3</span><span class="visually-hidden">unread messages</span></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
        
                                <div class="dropdown-head rounded-top">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h6 class="mb-0 fs-lg fw-semibold"> Notifications <span class="badge bg-danger-subtle text-danger fs-sm notification-badge"> 3</span></h6>
                                            </div>
                                            <div class="col-auto dropdown">
                                                <a href="javascript:void(0);" data-bs-toggle="dropdown" class="link-secondary fs-md"><i class="bi bi-three-dots-vertical"></i></a>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#">All Clear</a></li>
                                                    <li><a class="dropdown-item" href="#">Mark all as read</a></li>
                                                    <li><a class="dropdown-item" href="#">Archive All</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
        
                                </div>
        
                                <div class="pb-2 ps-0" id="notificationItemsTabContent">
                                    <div data-simplebar style="max-height: 300px;" class="pe-0">
                                        <h6 class="text-overflow text-muted fs-sm my-2 notification-title px-3">Today</h6>
        
                                        <div class="text-reset notification-item d-block dropdown-item position-relative border-dashed border-bottom">
                                            <div class="d-flex">
                                                <div class="position-relative me-3 flex-shrink-0">
                                                    <img src="admin-assets/images/users/32/avatar-3.jpg" class="rounded-circle avatar-xs" alt="user-pic">
                                                    <span class="active-badge position-absolute start-100 translate-middle p-1 bg-danger rounded-circle">
                                                        <span class="visually-hidden">Un Active</span>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fs-md text-muted">
                                                        <p class="mb-1 text-muted"><b>Angela Bernier</b> mentioned you in <a href="#!">This Project</a></p>
                                                    </div>
                                                    <p class="mb-0 fs-xs fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 48 min ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-base">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check02">
                                                        <label class="form-check-label" for="all-notification-check02"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="text-reset notification-item d-block dropdown-item position-relative border-dashed border-bottom">
                                            <div class="d-flex">
                                                <div class="position-relative me-3 flex-shrink-0">
                                                    <img src="admin-assets/images/users/32/avatar-2.jpg" class="rounded-circle avatar-xs" alt="user-pic">
                                                    <span class="active-badge position-absolute start-100 translate-middle p-1 bg-success rounded-circle">
                                                        <span class="visually-hidden">New alerts</span>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fs-md text-muted">
                                                        <p class="mb-1">Answered to your comment on the cash flow forecast's graph 🔔.</p>
                                                    </div>
                                                    <p class="mb-0 fs-xs fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 1 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-base">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check02">
                                                        <label class="form-check-label" for="all-notification-check02"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
        
                                        <h6 class="text-overflow text-muted fs-sm my-3 px-3 notification-title">Read Before</h6>
        
                                        <div class="text-reset notification-item d-block dropdown-item position-relative border-dashed border-bottom">
                                            <div class="d-flex">
        
                                                <div class="position-relative me-3 flex-shrink-0">
                                                    <img src="admin-assets/images/users/32/avatar-8.jpg" class="rounded-circle avatar-xs" alt="user-pic">
                                                    <span class="active-badge position-absolute start-100 translate-middle p-1 bg-warning rounded-circle">
                                                        <span class="visually-hidden">New alerts</span>
                                                    </span>
                                                </div>
        
                                                <div class="flex-grow-1">
                                                    <div class="fs-md text-muted">
                                                        <p class="mb-1">We talked about a project on linkedin.</p>
                                                    </div>
                                                    <p class="mb-0 fs-xs fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 4 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-base">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check04">
                                                        <label class="form-check-label" for="all-notification-check04"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions" id="notification-actions">
                                        <div class="d-flex text-muted justify-content-center align-items-center">
                                            Select <div id="select-content" class="text-body fw-semibold px-1">0</div> Result <button type="button" class="btn btn-link link-danger p-0 ms-2" data-bs-toggle="modal" data-bs-target="#removeNotificationModal">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle header-profile-user" src="admin-assets/images/users/32/avatar-1.jpg" alt="Header Avatar">
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Richard Marshall</span>
                                        <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text">Founder</span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <h6 class="dropdown-header">Welcome Richard!</h6>
                                <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                                <a class="dropdown-item" href="apps-chat.html"><i class="mdi mdi-message-text-outline text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Messages</span></a>
                                <a class="dropdown-item" href="apps-tickets-overview.html"><i class="mdi mdi-calendar-check-outline text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>
                                <a class="dropdown-item" href="pages-faqs.html"><i class="mdi mdi-lifebuoy text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Help</span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-wallet text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Balance : <b>$8451.36</b></span></a>
                                <a class="dropdown-item" href="pages-profile-settings.html"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Settings</span></a>
                                <a class="dropdown-item" href="auth-lockscreen.html"><i class="mdi mdi-lock text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
                                <a class="dropdown-item" href="auth-logout.html"><i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- removeNotificationModal -->
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body p-md-5">
                        <div class="text-center">
                            <div class="text-danger">
                                <i class="bi bi-trash display-4"></i>
                            </div>
                            <div class="mt-4 fs-base">
                                <h4 class="mb-1">Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                        </div>
                    </div>
        
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <!-- removeCartModal -->
        <div id="removeCartModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-cartmodal"></button>
                    </div>
                    <div class="modal-body p-md-5">
                        <div class="text-center">
                            <div class="text-danger">
                                <i class="bi bi-trash display-5"></i>
                            </div>
                            <div class="mt-4">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this product ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="remove-cartproduct">Yes, Delete It!</button>
                        </div>
                    </div>
        
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->            
        <ul class="nav nav-underline menu-tabs flex-nowrap overflow-x-auto" id="menu-tabs">
            <li class="nav-item flex-shrink-0">
                <a class="nav-link" aria-current="page" href="{{ route('dashboard') }}">Dashboard</a>
            </li>
        </ul>
    </div>

