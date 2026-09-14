<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PendingBookingController;
use App\Http\Controllers\RoomCategoryController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\WalkInController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public routes are registered first (login, marketing, the public booking
| form); every admin route is grouped under the `auth` middleware near the
| bottom (BL-011, change 007-admin-authentication). Each block is one
| backlog item's own routes, added as that item was built.
|
*/

/*
| MOD-001 - Admin Authentication (BL-010). Public - a session doesn't exist
| yet at this point in the request.
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
| MOD-006 - Public Marketing Site (BL-009). Replaces the foundation's shell
| placeholder at '/' - see resources/views/marketing/home.blade.php and
| .specclaw/changes/004-public-marketing-site/design.md. '/rooms-overview',
| not '/room' or '/rooms', to avoid colliding with the admin 'rooms.*'
| resource below.
*/
Route::get('/', [MarketingController::class, 'home'])->name('marketing.home');
Route::get('/rooms-overview', [MarketingController::class, 'room'])->name('marketing.room');
Route::get('/services', [MarketingController::class, 'services'])->name('marketing.services');
Route::get('/food', [MarketingController::class, 'food'])->name('marketing.food');

/*
| MOD-004 - Booking & Stay Lifecycle (BL-015). Public reservation intake -
| the only MOD-004 routes that stay outside the auth-gated group below.
*/
Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

/*
| BL-011 - every admin screen requires an authenticated session. Laravel's
| own `Authenticate` middleware redirects to route('login') (see
| .specclaw/changes/007-admin-authentication/design.md). CQ-001 (the
| legacy's unwhitelisted dynamic include) is satisfied structurally by
| using Laravel's router at all - there is no equivalent dynamic-include
| mechanism anywhere in this file to whitelist.
*/
Route::middleware('auth')->group(function () {
    // MOD-004 admin routes (BL-016/BL-017/BL-018/BL-019/BL-020/BL-021/BL-022/BL-023).
    Route::get('/walk-in', [WalkInController::class, 'available'])->name('walk-in.available');
    Route::get('/walk-in/create', [WalkInController::class, 'create'])->name('walk-in.create');
    Route::post('/walk-in', [WalkInController::class, 'store'])->name('walk-in.store');
    Route::get('/bookings/pending', [PendingBookingController::class, 'index'])->name('bookings.pending');
    Route::delete('/bookings/pending/{booking}', [PendingBookingController::class, 'destroy'])->name('bookings.pending.destroy');
    Route::get('/bookings/pending/{booking}/convert', [PendingBookingController::class, 'showConvert'])->name('bookings.pending.convert.show');
    Route::post('/bookings/pending/{booking}/convert', [PendingBookingController::class, 'convert'])->name('bookings.pending.convert');
    Route::get('/stays', [StayController::class, 'index'])->name('stays.index');
    Route::get('/stays/{booking}/checkout', [StayController::class, 'showCheckout'])->name('stays.checkout.show');
    Route::post('/stays/{booking}/checkout', [StayController::class, 'checkout'])->name('stays.checkout');
    Route::get('/stays/{booking}/edit', [StayController::class, 'edit'])->name('stays.edit');
    Route::put('/stays/{booking}', [StayController::class, 'updateDate'])->name('stays.update');
    Route::get('/stays/{booking}', [StayController::class, 'show'])->name('stays.show');

    // MOD-005 - Customer Management (BL-006/BL-007/BL-008).
    Route::resource('customers', CustomerController::class)->except(['show']);

    // MOD-003 - Room & Rate Management (BL-002/BL-003/BL-005/BL-004).
    Route::resource('room-categories', RoomCategoryController::class);
    Route::resource('rooms', RoomController::class)->except(['show']);
});
