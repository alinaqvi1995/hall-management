<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Roles, the platform owner account, and Pakistan's provinces and cities.
 * Venue/booking sample data lives in DemoDataSeeder.
 */
class PanelRolesSeeder extends Seeder
{
    public const ROLES = [
        'super_admin' => 'Super Admin',
        'hall_admin' => 'Hall Admin',
        'manager' => 'Manager',
        'user' => 'Staff',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $slug => $name) {
            Role::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->roles()->sync([Role::where('slug', 'super_admin')->value('id')]);

        $this->seedLocations($superAdmin->id);

        $this->command?->info('Seeded roles, super admin, provinces and cities.');
    }

    private function seedLocations(int $userId): void
    {
        foreach (self::CITIES_BY_PROVINCE as $provinceName => $cities) {
            $state = State::updateOrCreate(
                ['name' => $provinceName],
                ['created_by' => $userId, 'updated_by' => $userId]
            );

            foreach ($cities as $cityName) {
                City::updateOrCreate(
                    ['name' => $cityName, 'state_id' => $state->id],
                    ['created_by' => $userId, 'updated_by' => $userId]
                );
            }
        }
    }

    /** Pakistan's provinces/territories and their principal cities. */
    private const CITIES_BY_PROVINCE = [
        'Punjab' => [
            'Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Gujranwala',
            'Sialkot', 'Sargodha', 'Bahawalpur', 'Sheikhupura', 'Rahim Yar Khan',
            'Jhang', 'Gujrat', 'Kasur', 'Sahiwal', 'Okara', 'Mianwali',
            'Attock', 'Dera Ghazi Khan', 'Muzaffargarh', 'Chiniot', 'Vehari',
            'Khanewal', 'Hafizabad', 'Mandi Bahauddin', 'Lodhran', 'Narowal',
            'Pakpattan', 'Bhakkar', 'Toba Tek Singh', 'Jhelum', 'Nankana Sahib',
        ],
        'Sindh' => [
            'Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Mirpur Khas',
            'Shaheed Benazirabad', 'Jacobabad', 'Shikarpur', 'Kashmore',
            'Ghotki', 'Dadu', 'Khairpur', 'Thatta', 'Badin', 'Sanghar',
            'Umerkot', 'Tharparkar', 'Kamber Shahdadkot',
        ],
        'Khyber Pakhtunkhwa' => [
            'Peshawar', 'Mardan', 'Abbottabad', 'Swat', 'Mansehra',
            'Kohat', 'Dera Ismail Khan', 'Charsadda', 'Nowshera',
            'Haripur', 'Bannu', 'Swabi', 'Shangla', 'Karak', 'Hangu',
            'Battagram', 'Buner', 'Khyber', 'Chitral',
        ],
        'Balochistan' => [
            'Quetta', 'Gwadar', 'Turbat', 'Khuzdar', 'Sibi',
            'Zhob', 'Loralai', 'Noshki', 'Chaman', 'Hub',
            'Kalat', 'Panjgur', 'Kharan', 'Ziarat', 'Mach', 'Dera Murad Jamali',
        ],
        'Islamabad Capital Territory' => ['Islamabad'],
        'Azad Jammu & Kashmir' => [
            'Muzaffarabad', 'Mirpur', 'Rawalakot', 'Bagh', 'Kotli',
            'Bhimber', 'Neelum', 'Hattian Bala',
        ],
        'Gilgit Baltistan' => [
            'Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Shigar',
            'Kharmang', 'Astore', 'Ghanche',
        ],
    ];
}
