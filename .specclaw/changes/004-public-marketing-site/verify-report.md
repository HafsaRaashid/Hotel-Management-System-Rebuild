# Verification Report: 004-public-marketing-site

**Verified:** 2026-09-14
**Model:** Claude Sonnet 5
**Verdict:** PASS

## Acceptance Criteria

- ✅ **AC-1 (FR-1):** "GET `/` returns 200 and the response body contains every seeded `RoomCategory`'s `name` and `price`"
  - Code: `routes/web.php:30` `Route::get('/', [MarketingController::class, 'home'])->name('marketing.home');`; `MarketingController.php:12-14` `return view('marketing.home', ['categories' => RoomCategory::all()]);`; `home.blade.php:23-24` `{{ $category->name }}` / `${{ $category->price }} / night`.
  - Test: `MarketingSiteTest::test_home_page_shows_live_category_pricing` — `$response->assertStatus(200);` then asserts `$response->assertSee($category->name)` / `assertSee((string) $category->price)` for every seeded category. Rerun result: `✓ home page shows live category pricing 0.20s` (PASS).

- ✅ **AC-2 (FR-2):** "GET the room details route returns 200 and the response body contains every seeded `RoomCategory`'s `name` and `price`"
  - Code: `routes/web.php:31` `Route::get('/rooms-overview', [MarketingController::class, 'room'])->name('marketing.room');`; `room.blade.php:21-22` identical rendering pattern to home.
  - Test: `MarketingSiteTest::test_room_details_page_shows_live_category_pricing`. Rerun result: `✓ room details page shows live category pricing 0.18s` (PASS).

- ✅ **AC-3 (FR-1/FR-2, live-data proof):** "Changing a `RoomCategory`'s `price` in the database and re-requesting `/` ... reflects the new price"
  - Code: `MarketingController::home()` calls `RoomCategory::all()` fresh on every request (no caching/memoization present anywhere in the controller or model).
  - Test: `MarketingSiteTest::test_home_page_reflects_a_price_change` — `$category->update(['price' => 12345]);` then `$response->assertSee('12345');`. Rerun result: `✓ home page reflects a price change 0.17s` (PASS).

- ✅ **AC-4 (FR-3):** "GET the services route and GET the food & drinks route both return 200"
  - Code: `routes/web.php:32-33` — `/services` → `marketing.services`, `/food` → `marketing.food`.
  - Test: `test_services_page_answers` / `test_food_page_answers` — `$this->get(route(...))->assertStatus(200);`. Rerun: `✓ services page answers 0.20s`, `✓ food page answers 0.23s` (PASS).

- ✅ **AC-5 (FR-4):** "All four pages' response bodies contain the nav links' text (Home, Room, Services, Foods, Book Now)"
  - Code: `marketing-nav.blade.php:11-15` — link text `Home`, `Room`, `Services`, `Foods`, `Book Now`; included via `@include('partials.marketing-nav')` in all four views (`home.blade.php:12`, `room.blade.php:11`, `services.blade.php:12`, `food.blade.php:11`).
  - Test: `test_all_four_pages_show_the_nav_links` iterates all 4 routes asserting all 5 link texts. Rerun: `✓ all four pages show the nav links 0.40s` (PASS).

- ✅ **AC-6 (FR-5):** "The 'Book Now' link's `href` is `/book` (a literal path ... not asserting the route resolves)"
  - Code: `marketing-nav.blade.php:15` `<li class="nav-item"><a class="nav-link" href="/book">Book Now</a></li>` — a literal string, not `route('book')` or similar.
  - Test: `test_book_now_link_points_at_a_plain_book_path` — `$response->assertSee('href="/book"', false);`. Rerun: `✓ book now link points at a plain book path 0.20s` (PASS).
  - ⚠️ Edge case confirmed as documented, not a defect: `/book` itself is not routed anywhere in `routes/web.php` (only `/`, `/rooms-overview`, `/services`, `/food` are registered for MOD-006), so visiting it 404s — matches spec's Edge Cases section exactly.

## Independent Confirmations

- **`shell.blade.php` deletion:** `ls resources/views/shell.blade.php` → `No such file or directory`. `grep -rn "shell" routes/` matches only a comment (`routes/web.php:24`, "Replaces the foundation's shell placeholder"), no route registration. `git show 736b784` confirms `test_the_shell_route_renders` was removed (8 lines deleted) while `test_the_health_check_endpoint_answers` remains intact in `tests/Feature/HealthCheckTest.php`.
- **`/book` href is a literal string:** confirmed above (AC-6) — `href="/book"` is plain markup, not a `route()` helper call, so it cannot throw `RouteNotFoundException`.
- **No collision with admin `rooms.*` resource:** `routes/web.php:31` registers `/rooms-overview` (URI) / `marketing.room` (name); `routes/web.php:48` registers `Route::resource('rooms', RoomController::class)` which owns URI prefix `rooms` and route names `rooms.*`. `/rooms-overview` ≠ `/rooms` and `marketing.room` ≠ any `rooms.*` name — no collision. A code comment (`routes/web.php:26-28`) documents this was a deliberate choice.

## Test Results

Re-ran the full suite independently in `php:8.2-cli` (a stale local, gitignored `bootstrap/cache/config.php` pinning `DB_CONNECTION=mysql` caused a spurious first failure unrelated to the app — cleared before the real run):

```
Tests:    36 passed (420 assertions)
Duration: 22.59s
```
including all 7 in `MarketingSiteTest`:
```
✓ home page shows live category pricing        0.20s
✓ room details page shows live category pricing 0.18s
✓ home page reflects a price change             0.17s
✓ services page answers                         0.20s
✓ food page answers                             0.23s
✓ all four pages show the nav links             0.40s
✓ book now link points at a plain book path     0.20s
```

Lint (`vendor/bin/pint --test`), re-run independently:
```
FAIL  .... 38 files, 4 style issues
⨯ bootstrap/providers.php  fully_qualified_strict_types, single_line_after_imports
⨯ config/auth.php          fully_qualified_strict_types, single_line_after_imports
⨯ config/logging.php       no_unused_imports
⨯ public/index.php         no_unused_imports
```
Confirmed via `git log --oneline --all -- bootstrap/providers.php config/auth.php config/logging.php public/index.php` → only `e53b6f5` ("chore: track the Laravel foundation scaffold and specclaw project records") touches these files. None of this change's commits (`1fb5d25`, `ad8c56b`, `ab817f6`, `736b784`, `63c4526`) touch them — pre-existing, out-of-scope, non-blocking per established precedent.

## Issues Found

No issues found.

## Summary

**Passed:** 6/6 criteria
**Failed:** 0/6 criteria
**Verdict:** PASS
