<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Headline figures for a hall (or all halls) over a date range.
     * "Billed" is what was invoiced; "collected" is what actually came in.
     */
    public function summary(?int $hallId, CarbonInterface $from, CarbonInterface $to): array
    {
        // A single pass over the bookings rather than five separate COUNT/SUM
        // round trips, which is what the dashboard used to cost.
        $stats = $this->bookingScope($hallId, $from, $to)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'confirmed') as confirmed")
            ->selectRaw("SUM(status = 'pending') as pending")
            ->selectRaw("SUM(status = 'cancelled') as cancelled")
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' THEN guest_count ELSE 0 END) as guests")
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END) as billed")
            ->first();

        $billed = (float) ($stats->billed ?? 0);
        $collected = $this->collected($hallId, $from, $to);
        $expenses = (float) $this->expenseScope($hallId, $from, $to)->sum('amount');

        return [
            'bookings_total' => (int) ($stats->total ?? 0),
            'bookings_confirmed' => (int) ($stats->confirmed ?? 0),
            'bookings_pending' => (int) ($stats->pending ?? 0),
            'bookings_cancelled' => (int) ($stats->cancelled ?? 0),
            'guests' => (int) ($stats->guests ?? 0),
            'billed' => round($billed, 2),
            'collected' => round($collected, 2),
            'outstanding' => round(max($billed - $collected, 0), 2),
            'expenses' => round($expenses, 2),
            'profit' => round($collected - $expenses, 2),
        ];
    }

    /** Money received minus refunds in the window. */
    public function collected(?int $hallId, CarbonInterface $from, CarbonInterface $to): float
    {
        $row = Payment::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END) as received")
            ->selectRaw("SUM(CASE WHEN direction = 'refund' THEN amount ELSE 0 END) as refunded")
            ->first();

        return round((float) ($row->received ?? 0) - (float) ($row->refunded ?? 0), 2);
    }

    /**
     * Revenue collected per day, gap-filled so the chart has no missing days.
     *
     * @return Collection<string, float>
     */
    public function revenueByDay(?int $hallId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rows = Payment::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->where('direction', 'in')
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('paid_on as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDays($from, $to, fn ($key) => (float) ($rows[$key] ?? 0));
    }

    /**
     * Bookings created per day, gap-filled.
     *
     * @return Collection<string, int>
     */
    public function bookingsByDay(?int $hallId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rows = Booking::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereBetween('start_datetime', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(start_datetime) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDays($from, $to, fn ($key) => (int) ($rows[$key] ?? 0));
    }

    /** Bookings grouped by event type, biggest first. */
    public function eventTypeBreakdown(?int $hallId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->bookingScope($hallId, $from, $to)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('event_type, COUNT(*) as total, SUM(total_amount) as amount')
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => Booking::EVENT_TYPES[$row->event_type] ?? ($row->event_type ?: 'Unspecified'),
                'total' => (int) $row->total,
                'amount' => (float) $row->amount,
            ]);
    }

    /** Expenses grouped by category, biggest first. */
    public function expenseBreakdown(?int $hallId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->expenseScope($hallId, $from, $to)
            ->with('category')
            ->selectRaw('expense_category_id, SUM(amount) as amount')
            ->groupBy('expense_category_id')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->category->name ?? 'Uncategorised',
                'amount' => (float) $row->amount,
            ]);
    }

    /**
     * Occupancy as booked lawn-days over available lawn-days.
     * Cancelled bookings are excluded — a released slot was never occupied.
     */
    public function occupancy(?int $hallId, CarbonInterface $from, CarbonInterface $to, int $lawnCount): array
    {
        $days = max($from->diffInDays($to) + 1, 1);
        $capacity = max($lawnCount * $days, 1);

        $booked = $this->bookingScope($hallId, $from, $to)
            ->where('status', '!=', 'cancelled')
            ->count();

        return [
            'booked_slots' => $booked,
            'capacity_slots' => $capacity,
            'percent' => round(min($booked / $capacity * 100, 100), 1),
        ];
    }

    /**
     * Bookings with money still owed, soonest event first — the follow-up list.
     */
    public function outstanding(?int $hallId, int $limit = 50)
    {
        return Booking::query()
            ->with(['customer', 'hall', 'lawn'])
            ->withPaymentTotals()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereNotIn('status', ['cancelled'])
            ->whereIn('payment_status', ['pending', 'partial'])
            ->orderBy('start_datetime')
            ->limit($limit)
            ->get();
    }

    /** Every event on a given day — the daily operations sheet. */
    public function dailySheet(?int $hallId, CarbonInterface $day)
    {
        return Booking::query()
            ->with(['customer', 'hall', 'lawn', 'package', 'addons', 'staff'])
            ->withPaymentTotals()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereDate('start_datetime', '<=', $day->toDateString())
            ->whereDate('end_datetime', '>=', $day->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_datetime')
            ->get();
    }

    /** Events starting within the next $days days. */
    public function upcoming(?int $hallId, int $days = 30, int $limit = 10)
    {
        return Booking::query()
            ->with(['customer', 'hall', 'lawn'])
            ->withPaymentTotals()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_datetime', [now(), now()->addDays($days)])
            ->orderBy('start_datetime')
            ->limit($limit)
            ->get();
    }

    /* ----------------------------------------------------------------- internals */

    private function bookingScope(?int $hallId, CarbonInterface $from, CarbonInterface $to)
    {
        return Booking::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereBetween('start_datetime', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    private function expenseScope(?int $hallId, CarbonInterface $from, CarbonInterface $to)
    {
        return Expense::query()
            ->when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()]);
    }

    /**
     * Build a date-keyed series covering every day in the range, so charts do
     * not silently compress missing days.
     */
    private function fillDays(CarbonInterface $from, CarbonInterface $to, callable $value): Collection
    {
        $series = collect();
        $cursor = Carbon::parse($from->toDateString());
        $last = Carbon::parse($to->toDateString());

        while ($cursor->lte($last)) {
            $key = $cursor->toDateString();
            $series->put($key, $value($key));
            $cursor->addDay();
        }

        return $series;
    }
}
