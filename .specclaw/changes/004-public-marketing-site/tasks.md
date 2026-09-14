# Tasks: Public Marketing Site (MOD-006)

**Change:** 004-public-marketing-site
**Created:** 2026-09-14
**Total Tasks:** 5

## Summary

One wave — small, independent-shaped pieces with light sequencing (views need the nav partial; routes need the controller and views; the shell removal and its test cleanup can land anywhere after routes switch over).

## Tasks

### Wave 1

- [x] `T1` — MarketingController + nav partial
  - Files: `app/Http/Controllers/MarketingController.php` (create), `resources/views/partials/marketing-nav.blade.php` (create)
  - Estimate: small
  - Kind: impl
  - Depends: none
  - Notes: `home()`/`room()` pass `['categories' => RoomCategory::all()]`; `services()`/`food()` pass nothing. Nav partial: links to `route('marketing.home')`, `route('marketing.room')`, `route('marketing.services')`, `route('marketing.food')`, and a plain `<a href="/book">Book Now</a>` (FR-5 — not `route()`, BL-015 doesn't exist).

- [x] `T2` — Marketing views
  - Files: `resources/views/marketing/home.blade.php` (create), `resources/views/marketing/room.blade.php` (create), `resources/views/marketing/services.blade.php` (create), `resources/views/marketing/food.blade.php` (create)
  - Estimate: medium
  - Kind: impl
  - Depends: T1
  - Notes: `@extends('layouts.app')`, `@include('partials.marketing-nav')` near the top of each `@section('content')`. `home.blade.php`/`room.blade.php`: `@foreach ($categories as $category)` rendering `{{ $category->name }}` and `{{ $category->price }}` (AC-1, AC-2, AC-3). `services.blade.php`/`food.blade.php`: static placeholder content, no data (AC-4).

- [x] `T3` — Routes + shell removal
  - Files: `routes/web.php` (modify), `resources/views/shell.blade.php` (delete)
  - Estimate: small
  - Kind: config
  - Depends: T1, T2
  - Notes: Replace `Route::view('/', 'shell')->name('shell');` with the four named GET routes per design.md's API Changes (`marketing.home` at `/`, `marketing.room` at `/rooms-overview`, `marketing.services` at `/services`, `marketing.food` at `/food`). Delete `shell.blade.php` — no route references it after this.

- [x] `T4` — Remove the obsolete shell test
  - Files: `tests/Feature/HealthCheckTest.php` (modify)
  - Estimate: small
  - Kind: test
  - Depends: T3
  - Notes: Remove `test_the_shell_route_renders` only — it asserted the placeholder shell's behavior at `/`, which no longer exists. Leave `test_the_health_check_endpoint_answers` untouched.

- [x] `T5` — Marketing site feature tests
  - Files: `tests/Feature/MarketingSiteTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T3
  - Notes: AC-1 through AC-6. AC-3 (live-data proof): seed categories via the migration (already seeded, `RefreshDatabase` re-runs it), update one category's `price` via `RoomCategory::where(...)->update(...)`, re-request the page, assert the new value appears. AC-6: assert the rendered HTML contains `href="/book"` literally (e.g. `$response->assertSee('href="/book"', false)`), not that the route resolves.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
