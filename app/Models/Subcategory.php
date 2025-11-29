<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class Subcategory extends Model
{
    use LogsActivity;
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'status',
        'created_by',
        'modified_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    protected static function booted()
    {
        static::creating(function ($subcategory) {
            if (Auth::check()) {
                $subcategory->created_by = Auth::id();
                $subcategory->modified_by = Auth::id();
            }
        });

        static::updating(function ($subcategory) {
            if (Auth::check()) {
                $subcategory->modified_by = Auth::id();
            }
        });
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

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'N/A';
    }

    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'N/A';
    }
    public function getEditorNameAttribute()
    {
        return $this->editor ? $this->editor->name : 'N/A';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('Md, Y h:ia') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Md, Y h:ia') : '-';
    }
}
