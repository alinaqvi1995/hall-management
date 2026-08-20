<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\City;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Hall;
use App\Models\Lawn;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\State;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample venues, bookings, payments and expenses so every screen has data.
 * Safe to re-run: existing rows are matched on their natural keys.
 */
class DemoDataSeeder extends Seeder
{
    private const VENUES = [
        [
            'name' => 'Shalimar Grand Marquee',
            'owner' => 'Ch. Muhammad Aslam',
            'phone' => '0301-4567890',
            'city' => 'Lahore',
            'province' => 'Punjab',
            'area' => 'Johar Town',
            'type' => 'marquee',
            'capacity' => 1200,
            'rate' => 2200,
            'lawns' => [['Main Hall', 700], ['Rose Lawn', 350], ['Family Hall', 200]],
        ],
        [
            'name' => 'Gulshan Banquet Complex',
            'owner' => 'Syed Kamran Ali',
            'phone' => '0321-8887766',
            'city' => 'Karachi',
            'province' => 'Sindh',
            'area' => 'Gulshan-e-Iqbal',
            'type' => 'banquet',
            'capacity' => 900,
            'rate' => 2600,
            'lawns' => [['Emerald Hall', 500], ['Pearl Hall', 400]],
        ],
        [
            'name' => 'Margalla View Lawns',
            'owner' => 'Mrs. Sadia Khan',
            'phone' => '0333-2223344',
            'city' => 'Islamabad',
            'province' => 'Islamabad Capital Territory',
            'area' => 'F-11',
            'type' => 'lawn',
            'capacity' => 600,
            'rate' => 3000,
            'lawns' => [['Open Lawn', 400], ['Covered Marquee', 200]],
        ],
    ];

    private const PACKAGES = [
        ['Standard Buffet', 'buffet', 1650, 150, ['Chicken Karahi', 'Beef Pulao', 'Mix Vegetable', 'Naan', 'Salad', 'Kheer']],
        ['Premium Buffet', 'buffet', 2400, 200, ['Mutton Karahi', 'Chicken Tikka', 'Beef Biryani', 'Fish Fry', 'Naan', 'Raita', 'Gulab Jamun', 'Ice Cream']],
        ['One Dish (Govt. Rule)', 'one_dish', 950, 100, ['Chicken Biryani', 'Raita', 'Salad', 'Soft Drink']],
        ['Nikah Set Menu', 'set_menu', 1300, 80, ['Chicken Qorma', 'Zarda', 'Naan', 'Salad']],
    ];

    private const ADDONS = [
        ['Stage Decoration', 35000, 'fixed'],
        ['Floral Entrance', 18000, 'fixed'],
        ['DJ & Sound System', 25000, 'fixed'],
        ['Photography & Videography', 60000, 'fixed'],
        ['Cold Drinks', 120, 'per_head'],
        ['Welcome Drink', 90, 'per_head'],
        ['Fireworks / Sparklers', 22000, 'fixed'],
        ['Generator Backup', 15000, 'fixed'],
    ];

    private const EXPENSE_CATEGORIES = [
        'Catering & Food', 'Decoration', 'Utilities', 'Staff Wages',
        'Maintenance', 'Marketing', 'Rent & Taxes', 'Miscellaneous',
    ];

    private const CUSTOMERS = [
        ['Muhammad Bilal Ahmed', '0300-1234567', '35202-1234567-1', 'bilal.ahmed@example.com', 'House 22, Model Town, Lahore'],
        ['Ayesha Siddiqui', '0333-9876543', '42101-9876543-2', 'ayesha.s@example.com', 'Flat 5B, Clifton, Karachi'],
        ['Usman Tariq', '0345-5551122', '61101-5551122-3', null, 'Street 14, G-9, Islamabad'],
        ['Fatima Noor', '0312-4443322', '35201-4443322-4', 'fatima.noor@example.com', 'Wapda Town, Lahore'],
        ['Hassan Raza', '0301-7778899', '42201-7778899-5', null, 'North Nazimabad, Karachi'],
        ['Zainab Malik', '0322-1112233', '35102-1112233-6', 'zainab.malik@example.com', 'Cantt, Rawalpindi'],
        ['Abdul Rehman Sheikh', '0308-6665544', '33100-6665544-7', null, 'Satellite Town, Faisalabad'],
        ['Sana Javed', '0334-2223311', '17301-2223311-8', 'sana.j@example.com', 'University Road, Peshawar'],
    ];

