<?php
namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        // --- Superadmin: global stats ---
        if ($user->hasRole('super_admin')) {
            $userCount = User::count();
            $hallCount = Hall::count();

            // Growth charts
            $usersLast7Days = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->pluck('count', 'date');

            $hallsLast7Days = Hall::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->pluck('count', 'date');
        } else {
                                      // --- Hall Admin: hall-specific stats ---
            $hall      = $user->hall; // Assuming each user is linked to a hall
            $userCount = $hall->users()->count();
            $hallCount = null; // Not shown to hall admin

            $usersLast7Days = $hall->users()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->pluck('count', 'date');

            $hallsLast7Days = collect(); // Not shown
        }

        return view('dashboard.index', compact(
            'userCount',
            'hallCount',
            'usersLast7Days',
            'hallsLast7Days',
        ));
    }
}
