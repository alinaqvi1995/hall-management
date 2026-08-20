<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'secondary_phone',
        'email',
        'cnic',
        'address',
        'is_blacklisted',
        'blacklist_reason',
    ];

    protected $casts = [
        'is_blacklisted' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /** Bookings at the halls the current user is allowed to see. */
    public function visibleBookings()
    {
        return $this->bookings()->visibleTo();
    }

    public function scopeBlacklisted($query)
    {
        return $query->where('is_blacklisted', true);
    }

    /**
     * Customers who have at least one booking at a hall the user can see.
     * Customers are shared across halls, so the join is what scopes them.
     */
    public function scopeVisibleTo($query, $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->hall_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('bookings', function ($q) use ($user) {
            $q->where('hall_id', $user->hall_id);
        });
    }

    public function getFullInfoAttribute(): string
    {
        return $this->phone
            ? $this->name.' ('.$this->phone.')'
            : (string) $this->name;
    }

    /** CNIC formatted as 00000-0000000-0 when 13 digits are present. */
    public function getFormattedCnicAttribute(): ?string
    {
        if (! $this->cnic) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->cnic);

        if (strlen($digits) !== 13) {
            return $this->cnic;
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12, 1);
    }

    public function getTotalBookingsAttribute(): int
    {
        return $this->bookings()->count();
    }
}
