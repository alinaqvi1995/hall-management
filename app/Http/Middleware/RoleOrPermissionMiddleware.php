<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleOrPermissionMiddleware
{
    // Usage: ->middleware('role_or_permission:admin|editor,view-categories|edit-categories')
    public function handle(Request $request, Closure $next, string $roles = '', string $permissions = '')
    {
        $user = $request->user();
        if (!$user) abort(403, 'Forbidden');

        $rolesArr = array_filter(explode('|', (string) $roles));
        $permsArr = array_filter(explode('|', (string) $permissions));

        if (($rolesArr && $user->hasAnyRole($rolesArr)) || ($permsArr && $user->hasAnyPermission($permsArr))) {
            return $next($request);
        }

        abort(403, 'Forbidden');
    }
}
