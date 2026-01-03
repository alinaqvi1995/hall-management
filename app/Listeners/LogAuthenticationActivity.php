<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use App\Models\Activity;
use Illuminate\Support\Facades\Request;

class LogAuthenticationActivity
{
    /**
     * Handle authentication events.
     */
    public function handle($event)
    {
        $description = '';
        $subject = null;
        $properties = [
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'location'   => [
                'city'    => Request::header('X-Geo-City'),
                'region'  => Request::header('X-Geo-Region'),
                'country' => Request::header('X-Geo-Country'),
            ]
        ];

        if ($event instanceof Login) {
            $description = 'User logged in';
            $subject = $event->user;
        } elseif ($event instanceof Logout) {
            $description = 'User logged out';
            $subject = $event->user;
        } elseif ($event instanceof Failed) {
            $description = 'Failed login attempt';
            $properties['credentials'] = [
                'email' => $event->credentials['email'] ?? 'unknown',
            ];
            $subject = $event->user; // Might be null if user not found
        } elseif ($event instanceof Lockout) {
            $description = 'User account locked out';
            $properties['email'] = $event->request->input('email');
        }

        if ($description) {
            Activity::create([
                'log_name'     => 'auth',
                'description'  => $description,
                'causer_type'  => $subject ? get_class($subject) : null,
                'causer_id'    => $subject ? $subject->id : null,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject ? $subject->id : null,
                'properties'   => $properties,
            ]);
        }
    }
}
