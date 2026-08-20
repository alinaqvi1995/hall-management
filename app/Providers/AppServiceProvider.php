<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\Permission;
use App\Models\User;
use App\Policies\BookingPolicy;
use App\Policies\HallPolicy;
use App\Policies\UserPolicy;
use App\Repositories\Hall\HallRepository;
use App\Repositories\Hall\HallRepositoryInterface;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HallRepositoryInterface::class, HallRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Hall::class, HallPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (User $user) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });

        $this->registerPermissionGates();

        Event::listen(
            [Login::class, Logout::class, Failed::class, Lockout::class],
            LogAuthenticationActivity::class
        );
    }

    /**
     * Turn every row in `permissions` into a gate ability, so `@can('view-bookings')`
     * works in Blade without a per-check database lookup.
     *
     * Wrapped because this runs during `migrate` and `db:seed` too, when the
     * table may not exist yet.
     */
    private function registerPermissionGates(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }

            foreach (Permission::pluck('slug') as $slug) {
                Gate::define($slug, fn (User $user) => $user->hasPermission($slug));
            }
        } catch (QueryException) {
            // No usable database connection yet (fresh install, no .env).
        }
    }
}
