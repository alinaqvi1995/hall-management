<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\UserManagementController;
use App\Http\Controllers\Backend\UserTrustedIpController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

/*
|--------------------------------------------------------------------------
| OTP verification (pre-session-hardening step)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [OtpController::class, 'showVerifyForm'])->name('verify.otp');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('verify.otp.post');
    Route::get('/resend-otp', [OtpController::class, 'resendOtp'])->name('resend.otp');
});

Route::middleware(['auth', 'check_active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Bookings
    |----------------------------------------------------------------------
    | The static /bookings/* routes are declared BEFORE the resource, or
    | `bookings/{booking}` would swallow them and 404 on a missing model.
    */
    Route::get('bookings/events', [BookingController::class, 'calendarEvents'])->name('bookings.events');
    Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::resource('bookings', BookingController::class);

    /*
    |----------------------------------------------------------------------
    | Money
    |----------------------------------------------------------------------
    */
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::resource('expenses', ExpenseController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | Catalogue
    |----------------------------------------------------------------------
    */
    Route::get('packages/for-hall', [PackageController::class, 'forHall'])->name('packages.forHall');
    Route::resource('packages', PackageController::class)->except(['show']);

    Route::get('addons/for-hall', [AddonController::class, 'forHall'])->name('addons.forHall');
    Route::resource('addons', AddonController::class)->only(['index', 'store', 'update', 'destroy']);

    /*
    |----------------------------------------------------------------------
    | People
    |----------------------------------------------------------------------
    */
    Route::get('customers/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup');
    Route::post('customers/{customer}/blacklist', [CustomerController::class, 'toggleBlacklist'])
        ->name('customers.blacklist');
    Route::resource('customers', CustomerController::class)->only(['index', 'show']);

    // Bound as {member} so it does not collide with the `staff` resource name.
    Route::resource('staff', StaffController::class)->parameters(['staff' => 'member']);
    Route::resource('vendors', VendorController::class)->only(['index', 'store', 'update', 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Venues
    |----------------------------------------------------------------------
    */
    Route::get('halls/{hall}/lawns', [HallController::class, 'lawns'])->name('halls.lawns');
    Route::resource('halls', HallController::class);

    /*
    |----------------------------------------------------------------------
    | Reports
    |----------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('outstanding', [ReportController::class, 'outstanding'])->name('outstanding');
        Route::get('daily-sheet', [ReportController::class, 'dailySheet'])->name('dailySheet');
        Route::get('profitability', [ReportController::class, 'profitability'])->name('profitability');
    });

    /*
    |----------------------------------------------------------------------
    | Locations
    |----------------------------------------------------------------------
    */
    Route::resource('states', StateController::class)->except(['show', 'create', 'edit']);
    Route::resource('cities', CityController::class)->except(['show', 'create', 'edit']);

    /*
    |----------------------------------------------------------------------
    | Administration
    |----------------------------------------------------------------------
    */
    Route::get('/users', [UserManagementController::class, 'allUsers'])->name('dashboard.users.index');
    Route::get('/users/create', [UserManagementController::class, 'userCreate'])->name('dashboard.users.create');
    Route::post('/users', [UserManagementController::class, 'userStore'])->name('dashboard.users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'userEdit'])->name('dashboard.users.edit');
    Route::put('/users/{id}', [UserManagementController::class, 'userUpdate'])->name('dashboard.users.update');
    Route::delete('/users/{id}', [UserManagementController::class, 'userDestroy'])->name('dashboard.users.destroy');
    Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggleActive');
    Route::post('/users/{user}/force-logout', [UserManagementController::class, 'forceLogout'])->name('users.forceLogout');

    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    Route::get('/activity_logs', [AdminController::class, 'activityLogs'])->name('view.activity_logs');
    Route::resource('trusted-ips', UserTrustedIpController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | Profile
    |----------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // PATCH is accepted as well as PUT so either verb works from a form.
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Self-deletion is deliberately not offered: a staff account is referenced by
    // the bookings, payments and expenses it created. Deactivate the user from
    // the Users screen instead.
});

require __DIR__.'/auth.php';
