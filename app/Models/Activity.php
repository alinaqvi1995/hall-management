<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $table = 'activity_log';

    /**
     * Casts for JSON / datetime columns.
     */
    protected $fillable = [
        'log_name',
        'description',
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'properties',
    ];

    // If 'properties' is JSON, cast it
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Example relationship: the user who caused the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Example relationship: the model this activity is about.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: latest first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
