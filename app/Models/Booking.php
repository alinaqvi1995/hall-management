<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    /** Statuses that still occupy the venue and therefore block the slot. */
    public const BLOCKING_STATUSES = ['pending', 'confirmed'];

    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ];

    /**
     * Status palette, shared by the badges and the calendar so the two cannot
     * drift apart. Previously "completed" was blue on the calendar but rendered
     * a `primary` badge, which became brand orange and clashed with the amber
     * used for "pending".
     */
    public const STATUS_COLOURS = [
        'pending' => '#d97706',
        'confirmed' => '#16a34a',
        'completed' => '#2563eb',
        'cancelled' => '#dc2626',
    ];

    public const EVENT_TYPES = [
        'shadi' => 'Shadi / Wedding',
        'mehndi' => 'Mehndi',
        'baraat' => 'Baraat',
        'walima' => 'Walima',
        'nikah' => 'Nikah',
        'engagement' => 'Engagement',
        'aqiqah' => 'Aqiqah',
        'birthday' => 'Birthday',
        'corporate' => 'Corporate Event',
        'seminar' => 'Seminar / Conference',
        'other' => 'Other',
    ];

    protected $fillable = [
        'created_by', 'updated_by', 'hall_id', 'lawn_id', 'package_id',
        'customer_id', 'event_type', 'start_datetime', 'end_datetime',
        'quote_price', 'booking_price', 'advance_paid',
        'capacity', 'guest_count', 'per_head_rate', 'menu_amount',
        'addons_amount', 'hall_rent', 'discount', 'tax_percent', 'tax_amount',
        'total_amount', 'payment_status', 'status', 'notes', 'facilities',
        'booking_number', 'cancelled_at', 'cancellation_reason',
        'cancellation_charge', 'cancelled_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'cancelled_at' => 'datetime',
        'quote_price' => 'decimal:2',
        'booking_price' => 'decimal:2',
        'advance_paid' => 'decimal:2',
        'per_head_rate' => 'decimal:2',
        'menu_amount' => 'decimal:2',
        'addons_amount' => 'decimal:2',
        'hall_rent' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancellation_charge' => 'decimal:2',
        'capacity' => 'integer',
        'guest_count' => 'integer',
        'facilities' => 'array',
    ];

    /* ---------------------------------------------------------------- relations */

    public function lawn()
    {
        return $this->belongsTo(Lawn::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'booking_addon')
            ->withPivot(['quantity', 'unit_price', 'line_total'])
            ->withTimestamps();
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'booking_staff')
            ->withPivot(['role', 'wage'])
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /* ------------------------------------------------------------------- scopes */

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING_STATUSES);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Bookings whose event window overlaps [$start, $end).
     *
     * Half-open comparison, so a booking ending at 18:00 does not collide with
     * one starting at 18:00 - back-to-back events on the same lawn are legal.
     */
    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);
    }

    public function scopeBetween(Builder $query, $start, $end): Builder
    {
        return $query->overlapping($start, $end);
    }

    /**
     * Pre-aggregate the ledger so `amount_paid` and `balance_due` do not fire
     * two SUM queries per row. Any list that shows a balance should use this.
     */
    public function scopeWithPaymentTotals(Builder $query): Builder
    {
        return $query->withSum(
            ['payments as payments_received' => fn ($q) => $q->where('direction', 'in')],
            'amount'
        )->withSum(
            ['payments as payments_refunded' => fn ($q) => $q->where('direction', 'refund')],
            'amount'
        );
    }

    /* --------------------------------------------------------------- accessors */

    public function getFormattedBookingNumberAttribute(): string
    {
        return $this->booking_number ?: 'HL-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? 'Unknown';
    }

    public function getEventTypeLabelAttribute(): ?string
    {
        if (! $this->event_type) {
            return null;
        }

        return self::EVENT_TYPES[$this->event_type] ?? ucfirst($this->event_type);
    }

    /**
     * Badge tone for this status. `completed` maps to a custom utility rather
     * than a Bootstrap contextual class, so it matches its calendar colour.
     */
    public function getStatusColourAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'success',
            'completed' => 'completed',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    /** Hex used for this booking's calendar event. */
    public function getCalendarColourAttribute(): string
    {
        return self::STATUS_COLOURS[$this->status] ?? self::STATUS_COLOURS['pending'];
    }

    public function getPaymentStatusColourAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'refunded' => 'secondary',
            default => 'danger',
        };
    }

    /**
     * Sum of receipts minus refunds. Authoritative over the legacy column.
     *
     * Reads pre-aggregated columns from withPaymentTotals() when present, then
     * an eager-loaded relation, and only queries as a last resort.
     */
    public function getAmountPaidAttribute(): float
    {
        // Presence of the key is the signal, not its value: SUM over no rows
        // returns null, which is a legitimate "nothing paid yet".
        if (array_key_exists('payments_received', $this->attributes)
            || array_key_exists('payments_refunded', $this->attributes)) {
            return round(
                (float) ($this->attributes['payments_received'] ?? 0)
                - (float) ($this->attributes['payments_refunded'] ?? 0),
                2
            );
        }

        if ($this->relationLoaded('payments')) {
            $total = 0.0;
            foreach ($this->payments as $payment) {
                $total += $payment->signed_amount;
            }

            return round($total, 2);
        }

        return round(
            (float) $this->payments()->receipts()->sum('amount')
            - (float) $this->payments()->refunds()->sum('amount'),
            2
        );
    }

    public function getBalanceDueAttribute(): float
    {
        return round((float) $this->total_amount - $this->amount_paid, 2);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance_due <= 0.009;
    }

    /** Advance the hall's policy expects before the booking is held. */
    public function getRequiredAdvanceAttribute(): float
    {
        $percent = $this->hall ? $this->hall->advance_policy_percent : 0;

        return round((float) $this->total_amount * (float) $percent / 100, 2);
    }

    public function getProfitAttribute(): float
    {
        return round($this->amount_paid - (float) $this->expenses()->sum('amount'), 2);
    }

    /* ---------------------------------------------------------------- behaviour */

    public function isPastBooking(): bool
    {
        return $this->end_datetime !== null && $this->end_datetime->isPast();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /** Cancelled or finished events must not be edited into a live slot. */
    public function isEditable(): bool
    {
        return ! in_array($this->status, ['cancelled', 'completed'], true);
    }

    public function hasFacility($facility): bool
    {
        return in_array($facility, $this->facilities ?? [], true);
    }
}
