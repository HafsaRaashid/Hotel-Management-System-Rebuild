<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Foundation-only (frontend-routing pillar). This file intentionally
| registers no capability route - no public marketing page (MOD-006), no
| admin screen (MOD-001..MOD-005, MOD-007), and no auth route. The single
| route below proves the shell renders end to end through the layout;
| every backlog item replaces or extends it, one item at a time.
|
| The public/admin route-group split implied by the target architecture's
| container view is deliberately not pre-empted here: which routes get
| grouped under which middleware/prefix is part of a future backlog item's
| own design (the whitelisted front-controller replacement), not this
| scaffold's.
|
*/

Route::view('/', 'shell')->name('shell');

/*
| MOD-005 - Customer Management (BL-006/BL-007/BL-008). No `/admin` prefix
| yet - that is BL-011's job (the front-controller/whitelist replacement,
| not yet built); see .specclaw/changes/002-customer-management/design.md.
*/
Route::resource('customers', CustomerController::class)->except(['show']);
