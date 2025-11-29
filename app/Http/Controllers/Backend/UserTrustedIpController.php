<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTrustedIp;
use Illuminate\Http\Request;

class UserTrustedIpController extends Controller
{
    public function index()
    {
        $ips = UserTrustedIp::with('user')->get();
        $users = User::select('id', 'name')->get();

        return view('dashboard.pages.trusted-ips', compact('ips', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'required|ip'
        ]);

        UserTrustedIp::create([
            'user_id' => $request->user_id,
            'ip_address' => $request->ip_address
        ]);

        return redirect()->route('trusted-ips.index')->with('success', 'Trusted IP added successfully!');
    }

    public function update(Request $request, UserTrustedIp $trustedIp)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'required|ip'
        ]);

        $trustedIp->update([
            'user_id' => $request->user_id,
            'ip_address' => $request->ip_address
        ]);

        return redirect()->route('trusted-ips.index')->with('success', 'Trusted IP updated successfully!');
    }

    public function destroy(UserTrustedIp $trustedIp)
    {
        $trustedIp->delete();

        return redirect()->route('trusted-ips.index')->with('success', 'Trusted IP deleted successfully!');
    }
}
