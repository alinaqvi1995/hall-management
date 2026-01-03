<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Blog;
use App\Models\User;
use App\Models\Hall;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $currentUser = Auth::user();

        $usersQuery = User::query();
        $hallsQuery = Hall::query();
        $bookingsQuery = Booking::query();

        if ($currentUser && !$currentUser->hasRole('super_admin')) {
            $usersQuery->where('hall_id', $currentUser->hall_id);
            $hallsQuery->where('id', $currentUser->hall_id);
            $bookingsQuery->where('hall_id', $currentUser->hall_id);
        }

        $view->with([
            'usersCount' => $usersQuery->count(),
            'blogsCount' => Blog::count(),
            'categoriesCount' => Category::count(),
            'subcategoriesCount' => Subcategory::count(),
            'hallsCount' => $hallsQuery->count(),
            'bookingsCount' => $bookingsQuery->count(),
        ]);
    }
}
