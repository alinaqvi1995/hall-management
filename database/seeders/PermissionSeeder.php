<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard'],

            // Profile
            ['name' => 'View Profile', 'slug' => 'view-profile'],
            ['name' => 'Edit Profile', 'slug' => 'edit-profile'],

            // Bookings
            ['name' => 'View Bookings', 'slug' => 'view-bookings'],
            ['name' => 'Create Bookings', 'slug' => 'create-bookings'],
            ['name' => 'Edit Bookings', 'slug' => 'edit-bookings'],
            ['name' => 'Delete Bookings', 'slug' => 'delete-bookings'],

            // Halls
            ['name' => 'View Halls', 'slug' => 'view-halls'],
            ['name' => 'Create Halls', 'slug' => 'create-halls'],
            ['name' => 'Edit Halls', 'slug' => 'edit-halls'],
            ['name' => 'Delete Halls', 'slug' => 'delete-halls'],

            // Users
            ['name' => 'View Users', 'slug' => 'view-users'],
            ['name' => 'Create Users', 'slug' => 'create-users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'view-roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles'],

            // Permissions
            ['name' => 'View Permissions', 'slug' => 'view-permissions'],
            ['name' => 'Create Permissions', 'slug' => 'create-permissions'],
            ['name' => 'Edit Permissions', 'slug' => 'edit-permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'delete-permissions'],

            // Categories
            ['name' => 'View Categories', 'slug' => 'view-categories'],
            ['name' => 'Create Categories', 'slug' => 'create-categories'],
            ['name' => 'Edit Categories', 'slug' => 'edit-categories'],
            ['name' => 'Delete Categories', 'slug' => 'delete-categories'],

            // Subcategories
            ['name' => 'View Subcategories', 'slug' => 'view-subcategories'],
            ['name' => 'Create Subcategories', 'slug' => 'create-subcategories'],
            ['name' => 'Edit Subcategories', 'slug' => 'edit-subcategories'],
            ['name' => 'Delete Subcategories', 'slug' => 'delete-subcategories'],

            // Cities
            ['name' => 'View Cities', 'slug' => 'view-cities'],
            ['name' => 'Create Cities', 'slug' => 'create-cities'],
            ['name' => 'Edit Cities', 'slug' => 'edit-cities'],
            ['name' => 'Delete Cities', 'slug' => 'delete-cities'],

            // States
            ['name' => 'View States', 'slug' => 'view-states'],
            ['name' => 'Create States', 'slug' => 'create-states'],
            ['name' => 'Edit States', 'slug' => 'edit-states'],
            ['name' => 'Delete States', 'slug' => 'delete-states'],

            // Blogs
            ['name' => 'View Blogs', 'slug' => 'view-blogs'],
            ['name' => 'Create Blogs', 'slug' => 'create-blogs'],
            ['name' => 'Edit Blogs', 'slug' => 'edit-blogs'],
            ['name' => 'Delete Blogs', 'slug' => 'delete-blogs'],

            // Activity Logs
            ['name' => 'View Activity Logs', 'slug' => 'view-activityLogs'],

            // Trusted IPs
            ['name' => 'View Trusted IPs', 'slug' => 'view-trustedIps'],
            ['name' => 'Create Trusted IPs', 'slug' => 'create-trustedIps'],
            ['name' => 'Edit Trusted IPs', 'slug' => 'edit-trustedIps'],
            ['name' => 'Delete Trusted IPs', 'slug' => 'delete-trustedIps'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name']]
            );
        }
    }
}
