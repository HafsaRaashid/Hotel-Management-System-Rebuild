# Design: Admin Authentication & Access Control (MOD-001)

**Change:** 007-admin-authentication
**Created:** 2026-09-14

## Technical Approach

`AuthController` (login/logout) using Laravel's built-in `Auth` facade against a session guard (already configured in `config/auth.php`). A new `users` migration + `User` model back that guard's provider. Every existing admin route group is wrapped in `Route::middleware('auth')->group(...)` — Laravel's own `Authenticate` middleware, no custom gate needed (CQ-001 is satisfied by the router itself being a compiled whitelist, not by new code).

## Architecture

```
GET/POST /login, POST /logout ──> AuthController ──Auth::login()/Auth::attempt()──> User (session guard)

Route::middleware('auth')->group(function () {
    customers.*, rooms.*, room-categories.*, bookings.pending.*, walk-in.*, stays.*
})
    └──> unauthenticated request redirects to route('login') (Laravel's Authenticate::redirectTo())

partials/admin-nav.blade.php ── included by every admin screen (mirrors partials/marketing-nav.blade.php)
```

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/<ts>_create_users_table.php` | create | `users`: `id`, `name`, `username` (unique, case-insensitive collation), `password` (hashed), `type` (unsigned tiny int, default 2). Seeds one admin user in `up()` — see spec.md's Notes for the documented placeholder credentials. |
| `app/Models/User.php` | create | `extends Authenticatable` (not the generic `Model`). `$fillable`: `name`, `username`, `password`, `type`. `$hidden`: `password`. `$casts`: `password => 'hashed'` (Laravel's automatic `Hash::make()` on assignment). |
| `app/Http/Controllers/AuthController.php` | create | `showLogin()`, `login()`, `logout()`. |
| `resources/views/auth/login.blade.php` | create | Login form. |
| `resources/views/partials/admin-nav.blade.php` | create | Links to every existing admin index route, a logout button, `{{ auth()->user()->name }}`. |
| `routes/web.php` | modify | Add `login`/`logout` routes (public). Wrap every existing admin route registration in `Route::middleware('auth')->group(...)`. |
| `resources/views/customers/index.blade.php`, `rooms/index.blade.php`, `room_categories/index.blade.php`, `bookings/pending.blade.php`, `walk-in/available.blade.php`, `stays/index.blade.php` | modify | `@include('partials.admin-nav')` near the top of `@section('content')` — same pattern change 004 used for `partials.marketing-nav`. |
| `tests/Feature/CustomerManagementTest.php`, `RoomManagementTest.php`, `RoomCategoryManagementTest.php`, `PendingBookingTest.php`, `WalkInTest.php`, `StayLifecycleTest.php` | modify | Add `$this->actingAs(User::first());` to each class's `setUp()` — see Key Decisions for why this doesn't disturb their existing CSRF-rejection tests. |
| `tests/Feature/AdminAuthenticationTest.php` | create | AC-1 through AC-10. |

## Data Model Changes

**`users`:**

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `name` | string | |
| `username` | string, **unique** | CQ-019 — case-insensitive collation (MySQL/MariaDB default for a plain `string()` column already matches the legacy's own `_ci` behavior; no special collation clause needed beyond the `unique()` index itself). |
| `password` | string | Hashed via the model's `'hashed'` cast (CQ-002) — never plaintext. |
| `type` | unsigned tiny integer, default 2 | 1=admin, 2=staff (domain-model.md Enumeration 3), matching `users.type`'s existing legacy meaning. |
| `created_at`/`updated_at` | timestamps | |

## API Changes

New routes: `GET /login` (`login`), `POST /login` (`login.attempt`), `POST /logout` (`logout`) — all public. Every existing admin route (unchanged URIs/names) now requires authentication.

## Key Decisions

- **`Route::middleware('auth')->group()` wraps existing route *registrations* in `routes/web.php`, not new duplicate route definitions.** The existing `Route::resource(...)`/`Route::get(...)` calls move inside the group closure as-is — no route name or URI changes, so every `route('customers.index')`-style call anywhere in the codebase (controllers, views, tests) keeps working unchanged.
- **CSRF-rejection tests in the 6 existing admin test files need no changes.** Laravel's `web` middleware group (which includes `ValidateCsrfToken`) wraps the entire `routes/web.php` file automatically and runs *before* a route-specific `auth` middleware in the pipeline (outer-to-inner: global `web` group, then the route's own `->middleware('auth')`). A CSRF-missing POST is rejected with 419 at the CSRF layer regardless of authentication state, so every existing `test_..._without_csrf_token_is_rejected` test keeps passing exactly as written. Only tests expecting a *successful* request need `actingAs()`.
- **`actingAs(User::first())` in each retrofitted test class's `setUp()`, not per-test.** `RefreshDatabase` re-migrates before the suite and wraps each test in a transaction, so the migration's seeded admin user exists in every test already — `User::first()` reliably fetches it without each test needing to construct its own user.
- **No `password_reset_tokens`/"remember me"/registration flow.** None of these are named in any DR/CQ decision or in functional-spec.md's capability description (login + logout only). Building them would be speculative scope.
- **DR-012's role-visibility half is not implemented in `admin-nav.blade.php`.** There is no "Users" screen to conditionally show yet — see spec.md's Notes. The nav partial links only to screens that exist.

## Risks & Mitigations

- **Risk:** this change touches every prior change's test suite (6 files) — the widest blast radius so far. **Mitigation:** the retrofit is mechanical and uniform (one `setUp()` addition per file, explained above), and the full suite is run after every task, not just at the end.
- **Risk:** seeded placeholder credentials (`admin`/`password123`) could be mistaken for a real security posture. **Mitigation:** documented explicitly in spec.md's Notes and in `.specclaw/context.md` as development-only, not a decision.
