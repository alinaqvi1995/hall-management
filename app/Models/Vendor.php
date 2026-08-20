<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'hall_id', 'name', 'company', 'service_type', 'phone', 'email',
        'cnic', 'address', 'notes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
