<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-expenses')->only(['index']);
        $this->middleware('permission:create-expenses')->only(['create', 'store']);
        $this->middleware('permission:edit-expenses')->only(['edit', 'update']);
        $this->middleware('permission:delete-expenses')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();

        $expenses = Expense::visibleTo()
            ->with(['category', 'vendor', 'booking.customer', 'hall:id,name', 'creator:id,name'])
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->when($request->filled('expense_category_id'),
                fn ($q) => $q->where('expense_category_id', $request->integer('expense_category_id')))
            ->orderByDesc('spent_on')
            ->orderByDesc('id')
            ->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'total' => $expenses->sum('amount'),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create()
    {
        return view('expenses.form', $this->formData(new Expense([
            'spent_on' => now()->toDateString(),
            'method' => 'cash',
        ])));
    }

    public function store(ExpenseRequest $request)
    {
        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['created_by'] = Auth::id();

        $this->guardBookingBelongsToHall($data);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $this->authorizeHallAccess($expense);

        return view('expenses.form', $this->formData($expense));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $this->authorizeHallAccess($expense);

        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);

        $this->guardBookingBelongsToHall($data);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeHallAccess($expense);

        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }

    /**
     * An expense may only be attached to a booking at the same hall, otherwise
     * one venue's costs would land in another venue's profit figures.
     */
    private function guardBookingBelongsToHall(array $data): void
    {
        if (empty($data['booking_id'])) {
            return;
        }

        $ok = Booking::where('id', $data['booking_id'])
            ->where('hall_id', $data['hall_id'])
            ->exists();

        abort_unless($ok, 422, 'The selected booking does not belong to this hall.');
    }

    private function formData(Expense $expense): array
    {
        $hallId = $expense->hall_id ?: $this->scopedHallId();

        return [
            'expense' => $expense,
            'halls' => $this->selectableHalls(),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'vendors' => Vendor::visibleTo()->active()->orderBy('name')->get(['id', 'name', 'hall_id']),
            'bookings' => Booking::visibleTo()
                ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
                ->with('customer:id,name')
                ->orderByDesc('start_datetime')
                ->limit(200)
                ->get(['id', 'booking_number', 'customer_id', 'start_datetime', 'hall_id']),
        ];
    }
}
