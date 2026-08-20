<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            PanelRolesSeeder::class,
            // Must run after both of the above: it links the two tables, which
            // nothing did before, leaving every non-super-admin role with zero
            // permissions.
            RolePermissionSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
