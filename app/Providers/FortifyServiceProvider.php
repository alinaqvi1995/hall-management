<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Hall;
use App\Models\Booking;
use App\Policies\UserPolicy;
use App\Policies\HallPolicy;
use App\Policies\BookingPolicy;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies individually
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Hall::class, HallPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
