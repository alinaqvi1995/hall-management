<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Contact Details
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();

            // Personal Details
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('religion')->nullable();
            $table->string('cnic')->nullable();
            $table->string('nationality')->nullable();

            // Education & Skills
            $table->string('highest_qualification')->nullable();
            $table->string('previous_company')->nullable();
            $table->string('previous_designation')->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('skills')->nullable();

            // Employment Details
            $table->string('employee_code')->unique()->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_resignation')->nullable();
            $table->string('employment_status')->default('Active');

            // Financial Details
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('allowance', 10, 2)->nullable();
            $table->decimal('bonus', 10, 2)->nullable();
            $table->decimal('commission', 5, 2)->default(0);
            $table->decimal('margin', 5, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('tax_number')->nullable();

            // Referral & Bonus
            $table->string('referral_code')->unique()->nullable();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('referral_bonus', 10, 2)->default(0);

            // Document Details
            $table->string('profile_image')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('cnic_front_path')->nullable();
            $table->string('cnic_back_path')->nullable();

            // Miscellaneous
            $table->string('blood_group')->nullable();
            $table->tinyInteger('is_remote')->default(0);
            $table->string('shift_timings')->nullable();
            $table->integer('working_hours_per_week')->nullable();
            $table->integer('leave_balance')->default(0);
            $table->text('notes')->nullable();

            // System Flags
            $table->boolean('can_login')->default(true);
            $table->boolean('two_factor_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