    private const STAFF = [
        ['Ghulam Rasool', 'Hall Manager', 'permanent', 65000],
        ['Nadeem Akhtar', 'Head Waiter', 'permanent', 38000],
        ['Shahid Mehmood', 'Chef', 'permanent', 75000],
        ['Iqbal Hussain', 'Security Guard', 'permanent', 30000],
        ['Rashid Ali', 'Waiter', 'daily_wage', null],
        ['Kashif Nawaz', 'Cleaner', 'daily_wage', null],
        ['Tanveer Abbas', 'Electrician', 'contract', 25000],
    ];

    private const VENDORS = [
        ['Al-Madina Caterers', 'Catering', '0301-2223344'],
        ['Royal Decor Services', 'Decoration', '0321-5556677'],
        ['SoundWave DJ', 'Sound & Lighting', '0333-8889900'],
        ['PicturePerfect Studio', 'Photography', '0345-1112233'],
        ['Kohinoor Generators', 'Power Backup', '0300-4445566'],
    ];

    public function run(): void
    {
        $superAdminId = User::where('email', 'superadmin@mail.com')->value('id');
        $hallAdminRole = Role::where('slug', 'hall_admin')->value('id');
        $managerRole = Role::where('slug', 'manager')->value('id');
        $staffRole = Role::where('slug', 'user')->value('id');

        foreach (self::EXPENSE_CATEGORIES as $name) {
            ExpenseCategory::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                ['name' => $name]
            );
        }

        $customers = collect(self::CUSTOMERS)->map(fn ($c) => Customer::updateOrCreate(
            ['cnic' => $c[2]],
            ['name' => $c[0], 'phone' => $c[1], 'email' => $c[3], 'address' => $c[4]]
        ));

        foreach (self::VENUES as $index => $venue) {
            $this->seedVenue(
                $venue,
                $index + 1,
                $superAdminId,
                $customers,
                compact('hallAdminRole', 'managerRole', 'staffRole')
            );
        }

