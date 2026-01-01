@extends('dashboard.includes.partial.base')

@section('title', 'Bookings')

@section('content')
    <style>
        /* Calendar container — match dashboard card background */
        #calendar {
            background: #f8f9fa;
            /* replace with your panel bg color */
            color: #343a40;
            /* your standard text color */
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            font-family: 'Your-Dashboard-Font', sans-serif;
        }

        /* Header toolbar — match navbar / panel header style */
        .fc .fc-toolbar-title {
            color: #212529;
            /* match dashboard headings */
            font-weight: 500;
        }

        .fc .fc-button {
            background: #ffffff;
            /* dashboard button bg */
            border: 1px solid #ced4da;
            color: #495057;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .fc .fc-button:hover {
            background: #e9ecef;
        }

        /* Column headers (Sun–Sat) */
        .fc-col-header-cell {
            background: #e9ecef;
            /* match table header bg in your theme */
            color: #6c757d;
            border-color: #dee2e6;
        }

        /* Day cells */
        .fc-daygrid-day {
            background: #ffffff;
            /* dashboard card bg */
            border-color: #dee2e6;
        }

        .fc-daygrid-day-number {
            color: #495057;
        }

        /* Today highlight */
        .fc-day-today {
            background: #fff3cd;
            /* subtle highlight color from theme */
            border: 1px solid #ffeeba;
            border-radius: 4px;
        }

        /* Events — use your theme’s primary / success colors */
        .fc-event {
            background: #0d6efd;
            /* e.g. bootstrap primary */
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 0.85rem;
        }
    </style>
    <h6 class="mb-0 text-uppercase">Bookings</h6>
    <hr>

    {{-- @can('create-bookings') --}}
    @if (auth()->user()->isSuperAdmin() || auth()->user()->isHallAdmin())
        <div class="mb-3 text-end">
            <a href="{{ route('bookings.create') }}" class="btn btn-grd btn-grd-primary">
                <i class="material-icons-outlined">add</i> Add Booking
            </a>
        </div>
    @endif
    {{-- @endcan --}}

    {{-- TABS --}}
    <ul class="nav nav-tabs mb-3" id="bookingTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#calendarView">
                Calendar View
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#listView">
                List View
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- CALENDAR TAB (DEFAULT) --}}
        <div class="tab-pane fade show active" id="calendarView">
            <div id="calendar" style="min-height: 700px;"></div>
        </div>

        {{-- LIST TAB --}}
        <div class="tab-pane fade" id="listView">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle datatable">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Booking Number</th>
                                    <th>Customer</th>
                                    <th>Hall - Lawn</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    {{-- <th>Booking Price</th>
                                    <th>Advance</th> --}}
                                    <th>Payments</th>
                                    <th>Status</th>
                                    <th>Created / Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $booking->booking_number }}</td>
                                        <td>
                                            <a href="{{ route('bookings.edit', $booking->id) }}">
                                                {{ $booking->customer->name ?? '-' }}
                                                ({{ $booking->customer->cnic ?? '-' }})
                                            </a>
                                        </td>
                                        <td>
                                            {{ $booking->hall->name ?? '' }}
                                            ({{ $booking->lawn->name ?? '' }})
                                        </td>
                                        <td>{{ $booking->start_datetime?->format('d M Y h:i A') }}</td>
                                        <td>{{ $booking->end_datetime?->format('d M Y h:i A') }}</td>
                                        <td>
                                            <strong>Booking Price: </strong>
                                            {{ number_format($booking->booking_price ?? 0, 2) }}Rs<br>
                                            <strong>Advance: </strong>
                                            {{ number_format($booking->advance_paid ?? 0, 2) }}Rs<br>
                                            <strong>Status: </strong> {{ ucfirst($booking->payment_status) }}
                                        </td>
                                        <td>{{ $booking->status_label }}</td>
                                        <td>
                                            Created: {{ $booking->created_at->format('d M Y h:i A') }}
                                            <br>
                                            Modified: {{ $booking->updated_at->format('d M Y h:i A') }}
                                        </td>
                                        <td>
                                            @can('edit-bookings')
                                                <a href="{{ route('bookings.edit', $booking->id) }}"
                                                    class="btn btn-sm btn-info" title="Edit">
                                                    <i class="material-icons-outlined">edit</i>
                                                </a>
                                            @endcan

                                            <a href="{{ route('bookings.invoice', $booking->id) }}"
                                                class="btn btn-sm btn-secondary" target="_blank" title="Invoice">
                                                <i class="material-icons-outlined">receipt</i>
                                            </a>

                                            @can('delete-bookings')
                                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                        <i class="material-icons-outlined">delete</i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- tab-content end -->

@endsection

@section('extra_js')

    <!-- FullCalendar FIRST -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <!-- Now your calendar initializer -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            console.log('FullCalendar loaded?', typeof FullCalendar); // DEBUG

            let calendarEl = document.getElementById("calendar");

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                height: 700,
                events: @json($calendarEvents),
            });

            calendar.render();
        });
    </script>
@endsection
