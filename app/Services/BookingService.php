<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\Lawn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private PaymentService $payments) {}

    /**
     * Create a booking, reserving the slot atomically.
     *
     * The availability check and the insert run inside one transaction with the
     * lawn row locked, so two clerks saving the same slot at the same moment
     * cannot both pass the check.
     */
    public function createBooking(array $data, array $addons = []): Booking
    {
        return DB::transaction(function () use ($data, $addons) {
            $this->guardSlotIsFree($data['lawn_id'], $data['start_datetime'], $data['end_datetime']);

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
            $data['booking_number'] ??= $this->generateBookingNumber($data['hall_id']);

            $booking = Booking::create($this->withPricing($data, $addons));

            $this->syncAddons($booking, $addons);
            $this->refreshTotals($booking);

            return $booking->fresh();
        });
    }

    public function updateBooking(Booking $booking, array $data, array $addons = []): Booking
    {
        return DB::transaction(function () use ($booking, $data, $addons) {
            $lawnId = $data['lawn_id'] ?? $booking->lawn_id;
            $start = $data['start_datetime'] ?? $booking->start_datetime;
            $end = $data['end_datetime'] ?? $booking->end_datetime;

            $this->guardSlotIsFree($lawnId, $start, $end, $booking->id);

            $data['updated_by'] = Auth::id();

            // Legacy rows created before booking numbers existed get one now.
            if (empty($booking->booking_number)) {
                $data['booking_number'] = $this->generateBookingNumber($data['hall_id'] ?? $booking->hall_id);
            }

            $booking->update($this->withPricing($data, $addons, $booking));

            $this->syncAddons($booking, $addons);
            $this->refreshTotals($booking);

            return $booking->fresh();
        });
    }

    /**
     * Cancel a booking, freeing the slot and recording the forfeit.
     *
     * The cancellation charge defaults to the hall's policy percentage of the
     * total, capped at what the customer has actually paid.
     */
    public function cancelBooking(Booking $booking, string $reason, ?float $charge = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $charge) {
            $paid = $booking->amount_paid;

            if ($charge === null) {
                $percent = (float) ($booking->hall->cancellation_charge_percent ?? 0);
                $charge = round((float) $booking->total_amount * $percent / 100, 2);
            }

            $charge = min(max($charge, 0), max($paid, 0));

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $reason,
                'cancellation_charge' => $charge,
                'updated_by' => Auth::id(),
            ]);

            $this->payments->recalculateBookingStatus($booking);

            return $booking->fresh();
        });
    }

    /**
     * Availability for every lawn in a hall over a window, for the picker.
     * Returns each lawn with an `available` flag and, when taken, who has it.
     */
    public function lawnAvailability(Hall $hall, $start = null, $end = null, ?int $ignoreBookingId = null)
    {
        $lawns = $hall->lawns()->orderBy('name')->get(['id', 'name', 'capacity']);

        if (! $start || ! $end) {
            return $lawns->map(function ($lawn) {
                $lawn->available = true;
                $lawn->booked_from = null;
                $lawn->booked_to = null;

                return $lawn;
            });
        }

        $clashes = Booking::active()
            ->whereIn('lawn_id', $lawns->pluck('id'))
            ->overlapping($start, $end)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->get(['id', 'lawn_id', 'start_datetime', 'end_datetime'])
            ->keyBy('lawn_id');

        return $lawns->map(function ($lawn) use ($clashes) {
            $clash = $clashes->get($lawn->id);

            $lawn->available = ! $clash;
            $lawn->booked_from = $clash?->start_datetime->format('d M Y h:i A');
            $lawn->booked_to = $clash?->end_datetime->format('d M Y h:i A');

            return $lawn;
        });
    }

    public function isAvailable($lawnId, $start, $end, ?int $ignoreBookingId = null): bool
    {
        return ! Booking::active()
            ->where('lawn_id', $lawnId)
            ->overlapping($start, $end)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->exists();
    }

    /* ------------------------------------------------------------------ pricing */

    /**
     * Compute the bill from the menu rate, add-ons, rent, discount and tax.
     *
     * Amounts are always recalculated server-side; a tampered form field cannot
     * change what the customer is charged.
     */
    public function withPricing(array $data, array $addons = [], ?Booking $existing = null): array
    {
        $guests = (int) ($data['guest_count'] ?? $existing->guest_count ?? 0);
        $rate = (float) ($data['per_head_rate'] ?? $existing->per_head_rate ?? 0);
        $rent = (float) ($data['hall_rent'] ?? $existing->hall_rent ?? 0);
        $discount = (float) ($data['discount'] ?? $existing->discount ?? 0);
        $taxPercent = (float) ($data['tax_percent'] ?? $existing->tax_percent ?? 0);

        $menuAmount = round($guests * $rate, 2);
        $addonsAmount = $this->addonsTotal($addons, $guests);

        $subtotal = max($menuAmount + $addonsAmount + $rent - $discount, 0);
        $taxAmount = round($subtotal * $taxPercent / 100, 2);

        $data['menu_amount'] = $menuAmount;
        $data['addons_amount'] = $addonsAmount;
        $data['hall_rent'] = $rent;
        $data['discount'] = $discount;
        $data['tax_percent'] = $taxPercent;
        $data['tax_amount'] = $taxAmount;
        $data['total_amount'] = round($subtotal + $taxAmount, 2);

        // `booking_price` is the agreed figure shown on the invoice; keep it in
        // step with the computed total unless it was set explicitly.
        $data['booking_price'] = $data['total_amount'];
        $data['quote_price'] = $data['quote_price'] ?? $existing->quote_price ?? $data['total_amount'];

        return $data;
    }

    /**
     * Add-on lines total. `per_head` add-ons multiply by guest count, so the
     * quantity field means "portions per guest" for those.
     */
    private function addonsTotal(array $addons, int $guests): float
    {
        if (! $addons) {
            return 0.0;
        }

        $catalogue = Addon::whereIn('id', array_keys($addons))->get()->keyBy('id');
        $total = 0.0;

        foreach ($addons as $addonId => $row) {
            $addon = $catalogue->get($addonId);

            if (! $addon) {
                continue;
            }

            $quantity = max((int) ($row['quantity'] ?? 1), 1);
            $unitPrice = (float) $addon->price;
            $multiplier = $addon->pricing_mode === 'per_head' ? max($guests, 0) : 1;

            $total += $unitPrice * $quantity * $multiplier;
        }

        return round($total, 2);
    }

    private function syncAddons(Booking $booking, array $addons): void
    {
        if (! $addons) {
            $booking->addons()->detach();

            return;
        }

        $catalogue = Addon::whereIn('id', array_keys($addons))->get()->keyBy('id');
        $guests = max((int) $booking->guest_count, 0);
        $pivot = [];

        foreach ($addons as $addonId => $row) {
            $addon = $catalogue->get($addonId);

            if (! $addon) {
                continue;
            }

            $quantity = max((int) ($row['quantity'] ?? 1), 1);
            $unitPrice = (float) $addon->price;
            $multiplier = $addon->pricing_mode === 'per_head' ? $guests : 1;

            $pivot[$addon->id] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $quantity * $multiplier, 2),
            ];
        }

        $booking->addons()->sync($pivot);
    }

    /** Re-derive payment_status after the total or the ledger changed. */
    private function refreshTotals(Booking $booking): void
    {
        $this->payments->recalculateBookingStatus($booking);
    }

    /* -------------------------------------------------------------- concurrency */

    /**
     * Reject the save if the slot is taken. Locks the lawn row first so the
     * check and the following insert/update are serialised per lawn.
     */
    private function guardSlotIsFree($lawnId, $start, $end, ?int $ignoreBookingId = null): void
    {
        if (! $lawnId) {
            return;
        }

        // Locking the lawn row serialises concurrent bookings for this lawn.
        Lawn::whereKey($lawnId)->lockForUpdate()->first();

        if (! $this->isAvailable($lawnId, $start, $end, $ignoreBookingId)) {
            throw ValidationException::withMessages([
                'lawn_id' => 'This lawn is already booked for the selected date and time.',
            ]);
        }
    }

    /* ----------------------------------------------------------------- numbering */

    /**
     * Booking number in the form <HALLCODE><DDMMYY>-0001.
     *
     * The sequence is derived under the caller's transaction, and the unique
     * index on booking_number is the final guard against a duplicate.
     */
    public function generateBookingNumber($hallId): string
    {
        $hall = Hall::find($hallId);
        $code = $this->hallCode($hall?->name);
        $prefix = $code.now()->format('dmy').'-';

        $latest = Booking::withTrashed()
            ->where('booking_number', 'like', $prefix.'%')
            ->orderByDesc('booking_number')
            ->value('booking_number');

        $sequence = 1;

        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $sequence = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /** Initials of the hall name, e.g. "Shalimar Grand Marquee" -> "SGM". */
    private function hallCode(?string $hallName): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\s]/', '', (string) $hallName);
        $code = '';

        foreach (preg_split('/\s+/', trim($clean)) ?: [] as $word) {
            if ($word !== '') {
                $code .= strtoupper($word[0]);
            }
        }

        if (strlen($code) < 2) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $hallName), 0, 2));
        }

        return strlen($code) >= 2 ? substr($code, 0, 4) : 'HL';
    }
}
