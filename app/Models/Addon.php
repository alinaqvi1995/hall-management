<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'hall_id', 'name', 'price', 'pricing_mode', 'description', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const PRICING_MODES = [
        'fixed' => 'Fixed price',
        'per_head' => 'Per head',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_addon')
            ->withPivot(['quantity', 'unit_price', 'line_total'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPricingModeLabelAttribute(): string
    {
        return self::PRICING_MODES[$this->pricing_mode] ?? $this->pricing_mode;
    }
}
