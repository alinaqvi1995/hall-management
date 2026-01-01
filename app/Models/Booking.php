<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'created_by',
        'updated_by',
        'hall_id',
        'lawn_id',
        'customer_id',
        'start_datetime',
        'end_datetime',
        'quote_price',
        'booking_price',
        'advance_paid',
        'payment_status',
        'status',
        'notes',
        'capacity',
        'facilities',
        'booking_number',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'quote_price' => 'decimal:2',
        'booking_price' => 'decimal:2',
        'advance_paid' => 'decimal:2',
        'capacity' => 'integer',
        'facilities' => 'array',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function lawn()
    {
        return $this->belongsTo(Lawn::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForHall($query, $hallId)
    {
        return $query->where('hall_id', $hallId);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_datetime', [$start, $end])
                ->orWhereBetween('end_datetime', [$start, $end])
                ->orWhere(function ($r) use ($start, $end) {
                    $r->where('start_datetime', '<=', $start)
                        ->where('end_datetime', '>=', $end);
                });
        });
    }

    public function getFormattedBookingNumberAttribute()
    {
        return $this->booking_number ?? 'HL-' . now()->format('Y') . '-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public static function isHallBooked($hallId, $start, $end, $excludeId = null)
    {
        return self::where('hall_id', $hallId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($r) use ($start, $end) {
                        $r->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            })
            ->exists();
    }

    public function isPastBooking()
    {
        return $this->end_datetime->isPast();
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'confirmed' => 'Confirmed',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function hasFacility($facility)
    {
        return in_array($facility, $this->facilities ?? []);
    }

    public function addFacility($facility)
    {
        $facilities = $this->facilities ?? [];
        if (! in_array($facility, $facilities)) {
            $facilities[] = $facility;
            $this->facilities = $facilities;
            $this->save();
        }
    }
}
