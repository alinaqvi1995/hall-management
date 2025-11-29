<?php
namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CustomerService;

class BookingController extends Controller
{
    protected $customerService;
    protected $bookingService;

    public function __construct(CustomerService $customerService, BookingService $bookingService)
    {
        $this->customerService = $customerService;
        $this->bookingService  = $bookingService;
    }

    public function index()
    {
        $bookings = Booking::with('customer', 'hall')->get();

        // Prepare events for FullCalendar
        $calendarEvents = $bookings->map(function ($b) {
            return [
                'id'    => $b->id,
                'title' => $b->customer->name . ' - ' . $b->hall->name,
                'start' => $b->start_datetime,
                'end'   => $b->end_datetime,
                'url'   => route('bookings.edit', $b->id),
            ];
        });

        return view('bookings.index', [
            'bookings'       => $bookings,
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function create()
    {
        $halls = \App\Models\Hall::all();
        return view('bookings.create', compact('halls'));
    }

    public function store(BookingRequest $request)
    {
        $customer = $this->customerService->createOrUpdate([
            'name'    => $request->customer_name,
            'phone'   => $request->customer_phone,
            'email'   => $request->customer_email,
            'cnic'    => $request->customer_cnic,
            'address' => $request->customer_address,
        ]);

        if (! $this->bookingService->checkAvailability($request->hall_id, $request->start_datetime, $request->end_datetime)) {
            return back()->withErrors(['hall_id' => 'Selected hall is already booked for this time range.'])->withInput();
        }

        $this->bookingService->createBooking(array_merge(
            $request->only([
                'hall_id', 'start_datetime', 'end_datetime', 'capacity',
                'quote_price', 'booking_price', 'advance_paid', 'payment_status', 'status', 'notes', 'facilities',
            ]),
            ['customer_id' => $customer->id]
        ));

        return redirect()->route('bookings.create')->with('success', 'Booking created successfully.');
    }

    public function edit(Booking $booking)
    {
        $halls = \App\Models\Hall::all();
        $booking->load('customer');
        return view('bookings.edit', compact('booking', 'halls'));
    }

    public function update(BookingRequest $request, Booking $booking)
    {
        $customer = $this->customerService->createOrUpdate([
            'name'    => $request->customer_name,
            'phone'   => $request->customer_phone,
            'email'   => $request->customer_email,
            'cnic'    => $request->customer_cnic,
            'address' => $request->customer_address,
        ], $booking->customer_id);

        if (! $this->bookingService->checkAvailability($request->hall_id, $request->start_datetime, $request->end_datetime, $booking->id)) {
            return back()->withErrors(['hall_id' => 'Selected hall is already booked for this time range.'])->withInput();
        }

        $this->bookingService->updateBooking($booking, array_merge(
            $request->only([
                'hall_id', 'start_datetime', 'end_datetime', 'capacity',
                'quote_price', 'booking_price', 'advance_paid', 'payment_status', 'status', 'notes', 'facilities',
            ]),
            ['customer_id' => $customer->id]
        ));

        return redirect()->route('bookings.edit', $booking->id)->with('success', 'Booking updated successfully.');
    }
}
