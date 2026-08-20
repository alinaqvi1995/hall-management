<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'hall_id', 'name', 'designation', 'phone', 'cnic', 'address',
        'monthly_salary', 'employment_type', 'joined_on', 'is_active', 'notes',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'joined_on' => 'date',
        'is_active' => 'boolean',
    ];

    public const EMPLOYMENT_TYPES = [
        'permanent' => 'Permanent',
        'daily_wage' => 'Daily Wage',
        'contract' => 'Contract',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_staff')
            ->withPivot(['role', 'wage'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? $this->employment_type;
    }
}
