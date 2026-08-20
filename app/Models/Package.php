<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'hall_id', 'name', 'type', 'per_head_rate', 'min_guests',
        'description', 'items', 'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'per_head_rate' => 'decimal:2',
        'min_guests' => 'integer',
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'buffet' => 'Buffet',
        'set_menu' => 'Set Menu',
        'one_dish' => 'One Dish',
        'custom' => 'Custom',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }
}
