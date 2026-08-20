<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawn extends Model
{
    use BelongsToHall, HasFactory;

    protected $fillable = [
        'hall_id',
        'name',
        'capacity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Is this lawn free for the given window?
     * Cancelled bookings release the slot; only pending/confirmed ones hold it.
     */
    public function isAvailableBetween($start, $end, ?int $ignoreBookingId = null): bool
    {
        return ! $this->bookings()
            ->active()
            ->overlapping($start, $end)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->exists();
    }

    protected static function booted(): void
    {
        static::creating(function (self $lawn) {
            if (auth()->check()) {
                $lawn->created_by ??= auth()->id();
                $lawn->updated_by ??= auth()->id();
            }
        });

        static::updating(function (self $lawn) {
            if (auth()->check()) {
                $lawn->updated_by = auth()->id();
            }
        });
    }
}