        $this->command?->info('Seeded '.count(self::VENUES).' venues with bookings, payments and expenses.');
    }

    private function seedVenue(array $venue, int $n, ?int $superAdminId, $customers, array $roles): void
    {
        $state = State::where('name', $venue['province'])->first();
        $city = City::where('name', $venue['city'])->where('state_id', $state?->id)->first();

        $hall = Hall::updateOrCreate(
            ['name' => $venue['name']],
            [
                'owner_name' => $venue['owner'],
                'phone' => $venue['phone'],
                'email' => 'info@'.str($venue['name'])->slug()->value().'.pk',
                'address' => $venue['area'].', '.$venue['city'],
                'state_id' => $state?->id,
                'city_id' => $city?->id,
                'state' => $venue['province'],
                'city' => $venue['city'],
                'country' => 'Pakistan',
                'area' => $venue['area'],
                'hall_types' => $venue['type'],
                'hall_capacity' => $venue['capacity'],
                'default_per_head_rate' => $venue['rate'],
                'advance_policy_percent' => 25,
                'cancellation_charge_percent' => 10,
                'tax_percent' => 5,
                'registration_number' => 'REG-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'ntn_number' => '： '.rand(1000000, 9999999).'-'.rand(1, 9),
                'gst_number' => '17-00-'.rand(1000, 9999).'-'.rand(100, 999).'-'.rand(10, 99),
                'established_at' => now()->subYears(rand(3, 15))->startOfYear(),
                'description' => $venue['name'].' offers air-conditioned halls, ample parking and in-house catering for weddings and corporate events in '.$venue['city'].'.',
                'status' => 1,
                'created_by' => $superAdminId,
                'updated_by' => $superAdminId,
            ]
        );

        // Fix the NTN placeholder that picked up a stray character.
        $hall->update(['ntn_number' => rand(1000000, 9999999).'-'.rand(1, 9)]);

        foreach ($venue['lawns'] as [$lawnName, $lawnCapacity]) {
            Lawn::updateOrCreate(
                ['hall_id' => $hall->id, 'name' => $lawnName],
                ['capacity' => $lawnCapacity, 'created_by' => $superAdminId, 'updated_by' => $superAdminId]
            );
        }

        $this->seedTeam($hall, $n, $roles);

        foreach (self::PACKAGES as [$pName, $pType, $pRate, $pMin, $pItems]) {
            Package::updateOrCreate(
                ['hall_id' => $hall->id, 'name' => $pName],
                [
                    'type' => $pType,
                    // Nudge rates per venue so the halls are not identical.
                    'per_head_rate' => $pRate + ($n - 1) * 150,
                    'min_guests' => $pMin,
                    'items' => $pItems,
                    'description' => $pName.' served buffet style with unlimited refills.',
                    'is_active' => true,
                    'created_by' => $superAdminId,
                    'updated_by' => $superAdminId,
                ]
            );
        }

        foreach (self::ADDONS as [$aName, $aPrice, $aMode]) {
            Addon::updateOrCreate(
                ['hall_id' => $hall->id, 'name' => $aName],
                ['price' => $aPrice, 'pricing_mode' => $aMode, 'is_active' => true]
            );
        }

        foreach (self::STAFF as [$sName, $sRole, $sType, $sSalary]) {
            Staff::updateOrCreate(
                ['hall_id' => $hall->id, 'name' => $sName],
                [
                    'designation' => $sRole,
                    'employment_type' => $sType,
                    'monthly_salary' => $sSalary,
                    'phone' => '03'.rand(10, 45).'-'.rand(1000000, 9999999),
                    'joined_on' => now()->subMonths(rand(2, 48)),
                    'is_active' => true,
                ]
            );
        }

        foreach (self::VENDORS as [$vName, $vService, $vPhone]) {
            Vendor::updateOrCreate(
                ['hall_id' => $hall->id, 'name' => $vName],
                ['service_type' => $vService, 'phone' => $vPhone, 'company' => $vName, 'is_active' => true]
            );
        }

        $this->seedBookings($hall, $customers, $superAdminId);
    }

    private function seedTeam(Hall $hall, int $n, array $roles): void
    {
        $accounts = [
            ["halladmin{$n}@mail.com", "Hall Admin {$n}", $roles['hallAdminRole']],
            ["manager{$n}@mail.com", "Manager {$n}", $roles['managerRole']],
            ["staff{$n}@mail.com", "Staff {$n}", $roles['staffRole']],
        ];

        foreach ($accounts as [$email, $name, $roleId]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('12345678'),
                    'hall_id' => $hall->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($roleId) {
                $user->roles()->sync([$roleId]);
            }
        }
    }

    /**
     * Bookings spread across the past and the next two months, with payments and
     * expenses attached, so reports and the calendar have something to show.
     */
    private function seedBookings(Hall $hall, $customers, ?int $superAdminId): void
    {
        if ($hall->bookings()->exists()) {
            return; // Already seeded; do not duplicate.
        }

        $lawns = $hall->lawns()->get();
        $packages = $hall->packages()->get();
        $addons = $hall->addons()->get();
        $categories = ExpenseCategory::pluck('id', 'slug');
        $vendors = $hall->vendors()->pluck('id');
        $eventTypes = array_keys(Booking::EVENT_TYPES);

        // Day offsets relative to today, with the status each should carry.
        $schedule = [
            [-52, 'completed'], [-41, 'completed'], [-33, 'cancelled'],
            [-27, 'completed'], [-18, 'completed'], [-11, 'completed'],
            [-5, 'completed'], [0, 'confirmed'], [3, 'confirmed'],
            [7, 'confirmed'], [12, 'pending'], [16, 'confirmed'],
            [21, 'pending'], [28, 'confirmed'], [35, 'pending'],
            [44, 'confirmed'], [51, 'pending'],
        ];

        foreach ($schedule as $i => [$offset, $status]) {
            $lawn = $lawns[$i % $lawns->count()];
            $package = $packages[$i % $packages->count()];
            $customer = $customers[$i % $customers->count()];

            $guests = min(
                (int) ($lawn->capacity * (0.55 + ($i % 4) * 0.1)),
                max($lawn->capacity, 50)
            );
            $guests = max($guests, $package->min_guests);

            $start = now()->addDays($offset)->setTime($i % 2 === 0 ? 19 : 13, 0);
            $end = (clone $start)->addHours(5);

            $rate = (float) $package->per_head_rate;
            $rent = 0.0;
            $menuAmount = round($guests * $rate, 2);

            // Two add-ons per booking, rotating through the catalogue.
            $chosen = $addons->slice($i % max($addons->count() - 1, 1), 2);
            $addonsAmount = 0.0;
            $pivot = [];

            foreach ($chosen as $addon) {
                $multiplier = $addon->pricing_mode === 'per_head' ? $guests : 1;
                $line = round((float) $addon->price * $multiplier, 2);
                $addonsAmount += $line;
                $pivot[$addon->id] = ['quantity' => 1, 'unit_price' => $addon->price, 'line_total' => $line];
            }

            $discount = $i % 5 === 0 ? 15000 : 0;
            $taxPercent = (float) $hall->tax_percent;
            $subtotal = max($menuAmount + $addonsAmount + $rent - $discount, 0);
            $taxAmount = round($subtotal * $taxPercent / 100, 2);
            $total = round($subtotal + $taxAmount, 2);

            $booking = Booking::create([
                'hall_id' => $hall->id,
                'lawn_id' => $lawn->id,
                'package_id' => $package->id,
                'customer_id' => $customer->id,
                'event_type' => $eventTypes[$i % count($eventTypes)],
                'booking_number' => $this->bookingNumber($hall, $start, $i),
                'start_datetime' => $start,
                'end_datetime' => $end,
                'capacity' => $guests,
                'guest_count' => $guests,
                'per_head_rate' => $rate,
                'menu_amount' => $menuAmount,
                'addons_amount' => $addonsAmount,
                'hall_rent' => $rent,
                'discount' => $discount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'quote_price' => $total,
                'booking_price' => $total,
                'status' => $status,
                'payment_status' => 'pending',
                'notes' => $status === 'cancelled' ? 'Customer postponed the event.' : null,
                'cancelled_at' => $status === 'cancelled' ? $start->copy()->subDays(9) : null,
                'cancellation_reason' => $status === 'cancelled' ? 'Family emergency; event postponed indefinitely.' : null,
                'cancellation_charge' => $status === 'cancelled' ? round($total * 0.1, 2) : 0,
                'cancelled_by' => $status === 'cancelled' ? $superAdminId : null,
                'created_by' => $superAdminId,
                'updated_by' => $superAdminId,
            ]);

            $booking->addons()->sync($pivot);

            $this->seedPayments($booking, $status, $total, $superAdminId);
            $this->seedExpenses($booking, $status, $categories, $vendors, $superAdminId);
        }

        $this->seedOverheads($hall, $categories, $vendors, $superAdminId);
    }

    private function bookingNumber(Hall $hall, $start, int $i): string
    {
        $code = collect(preg_split('/\s+/', $hall->name))
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->join('');

        return substr($code, 0, 4).$start->format('dmy').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Past events are settled; upcoming ones carry an advance or nothing yet. */
    private function seedPayments(Booking $booking, string $status, float $total, ?int $userId): void
    {
        $plan = match ($status) {
            'completed' => [[0.25, -40], [0.45, -12], [0.30, -1]],
            'confirmed' => [[0.25, -20], [0.25, -6]],
            'pending' => $booking->id % 3 === 0 ? [[0.15, -4]] : [],
            'cancelled' => [[0.25, -35]],
            default => [],
        };

        $seq = 1;
        $paid = 0.0;

        foreach ($plan as [$share, $dayOffset]) {
            $amount = round($total * $share, 2);

            if ($amount <= 0) {
                continue;
            }

            $paid += $amount;

            Payment::create([
                'booking_id' => $booking->id,
                'hall_id' => $booking->hall_id,
                'receipt_number' => 'RCPT-'.$booking->booking_number.'-'.str_pad((string) $seq++, 2, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'method' => ['cash', 'bank_transfer', 'easypaisa', 'cheque'][$seq % 4],
                'direction' => 'in',
                'paid_on' => now()->addDays($dayOffset)->toDateString(),
                'reference' => 'TXN'.rand(100000, 999999),
                'received_by' => $userId,
            ]);
        }

        // A cancelled booking refunds whatever exceeded the forfeit.
        if ($status === 'cancelled' && $paid > 0) {
            $refund = round(max($paid - (float) $booking->cancellation_charge, 0), 2);

            if ($refund > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'hall_id' => $booking->hall_id,
                    'receipt_number' => 'RCPT-'.$booking->booking_number.'-'.str_pad((string) $seq, 2, '0', STR_PAD_LEFT),
                    'amount' => $refund,
                    'method' => 'bank_transfer',
                    'direction' => 'refund',
                    'paid_on' => now()->subDays(30)->toDateString(),
                    'notes' => 'Refund after deducting cancellation charge.',
                    'received_by' => $userId,
                ]);
            }
        }

        $paidNet = (float) Payment::where('booking_id', $booking->id)->where('direction', 'in')->sum('amount')
            - (float) Payment::where('booking_id', $booking->id)->where('direction', 'refund')->sum('amount');

        $booking->update([
            'advance_paid' => max($paidNet, 0),
            'payment_status' => match (true) {
                $status === 'cancelled' && $paidNet <= 0.009 => 'refunded',
                $total > 0 && $paidNet >= $total - 0.009 => 'paid',
                $paidNet > 0.009 => 'partial',
                default => 'pending',
            },
        ]);
    }

    /** Event costs, only for events that actually ran. */
    private function seedExpenses(Booking $booking, string $status, $categories, $vendors, ?int $userId): void
    {
        if (! in_array($status, ['completed', 'confirmed'], true)) {
            return;
        }

        $lines = [
            ['Catering cost for '.$booking->guest_count.' guests', 'catering-food', round((float) $booking->menu_amount * 0.58, 2)],
            ['Decoration and staging', 'decoration', round((float) $booking->addons_amount * 0.45, 2)],
            ['Event staff wages', 'staff-wages', 12000 + ($booking->id % 5) * 1500],
        ];

        foreach ($lines as $i => [$title, $slug, $amount]) {
            if ($amount <= 0) {
                continue;
            }

            Expense::create([
                'hall_id' => $booking->hall_id,
                'booking_id' => $booking->id,
                'expense_category_id' => $categories[$slug] ?? null,
                'vendor_id' => $vendors[$i % max($vendors->count(), 1)] ?? null,
                'title' => $title,
                'amount' => $amount,
                'method' => $i === 2 ? 'cash' : 'bank_transfer',
                'spent_on' => $booking->start_datetime->copy()->subDays(1)->toDateString(),
                'created_by' => $userId,
            ]);
        }
    }

    /** Monthly running costs not tied to any single event. */
    private function seedOverheads(Hall $hall, $categories, $vendors, ?int $userId): void
    {
        $overheads = [
            ['Electricity bill', 'utilities', 185000],
            ['Sui gas bill', 'utilities', 42000],
            ['Property tax instalment', 'rent-taxes', 95000],
            ['Air-conditioner servicing', 'maintenance', 38000],
            ['Facebook & Instagram ads', 'marketing', 25000],
            ['Monthly staff salaries', 'staff-wages', 273000],
        ];

        foreach ([1, 2] as $monthsAgo) {
            foreach ($overheads as $i => [$title, $slug, $amount]) {
                Expense::create([
                    'hall_id' => $hall->id,
                    'expense_category_id' => $categories[$slug] ?? null,
                    'vendor_id' => $i === 3 ? ($vendors[$i % max($vendors->count(), 1)] ?? null) : null,
                    'title' => $title,
                    'amount' => $amount + rand(-5000, 5000),
                    'method' => 'bank_transfer',
                    'spent_on' => now()->subMonths($monthsAgo)->endOfMonth()->toDateString(),
                    'created_by' => $userId,
                ]);
            }
        }
    }
}
