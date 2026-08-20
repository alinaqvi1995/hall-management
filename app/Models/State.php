<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_by', 'updated_by'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function halls()
    {
        return $this->hasMany(Hall::class, 'state_id');
    }

    // The index view shows who added each province; without these the columns
    // rendered blank.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
