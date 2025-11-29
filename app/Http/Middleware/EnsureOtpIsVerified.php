<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureOtpIsVerified
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            if (!$user->otp_verified) {
                return redirect()->route('verify.otp')->with('error', 'Please verify OTP first.');
            }
        }

        return $next($request);
    }
}
