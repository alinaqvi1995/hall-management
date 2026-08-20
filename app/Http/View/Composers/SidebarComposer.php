<?php

namespace App\Http\View\Composers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Hall;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Counts shown as badges beside the sidebar links.
     *
     * Only computed once per request and only for signed-in users — the previous
     * version ran a handful of COUNT queries on every view render, including
     * guest pages, error pages and mail templates.
     *
     * @var array<string, int>|null
     */
    private static ?array $cache = null;

    public function compose(View $view): void
    {
        $view->with('sidebarCounts', static::$cache ??= $this->counts());
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $hallId = $user->isSuperAdmin() ? null : ($user->hall_id ?: 0);
        $scope = fn ($query) => $hallId === null ? $query : $query->where('hall_id', $hallId);

        return [
            'halls' => $hallId === null ? Hall::count() : Hall::where('id', $hallId)->count(),
            'bookings' => $scope(Booking::query())->whereNot('status', 'cancelled')->count(),
            'upcoming' => $scope(Booking::query())
                ->whereNot('status', 'cancelled')
                ->where('start_datetime', '>=', now())
                ->count(),
            'payments' => $scope(Payment::query())->count(),
            'expenses' => $scope(Expense::query())->count(),
            'packages' => $scope(Package::query())->count(),
            'staff' => $scope(Staff::query())->count(),
            'vendors' => $scope(Vendor::query())->count(),
            'users' => $hallId === null ? User::count() : User::where('hall_id', $hallId)->count(),
            'customers' => Customer::visibleTo($user)->count(),
        ];
    }
}
