<?php
namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Hall;
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
        $this->authorize('viewAny', Booking::class);

        $user = auth()->user();

        // Fetch bookings based on role
        if ($user->isSuperAdmin()) {
            $bookings = Booking::with('customer', 'hall')->get();
        } elseif ($user->isHallAdmin()) {
            $bookings = Booking::with('customer', 'hall')
                ->where('hall_id', $user->hall_id)
                ->get();
        } else {
            // Other users with view-bookings permission
            $bookings = Booking::with('customer', 'hall')->get();
        }

        // Prepare events for FullCalendar
        $calendarEvents = $bookings->map(function ($b) {
            return [
                'id'    => $b->id,
                'title' => ($b->customer->name ?? 'Customer') . ' - ' . ($b->hall->name ?? 'Hall'),
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
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            $halls = Hall::all();
        } else if ($user->hasRole('hall_admin')) {

            if ($user->hall_id ?? false) {
                $halls = Hall::where('id', $user->hall_id)->get();
            } else if ($user->halls()->exists()) {
                $halls = $user->halls;
            } else {
                $halls = collect();
            }
        } else {
            $halls = collect();
        }

        return view('bookings.create', compact('halls'));
    }

    // public function create()
    // {
    //     $halls = \App\Models\Hall::all();
    //     return view('bookings.create', compact('halls'));
    // }

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
                'quote_price', 'booking_price', 'advance_paid', 'payment_status', 'status', 'notes', 'facilities', 'lawn_id',
            ]),
            ['customer_id' => $customer->id]
        ));

        return redirect()->route('bookings.create')->with('success', 'Booking created successfully.');
    }

    public function edit(Booking $booking)
    {
        $start = $booking->start_datetime;
        $end   = $booking->end_datetime;

        $hall = $booking->hall;
        $lawns = $hall->lawns()->select('id', 'name', 'capacity')->get();

        if ($start && $end) {
            $startDate = \Carbon\Carbon::parse($start);
            $endDate   = \Carbon\Carbon::parse($end);

            $lawns->transform(function ($lawn) use ($startDate, $endDate) {
                // Check if any booking overlaps in time
                $booking = Booking::where('lawn_id', $lawn->id)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start_datetime', '<', $endDate)
                            ->where('end_datetime', '>', $startDate);
                    })
                    ->first();

                if ($booking) {
                    $lawn->available   = false;
                    $lawn->booked_from = $booking->start_datetime->format('d M Y h:i A');
                    $lawn->booked_to   = $booking->end_datetime->format('d M Y h:i A');
                } else {
                    $lawn->available   = true;
                    $lawn->booked_from = null;
                    $lawn->booked_to   = null;
                }

                return $lawn;
            });
        } else {
            $lawns->transform(function ($lawn) {
                $lawn->available   = true;
                $lawn->booked_from = null;
                $lawn->booked_to   = null;
                return $lawn;
            });
        }

        $halls = \App\Models\Hall::all();
        $booking->load('customer');
        return view('bookings.edit', compact('booking', 'halls', 'lawns'));
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
                'quote_price', 'booking_price', 'advance_paid', 'payment_status', 'status', 'notes', 'facilities', 'lawn_id',
            ]),
            ['customer_id' => $customer->id]
        ));

        return redirect()->route('bookings.edit', $booking->id)->with('success', 'Booking updated successfully.');
    }
}
