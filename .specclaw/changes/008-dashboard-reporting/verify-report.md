# Verification Report: 008-dashboard-reporting

**Verified:** 2026-09-14
**Model:** claude-sonnet-5
**Verdict:** PASS

## Acceptance Criteria

- ✅ **AC-1:** Unauthenticated redirect to `/login`. `routes/web.php` puts `Route::get('/dashboard', ...)->name('dashboard')` inside `Route::middleware('auth')->group(...)`. Confirmed by `test_dashboard_redirects_to_login_when_unauthenticated`.
- ✅ **AC-2:** Authenticated 200 with all nine labels — confirmed against `resources/views/dashboard/index.blade.php`'s nine labeled cards.
- ✅ **AC-3:** Total Bookings = all rows regardless of status — `DashboardController.php`: `Booking::count()`, no filter.
- ✅ **AC-4:** Checked In / Checked Out exact match — `Booking::where('status', Booking::STATUS_CHECKED_IN)->count()` / `STATUS_CHECKED_OUT`. Independently cross-checked with direct `assertSame()` DB assertions in the test, not just HTML scraping.
- ✅ **AC-5:** Total Payment sums price of checked-out only — verified by planting non-zero `price` on non-checked-out rows and asserting the distinctive sum (297, not 1297) renders.
- ✅ **AC-6:** Available Rooms = `status=0` only; Total Rooms = all — cross-checked directly with `assertSame()`.
- ✅ **AC-7:** Plain counts for categories/customers/users — no filters, cross-checked directly.
- ✅ **AC-8:** Login redirects to dashboard, not `customers.index` — `AuthController::login()` targets `route('dashboard')`; `AdminAuthenticationTest`'s regression test correctly retrofitted, not left stale.
- ✅ **AC-9:** Nav link present on existing admin pages — first `<li>` in `admin-nav.blade.php`, verified via `assertSee(route('dashboard'), false)` on `customers.index`.
- ✅ **AC-10:** Zero writes — `DashboardController::index()` contains only read calls; before/after five-table row-count snapshot asserts equality.

## Independent Verification

**`bookings.price` claim:** confirmed via migration — `unsignedInteger('price')->default(0)`, not nullable. Laravel's `Query\Builder::sum()` already coalesces a null aggregate to `0` internally, so the original `?? 0` in the controller was defensive dead code — removed in a post-verify cleanup commit.

**Nine counters match FR-1 exactly** — confirmed line-by-line against `DashboardController.php`; `Booking::STATUS_CHECKED_IN`/`STATUS_CHECKED_OUT` constants confirmed to exist in `app/Models/Booking.php`.

**BL-012/users dependency note confirmed factually true** — `git log` shows the users migration/model were created by change `007-admin-authentication` (commit `da59342`), which merged before `008-dashboard-reporting` began. This change reads that pre-existing table; nothing was faked or secretly built.

**Auth-gate regression confirmed** — `/dashboard` route sits inside the `auth` middleware group, not outside it.

**Login redirect and nav link confirmed**, including that the pre-existing `AdminAuthenticationTest::test_login_with_correct_credentials_succeeds` was correctly updated to assert the new redirect target rather than left stale.

## Test Results

Independently re-run (not trusted from the build): `php artisan test` — **89 passed (610 assertions)**. `vendor/bin/pint --test` — 4 pre-existing, out-of-scope style issues only (confirmed via `git log` to predate this change and change 007 both); none of 008's changed files appear in the lint failure list.

## Issues Found

Two minor, non-blocking findings from the independent verify pass — both addressed in a post-verify cleanup commit:
1. **Dead defensive code** — `?? 0` on the Total Payment sum was unreachable (Eloquent's `sum()` already returns `0`, never `null`). Removed.
2. **Test-quality weakness** — `test_total_bookings_counts_every_status` used a substring `assertSee('4')` that could pass for the wrong reason (Total Customers also happened to equal 4 in that fixture). Replaced with a direct `assertSame(4, Booking::count())`, matching the pattern the other counter tests already use.

Neither was a functional/correctness bug; both are now resolved.

## Summary

**Passed:** 10/10 criteria
**Failed:** 0/10 criteria
**Verdict:** PASS
