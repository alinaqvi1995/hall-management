@extends('dashboard.includes.partial.base')
@section('title', 'Bookings Calendar')
@section('content')
    <div class="container-fluid p-0">
        <div id="calendar"></div>
    </div>
@endsection

@section('extra_css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
    </style>
@endsection

@section('extra_js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: '100vh',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: "{{ route('bookings.events') }}",
                eventClick: function(info) {
                    const bookingId = info.event.id;
                    window.location.href = `/bookings/${bookingId}/edit`;
                },
                eventColor: '#378006',
                navLinks: true,
                editable: false,
                selectable: false,
                nowIndicator: true
            });

            calendar.render();
        });
    </script>
@endsection

{{-- @extends('dashboard.includes.partial.base')
@section('title', 'Control Center Dashboard')
@section('content')
    <div class="container-fluid">

        <!-- Top Stats -->
        <div class="row mb-4">
            <!-- Users -->
            <div class="{{ auth()->user()->hasRole('super_admin') ? 'col-xl-3' : 'col-xl-4' }} col-md-6 mb-3">
                <div class="card bg-primary text-white rounded-4 shadow-sm">
                    <div class="card-body text-center">
                        <h1 class="fw-bold">{{ $userCount }}</h1>
                        <p class="mb-0">Users</p>
                    </div>
                </div>
            </div>

            <!-- Halls (Superadmin Only) -->
            @if (auth()->user()->isSuperAdmin())
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card bg-success text-white rounded-4 shadow-sm">
                        <div class="card-body text-center">
                            <h1 class="fw-bold">{{ $hallCount }}</h1>
                            <p class="mb-0">Halls</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Animals / Latest Users / KPIs -->
        <div class="row mb-4">
            <!-- Latest Users -->
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card rounded-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Latest Users</h5>
                        <ul class="list-unstyled">
                            @foreach (\App\Models\User::latest()->take(5)->get() as $user)
                                <li class="d-flex align-items-center gap-2 mb-2">
                                    <img src="https://placehold.co/45x45/png" class="rounded-circle" alt="">
                                    <span>{{ $user->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="{{ auth()->user()->hasRole('super_admin') ? 'col-xl-4' : 'col-xl-6' }} col-md-6 mb-3">
                <div class="card rounded-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">User Growth (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div id="userGrowthChart" style="height: 200px;"></div>
                    </div>
                </div>
            </div>

            @if (auth()->user()->hasRole('super_admin'))
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card rounded-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Hall Growth (Last 7 Days)</h5>
                        </div>
                        <div class="card-body">
                            <div id="hallGrowthChart" style="height: 200px;"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <script src="{{ asset('admin/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script>
        // User Growth Chart
        new ApexCharts(document.querySelector("#userGrowthChart"), {
            chart: {
                type: 'line',
                height: 200
            },
            series: [{
                name: 'Users',
                data: @json(array_values($usersLast7Days->toArray()))
            }],
            xaxis: {
                categories: @json(array_keys($usersLast7Days->toArray()))
            }
        }).render();

        // Hall Growth Chart (Superadmin only)
        @if (auth()->user()->hasRole('super_admin'))
            new ApexCharts(document.querySelector("#hallGrowthChart"), {
                chart: {
                    type: 'line',
                    height: 200
                },
                series: [{
                    name: 'Halls',
                    data: @json(array_values($hallsLast7Days->toArray()))
                }],
                xaxis: {
                    categories: @json(array_keys($hallsLast7Days->toArray()))
                }
            }).render();
        @endif
    </script>
@endsection --}}
