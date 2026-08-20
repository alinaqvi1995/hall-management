<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingCancelRequest;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Hall;
use App\Services\BookingService;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct(
        protected CustomerService $customerService,
        protected BookingService $bookingService,
        protected InvoiceService $invoiceService,
        protected PaymentService $payments,
    ) {
        $this->middleware('permission:view-bookings')->only(['index', 'show', 'invoice', 'calendarEvents']);
        $this->middleware('permission:create-bookings')->only(['create', 'store']);
        $this->middleware('permission:edit-bookings')->only(['edit', 'update', 'cancel']);
        $this->middleware('permission:delete-bookings')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = Booking::visibleTo()
            ->with(['customer', 'hall', 'lawn'])
            ->withPaymentTotals()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->orderByDesc('start_datetime')
            ->get();

        return view('bookings.index', [
            'bookings' => $bookings,
            'halls' => $this->selectableHalls(),
        ]);
    }

    /**
     * Calendar feed. FullCalendar requests a window, so only that slice is
     * loaded instead of every booking ever made.
     */
    public function calendarEvents(Request $request)
    {
        $start = $request->date('start') ?? now()->startOfMonth();
        $end = $request->date('end') ?? now()->endOfMonth()->addMonth();

        $bookings = Booking::visibleTo()
            ->with(['customer:id,name', 'hall:id,name', 'lawn:id,name'])
            ->overlapping($start, $end)
            ->get();

        return response()->json($bookings->map(fn (Booking $b) => [
            'id' => $b->id,
            'title' => ($b->customer->name ?? 'Customer').' · '.($b->lawn->name ?? $b->hall->name ?? 'Venue'),
            'start' => $b->start_datetime?->toIso8601String(),
            'end' => $b->end_datetime?->toIso8601String(),
            'url' => route('bookings.show', $b->id),
            'backgroundColor' => $this->calendarColour($b->status),
            'borderColor' => $this->calendarColour($b->status),
            'extendedProps' => [
                'status' => $b->status_label,
                'paymentStatus' => $b->payment_status_label,
                'guests' => $b->guest_count,
            ],
        ]));
    }

    private function calendarColour(string $status): string
    {
        return match ($status) {
            'confirmed' => '#16a34a',
            'completed' => '#2563eb',
            'cancelled' => '#dc2626',
            default => '#d97706',
        };
    }

    public function create()
    {
        $this->authorize('create', Booking::class);

        $halls = $this->selectableHalls();
        $hall = $halls->count() === 1 ? Hall::find($halls->first()->id) : null;

        // A blank model so the shared form partial can read the same fields on
        // create and on edit.
        $booking = new Booking([
            'status' => 'pending',
            'hall_id' => $hall?->id,
            'hall_rent' => 0,
            'discount' => 0,
            'tax_percent' => $hall->tax_percent ?? 0,
            'per_head_rate' => $hall->default_per_head_rate ?? null,
        ]);
        $booking->setRelation('addons', collect());

        return view('bookings.create', compact('halls', 'hall', 'booking'));
    }

    public function store(BookingRequest $request)
    {
        $this->authorize('create', Booking::class);

        $hallId = $this->hallIdForWrite($request->input('hall_id'));
        $advance = $request->advanceData();

        // The booking and the advance receipt are one action from the clerk's
        // point of view, so they commit or fail together — a rejected advance
        // must not leave a booking behind with the money unrecorded.
        $booking = DB::transaction(function () use ($request, $hallId, $advance) {
            $customer = $this->customerService->createOrUpdate($request->customerData());

            $booking = $this->bookingService->createBooking(
                array_merge($request->bookingData(), [
                    'hall_id' => $hallId,
                    'customer_id' => $customer->id,
                ]),
                $request->addonRows()
            );

            if ($advance) {
                $this->payments->record($booking, $advance);
                $booking = $booking->fresh();
            }

            return $booking;
        });

        // Render the invoice now so the share links on the next screen resolve.
        $invoiceUrl = $this->invoiceService->urlFor($booking);

        return view('bookings.booking-success', [
            'booking' => $booking->load(['customer', 'hall', 'lawn', 'package', 'addons', 'payments']),
            'invoiceUrl' => $invoiceUrl,
            'whatsappUrl' => $this->invoiceService->generateWhatsAppUrl($booking, url($invoiceUrl)),
            'gmailUrl' => $this->invoiceService->generateGmailUrl($booking, url($invoiceUrl)),
        ]);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load([
            'customer', 'hall', 'lawn', 'package', 'addons', 'staff',
            'payments.receiver', 'expenses.category', 'creator', 'updater', 'canceller',
        ]);

        $invoiceUrl = $this->invoiceService->urlFor($booking);

        return view('bookings.show', [
            'booking' => $booking,
            'invoiceUrl' => $invoiceUrl,
            'whatsappUrl' => $this->invoiceService->generateWhatsAppUrl($booking, url($invoiceUrl)),
            'gmailUrl' => $this->invoiceService->generateGmailUrl($booking, url($invoiceUrl)),
        ]);
    }

    public function edit(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (! $booking->isEditable()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'A '.strtolower($booking->status_label).' booking cannot be edited.');
        }

        $booking->load(['customer', 'addons', 'hall']);

        return view('bookings.edit', [
            'booking' => $booking,
            'halls' => $this->selectableHalls(),
            'hall' => $booking->hall,
            'lawns' => $this->bookingService->lawnAvailability(
                $booking->hall,
                $booking->start_datetime,
                $booking->end_datetime,
                $booking->id
            ),
        ]);
    }

    public function update(BookingRequest $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        abort_unless($booking->isEditable(), 422, 'This booking can no longer be edited.');

        $customer = $this->customerService->createOrUpdate(
            $request->customerData(),
            $booking->customer_id
        );

        $this->bookingService->updateBooking(
            $booking,
            array_merge($request->bookingData(), [
                'hall_id' => $this->hallIdForWrite($request->input('hall_id')),
                'customer_id' => $customer->id,
            ]),
            $request->addonRows()
        );

        // Amounts or dates may have moved, so the cached PDF is now wrong.
        $this->invoiceService->forget($booking);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    /** Cancel rather than delete, so the audit trail and the refund survive. */
    public function cancel(BookingCancelRequest $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        if ($booking->isCancelled()) {
            return back()->with('error', 'This booking is already cancelled.');
        }

        $this->bookingService->cancelBooking(
            $booking,
            $request->string('cancellation_reason'),
            $request->filled('cancellation_charge') ? (float) $request->input('cancellation_charge') : null
        );

        $this->invoiceService->forget($booking);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking cancelled. The slot is now available again.');
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $this->invoiceService->forget($booking);
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function invoice(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['customer', 'hall', 'lawn', 'package', 'addons', 'payments']);

        return view('bookings.invoice', ['booking' => $booking, 'forPdf' => false]);
    }
}
