<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Role;
use App\Models\Permission;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class UserManagementController extends Controller
{
    // public function __construct()
    // {
    //     $permissions = [
    //         'allUsers' => 'view-users',
    //         'userUpdate' => 'edit-users',
    //         'userDestroy' => 'delete-users',
    //     ];

    //     foreach ($permissions as $method => $permission) {
    //         $this->middleware("permission:{$permission}")->only($method);
    //     }
    // }

    /**
     * Show all users
     * Super admin → all users
     * Hall admin → only users from same hall
     */
    public function allUsers()
    {
        $currentUser = Auth::user();

        if ($currentUser->hasRole('super_admin')) {
            $users = User::with('detail', 'hall')->orderBy('created_at', 'desc')->get();
        } elseif ($currentUser->hasRole('hall_admin')) {
            $users = User::with('detail', 'hall')
                ->where('hall_id', $currentUser->hall_id)
                ->orderBy('created_at', 'desc')
                ->get();
            // $users = User::with('detail', 'hall')
            //     ->where('hall_id', $currentUser->hall_id)
            //     ->orderBy('created_at', 'desc')
            //     ->get();
        } else {
            abort(403, 'Unauthorized');
        }

        return view('dashboard.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->hasRole('hall_admin') && $user->hall_id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        $user->load('hall', 'detail');

        return view('dashboard.users.show', compact('user'));
    }

    public function userCreate()
    {
        $user = Auth::user();
        $roles = Role::all();
        // dd($roles->toArray());
        $permissions = Permission::all();
        $users = User::all();

        // Order states & cities alphabetically
        $states = State::orderBy('name', 'asc')->get();
        $cities = City::orderBy('name', 'asc')->get();

        Gate::authorize('create', $user);

        return view('dashboard.users.create', compact('roles', 'permissions', 'users', 'states', 'cities'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'roles' => 'required|array',
            'permissions' => 'nullable|array',
            'referral_code' => 'nullable|string|unique:user_details,referral_code',
        ]);

        $uploadedFiles = [];

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Upload Paths
            $uploadPath = public_path('userDocs');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $profileImage = $this->moveFile($request->file('profile_image'), $uploadPath, $uploadedFiles);
            $resumePath = $this->moveFile($request->file('resume_path'), $uploadPath, $uploadedFiles);
            $cnicFront = $this->moveFile($request->file('cnic_front_path'), $uploadPath, $uploadedFiles);
            $cnicBack = $this->moveFile($request->file('cnic_back_path'), $uploadPath, $uploadedFiles);

            // Create Details
            $user->detail()->create([
                // addresses
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'phone_1' => $request->phone_1,
                'phone_2' => $request->phone_2,

                // personal
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relation' => $request->emergency_contact_relation,

                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'spouse_name' => $request->spouse_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'marital_status' => $request->marital_status,
                'religion' => $request->religion,
                'cnic' => $request->cnic,
                'nationality' => $request->nationality,

                // employment
                'highest_qualification' => $request->highest_qualification,
                'previous_company' => $request->previous_company,
                'previous_designation' => $request->previous_designation,
                'experience_years' => $request->experience_years,
                'skills' => $request->skills,

                'employee_code' => $request->employee_code,
                'department' => $request->department,
                'designation' => $request->designation,
                'date_of_joining' => $request->date_of_joining,
                'date_of_resignation' => $request->date_of_resignation,
                'employment_status' => $request->employment_status ?? 'Active',

                // finance
                'basic_salary' => $request->basic_salary,
                'allowance' => $request->allowance,
                'bonus' => $request->bonus,
                'commission' => $request->commission,
                'margin' => $request->margin,
                'discount' => $request->discount,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'iban' => $request->iban,
                'tax_number' => $request->tax_number,

                // referral
                'referral_code' => $request->referral_code,
                'referred_by' => $request->filled('referred_by')
                    ? $request->referred_by
                    : null,
                'referral_bonus' => $request->referral_bonus ?? 0,

                // docs
                'profile_image' => $profileImage,
                'resume_path' => $resumePath,
                'cnic_front_path' => $cnicFront,
                'cnic_back_path' => $cnicBack,

                // misc
                'blood_group' => $request->blood_group,
                'is_remote' => $request->is_remote ? 1 : 0,
                'shift_timings' => $request->shift_timings,
                'working_hours_per_week' => $request->working_hours_per_week,
                'leave_balance' => $request->leave_balance,
                'notes' => $request->notes,

                'can_login' => $request->can_login ? 1 : 0,
                'two_factor_enabled' => $request->two_factor_enabled ? 1 : 0,
            ]);

            // Attach RBAC
            $user->roles()->sync($request->roles);
            $user->directPermissions()->sync($request->permissions ?? []);

            DB::commit();

            return redirect()->route('dashboard.users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($uploadedFiles as $file) {
                if (file_exists($file))
                    unlink($file);
            }

            Log::error('User creation failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to create user. ' . $e->getMessage());
        }
    }

    private function moveFile($file, $destinationPath, &$uploadedFiles)
    {
        if (!$file)
            return null;

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $fileName);

        $fullPath = $destinationPath . '/' . $fileName;
        $uploadedFiles[] = $fullPath;

        return 'userDocs/' . $fileName;
    }

    public function userEdit(User $user)
    {
        Gate::authorize('update', $user);

        $roles = Role::all();
        $permissions = Permission::all();
        $users = User::all();
        $states = State::orderBy('name')->get();
        $cities = City::orderBy('name')->get(); 

        return view('dashboard.users.edit', compact('user', 'roles', 'permissions', 'users', 'states', 'cities'));
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        Gate::authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'referral_code' => 'nullable|string|unique:user_details,referral_code,' . ($user->detail->id ?? 'NULL'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            $detail = $user->detail ?: new UserDetail([
                'user_id' => $user->id,
                'commission' => 0.00,
                'margin' => 0.00,
                'discount' => 0.00,
                'referral_bonus' => 0.00,
            ]);

            $uploadPath = public_path('userDocs');
            if (!file_exists($uploadPath))
                mkdir($uploadPath, 0777, true);

            $uploadedFiles = [];

            foreach (['profile_image', 'resume_path', 'cnic_front_path', 'cnic_back_path'] as $fileField) {
                if ($file = $request->file($fileField)) {
                    if ($detail->$fileField && file_exists(public_path($detail->$fileField))) {
                        unlink(public_path($detail->$fileField));
                    }
                    $detail->$fileField = $this->moveFile($file, $uploadPath, $uploadedFiles);
                }
            }

            $detail->fill($request->except([
                'name',
                'email',
                'password',
                'roles',
                'permissions',
                'profile_image',
                'resume_path',
                'cnic_front_path',
                'cnic_back_path'
            ]));

            $detail->commission = is_numeric($request->commission) ? $request->commission : 0;
            $detail->margin = is_numeric($request->margin) ? $request->margin : 0;
            $detail->discount = is_numeric($request->discount) ? $request->discount : 0;
            $detail->referral_bonus = is_numeric($request->referral_bonus) ? $request->referral_bonus : 0;
            $detail->leave_balance = is_numeric($request->leave_balance) ? $request->leave_balance : 0;

            $detail->save();

            if ($request->has('roles') && !empty($request->roles)) {
                $user->roles()->sync($request->roles);
            }
            $user->directPermissions()->sync($request->permissions ?? []);

            return redirect()->route('dashboard.users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function userDestroy($id)
    {
        $user = User::findOrFail($id);

        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('dashboard.users.index')->with('success', 'User deleted successfully.');
    }


    public function toggleActive(User $user)
    {
        Gate::authorize('update', $user);

        $user->is_active = !$user->is_active;
        if ($user->is_active) {
            $user->force_logout = false;
        }
        $user->save();

        return back()->with('success', 'User status updated.');
    }


    public function forceLogout(User $user)
    {
        Gate::authorize('update', $user);

        $user->force_logout = true;
        $user->save();

        return back()->with('success', 'User will be logged out on next request.');
    }
}

//     public function allUsers()
//     {
//         $currentUser = auth()->user();

//         // Super admin: see all users
//         if ($currentUser->hasRole('super_admin')) {
//             $users = User::with('detail', 'hall')->orderBy('created_at', 'desc')->get();
//         }

//         // Hall admin: see only users from their hall
//         else if ($currentUser->hasRole('hall_admin')) {
//             $users = User::with('detail', 'hall')
//                 ->where('hall_id', $currentUser->hall_id)
//                 ->orderBy('created_at', 'desc')
//                 ->get();
//         }

//         // Regular user: deny access
//         else {
//             abort(403, 'Unauthorized');
//         }

//         return view('dashboard.users.index', compact('users'));
//     }


//     public function userCreate()
//     {
//         $roles = Role::all();
//         $permissions = Permission::all();
//         $users = User::all(); // For referral dropdown

//         return view('dashboard.users.create', compact('roles', 'permissions', 'users'));
//     }

//     public function userStore(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email',
//             'password' => 'required|string|min:6',
//             'roles' => 'required|array',
//             'permissions' => 'nullable|array',
//             'referral_code' => 'nullable|string|unique:user_details,referral_code',
//         ]);

//         // Track uploaded files for rollback cleanup
//         $uploadedFiles = [];

//         try {
//             DB::beginTransaction();

//             /** ✅ Create User */
//             $user = User::create([
//                 'name' => $request->name,
//                 'email' => $request->email,
//                 'password' => Hash::make($request->password),
//                 'is_active' => $request->has('is_active') ? 1 : 0,
//             ]);

//             /** ✅ File Upload Handling (store in public/userDocs) */
//             $uploadPath = public_path('userDocs');
//             if (!file_exists($uploadPath)) {
//                 mkdir($uploadPath, 0777, true);
//             }

//             $profileImage = $this->moveFile($request->file('profile_image'), $uploadPath, $uploadedFiles);
//             $resumePath   = $this->moveFile($request->file('resume_path'), $uploadPath, $uploadedFiles);
//             $cnicFront    = $this->moveFile($request->file('cnic_front_path'), $uploadPath, $uploadedFiles);
//             $cnicBack     = $this->moveFile($request->file('cnic_back_path'), $uploadPath, $uploadedFiles);

//             /** ✅ Create UserDetail */
//             $user->detail()->create([
//                 'address_1' => $request->address_1,
//                 'address_2' => $request->address_2,
//                 'city' => $request->city,
//                 'state' => $request->state,
//                 'country' => $request->country,
//                 'postal_code' => $request->postal_code,
//                 'phone_1' => $request->phone_1,
//                 'phone_2' => $request->phone_2,
//                 'emergency_contact_name' => $request->emergency_contact_name,
//                 'emergency_contact_phone' => $request->emergency_contact_phone,
//                 'emergency_contact_relation' => $request->emergency_contact_relation,

//                 // Personal
//                 'father_name' => $request->father_name,
//                 'mother_name' => $request->mother_name,
//                 'spouse_name' => $request->spouse_name,
//                 'gender' => $request->gender,
//                 'date_of_birth' => $request->date_of_birth,
//                 'marital_status' => $request->marital_status,
//                 'religion' => $request->religion,
//                 'cnic' => $request->cnic,
//                 'nationality' => $request->nationality,

//                 // Education
//                 'highest_qualification' => $request->highest_qualification,
//                 'previous_company' => $request->previous_company,
//                 'previous_designation' => $request->previous_designation,
//                 'experience_years' => $request->experience_years,
//                 'skills' => $request->skills,

//                 // Employment
//                 'employee_code' => $request->employee_code,
//                 'department' => $request->department,
//                 'designation' => $request->designation,
//                 'date_of_joining' => $request->date_of_joining,
//                 'date_of_resignation' => $request->date_of_resignation,
//                 'employment_status' => $request->employment_status ?? 'Active',

//                 // Financial
//                 'basic_salary' => $request->basic_salary,
//                 'allowance' => $request->allowance,
//                 'bonus' => $request->bonus,
//                 'commission' => $request->commission,
//                 'margin' => $request->margin,
//                 'discount' => $request->discount,
//                 'bank_name' => $request->bank_name,
//                 'bank_account_number' => $request->bank_account_number,
//                 'iban' => $request->iban,
//                 'tax_number' => $request->tax_number,

//                 // Referral (safe handling)
//                 'referral_code' => $request->referral_code,
//                 'referred_by' => $request->filled('referred_by') && User::where('id', $request->referred_by)->exists() ? $request->referred_by : null,
//                 'referral_bonus' => $request->referral_bonus ?? 0,

//                 // Documents
//                 'profile_image' => $profileImage,
//                 'resume_path' => $resumePath,
//                 'cnic_front_path' => $cnicFront,
//                 'cnic_back_path' => $cnicBack,

//                 // Misc
//                 'blood_group' => $request->blood_group,
//                 'is_remote' => $request->is_remote ? 1 : 0,
//                 'shift_timings' => $request->shift_timings,
//                 'working_hours_per_week' => $request->working_hours_per_week,
//                 'leave_balance' => $request->leave_balance,
//                 'notes' => $request->notes,

//                 // Flags
//                 'can_login' => $request->can_login ? 1 : 0,
//                 'two_factor_enabled' => $request->two_factor_enabled ? 1 : 0,
//             ]);

//             /** ✅ Sync Roles, Permissions */
//             $user->roles()->sync($request->roles);
//             $user->directPermissions()->sync($request->permissions ?? []);

//             DB::commit();

//             return redirect()->route('dashboard.users.index')->with('success', 'User created successfully.');
//         } catch (\Exception $e) {
//             DB::rollBack();

//             // Delete uploaded files
//             foreach ($uploadedFiles as $file) {
//                 if (file_exists($file)) {
//                     unlink($file);
//                 }
//             }

//             Log::error('User creation failed: ' . $e->getMessage());

//             return back()->with('error', 'Failed to create user. ' . $e->getMessage());
//         }
//     }

//     /**
//      * Move file to destination and track it for rollback
//      */
//     private function moveFile($file, $destinationPath, &$uploadedFiles)
//     {
//         if (!$file) return null;

//         $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
//         $file->move($destinationPath, $fileName);

//         $fullPath = $destinationPath . '/' . $fileName;
//         $uploadedFiles[] = $fullPath;

//         return 'userDocs/' . $fileName;
//     }

//     public function userEdit($id)
//     {
//         $user = User::with('detail')->findOrFail($id);
//         $check = Gate::authorize('update', $user);
//         // dd($check);
//         $roles = Role::all();
//         $permissions = Permission::all();
//         $users = User::all(); // For referral selection

//         return view('dashboard.users.edit', compact('user', 'roles', 'permissions', 'users'));
//     }

//     public function userUpdate(Request $request, $id)
//     {
//         $user = User::findOrFail($id);

//         // Validate input
//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email,' . $user->id,
//             'password' => 'nullable|string|min:6',
//             // 'roles' => 'required|array',
//             'permissions' => 'nullable|array',
//             'referral_code' => 'nullable|string|unique:user_details,referral_code,' . ($user->detail->id ?? 'NULL'),
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()->withErrors($validator)->withInput();
//         }

//         try {
//             // Update core user info
//             $user->update([
//                 'name' => $request->name,
//                 'email' => $request->email,
//                 'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
//                 'is_active' => $request->has('is_active') ? 1 : 0,
//             ]);

//             // Get existing detail or create new with defaults
//             $detail = $user->detail ?: new UserDetail([
//                 'user_id' => $user->id,
//                 'commission' => 0.00,
//                 'margin' => 0.00,
//                 'discount' => 0.00,
//                 'referral_bonus' => 0.00,
//             ]);

//             // Ensure upload path exists
//             $uploadPath = public_path('userDocs');
//             if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);

//             $uploadedFiles = [];

//             // Handle file uploads
//             foreach (['profile_image', 'resume_path', 'cnic_front_path', 'cnic_back_path'] as $fileField) {
//                 if ($file = $request->file($fileField)) {
//                     if ($detail->$fileField && file_exists(public_path($detail->$fileField))) {
//                         unlink(public_path($detail->$fileField));
//                     }
//                     $detail->$fileField = $this->moveFile($file, $uploadPath, $uploadedFiles);
//                 }
//             }

//             // Fill other fields except excluded
//             $detail->fill($request->except([
//                 'name',
//                 'email',
//                 'password',
//                 'roles',
//                 'permissions',
//                 'profile_image',
//                 'resume_path',
//                 'cnic_front_path',
//                 'cnic_back_path'
//             ]));

//             // Ensure NOT NULL columns are set
//             $detail->commission = is_numeric($request->commission) ? $request->commission : 0.00;
//             $detail->margin = is_numeric($request->margin) ? $request->margin : 0.00;
//             $detail->discount = is_numeric($request->discount) ? $request->discount : 0.00;
//             $detail->referral_bonus = is_numeric($request->referral_bonus) ? $request->referral_bonus : 0.00;
//             $detail->leave_balance = is_numeric($request->leave_balance) ? $request->leave_balance : 0.00;

//             $detail->save();

//             // Sync roles, permissions
//             if ($request->has('roles') && !empty($request->roles)) {
//                 $user->roles()->sync($request->roles);
//             }
//             $user->directPermissions()->sync($request->permissions ?? []);

//             return redirect()->route('dashboard.users.index')
//                 ->with('success', 'User updated successfully.');
//         } catch (\Exception $e) {
//             return redirect()->back()
//                 ->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])
//                 ->withInput();
//         }
//     }

//     public function userDestroy($id)
//     {
//         $user = User::findOrFail($id);
//         $user->delete();
//         return redirect()->route('dashboard.users.index')->with('success', 'User deleted successfully.');
//     }

//     public function toggleActive(User $user)
//     {
//         $user->is_active = !$user->is_active;
//         if ($user->is_active) {
//             $user->force_logout = false;
//         }
//         $user->save();
//         return back()->with('success', 'User status updated.');
//     }

//     public function forceLogout(User $user)
//     {
//         $user->force_logout = true;
//         $user->save();
//         return back()->with('success', 'User will be logged out on next request.');
//     }
// }
