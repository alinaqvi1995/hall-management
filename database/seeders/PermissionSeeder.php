<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Every ability in the system, grouped by module.
     *
     * Keys are module labels; values are the actions available on that module.
     * Slugs come out as `<action>-<module key>`, e.g. `view-bookings`.
     */
    public const MODULES = [
        'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
        'reports' => ['label' => 'Reports', 'actions' => ['view']],
        'halls' => ['label' => 'Halls', 'actions' => ['view', 'create', 'edit', 'delete']],
        'bookings' => ['label' => 'Bookings', 'actions' => ['view', 'create', 'edit', 'delete']],
        'payments' => ['label' => 'Payments', 'actions' => ['view', 'create', 'delete']],
        'expenses' => ['label' => 'Expenses', 'actions' => ['view', 'create', 'edit', 'delete']],
        'packages' => ['label' => 'Packages', 'actions' => ['view', 'create', 'edit', 'delete']],
        'addons' => ['label' => 'Extra Services', 'actions' => ['view', 'create', 'edit', 'delete']],
        'customers' => ['label' => 'Customers', 'actions' => ['view', 'edit']],
        'staff' => ['label' => 'Staff', 'actions' => ['view', 'create', 'edit', 'delete']],
        'vendors' => ['label' => 'Vendors', 'actions' => ['view', 'create', 'edit', 'delete']],
        'users' => ['label' => 'Users', 'actions' => ['view', 'create', 'edit', 'delete']],
        'roles' => ['label' => 'Roles', 'actions' => ['view', 'create', 'edit', 'delete']],
        'permissions' => ['label' => 'Permissions', 'actions' => ['view', 'create', 'edit', 'delete']],
        'states' => ['label' => 'Provinces', 'actions' => ['view', 'create', 'edit', 'delete']],
        'cities' => ['label' => 'Cities', 'actions' => ['view', 'create', 'edit', 'delete']],
        'activityLogs' => ['label' => 'Activity Logs', 'actions' => ['view']],
        'trustedIps' => ['label' => 'Trusted IPs', 'actions' => ['view', 'create', 'edit', 'delete']],
    ];

    private const ACTION_LABELS = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ];

    public function run(): void
    {
        $keep = [];

        foreach (self::MODULES as $module => $config) {
            foreach ($config['actions'] as $action) {
                $slug = $action.'-'.$module;
                $keep[] = $slug;

                Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => self::ACTION_LABELS[$action].' '.$config['label']]
                );
            }
        }

        // Drop abilities left over from the removed blog/category modules so the
        // roles screen does not offer permissions that no longer guard anything.
        Permission::whereNotIn('slug', $keep)->delete();

        $this->command?->info('Seeded '.count($keep).' permissions.');
    }

    /** All slugs, for the role seeder. @return array<int, string> */
    public static function allSlugs(): array
    {
        $slugs = [];

        foreach (self::MODULES as $module => $config) {
            foreach ($config['actions'] as $action) {
                $slugs[] = $action.'-'.$module;
            }
        }

        return $slugs;
    }

    /** Slugs for the given modules. @return array<int, string> */
    public static function slugsFor(array $modules): array
    {
        $slugs = [];

        foreach ($modules as $module => $actions) {
            $available = self::MODULES[$module]['actions'] ?? [];

            foreach (($actions === '*' ? $available : (array) $actions) as $action) {
                if (in_array($action, $available, true)) {
                    $slugs[] = $action.'-'.$module;
                }
            }
        }

        return $slugs;
    }

    /** Human label for a slug, used by the roles UI. */
    public static function labelFor(string $slug): string
    {
        [$action, $module] = array_pad(explode('-', $slug, 2), 2, '');

        return (self::ACTION_LABELS[$action] ?? Str::title($action))
            .' '.(self::MODULES[$module]['label'] ?? Str::title($module));
    }
}
