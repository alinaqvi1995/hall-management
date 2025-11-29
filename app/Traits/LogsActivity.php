<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Activity;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        // Log Create
        static::created(function ($model) {
            self::logActivity($model, 'created', [
                'new_values' => $model->getAttributes()
            ]);
        });

        // Log Update
        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) return;

            self::logActivity($model, 'updated', [
                'old_values' => $model->getOriginal(),
                'new_values' => $model->getAttributes()
            ]);
        });

        // Log Delete
        static::deleted(function ($model) {
            self::logActivity($model, 'deleted', [
                'old_values' => $model->getOriginal()
            ]);
        });
    }

    protected static function logActivity($model, $action, array $properties = [])
    {
        Activity::create([
            'log_name'     => strtolower(class_basename($model)),
            'description'  => ucfirst(class_basename($model)) . " {$action}",
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'subject_type' => get_class($model),
            'subject_id'   => $model->id,
            'properties'   => array_merge($properties, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'location'   => [
                    'city'    => request()->header('X-Geo-City'),
                    'region'  => request()->header('X-Geo-Region'),
                    'country' => request()->header('X-Geo-Country'),
                ]
            ]),
        ]);
    }
}
