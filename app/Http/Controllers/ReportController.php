<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Lawn;
use App\Services\ReportService;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct(protected ReportService $reports)
    {
        $this->middleware('permission:view-reports');
    }

    /** Revenue, collections, expenses and profit over a chosen range. */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $hallId = $this->scopedHallId();

        return view('reports.index', [
            'summary' => $this->reports->summary($hallId, $from, $to),
            'revenueByDay' => $this->reports->revenueByDay($hallId, $from, $to),
            'bookingsByDay' => $this->reports->bookingsByDay($hallId, $from, $to),
            'eventTypes' => $this->reports->eventTypeBreakdown($hallId, $from, $to),
            'expenseBreakdown' => $this->reports->expenseBreakdown($hallId, $from, $to),
            'occupancy' => $this->reports->occupancy($hallId, $from, $to, $this->lawnCount($hallId)),
            'halls' => $this->selectableHalls(),
            'hallId' => $hallId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** Bookings with money still owed — the follow-up call list. */
    public function outstanding(Request $request)
    {
        $hallId = $this->scopedHallId();
        $bookings = $this->reports->outstanding($hallId, 500);

        return view('reports.outstanding', [
            'bookings' => $bookings,
            'totalOutstanding' => $bookings->sum(fn ($b) => max($b->balance_due, 0)),
            'halls' => $this->selectableHalls(),
            'hallId' => $hallId,
        ]);
    }

    /** Everything happening on one day — printable operations sheet. */
    public function dailySheet(Request $request)
    {
        $day = $request->date('day') ?? now();
        $hallId = $this->scopedHallId();

        return view('reports.daily-sheet', [
            'day' => $day,
            'bookings' => $this->reports->dailySheet($hallId, $day),
            'halls' => $this->selectableHalls(),
            'hallId' => $hallId,
        ]);
    }

    /**
     * Per-booking profitability: collected money against linked expenses.
     */
    public function profitability(Request $request)
    {
        [$from, $to] = $this->range($request);
        $hallId = $this->scopedHallId();

        $bookings = \App\Models\Booking::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->with(['customer:id,name', 'hall:id,name', 'lawn:id,name'])
            ->withSum('expenses', 'amount')
            ->withPaymentTotals()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_datetime', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('start_datetime')
            ->get();

        return view('reports.profitability', [
            'bookings' => $bookings,
            'halls' => $this->selectableHalls(),
            'hallId' => $hallId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /* ----------------------------------------------------------------- helpers */

    /**
     * Requested range, defaulting to the current month.
     * Swaps the bounds if they arrive reversed rather than returning nothing.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->date('from') ? Carbon::parse($request->date('from')) : now()->startOfMonth();
        $to = $request->date('to') ? Carbon::parse($request->date('to')) : now()->endOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    /** Lawns available for booking, the denominator for occupancy. */
    private function lawnCount(?int $hallId): int
    {
        return Lawn::when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->when(! $hallId, fn ($q) => $q->whereIn('hall_id', Hall::visibleTo()->select('id')))
            ->count();
    }
}
