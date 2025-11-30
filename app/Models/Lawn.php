<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawn extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'name',
        'capacity',
        'created_by',
        'updated_by',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
