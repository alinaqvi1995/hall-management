<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class Category extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'modified_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if (Auth::check()) {
                $category->created_by = Auth::id();
                $category->modified_by = Auth::id();
            }
        });

        static::updating(function ($category) {
            if (Auth::check()) {
                $category->modified_by = Auth::id();
            }
        });
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('Md, Y h:ia') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Md, Y h:ia') : '-';
    }

    public function getStatusFormattedAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 1:
                return '<span class="badge bg-success">Active</span>';
            case 0:
                return '<span class="badge bg-danger">Inactive</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }

    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'System';
    }

    public function getEditorNameAttribute()
    {
        return $this->editor ? $this->editor->name : 'System';
    }
}
