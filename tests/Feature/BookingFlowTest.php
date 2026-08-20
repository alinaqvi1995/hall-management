<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hall;
use App\Models\Lawn;
use App\Models\Package;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private Hall $hall;

    private Lawn $lawn;

    private Package $package;

    private Customer $customer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.pk',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($superAdminRole);

        $this->hall = Hall::create([
            'name' => 'Test Marquee',
            'status' => 1,
            'advance_policy_percent' => 25,
            'cancellation_charge_percent' => 10,
            'tax_percent' => 5,
        ]);

        $this->lawn = Lawn::create([
            'hall_id' => $this->hall->id,
            'name' => 'Main Hall',
            'capacity' => 500,
        ]);

        $this->package = Package::create([
            'hall_id' => $this->hall->id,
            'name' => 'Standard Buffet',
            'type' => 'buffet',
            'per_head_rate' => 1500,
            'min_guests' => 100,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '0300-1234567',
            'cnic' => '35201-1234567-1',
        ]);

        $this->actingAs($this->admin);
    }

    private function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'hall_id' => $this->hall->id,
            'lawn_id' => $this->lawn->id,
            'package_id' => $this->package->id,
            'customer_id' => $this->customer->id,
            'event_type' => 'shadi',
            'start_datetime' => now()->addDays(10)->setTime(19, 0)->toDateTimeString(),
            'end_datetime' => now()->addDays(11)->setTime(0, 0)->toDateTimeString(),
            'guest_count' => 300,
            'per_head_rate' => 1500,
            'hall_rent' => 0,
            'discount' => 0,
            'tax_percent' => 5,
            'status' => 'confirmed',
        ], $overrides);
    }

    public function test_it_computes_the_bill_from_guests_rate_and_tax(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());

        // 300 guests x 1500 = 450,000; +5% tax = 472,500
        $this->assertEquals(450000, (float) $booking->menu_amount);
        $this->assertEquals(22500, (float) $booking->tax_amount);
        $this->assertEquals(472500, (float) $booking->total_amount);
        $this->assertEquals(118125, $booking->required_advance, 'advance is 25% of the total');
    }

    public function test_it_prices_fixed_and_per_head_addons_separately(): void
    {
        $decor = Addon::create([
            'hall_id' => $this->hall->id,
            'name' => 'Stage Decor',
            'price' => 30000,
            'pricing_mode' => 'fixed',
        ]);

        $drinks = Addon::create([
            'hall_id' => $this->hall->id,
            'name' => 'Cold Drinks',
            'price' => 100,
            'pricing_mode' => 'per_head',
        ]);

        $booking = app(BookingService::class)->createBooking(
            $this->bookingPayload(['tax_percent' => 0]),
            [$decor->id => ['quantity' => 1], $drinks->id => ['quantity' => 1]]
        );

        // 30,000 fixed + (100 x 300 guests) = 60,000
        $this->assertEquals(60000, (float) $booking->addons_amount);
        $this->assertEquals(510000, (float) $booking->total_amount);
    }

    public function test_it_rejects_a_second_booking_that_overlaps_the_same_lawn(): void
    {
        app(BookingService::class)->createBooking($this->bookingPayload());

        $this->expectException(ValidationException::class);

        app(BookingService::class)->createBooking($this->bookingPayload([
            // Starts three hours into the first booking.
            'start_datetime' => now()->addDays(10)->setTime(22, 0)->toDateTimeString(),
            'end_datetime' => now()->addDays(11)->setTime(3, 0)->toDateTimeString(),
        ]));
    }

    public function test_it_allows_a_back_to_back_booking_that_starts_when_the_previous_ends(): void
    {
        app(BookingService::class)->createBooking($this->bookingPayload([
            'start_datetime' => now()->addDays(10)->setTime(12, 0)->toDateTimeString(),
            'end_datetime' => now()->addDays(10)->setTime(17, 0)->toDateTimeString(),
        ]));

        // Half-open windows: ending at 17:00 must not block a 17:00 start.
        $second = app(BookingService::class)->createBooking($this->bookingPayload([
            'start_datetime' => now()->addDays(10)->setTime(17, 0)->toDateTimeString(),
            'end_datetime' => now()->addDays(10)->setTime(23, 0)->toDateTimeString(),
        ]));

        $this->assertTrue($second->exists);
        $this->assertEquals(2, Booking::count());
    }

    public function test_a_cancelled_booking_releases_its_slot(): void
    {
        $first = app(BookingService::class)->createBooking($this->bookingPayload());

        app(BookingService::class)->cancelBooking($first, 'Customer postponed the event.');

        // The same window must now be bookable again.
        $second = app(BookingService::class)->createBooking($this->bookingPayload());

        $this->assertTrue($second->exists);
        $this->assertEquals('cancelled', $first->fresh()->status);
    }

    public function test_it_generates_sequential_booking_numbers_per_hall(): void
    {
        $first = app(BookingService::class)->createBooking($this->bookingPayload());
        $second = app(BookingService::class)->createBooking($this->bookingPayload([
            'start_datetime' => now()->addDays(20)->setTime(19, 0)->toDateTimeString(),
            'end_datetime' => now()->addDays(20)->setTime(23, 0)->toDateTimeString(),
        ]));

        $this->assertStringStartsWith('TM', $first->booking_number, 'code from "Test Marquee"');
        $this->assertStringEndsWith('-0001', $first->booking_number);
        $this->assertStringEndsWith('-0002', $second->booking_number);
    }

    public function test_the_lawn_picker_reports_availability(): void
    {
        app(BookingService::class)->createBooking($this->bookingPayload());

        $lawns = app(BookingService::class)->lawnAvailability(
            $this->hall,
            now()->addDays(10)->setTime(20, 0),
            now()->addDays(10)->setTime(23, 0)
        );

        $this->assertFalse($lawns->first()->available);
        $this->assertNotNull($lawns->first()->booked_from);
    }

    /* --------------------------------------------------------------- payments */

    public function test_payment_status_tracks_the_ledger(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());
        $payments = app(PaymentService::class);

        $this->assertEquals('pending', $booking->payment_status);

        $payments->record($booking, ['amount' => 100000, 'method' => 'cash', 'direction' => 'in']);
        $this->assertEquals('partial', $booking->fresh()->payment_status);
        $this->assertEquals(100000, $booking->fresh()->amount_paid);

        // Settle the remaining balance exactly.
        $payments->record($booking->fresh(), [
            'amount' => 372500, 'method' => 'bank_transfer', 'direction' => 'in',
        ]);

        $booking = $booking->fresh();
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals(0.0, $booking->balance_due);
        $this->assertTrue($booking->is_fully_paid);
    }

    public function test_it_refuses_a_payment_larger_than_the_outstanding_balance(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->record($booking, [
            'amount' => 999999, 'method' => 'cash', 'direction' => 'in',
        ]);
    }

    public function test_it_refuses_a_refund_larger_than_what_was_received(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());
        $payments = app(PaymentService::class);

        $payments->record($booking, ['amount' => 50000, 'method' => 'cash', 'direction' => 'in']);

        $this->expectException(ValidationException::class);

        $payments->record($booking->fresh(), [
            'amount' => 60000, 'method' => 'cash', 'direction' => 'refund',
        ]);
    }

    public function test_receipt_numbers_are_sequential_within_a_booking(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());
        $payments = app(PaymentService::class);

        $first = $payments->record($booking, ['amount' => 1000, 'method' => 'cash', 'direction' => 'in']);
        $second = $payments->record($booking->fresh(), ['amount' => 2000, 'method' => 'cash', 'direction' => 'in']);

        $this->assertStringEndsWith('-01', $first->receipt_number);
        $this->assertStringEndsWith('-02', $second->receipt_number);
    }

    public function test_deleting_a_payment_recalculates_the_booking_status(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());
        $payments = app(PaymentService::class);

        $payment = $payments->record($booking, [
            'amount' => 472500, 'method' => 'cash', 'direction' => 'in',
        ]);

        $this->assertEquals('paid', $booking->fresh()->payment_status);

        $payments->delete($payment);

        $this->assertEquals('pending', $booking->fresh()->payment_status);
        $this->assertEquals(0.0, $booking->fresh()->amount_paid);
    }

    public function test_cancellation_charge_is_capped_at_the_amount_received(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingPayload());

        // Only 10,000 collected, but the requested forfeit is far larger.
        app(PaymentService::class)->record($booking, [
            'amount' => 10000, 'method' => 'cash', 'direction' => 'in',
        ]);

        $cancelled = app(BookingService::class)->cancelBooking($booking->fresh(), 'No longer needed', 90000);

        $this->assertEquals(10000, (float) $cancelled->cancellation_charge);
    }

    /* ----------------------------------------------------------- permissions */

    public function test_a_hall_admin_can_reach_their_own_hall_but_not_another(): void
    {
        $hallAdminRole = Role::create(['name' => 'Hall Admin', 'slug' => 'hall_admin']);

        foreach (['view-halls', 'view-bookings', 'view-dashboard'] as $slug) {
            $hallAdminRole->permissions()->attach(
                Permission::create(['name' => $slug, 'slug' => $slug])->id
            );
        }

        $otherHall = Hall::create(['name' => 'Other Venue', 'status' => 1]);

        $hallAdmin = User::create([
            'name' => 'Hall Admin',
            'email' => 'halladmin@test.pk',
            'password' => 'password',
            'is_active' => true,
            'hall_id' => $this->hall->id,
        ]);
        $hallAdmin->roles()->attach($hallAdminRole);

        // The hall list must be reachable — this was a blanket 403 before.
        $this->actingAs($hallAdmin)->get('/halls')->assertOk();
        $this->actingAs($hallAdmin)->get('/halls/'.$this->hall->id)->assertOk();
        $this->actingAs($hallAdmin)->get('/halls/'.$otherHall->id)->assertForbidden();
    }

    public function test_bookings_are_scoped_to_the_users_hall(): void
    {
        $otherHall = Hall::create(['name' => 'Other Venue', 'status' => 1]);
        $otherLawn = Lawn::create(['hall_id' => $otherHall->id, 'name' => 'Their Lawn', 'capacity' => 100]);

        app(BookingService::class)->createBooking($this->bookingPayload());
        app(BookingService::class)->createBooking($this->bookingPayload([
            'hall_id' => $otherHall->id,
            'lawn_id' => $otherLawn->id,
            'guest_count' => 80,
        ]));

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@test.pk',
            'password' => 'password',
            'is_active' => true,
            'hall_id' => $this->hall->id,
        ]);

        $this->actingAs($staff);

        $this->assertEquals(1, Booking::visibleTo()->count(), 'only their own hall');
        $this->assertEquals(2, Booking::count(), 'both exist in the table');
    }
}
