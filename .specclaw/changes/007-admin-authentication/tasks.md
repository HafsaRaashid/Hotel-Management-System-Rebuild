# Tasks: Admin Authentication & Access Control (MOD-001)

**Change:** 007-admin-authentication
**Created:** 2026-09-14
**Total Tasks:** 6

## Summary

Data layer, then login/logout, then the middleware gate + nav retrofit (the widest-touching task), then the existing-test retrofit, then new tests. Every task ends with a full-suite run, not just its own new tests — this change has the widest blast radius so far.

## Tasks

### Wave 1 — Data layer

- [x] `T1` — Users migration + User model
  - Files: `database/migrations/2026_09_14_000006_create_users_table.php` (create), `app/Models/User.php` (create)
  - Estimate: medium
  - Kind: migration
  - Depends: none
  - Notes: Migration per design.md's Data Model Changes; seed one row in `up()` via `DB::table('users')->insert([...'password' => Hash::make('password123')...])` (CQ-002 — hashed even in the seed, not a raw string). Model extends `Illuminate\Foundation\Auth\User as Authenticatable`; `$fillable = ['name','username','password','type']`; `$hidden = ['password']`; `$casts = ['password' => 'hashed']`.

### Wave 2 — Login/Logout (BL-010)

- [x] `T2` — AuthController + login view + routes
  - Files: `app/Http/Controllers/AuthController.php` (create), `resources/views/auth/login.blade.php` (create), `routes/web.php` (modify — add `login`/`login.attempt`/`logout` routes, public)
  - Estimate: large
  - Kind: impl
  - Depends: T1
  - Notes: `showLogin()` returns the view. `login(Request $request)`: validate `username`/`password` both `required` (DR-001, FR-1) with distinct messages ("Username is required"/"Password is required" — legacy wording, no CQ requires exact text but nothing forbids matching it either). Then `$user = User::where('username', $validated['username'])->first();` — if no user, OR `$user->username !== $validated['username']` (CQ-020's exact-case recheck), OR `!Hash::check($validated['password'], $user->password)` (CQ-002/DR-002), reject with one generic message ("Incorrect username or password.") on the `username` field (FR-5 — one message, not per-cause). Otherwise `Auth::login($user);` and redirect to `route('customers.index')` (the first admin screen — no dashboard/MOD-007 exists yet to land on). `logout(Request $request)`: `Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();` then redirect to `route('login')`. `@csrf` on the login form (FR-7).

### Wave 3 — Session gate + admin nav (BL-011)

- [x] `T3` — Auth middleware group + admin nav partial, retrofitted onto every existing admin screen
  - Files: `routes/web.php` (modify), `resources/views/partials/admin-nav.blade.php` (create), `resources/views/customers/index.blade.php` (modify), `resources/views/rooms/index.blade.php` (modify), `resources/views/room_categories/index.blade.php` (modify), `resources/views/bookings/pending.blade.php` (modify), `resources/views/walk-in/available.blade.php` (modify), `resources/views/stays/index.blade.php` (modify)
  - Estimate: large
  - Kind: impl
  - Depends: T2
  - Notes: In `routes/web.php`, wrap the existing MOD-005/MOD-003/MOD-004 route registrations (`customers`, `room-categories`, `rooms`, `bookings/pending/*`, `walk-in/*`, `stays/*`) inside one `Route::middleware('auth')->group(function () { ... });` — move the existing lines into the closure, do not duplicate or rename them (design.md's Key Decisions). `admin-nav.blade.php`: links to `route('customers.index')`, `route('rooms.index')`, `route('room-categories.index')`, `route('bookings.pending')`, `route('walk-in.available')`, `route('stays.index')`, plus `{{ auth()->user()->name }}` and a `POST` logout form (`@csrf`). Add `@include('partials.admin-nav')` near the top of each of the 6 listed views' `@section('content')` (FR-10).

### Wave 4 — Retrofit existing tests to authenticate

- [x] `T4` — Add actingAs() to every existing admin-route test class
  - Files: `tests/Feature/CustomerManagementTest.php` (modify), `tests/Feature/RoomManagementTest.php` (modify), `tests/Feature/RoomCategoryManagementTest.php` (modify), `tests/Feature/PendingBookingTest.php` (modify), `tests/Feature/WalkInTest.php` (modify), `tests/Feature/StayLifecycleTest.php` (modify)
  - Estimate: medium
  - Kind: test
  - Depends: T3
  - Notes: Add (or extend, if a `setUp()` already exists) `protected function setUp(): void { parent::setUp(); $this->actingAs(\App\Models\User::first()); }` to each class. Design.md's Key Decisions explains why the existing CSRF-rejection tests in these files need no other change. Run the full suite after this task, not just these files — this is the task most likely to surface an unexpected break.

### Wave 5 — New authentication tests

- [x] `T5` — Admin authentication feature tests
  - Files: `tests/Feature/AdminAuthenticationTest.php` (create)
  - Estimate: large
  - Kind: test
  - Depends: T4
  - Notes: AC-1 through AC-10. AC-6/AC-7: loop over the six admin index routes, asserting a redirect-to-login when unauthenticated and a 200 when `actingAs()` the seeded admin. AC-8: assert the public routes (`marketing.home`, `marketing.room`, `marketing.services`, `marketing.food`, `booking.create`) return 200 with NO `actingAs()`. AC-4 (CQ-020): seed/fetch the admin user (`username: admin`), submit `Admin` (wrong case) with the correct password, assert rejection.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
