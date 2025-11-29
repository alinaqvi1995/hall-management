<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Hall extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'owner_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'area',
        'description',
        'hall_capacity',
        'hall_types',
        'registration_number',
        'established_at',
        'status',
        'notes',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
