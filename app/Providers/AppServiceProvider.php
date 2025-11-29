<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\Permission;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Repositories\Hall\HallRepositoryInterface;
use App\Repositories\Hall\HallRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HallRepositoryInterface::class, HallRepository::class);
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        try {
            Permission::pluck('slug')->each(function ($slug) {
                Gate::define($slug, function (User $user) use ($slug) {
                    return $user->hasPermission($slug);
                });
            });
        } catch (\Exception $e) {
        }
    }
}
