<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hall;
use App\Models\Lawn;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the booking and payment forms over HTTP, so request validation,
 * hall scoping and redirects are covered as well as the service layer.
 */
class BookingHttpTest extends TestCase
{
    use RefreshDatabase;

    private Hall $hall;

    private Lawn $lawn;

    private Package $package;

    private Addon $addon;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.pk',
            'password' => 'password',
        ]);
        $this->admin->roles()->attach($role);

        $this->hall = Hall::create([
            'name' => 'Royal Marquee',
            'status' => 1,
            'advance_policy_percent' => 25,
            'cancellation_charge_percent' => 10,
            'tax_percent' => 0,
        ]);

        $this->lawn = Lawn::create([
            'hall_id' => $this->hall->id,
            'name' => 'Grand Hall',
            'capacity' => 400,
        ]);

        $this->package = Package::create([
            'hall_id' => $this->hall->id,
            'name' => 'Buffet',
            'type' => 'buffet',
            'per_head_rate' => 2000,
            'min_guests' => 50,
            'is_active' => true,
        ]);

        $this->addon = Addon::create([
            'hall_id' => $this->hall->id,
            'name' => 'Stage Decor',
            'price' => 25000,
            'pricing_mode' => 'fixed',
        ]);

        $this->actingAs($this->admin);
    }

    private function form(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Bilal Ahmed',
            'customer_phone' => '0300-1234567',
            'customer_cnic' => '35201-1234567-1',
            'customer_email' => 'bilal@example.com',
            'customer_address' => 'Model Town, Lahore',

            'hall_id' => $this->hall->id,
            'lawn_id' => $this->lawn->id,
            'package_id' => $this->package->id,
            'event_type' => 'walima',

            'start_datetime' => now()->addDays(14)->setTime(19, 0)->format('Y-m-d\TH:i'),
            'end_datetime' => now()->addDays(15)->setTime(0, 0)->format('Y-m-d\TH:i'),

            'guest_count' => 200,
            'per_head_rate' => 2000,
            'hall_rent' => 0,
            'discount' => 0,
            'tax_percent' => 0,
            'status' => 'confirmed',
        ], $overrides);
    }

    public function test_a_booking_can_be_created_through_the_form(): void
    {
        $response = $this->post('/bookings', $this->form([
            'addons' => [$this->addon->id => ['selected' => 1, 'quantity' => 1]],
        ]));

        $response->assertOk()->assertSee('Booking Saved');

        $booking = Booking::first();

        $this->assertNotNull($booking);
        // 200 x 2000 = 400,000 menu + 25,000 decor
        $this->assertEquals(400000, (float) $booking->menu_amount);
        $this->assertEquals(25000, (float) $booking->addons_amount);
        $this->assertEquals(425000, (float) $booking->total_amount);

        // The customer is created from the same submission.
        $this->assertDatabaseHas('customers', ['cnic' => '35201-1234567-1', 'name' => 'Bilal Ahmed']);
    }

    public function test_it_reuses_an_existing_customer_matched_on_cnic(): void
    {
        Customer::create([
            'name' => 'Bilal A.',
            'cnic' => '35201-1234567-1',
            'phone' => '0300-0000000',
        ]);

        $this->post('/bookings', $this->form())->assertOk();

        // Matched, not duplicated — and the details are refreshed.
        $this->assertEquals(1, Customer::count());
        $this->assertDatabaseHas('customers', ['cnic' => '35201-1234567-1', 'name' => 'Bilal Ahmed']);
    }

    public function test_it_rejects_a_malformed_cnic(): void
    {
        $this->post('/bookings', $this->form(['customer_cnic' => '123']))
            ->assertSessionHasErrors('customer_cnic');

        $this->assertEquals(0, Booking::count());
    }

    public function test_it_rejects_a_guest_count_above_the_lawn_capacity(): void
    {
        $this->post('/bookings', $this->form(['guest_count' => 5000]))
            ->assertSessionHasErrors('guest_count');

        $this->assertEquals(0, Booking::count());
    }

    public function test_it_rejects_a_lawn_belonging_to_another_hall(): void
    {
        $otherHall = Hall::create(['name' => 'Other Venue', 'status' => 1]);
        $otherLawn = Lawn::create(['hall_id' => $otherHall->id, 'name' => 'Theirs', 'capacity' => 900]);

        $this->post('/bookings', $this->form(['lawn_id' => $otherLawn->id]))
            ->assertSessionHasErrors('lawn_id');

        $this->assertEquals(0, Booking::count());
    }

    public function test_it_rejects_a_start_date_in_the_past_on_create(): void
    {
        $this->post('/bookings', $this->form([
            'start_datetime' => now()->subDays(3)->format('Y-m-d\TH:i'),
            'end_datetime' => now()->subDays(3)->addHours(5)->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('start_datetime');
    }

    public function test_it_rejects_a_discount_larger_than_the_bill(): void
    {
        $this->post('/bookings', $this->form(['discount' => 9999999]))
            ->assertSessionHasErrors('discount');
    }

    public function test_a_double_booking_is_reported_on_the_lawn_field(): void
    {
        $this->post('/bookings', $this->form())->assertOk();

        // Same lawn, overlapping window.
        $this->post('/bookings', $this->form([
            'customer_cnic' => '35201-7654321-2',
            'customer_name' => 'Someone Else',
        ]))->assertSessionHasErrors('lawn_id');

        $this->assertEquals(1, Booking::count());
    }

    public function test_totals_are_recomputed_server_side_and_ignore_tampering(): void
    {
        // A crafted total_amount must not survive; the server recalculates.
        $this->post('/bookings', $this->form([
            'total_amount' => 1,
            'booking_price' => 1,
            'menu_amount' => 1,
        ]))->assertOk();

        $this->assertEquals(400000, (float) Booking::first()->total_amount);
    }

    /* --------------------------------------------------------------- payments */

    public function test_a_payment_can_be_recorded_through_the_form(): void
    {
        $this->post('/bookings', $this->form())->assertOk();
        $booking = Booking::first();

        $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 100000,
            'method' => 'cash',
            'direction' => 'in',
            'paid_on' => now()->toDateString(),
        ])->assertRedirect(route('bookings.show', $booking));

        $this->assertEquals(1, Payment::count());
        $this->assertEquals('partial', $booking->fresh()->payment_status);
    }

    public function test_a_future_dated_payment_is_rejected(): void
    {
        $this->post('/bookings', $this->form())->assertOk();

        $this->post('/payments', [
            'booking_id' => Booking::first()->id,
            'amount' => 1000,
            'method' => 'cash',
            'direction' => 'in',
            'paid_on' => now()->addWeek()->toDateString(),
        ])->assertSessionHasErrors('paid_on');

        $this->assertEquals(0, Payment::count());
    }

    /* ----------------------------------------------------------- cancellation */

    public function test_cancelling_requires_a_reason_and_frees_the_slot(): void
    {
        $this->post('/bookings', $this->form())->assertOk();
        $booking = Booking::first();

        // Too short a reason is rejected.
        $this->post("/bookings/{$booking->id}/cancel", ['cancellation_reason' => 'no'])
            ->assertSessionHasErrors('cancellation_reason');

        $this->post("/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Customer postponed the wedding.',
        ])->assertRedirect(route('bookings.show', $booking));

        $this->assertEquals('cancelled', $booking->fresh()->status);

        // The same slot is bookable again.
        $this->post('/bookings', $this->form([
            'customer_cnic' => '35201-7654321-2',
            'customer_name' => 'New Customer',
        ]))->assertOk();

        $this->assertEquals(2, Booking::count());
    }

    public function test_a_cancelled_booking_cannot_be_edited(): void
    {
        $this->post('/bookings', $this->form())->assertOk();
        $booking = Booking::first();

        $this->post("/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Customer postponed the wedding.',
        ]);

        // The edit screen redirects with an explanation rather than 500-ing.
        $this->get("/bookings/{$booking->id}/edit")
            ->assertRedirect(route('bookings.show', $booking));
    }

    /* --------------------------------------------------------------- tenancy */

    public function test_a_hall_admin_cannot_book_another_halls_lawn(): void
    {
        $hallAdminRole = Role::create(['name' => 'Hall Admin', 'slug' => 'hall_admin']);
        $otherHall = Hall::create(['name' => 'Other Venue', 'status' => 1]);
        $otherLawn = Lawn::create(['hall_id' => $otherHall->id, 'name' => 'Theirs', 'capacity' => 900]);

        foreach (['view-bookings', 'create-bookings'] as $slug) {
            $hallAdminRole->permissions()->attach(
                \App\Models\Permission::create(['name' => $slug, 'slug' => $slug])->id
            );
        }

        $hallAdmin = User::create([
            'name' => 'Hall Admin',
            'email' => 'ha@test.pk',
            'password' => 'password',
            'hall_id' => $this->hall->id,
        ]);
        $hallAdmin->roles()->attach($hallAdminRole);

        // hall_id is forced to their own hall, so the other hall's lawn fails.
        $this->actingAs($hallAdmin)
            ->post('/bookings', $this->form([
                'hall_id' => $otherHall->id,
                'lawn_id' => $otherLawn->id,
            ]))
            ->assertSessionHasErrors('lawn_id');

        $this->assertEquals(0, Booking::count());
    }

    /* ------------------------------------------------------- hall-only flow */

    public function test_a_hall_can_be_booked_with_rent_only_and_no_catering(): void
    {
        // The customer arranges their own caterer: no package, no per-head rate.
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 150000,
        ]))->assertOk()->assertSee('Booking Saved');

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertEquals(0, (float) $booking->menu_amount);
        $this->assertEquals(0, (float) $booking->per_head_rate);
        $this->assertNull($booking->package_id);
        $this->assertEquals(150000, (float) $booking->hall_rent);
        $this->assertEquals(150000, (float) $booking->total_amount);
    }

    public function test_hall_rent_only_still_applies_tax_and_discount(): void
    {
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => '',
            'hall_rent' => 200000,
            'discount' => 20000,
            'tax_percent' => 5,
        ]))->assertOk();

        $booking = Booking::first();

        // (200,000 - 20,000) = 180,000; +5% = 189,000
        $this->assertEquals(9000, (float) $booking->tax_amount);
        $this->assertEquals(189000, (float) $booking->total_amount);
    }

    public function test_hall_rent_only_plus_paid_extras(): void
    {
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 100000,
            'addons' => [$this->addon->id => ['selected' => 1, 'quantity' => 1]],
        ]))->assertOk();

        $booking = Booking::first();

        $this->assertEquals(0, (float) $booking->menu_amount);
        $this->assertEquals(25000, (float) $booking->addons_amount);
        $this->assertEquals(125000, (float) $booking->total_amount);
    }

    public function test_a_booking_that_charges_nothing_at_all_is_rejected(): void
    {
        // No rent, no catering, no extras — the invoice would total zero.
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 0,
        ]))->assertSessionHasErrors('hall_rent');

        $this->assertEquals(0, Booking::count());
    }

    public function test_extras_alone_are_enough_to_justify_a_booking(): void
    {
        // Nothing for the venue or the food, but a chargeable extra service.
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 0,
            'addons' => [$this->addon->id => ['selected' => 1, 'quantity' => 1]],
        ]))->assertOk();

        $this->assertEquals(25000, (float) Booking::first()->total_amount);
    }

    public function test_a_hall_only_booking_renders_its_invoice_and_detail_page(): void
    {
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 150000,
        ]))->assertOk();

        $booking = Booking::first();

        // Neither page may assume a menu line exists.
        $this->get("/bookings/{$booking->id}")->assertOk()->assertSee('Hall / lawn rent');
        $this->get("/bookings/{$booking->id}/invoice")->assertOk()->assertSee('arranged by customer');
    }

    public function test_a_discount_above_a_rent_only_bill_is_rejected(): void
    {
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 50000,
            'discount' => 60000,
        ]))->assertSessionHasErrors('discount');
    }

    /* --------------------------------------------- advance taken at booking */

    public function test_an_advance_can_be_collected_while_creating_the_booking(): void
    {
        $this->post('/bookings', $this->form([
            'advance_amount' => 100000,
            'advance_method' => 'cash',
            'advance_paid_on' => now()->toDateString(),
            'advance_reference' => 'CASH-001',
        ]))->assertOk()->assertSee('Advance of');

        $booking = Booking::first();
        $payment = Payment::first();

        $this->assertNotNull($payment);
        $this->assertEquals(100000, (float) $payment->amount);
        $this->assertEquals('cash', $payment->method);
        $this->assertEquals('in', $payment->direction);
        $this->assertEquals('CASH-001', $payment->reference);
        $this->assertEquals($booking->id, $payment->booking_id);

        // The booking's derived status reflects the receipt immediately.
        $this->assertEquals('partial', $booking->payment_status);
        $this->assertEquals(100000, $booking->amount_paid);
        $this->assertEquals(300000, $booking->balance_due);
    }

    public function test_an_advance_settling_the_whole_bill_marks_the_booking_paid(): void
    {
        $this->post('/bookings', $this->form([
            'advance_amount' => 400000,
            'advance_method' => 'bank_transfer',
            'advance_paid_on' => now()->toDateString(),
        ]))->assertOk();

        $booking = Booking::first();

        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals(0.0, $booking->balance_due);
    }

    public function test_no_payment_is_created_when_the_advance_is_left_blank(): void
    {
        $this->post('/bookings', $this->form(['advance_amount' => '']))->assertOk();

        $this->assertEquals(1, Booking::count());
        $this->assertEquals(0, Payment::count());
        $this->assertEquals('pending', Booking::first()->payment_status);
    }

    public function test_an_advance_above_the_total_is_rejected_and_no_booking_is_created(): void
    {
        $this->post('/bookings', $this->form([
            'advance_amount' => 900000,
            'advance_method' => 'cash',
            'advance_paid_on' => now()->toDateString(),
        ]))->assertSessionHasErrors('advance_amount');

        // Booking and payment must fail together — no orphan booking.
        $this->assertEquals(0, Booking::count());
        $this->assertEquals(0, Payment::count());
    }

    public function test_an_advance_requires_a_method_and_a_date(): void
    {
        $this->post('/bookings', $this->form([
            'advance_amount' => 5000,
            'advance_method' => '',
            'advance_paid_on' => '',
        ]))->assertSessionHasErrors(['advance_method', 'advance_paid_on']);

        $this->assertEquals(0, Booking::count());
    }

    public function test_an_advance_works_on_a_hall_rent_only_booking(): void
    {
        $this->post('/bookings', $this->form([
            'package_id' => null,
            'per_head_rate' => null,
            'hall_rent' => 120000,
            'advance_amount' => 30000,
            'advance_method' => 'easypaisa',
            'advance_paid_on' => now()->toDateString(),
        ]))->assertOk();

        $booking = Booking::first();

        $this->assertEquals(120000, (float) $booking->total_amount);
        $this->assertEquals(30000, $booking->amount_paid);
        $this->assertEquals('partial', $booking->payment_status);
    }
}
