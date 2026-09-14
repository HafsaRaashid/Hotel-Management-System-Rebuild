# Tasks: Admin Dashboard & Reporting (MOD-007, BL-024)

**Change:** 008-dashboard-reporting
**Created:** 2026-09-14
**Total Tasks:** 2

## Summary

Two tasks across two waves: build the dashboard feature (controller, view, route, login redirect, nav link), then a dependent test wave covering all nine counters, the auth gate, and the login-redirect/nav regressions.

## Tasks

### Wave 1 — Dashboard feature

- [x] `T1` — Add DashboardController, view, route, login redirect, and nav link
  - Files: `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard/index.blade.php`, `routes/web.php`, `app/Http/Controllers/AuthController.php`, `resources/views/partials/admin-nav.blade.php`
  - Estimate: small
  - Kind: impl
  - Depends: (none)
  - Notes: Per design.md — `index()` computes all nine counters directly via Eloquent (`Booking::count()`, `Booking::where('status', Booking::STATUS_CHECKED_IN)->count()`, etc.), Total Payment is `Booking::where('status', Booking::STATUS_CHECKED_OUT)->sum('price') ?? 0`. Add `Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')` as the first line inside the existing `auth` middleware group in `routes/web.php`. Change `AuthController::login()`'s success redirect from `route('customers.index')` to `route('dashboard')`. Add a "Dashboard" `<li>` as the first item in `admin-nav.blade.php`'s nav list, linking `route('dashboard')`.

### Wave 2 — Tests

- [x] `T2` — DashboardTest covering all nine counters and regressions
  - Files: `tests/Feature/DashboardTest.php`
  - Estimate: small
  - Kind: test
  - Depends: T1
  - Notes: Follow this codebase's established pattern (`actingAs(User::first())` in `setUp()`, `RefreshDatabase`). Cover AC-1 through AC-10 from spec.md: unauthenticated redirect (AC-1), authenticated 200 with all nine labels/values (AC-2), Total Bookings counts every status (AC-3), Checked In/Out counts exclude booked/cancelled (AC-4), Total Payment sums only checked-out `price` and ignores null-price pending/checked-in bookings (AC-5, edge case), Available vs Total Rooms (AC-6), plain counts for categories/customers/users (AC-7), login redirects to dashboard not customers.index (AC-8), nav link present on an existing screen e.g. customers.index (AC-9), zero-writes assertion via before/after row counts across all five tables (AC-10), and the fresh-install zero-counters edge case.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
