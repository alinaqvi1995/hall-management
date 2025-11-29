@extends('dashboard.includes.partial.base')

@section('title', 'Bookings')

@section('content')
    <h6 class="mb-0 text-uppercase">Bookings</h6>
    <hr>

    @can('create-bookings')
        <div class="mb-3 text-end">
            <a href="{{ route('bookings.create') }}" class="btn btn-grd btn-grd-primary">
                <i class="material-icons-outlined">add</i> Add Booking
            </a>
        </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Customer</th>
                            <th>Hall</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Booking Price</th>
                            <th>Advance Paid</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('bookings.edit', $booking->id) }}">
                                        {{ $booking->customer->name ?? '-' }}
                                        ({{ $booking->customer->cnic ?? '-' }})
                                    </a>
                                </td>
                                <td>{{ $booking->hall->name ?? '-' }}</td>
                                <td>{{ $booking->start_datetime?->format('d-M-Y H:i') ?? '-' }}</td>
                                <td>{{ $booking->end_datetime?->format('d-M-Y H:i') ?? '-' }}</td>
                                <td>{{ number_format($booking->booking_price ?? 0, 2) }}</td>
                                <td>{{ number_format($booking->advance_paid ?? 0, 2) }}</td>
                                <td>{{ ucfirst($booking->payment_status) }}</td>
                                <td>{!! $booking->status_label !!}</td>
                                <td>
                                    @can('edit-bookings')
                                        <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-info">
                                            <i class="material-icons-outlined">edit</i>
                                        </a>
                                    @endcan
                                    @can('delete-bookings')
                                        <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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
@endsection

@section('extra_js')
    {{-- Optional JS for datatable --}}
@endsection
