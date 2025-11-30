<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_by',
        'updated_by',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status == 1
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    public function getCreatorNameAttribute()
    {
        return $this->created_by ? optional(User::find($this->created_by))->name : null;
    }

    public function getEditorNameAttribute()
    {
        return $this->updated_by ? optional(User::find($this->updated_by))->name : null;
    }

    public function getEstablishedAtFormattedAttribute()
    {
        return $this->established_at ? $this->established_at->format('d-M-Y') : '-';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-M-Y h:i A') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d-M-Y h:i A') : '-';
    }

    public function lawns()
    {
        return $this->hasMany(Lawn::class, 'hall_id');
    }
}
