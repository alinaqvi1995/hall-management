<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct()
    {
        $permissions = [
            'activityLogs' => 'view-activityLogs',
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware("permission:{$permission}")->only($method);
        }
    }

    public function activityLogs(Request $request)
    {
        $search = $request->input('search');

        $logs = Activity::with(['causer', 'subject'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('log_name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('causer_type', 'LIKE', "%{$search}%")
                        ->orWhere('subject_type', 'LIKE', "%{$search}%")
                        ->orWhere('properties', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->appends(['search' => $search]);

        // ⭐ Convert properties into readable messages
        foreach ($logs as $log) {
            $old     = $log->properties['old_values'] ?? [];
            $new     = $log->properties['new_values'] ?? [];
            $changes = [];

            $modelName = class_basename($log->subject_type); // e.g., User, Category, Booking

            $excludeFields = ['created_at', 'updated_at', 'deleted_at', 'password'];

            if (Str::contains(strtolower($log->description), 'created')) {
                // NEW record: show only fields that have actual data
                foreach ($new as $key => $value) {
                    if (in_array($key, $excludeFields)) {
                        continue;
                    }

                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        continue;
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value); // nice display for array fields
                    }

                    $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": " . $value;
                }

                if (! empty($changes)) {
                    array_unshift($changes, "New {$modelName} created"); // add headline
                } else {
                    $changes[] = "New {$modelName} created (no details filled)";
                }
            } elseif (Str::contains(strtolower($log->description), 'updated')) {
                // UPDATED record: show only changed values except excluded
                foreach ($new as $key => $value) {
                    if (in_array($key, $excludeFields)) {
                        continue;
                    }

                    $oldValue = $old[$key] ?? null;
                    if ($oldValue != $value) {
                        if (is_array($value)) {
                            $value    = implode(', ', $value);
                            $oldValue = is_array($oldValue) ? implode(', ', $oldValue) : $oldValue;
                        }
                        $changes[] = ucfirst(str_replace('_', ' ', $key))
                            . " changed from '" . ($oldValue ?? '') . "' to '{$value}'";
                    }
                }
            } elseif (Str::contains(strtolower($log->description), 'deleted')) {
                $changes[] = "{$modelName} deleted: #" . ($log->subject_id ?? '-');
            }

            $log->readable_changes = $changes;
            $log->readable_causer  = $log->causer->name ?? ($log->causer_type . ' #' . $log->causer_id);
            $log->readable_subject = "{$modelName} #{$log->subject_id}";
        }

        return view('dashboard.pages.activity_logs', compact('logs', 'search'));
    }

    // public function activityLogs(Request $request)
    // {
    //     $search = $request->input('search');

    //     $logs = Activity::when($search, function ($query) use ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('log_name', 'LIKE', "%{$search}%")
    //                 ->orWhere('description', 'LIKE', "%{$search}%")
    //                 ->orWhere('causer_type', 'LIKE', "%{$search}%")
    //                 ->orWhere('subject_type', 'LIKE', "%{$search}%")
    //                 ->orWhere('properties', 'LIKE', "%{$search}%")
    //                 ->orWhere('created_at', 'LIKE', "%{$search}%");
    //         });
    //     })
    //         ->latest()
    //         ->paginate(20)
    //         ->appends(['search' => $search]);

    //     return view('dashboard.pages.activity_logs', compact('logs', 'search'));
    // }
}
