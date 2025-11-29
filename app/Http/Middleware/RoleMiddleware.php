<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        $user = $request->user();
        $roles = array_filter(explode('|', $roles));

        if (!$user || !$user->hasAnyRole($roles)) {
            abort(403, 'Forbidden');
        }
        return $next($request);
    }
}
