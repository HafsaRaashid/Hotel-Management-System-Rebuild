# Design: Admin Dashboard & Reporting (MOD-007, BL-024)

**Change:** 008-dashboard-reporting

## Technical Approach

One new controller, `App\Http\Controllers\DashboardController`, with a single `index()` action returning a Blade view with all nine counters computed inline (no separate service/query-object layer — nine simple `count()`/`sum()` calls don't warrant one, matching this codebase's existing pattern of thin controllers doing direct Eloquent queries, e.g. `PendingBookingController::index()`). Registered inside the existing `Route::middleware('auth')->group(...)` block in `routes/web.php`, alongside every other admin route.

## Architecture / File Changes

| File | Change |
|---|---|
| `app/Http/Controllers/DashboardController.php` | New. `index()` builds a `$counters` array (or explicit view variables) from `Booking`, `Room`, `RoomCategory`, `Customer`, `User` and returns `view('dashboard.index', ...)`. |
| `resources/views/dashboard/index.blade.php` | New. `@extends('layouts.app')`, `@include('partials.admin-nav')`, nine labeled counter cards. |
| `routes/web.php` | Add `Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')` inside the `auth` group, as the first line of the group (it's the landing page). |
| `app/Http/Controllers/AuthController.php` | `login()`'s success redirect: `route('customers.index')` → `route('dashboard')`. |
| `resources/views/partials/admin-nav.blade.php` | Add a "Dashboard" `<li>` as the first nav item, linking `route('dashboard')`. |
| `tests/Feature/DashboardTest.php` | New. Seeds fixture rows across all five tables with a mix of statuses, asserts each of the nine counters, the login-redirect change, the auth-gate, and the nav link. |

## Key Decisions

- **No query-object/service abstraction.** Nine straightforward aggregate queries in one controller method is simpler and more consistent with the codebase's existing controllers (`PendingBookingController`, `StayController`) than introducing a `DashboardService` for a read-only page with no reuse target. Revisit only if a second consumer of these same aggregates appears.
- **Total Payment sums `price`, filtered to checked-out bookings only.** `bookings.price` defaults to `0` and is not nullable; it is only ever written to a non-zero value by `StayController::checkout()` (change 005). Filtering by `STATUS_CHECKED_OUT` matches the legacy's own "Total Payment" semantics (only completed stays have a finalized payment) rather than relying on pending/checked-in rows happening to still be `0`. `?? 0` on the sum call covers the zero-rows case, where `sum()` already returns `0`, not `null` — belt-and-braces, not a required guard.
- **Route name `dashboard`, path `/dashboard`.** Not `/` — `/` is already `MarketingController::home` (MOD-006, public). The legacy's `admin/home.php` has no direct path-name parity requirement (it's reached via `admin/index.php?page=home`), so `/dashboard` following this codebase's existing `noun` route-naming convention (`customers`, `rooms`, `stays`) is a clean, unambiguous choice.
- **Dependency on BL-012 resolved as data-only**, per spec.md's Dependency Note — `User::count()` needs only the `users` table (built in change 007), not BL-012's List screen. No stub, no split.

## Risks

- **Low.** Purely additive, read-only, touches one shared file each in two places (`routes/web.php`, `admin-nav.blade.php`) already modified by every prior admin-facing change — same low-risk pattern as those changes.
