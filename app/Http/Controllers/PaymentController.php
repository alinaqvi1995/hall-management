<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct(protected PaymentService $payments)
    {
        $this->middleware('permission:view-payments')->only(['index', 'receipt']);
        $this->middleware('permission:create-payments')->only(['store']);
        $this->middleware('permission:delete-payments')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $payments = Payment::visibleTo()
            ->with(['booking.customer', 'booking.hall', 'receiver'])
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->string('method')))
            ->when($request->filled('direction'), fn ($q) => $q->where('direction', $request->string('direction')))
            ->when($request->filled('from'), fn ($q) => $q->where('paid_on', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('paid_on', '<=', $request->date('to')))
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->get();

        return view('payments.index', [
            'payments' => $payments,
            'totalIn' => $payments->where('direction', 'in')->sum('amount'),
            'totalRefund' => $payments->where('direction', 'refund')->sum('amount'),
        ]);
    }

    public function store(PaymentRequest $request)
    {
        $booking = Booking::findOrFail($request->input('booking_id'));

        // Recording money against a booking is an edit of that booking.
        $this->authorize('update', $booking);

        $payment = $this->payments->record($booking, $request->validated());

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', $request->input('direction') === 'refund'
                ? 'Refund of Rs. '.number_format($payment->amount, 2).' recorded.'
                : 'Payment received. Receipt '.$payment->receipt_number.' created.');
    }

    public function destroy(Payment $payment)
    {
        $booking = $payment->booking;

        $this->authorize('update', $booking);
        $this->authorizeHallAccess($payment);

        $this->payments->delete($payment);

        return back()->with('success', 'Payment entry removed and balances recalculated.');
    }

    /** Printable receipt for a single payment. */
    public function receipt(Payment $payment)
    {
        $this->authorizeHallAccess($payment);
        $this->authorize('view', $payment->booking);

        $payment->load(['booking.customer', 'booking.hall', 'booking.lawn', 'receiver']);

        return view('payments.receipt', compact('payment'));
    }
}
