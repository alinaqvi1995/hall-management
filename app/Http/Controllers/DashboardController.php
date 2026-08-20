<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hall;
use App\Models\Lawn;
use App\Models\User;
use App\Services\ReportService;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct(protected ReportService $reports) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $hallId = $this->scopedHallId();

        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        // A hall admin whose account was never linked to a hall used to hit a
        // fatal here; now they get an empty dashboard and a clear warning.
        $hall = $hallId ? Hall::find($hallId) : null;

        $summary = $this->reports->summary($hallId, $from, $to);

        return view('dashboard.index', [
            'hall' => $hall,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'needsHallLink' => ! $user->isSuperAdmin() && ! $user->hall_id,

            'summary' => $summary,
            'occupancy' => $this->reports->occupancy($hallId, $from, $to, $this->lawnCount($hallId)),

            'hallCount' => $user->isSuperAdmin() ? Hall::count() : null,
            'userCount' => User::when($hallId, fn ($q) => $q->where('hall_id', $hallId))->count(),
            'lawnCount' => $this->lawnCount($hallId),

            'todayBookings' => $this->reports->dailySheet($hallId, now()),
            'upcoming' => $this->reports->upcoming($hallId, 30, 8),
            'outstanding' => $this->reports->outstanding($hallId, 8),

            'revenueByDay' => $this->reports->revenueByDay($hallId, now()->subDays(29), now()),
            'bookingsByDay' => $this->reports->bookingsByDay($hallId, now()->subDays(29), now()),
            'eventTypes' => $this->reports->eventTypeBreakdown($hallId, $from, $to),

            'statusCounts' => $this->statusCounts($hallId),
        ]);
    }

    /** Live booking counts by status, for the status strip. */
    private function statusCounts(?int $hallId): array
    {
        $rows = Booking::when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending' => (int) ($rows['pending'] ?? 0),
            'confirmed' => (int) ($rows['confirmed'] ?? 0),
            'completed' => (int) ($rows['completed'] ?? 0),
            'cancelled' => (int) ($rows['cancelled'] ?? 0),
        ];
    }

    private function lawnCount(?int $hallId): int
    {
        return Lawn::when($hallId, fn ($q) => $q->where('hall_id', $hallId))
            ->when(! $hallId, fn ($q) => $q->whereIn('hall_id', Hall::visibleTo()->select('id')))
            ->count();
    }
}
