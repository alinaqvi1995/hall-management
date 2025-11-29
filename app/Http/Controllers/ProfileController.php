<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $permissions = [
            'update'  => 'edit-profile',
            'destroy' => 'delete-profile',
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware("permission:{$permission}")->only($method);
        }
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        if (!$user->detail) {
            $user->detail()->create([]);
            $user->refresh(); // refresh the relation
        }

        return view('dashboard.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Update basic fields
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle password change
        if ($request->filled('password')) {
            // Make sure password confirmation is correct (ProfileUpdateRequest should validate this)
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Update user detail
        $detailData = $request->only([
            'phone_1',
            'date_of_birth',
            'gender',
            'address_1',
            'city',
            'state',
            'country',
            'postal_code',
            'emergency_contact_name',
            'emergency_contact_relation',
            'emergency_contact_phone',
            'department',
            'designation',
            'shift_timings'
        ]);

        $detail = $user->detail ?? $user->detail()->create([]);
        $detail->fill($detailData);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $fileName = Str::slug($user->name) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $folderPath = 'userDocs/';
            $file->move(public_path($folderPath), $fileName);

            $detail->profile_image = $folderPath . $fileName;
        }

        $detail->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
