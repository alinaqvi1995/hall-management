<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Require every listed permission.
     *
     * Usage: ->middleware('permission:view-bookings,edit-bookings')
     *
     * A previous version also inspected a `?status=` query parameter and matched
     * it against `view-quotes-*` permissions carried over from another project.
     * That made any URL with a status filter return 403 for non-super-admins, so
     * it has been removed.
     */
    public function handle(Request $request, Closure $next, string $permissions)
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Forbidden');
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $required = array_values(array_filter(array_map('trim', explode(',', $permissions))));

        if ($required && ! $user->hasAllPermissions($required)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
