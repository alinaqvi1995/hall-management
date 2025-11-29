<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class UserDetail extends Model
{
    use LogsActivity, HasFactory;

    protected $table = 'user_details';

    protected $fillable = [
        'user_id',

        // Contact Details
        'address_1',
        'address_2',
        'city',
        'state',
        'country',
        'postal_code',
        'phone_1',
        'phone_2',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',

        // Personal Details
        'father_name',
        'mother_name',
        'spouse_name',
        'gender',
        'date_of_birth',
        'marital_status',
        'religion',
        'cnic',
        'nationality',

        // Education & Skills
        'highest_qualification',
        'previous_company',
        'previous_designation',
        'experience_years',
        'skills',

        // Employment Details
        'employee_code',
        'department',
        'designation',
        'date_of_joining',
        'date_of_resignation',
        'employment_status',

        // Financial Details
        'basic_salary',
        'allowance',
        'bonus',
        'commission',
        'margin',
        'discount',
        'bank_name',
        'bank_account_number',
        'iban',
        'tax_number',

        // Referral & Bonus
        'referral_code',
        'referred_by',
        'referral_bonus',

        // Document Details
        'profile_image',
        'resume_path',
        'cnic_front_path',
        'cnic_back_path',

        // Miscellaneous
        'blood_group',
        'is_remote',
        'shift_timings',
        'working_hours_per_week',
        'leave_balance',
        'notes',

        // System Flags
        'can_login',
        'two_factor_enabled',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'date_of_resignation' => 'date',
        'employment_status' => 'boolean',
        'is_remote' => 'boolean',
        'can_login' => 'boolean',
        'two_factor_enabled' => 'boolean',
    ];

    /** Relationships */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /** Accessors & Mutators */

    public function getFullAddressAttribute()
    {
        return trim("{$this->address_1}, {$this->address_2}, {$this->city}, {$this->state}, {$this->country}");
    }

    public function getEmergencyContactAttribute()
    {
        return "{$this->emergency_contact_name} ({$this->emergency_contact_relation}) - {$this->emergency_contact_phone}";
    }
}