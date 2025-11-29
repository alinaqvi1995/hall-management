<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Hall;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\Hash;

class PanelRolesSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------
        // ROLES
        // -----------------------------
        $superAdminRole = Role::updateOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $hallAdminRole  = Role::updateOrCreate(['slug' => 'hall_admin'],  ['name' => 'Hall Admin']);
        $userRole       = Role::updateOrCreate(['slug' => 'user'],        ['name' => 'User']);

        // -----------------------------
        // SUPER ADMIN
        // -----------------------------
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $superAdmin->roles()->sync([$superAdminRole->id]);

        // -----------------------------
        // STATES
        // -----------------------------
        $pakistanStates = [
            'Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan',
            'Islamabad Capital Territory', 'Azad Jammu & Kashmir', 'Gilgit Baltistan'
        ];

        $states = [];
        foreach ($pakistanStates as $stateName) {
            $states[$stateName] = State::updateOrCreate(
                ['name' => $stateName],
                [
                    'created_by' => $superAdmin->id,
                    'updated_by' => $superAdmin->id,
                ]
            );
        }

        // -----------------------------
        // CITIES
        // -----------------------------
        $citiesByProvince = [
            "Punjab" => [
                "Lahore","Faisalabad","Rawalpindi","Multan","Gujranwala",
                "Sialkot","Sargodha","Bahawalpur","Sheikhupura","Rahim Yar Khan",
                "Jhang","Gujrat","Kasur","Sahiwal","Okara","Mianwali",
                "Attock","Dera Ghazi Khan","Muzaffargarh","Chiniot","Vehari",
                "Khanewal","Hafizabad","Mandi Bahauddin","Lodhran","Narowal",
                "Pakpattan","Bhakkar","Toba Tek Singh","Jhelum","Nankana Sahib"
            ],
            "Sindh" => [
                "Karachi","Hyderabad","Sukkur","Larkana","Mirpur Khas",
                "Shaheed Benazirabad","Jacobabad","Shikarpur","Kashmore",
                "Ghotki","Dadu","Khairpur","Thatta","Badin","Sanghar",
                "Umerkot","Tharparkar","Kambar Shahdadkot"
            ],
            "Khyber Pakhtunkhwa" => [
                "Peshawar","Mardan","Abbottabad","Swat","Mansehra",
                "Kohat","Dera Ismail Khan","Charsadda","Nowshera",
                "Haripur","Bannu","Swabi","Shangla","Karak","Hangu",
                "Battagram","Buner","Khyber","Chitral"
            ],
            "Balochistan" => [
                "Quetta","Gwadar","Turbat","Khuzdar","Sibi",
                "Zhob","Loralai","Noshki","Chaman","Hub",
                "Kalāt","Panjgur","Kharan","Ziarat","Mach","Dera Murad Jamali"
            ],
            "Islamabad Capital Territory" => ["Islamabad"],
            "Azad Jammu & Kashmir" => [
                "Muzaffarabad","Mirpur","Rawalakot","Bagh","Kotli",
                "Bhimber","Neelum","Hattian Bala"
            ],
            "Gilgit Baltistan" => [
                "Gilgit","Skardu","Hunza","Ghizer","Shigar",
                "Kharmang","Astore","Ghanche"
            ]
        ];

        foreach ($citiesByProvince as $stateName => $cities) {
            $state = $states[$stateName];
            foreach ($cities as $cityName) {
                City::updateOrCreate(
                    ['name' => $cityName, 'state_id' => $state->id],
                    [
                        'created_by' => $superAdmin->id,
                        'updated_by' => $superAdmin->id,
                    ]
                );
            }
        }

        // -----------------------------
        // FARMS + FARM ADMINS
        // -----------------------------
        $halls = [];
        $hallAdmins = [];

        for ($i = 1; $i <= 3; $i++) {
            // Pick random state and city for hall
            $state = $states[array_rand($states)];
            $city = $state->cities()->inRandomOrder()->first();

            // Create Hall
            $halls[$i] = Hall::create([
                'name' => "Hall $i",
                'owner_name' => "Owner $i",
                'state' => $state->name,
                'city' => $city->name ?? 'Unknown',
                'created_by' => $superAdmin->id,
                'updated_by' => $superAdmin->id,
            ]);

            // Create Hall Admin
            $hallAdmins[$i] = User::create([
                'name' => "Hall Admin $i",
                'email' => "halladmin$i@mail.com",
                'password' => Hash::make('12345678'),
                'hall_id' => $halls[$i]->id,
            ]);

            $hallAdmins[$i]->roles()->sync([$hallAdminRole->id]);
        }

        // -----------------------------
        // NORMAL USERS (3 per hall)
        // -----------------------------
        for ($i = 1; $i <= 3; $i++) {
            for ($u = 1; $u <= 3; $u++) {
                $user = User::create([
                    'name' => "User{$i}_{$u}",
                    'email' => "user{$i}{$u}@mail.com",
                    'password' => Hash::make('12345678'),
                    'hall_id' => $halls[$i]->id,
                ]);

                $user->roles()->sync([$userRole->id]);
            }
        }
        
        // $permissions = [
        //     // Dashboard
        //     ['name' => 'View Dashboard', 'slug' => 'view-dashboard'],

        //     // Profile
        //     ['name' => 'View Profile', 'slug' => 'view-profile'],
        //     ['name' => 'Edit Profile', 'slug' => 'edit-profile'],
        //     ['name' => 'Delete Profile', 'slug' => 'delete-profile'],

        //     // Categories
        //     ['name' => 'View Categories', 'slug' => 'view-categories'],
        //     ['name' => 'Create Categories', 'slug' => 'create-categories'],
        //     ['name' => 'Edit Categories', 'slug' => 'edit-categories'],
        //     ['name' => 'Delete Categories', 'slug' => 'delete-categories'],

        //     // Subcategories
        //     ['name' => 'View Subcategories', 'slug' => 'view-subcategories'],
        //     ['name' => 'Create Subcategories', 'slug' => 'create-subcategories'],
        //     ['name' => 'Edit Subcategories', 'slug' => 'edit-subcategories'],
        //     ['name' => 'Delete Subcategories', 'slug' => 'delete-subcategories'],

        //     // Blogs
        //     ['name' => 'View Blogs', 'slug' => 'view-blogs'],
        //     ['name' => 'Create Blogs', 'slug' => 'create-blogs'],
        //     ['name' => 'Edit Blogs', 'slug' => 'edit-blogs'],
        //     ['name' => 'Delete Blogs', 'slug' => 'delete-blogs'],

        //     // Services
        //     ['name' => 'View Services', 'slug' => 'view-services'],
        //     ['name' => 'Create Services', 'slug' => 'create-services'],
        //     ['name' => 'Edit Services', 'slug' => 'edit-services'],
        //     ['name' => 'Delete Services', 'slug' => 'delete-services'],

        // ];

        // foreach ($permissions as $perm) {
        //     Permission::updateOrCreate(
        //         ['slug' => $perm['slug']],
        //         ['name' => $perm['name']]
        //     );
        // }

        $this->command->info("✅ Seeded: SuperAdmin, All States, Cities, 3 Halls, 3 HallAdmins, 9 Users.");
    }
}
