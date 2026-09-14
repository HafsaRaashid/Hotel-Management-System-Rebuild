<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\RoomCategoryController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The public/admin route-group split implied by the target architecture's
| container view is deliberately not pre-empted here: which routes get
| grouped under which middleware/prefix is part of a future backlog item's
| own design (the whitelisted front-controller replacement, BL-011, not
| yet built), not this file's. Each block below is one backlog item's own
| routes, added as that item was built.
|
*/

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
| MOD-004 - Booking & Stay Lifecycle (BL-015). Public reservation intake.
*/
Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

/*
| MOD-005 - Customer Management (BL-006/BL-007/BL-008). No `/admin` prefix
| yet - that is BL-011's job (the front-controller/whitelist replacement,
| not yet built); see .specclaw/changes/002-customer-management/design.md.
*/
Route::resource('customers', CustomerController::class)->except(['show']);

/*
| MOD-003 - Room & Rate Management (BL-002/BL-003/BL-005). No `destroy` on
| rooms - BL-004 (Room Delete) is held out of this change; see
| .specclaw/changes/003-room-rate-management/spec.md.
*/
Route::resource('room-categories', RoomCategoryController::class);
Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);
