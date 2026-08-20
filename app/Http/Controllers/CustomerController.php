<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-customers')->only(['index', 'show', 'lookup']);
        $this->middleware('permission:edit-customers')->only(['toggleBlacklist']);
    }

    public function index(Request $request)
    {
        $customers = Customer::visibleTo()
            ->withCount('bookings')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('cnic', 'like', $term));
            })
            ->when($request->string('filter') === 'blacklisted', fn ($q) => $q->blacklisted())
            ->orderBy('name')
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        // A hall admin may only open a customer who has booked at their hall.
        abort_unless(
            Customer::visibleTo()->whereKey($customer->getKey())->exists(),
            403,
            'Unauthorized'
        );

        $bookings = $customer->bookings()
            ->visibleTo()
            ->with(['hall:id,name', 'lawn:id,name'])
            ->withPaymentTotals()
            ->orderByDesc('start_datetime')
            ->get();

        return view('customers.show', [
            'customer' => $customer,
            'bookings' => $bookings,
            'lifetimeValue' => $bookings->where('status', '!=', 'cancelled')->sum('total_amount'),
            'outstanding' => $bookings->sum(fn ($b) => max($b->balance_due, 0)),
        ]);
    }

    /**
     * CNIC lookup for the booking form, so a returning customer's details
     * prefill instead of being retyped.
     */
    public function lookup(Request $request)
    {
        $cnic = trim((string) $request->query('cnic'));

        if (strlen($cnic) < 5) {
            return response()->json(null);
        }

        $customer = Customer::where('cnic', $cnic)->first();

        if (! $customer) {
            return response()->json(null);
        }

        return response()->json([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'secondary_phone' => $customer->secondary_phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'is_blacklisted' => $customer->is_blacklisted,
            'blacklist_reason' => $customer->blacklist_reason,
            'total_bookings' => $customer->bookings()->count(),
        ]);
    }

    /** Flag a customer so staff are warned before taking another booking. */
    public function toggleBlacklist(Request $request, Customer $customer)
    {
        abort_unless(
            Customer::visibleTo()->whereKey($customer->getKey())->exists(),
            403,
            'Unauthorized'
        );

        $validated = $request->validate([
            'blacklist_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $blacklisting = ! $customer->is_blacklisted;

        if ($blacklisting && empty($validated['blacklist_reason'])) {
            return back()->with('error', 'A reason is required when blacklisting a customer.');
        }

        $customer->update([
            'is_blacklisted' => $blacklisting,
            'blacklist_reason' => $blacklisting ? $validated['blacklist_reason'] : null,
        ]);

        return back()->with('success', $blacklisting
            ? 'Customer blacklisted.'
            : 'Customer removed from the blacklist.');
    }
}
