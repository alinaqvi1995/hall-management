<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ActivityLogService
{
    /**
     * Log detailed activity
     *
     * @param string $logName
     * @param string $description
     * @param mixed $subject
     * @param array $oldValues
     * @param array $newValues
     * @param array $extraProperties
     * @return void
     */
    public function log(
        string $logName,
        string $description,
        $subject,
        array $oldValues = [],
        array $newValues = [],
        array $extraProperties = []
    ): void {
        $properties = array_merge(
            $this->getRequestContext(),
            $extraProperties,
            [
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]
        );

        activity($logName)
            ->causedBy(Auth::user())
            ->performedOn($subject)
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Get request context: IP, user agent, location
     */
    protected function getRequestContext(): array
    {
        $ip = request()->ip();
        $userAgent = request()->header('User-Agent');

        $location = null;
        try {
            $response = Http::get("https://ipapi.co/{$ip}/json/");
            if ($response->ok()) {
                $location = [
                    'ip' => $ip,
                    'city' => $response['city'] ?? null,
                    'region' => $response['region'] ?? null,
                    'country' => $response['country_name'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            $location = ['ip' => $ip];
        }

        return [
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'location' => $location,
        ];
    }
}
