# Verification Report: 007-admin-authentication

**Verified:** 2026-09-14
**Model:** claude-sonnet-5
**Verdict:** PASS

## Acceptance Criteria

- ✅ **AC-1 (FR-1):** Blank username/password re-renders form with validation error, no session established — `test_login_with_blank_fields_is_rejected` asserts `SessionHasErrors(['username','password'])` and `assertGuest()`. Passed.
- ✅ **AC-2 (FR-2/FR-3):** Seeded admin's exact credentials log in and reach an admin page — `test_login_with_correct_credentials_succeeds` asserts redirect to `customers.index`, `assertAuthenticated()`, and a followed-redirect 200. Passed.
- ✅ **AC-3 (FR-2):** Correct username + wrong password rejected — `test_login_with_wrong_password_is_rejected`. Passed.
- ✅ **AC-4 (FR-4):** Correct username in wrong case (`Admin` vs seeded `admin`) with correct password rejected — `test_login_with_wrong_case_username_is_rejected`. Passed, and independently confirmed by code trace.
- ✅ **AC-5 (FR-6):** Logout ends session; subsequent admin request redirects to login — `test_logout_ends_the_session`. Passed.
- ✅ **AC-6 (FR-8):** All six admin routes redirect to login when unauthenticated — `test_every_admin_route_redirects_to_login_when_unauthenticated`. Passed.
- ✅ **AC-7 (FR-8, regression):** Same six routes render 200 when authenticated — `test_every_admin_route_answers_when_authenticated`, plus all 6 retrofitted legacy test files pass in full with `actingAs()` added to `setUp()`. Passed.
- ✅ **AC-8 (public routes unaffected):** `/`, `/rooms-overview`, `/services`, `/food`, `GET /book` all reachable unauthenticated. Passed.
- ✅ **AC-9 (FR-10):** Admin page body contains links to every other admin screen — matches `admin-nav.blade.php`'s 6 links. Passed.
- ✅ **AC-10 (FR-7):** POST to login without CSRF token rejected with 419 before handler logic runs. Passed, middleware ordering independently confirmed via Laravel framework source.

## Independent Verification

**CQ-020 case-sensitivity trace (`AuthController::login()`):** the `||` short-circuit chain (`! $user || $user->username !== $validated['username'] || ! Hash::check(...)`) rejects a case-different username match even though the DB lookup itself is case-insensitive — confirmed correct by code trace, not just test-passing.

**Middleware ordering (CSRF before auth):** confirmed directly in Laravel framework source — `ValidateCsrfToken` is a member of the `web` group applied automatically by `withRouting()`; `auth` is a route-level middleware alias layered on top in `routes/web.php`. `bootstrap/app.php` makes no customization. CSRF genuinely runs before auth for every admin POST route.

**Route coverage cross-check:** exact match between spec.md's six named admin route groups and what's inside `Route::middleware('auth')->group(...)` in `routes/web.php` — nothing extra, nothing missing. Login/logout, marketing routes, and public booking routes correctly sit outside the group.

**Seeded user hashing:** `Hash::make('password123')` at seed time in the users migration; `User` model additionally casts `password` as `'hashed'`.

**Lint-exemption provenance:** `git log`/`git diff` confirm the 4 pre-existing Pint issues (`bootstrap/providers.php`, `config/auth.php`, `config/logging.php`, `public/index.php`) trace to the original scaffold commit `e53b6f5`, untouched by this change.

## Test Results

- `php artisan test`: **78 passed (578 assertions)**, 22.06s.
- `vendor/bin/pint --test`: 4 pre-existing, out-of-scope style issues only. No new lint issues.

## Issues Found

No issues found.

## Summary

**Passed:** 10/10 criteria
**Failed:** 0/10 criteria
**Verdict:** PASS
