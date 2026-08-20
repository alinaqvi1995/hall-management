<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Attaches permissions to roles.
 *
 * This mapping did not exist before: roles and permissions were both seeded but
 * never linked, so every role except super_admin (which passes through
 * Gate::before) was denied on every page and rendered an empty sidebar.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Which modules each role can touch. '*' means every action on that module.
     */
    public const ROLE_MATRIX = [
        // Owns the platform. Also short-circuited by Gate::before, but the rows
        // are written anyway so the roles screen shows the truth.
        'super_admin' => '*',

        // Runs one venue end to end, including its books and its people.
        'hall_admin' => [
            'dashboard' => '*',
            'reports' => '*',
            'halls' => ['view', 'edit'],
            'bookings' => '*',
            'payments' => '*',
            'expenses' => '*',
            'packages' => '*',
            'addons' => '*',
            'customers' => '*',
            'staff' => '*',
            'vendors' => '*',
            'users' => ['view', 'create', 'edit'],
            'activityLogs' => ['view'],
        ],

        // Front-desk clerk: takes bookings and collects money, but cannot delete
        // records, manage people or see the venue's cost base.
        'manager' => [
            'dashboard' => '*',
            'reports' => ['view'],
            'halls' => ['view'],
            'bookings' => ['view', 'create', 'edit'],
            'payments' => ['view', 'create'],
            'expenses' => ['view', 'create'],
            'packages' => ['view'],
            'addons' => ['view'],
            'customers' => ['view', 'edit'],
            'staff' => ['view'],
            'vendors' => ['view'],
        ],

        // Read-only booking staff.
        'user' => [
            'dashboard' => '*',
            'halls' => ['view'],
            'bookings' => ['view'],
            'packages' => ['view'],
            'addons' => ['view'],
            'customers' => ['view'],
        ],
    ];

    public function run(): void
    {
        $permissionIds = Permission::pluck('id', 'slug');

        foreach (self::ROLE_MATRIX as $roleSlug => $modules) {
            $role = Role::where('slug', $roleSlug)->first();

            if (! $role) {
                $this->command?->warn("Role [{$roleSlug}] not found; skipped.");

                continue;
            }

            $slugs = $modules === '*'
                ? PermissionSeeder::allSlugs()
                : PermissionSeeder::slugsFor($modules);

            $ids = collect($slugs)
                ->map(fn ($slug) => $permissionIds[$slug] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $role->permissions()->sync($ids);

            $this->command?->info("Role [{$roleSlug}]: ".count($ids).' permissions attached.');
        }
    }
}
