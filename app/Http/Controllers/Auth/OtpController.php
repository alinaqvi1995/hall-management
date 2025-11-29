<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\UserTrustedIp;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login again.');
        }

        if ($user->otp_code == $request->otp && now()->lessThan($user->otp_expires_at)) {
            $user->otp_verified = true;
            $user->otp_code = null;
            $user->save();

            // ✅ Add current IP if it's new
            $currentIp = $request->ip();
            if (!$user->trustedIps()->where('ip_address', $currentIp)->exists()) {
                UserTrustedIp::create([
                    'user_id' => $user->id,
                    'ip_address' => $currentIp
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'OTP verified successfully!');
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }

    public function resendOtp()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $otp = rand(100000, 999999);
        -$user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            Mail::raw("OTP resend request for user: {$user->email}\n\nNew OTP: {$otp}\n\nThis code will expire in 10 minutes.", function ($message) use ($user) {
                $message->to('bridgewayuship@gmail.com')->subject("Resent Login OTP for {$user->email}");
            });
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Unable to resend OTP. Please try again later.']);
        }
        // Mail::raw("Your new OTP is: {$otp}. It will expire in 10 minutes.", function ($message) use ($user) {
        //     $message->to($user->email)->subject('Your New OTP');
        // });

        return back()->with('success', 'A new OTP has been sent to your email.');
    }
}
