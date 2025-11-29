<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\BlogController as BackendBlogController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\UserManagementController;
use App\Http\Controllers\Backend\UserTrustedIpController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::view('/contact', 'site.contact')->name('contact');

Route::get('/thank-you', function () {
    return view('frontend.thankyou');
})->name('frontend.thankyou');

Route::get('/verify-otp', [OtpController::class, 'showVerifyForm'])->name('verify.otp');
Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('verify.otp.post');
Route::get('/resend-otp', [OtpController::class, 'resendOtp'])->name('resend.otp');

// 🔐 Auth & Profile
// Route::middleware(['auth', 'check_active', 'otp.verified'])->group(function () {
Route::middleware(['auth', 'check_active'])->group(function () {
    // Route::get('/dashboard', fn() => view('dashboard.index'))->middleware('verified')->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('trusted-ips', UserTrustedIpController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::get('/get_subcategories_by_category/{category_id}', [SubcategoryController::class, 'getSubcategories'])->name('subcategories.by.category');

    // Route::middleware(['admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'allUsers'])->name('dashboard.users.index');
    Route::get('/users/create', [UserManagementController::class, 'userCreate'])->name('dashboard.users.create');
    Route::post('/users', [UserManagementController::class, 'userStore'])->name('dashboard.users.store');
    Route::delete('/users/{id}', [UserManagementController::class, 'userDestroy'])->name('dashboard.users.destroy');
    Route::post('{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggleActive');
    Route::post('{user}/force-logout', [UserManagementController::class, 'forceLogout'])->name('users.forceLogout');

    Route::get('/activity_logs', [AdminController::class, 'activityLogs'])->name('view.activity_logs');

    Route::resource('roles', RoleController::class);

    Route::resource('permissions', PermissionController::class);
    // });

    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'userEdit'])->name('dashboard.users.edit');
    Route::put('/users/{id}', [UserManagementController::class, 'userUpdate'])->name('dashboard.users.update');

    Route::resource('blogs', BackendBlogController::class);

    // Hall
    Route::resource('halls', HallController::class);

    // Hall
    Route::resource('bookings', BookingController::class);
    Route::get('bookings/events', [DashboardController::class, 'getBookings'])->name('bookings.events');


    Route::resource('states', StateController::class);
    Route::resource('cities', CityController::class);

});

require __DIR__ . '/auth.php';

// Route::get('/categories', [CategoryController::class, 'index'])
//     ->middleware('permission:view-categories');

//     Route::get('/dashboard', fn() => view('dashboard'))
//     ->middleware('role:admin|editor');

//     Route::get('/categories', [CategoryController::class, 'index'])
//     ->middleware('role_or_permission:admin|editor,view_categories|edit_categories');
