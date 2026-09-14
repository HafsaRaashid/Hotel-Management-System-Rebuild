# Design: Public Marketing Site (MOD-006)

**Change:** 004-public-marketing-site
**Created:** 2026-09-14

## Technical Approach

A single `MarketingController` with four simple GET actions, no models beyond reading `RoomCategory` (already built). Four Blade views, each including a shared nav partial (not a new layout — `layouts/app.blade.php` stays a neutral shared shell used by admin screens too; a dedicated public layout would be premature for four static-shaped pages).

Replaces the foundation's placeholder `Route::view('/', 'shell')` — `resources/views/shell.blade.php`'s own header comment names this exact replacement as expected.

## Architecture

```
routes/web.php ──> MarketingController::home/room/services/food ──> resources/views/marketing/{home,room,services,food}.blade.php
                                    │                                         │
                                    └──> RoomCategory::all() (home, room only) ┘
resources/views/partials/marketing-nav.blade.php ── included by all four views
```

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/MarketingController.php` | create | `home()`, `room()` — both pass `RoomCategory::all()`. `services()`, `food()` — no data. |
| `resources/views/partials/marketing-nav.blade.php` | create | Shared nav: Home/Room/Services/Foods (named routes) + Book Now (`href="/book"`, plain string per spec.md FR-5). |
| `resources/views/marketing/home.blade.php` | create | FR-1: iterates `$categories`, renders `name`/`price`. Includes the nav partial. |
| `resources/views/marketing/room.blade.php` | create | FR-2: same category data. Includes the nav partial. |
| `resources/views/marketing/services.blade.php` | create | FR-3: static content. Includes the nav partial. |
| `resources/views/marketing/food.blade.php` | create | FR-3: static content. Includes the nav partial. |
| `routes/web.php` | modify | Replace `Route::view('/', 'shell')->name('shell');` with `Route::get('/', [MarketingController::class, 'home'])->name('marketing.home');` plus three more named GET routes. |
| `resources/views/shell.blade.php` | delete | No longer referenced by any route once `/` is replaced — its own header comment names this exact change as the reason for its removal. |
| `tests/Feature/HealthCheckTest.php` | modify | Remove `test_the_shell_route_renders` — it asserted the placeholder shell's behavior at `/`, which this change replaces entirely; `test_the_health_check_endpoint_answers` (`/up`, unrelated) is untouched. |
| `tests/Feature/MarketingSiteTest.php` | create | AC-1 through AC-6. |

## Data Model Changes

None — reads `RoomCategory` only, no schema change.

## API Changes

New routes (all public, no auth, no `/admin` prefix — same reasoning as every prior change):
- `GET /` → `marketing.home` (replaces the old `shell` route)
- `GET /rooms-overview` → `marketing.room` (named to avoid colliding with the existing `rooms.*` resource from change `003-room-rate-management`, which owns `/rooms` for the admin Room CRUD screens)
- `GET /services` → `marketing.services`
- `GET /food` → `marketing.food`

## Key Decisions

- **`/rooms-overview` instead of `/room` or `/rooms`** — the legacy path was `homepage/room.php`, but this rebuild already has `Route::resource('rooms', RoomController::class)` from change `003-room-rate-management` owning `/rooms` (admin CRUD). A literal `/room` or `/rooms` would either collide or read confusingly next to the admin resource. No decision pins the exact public URL, so a clear, non-colliding name is used instead — flagged explicitly since it deviates from the legacy path, unlike every other route in this project so far.
- **No new public layout.** `layouts/app.blade.php` (already shared by admin screens and the old shell) stays as-is; the nav lives in its own partial included per-page, not baked into the shared layout, so admin CRUD pages aren't affected.
- **`shell.blade.php` is deleted, not left dangling.** Its own comment says it exists only until MOD-006 replaces the root route — that has now happened.
- **Book Now uses a plain string, not `route()`.** Explained in spec.md's FR-5 — avoids a `RouteNotFoundException` on every marketing page until BL-015 exists.

## Risks & Mitigations

- **Risk:** Deleting `shell.blade.php` could look like an unrelated deletion to a reviewer. **Mitigation:** the file's own header comment predicted and named this exact change; cited directly in this design and in the task that removes it.
- **Risk:** `/book` 404s until BL-015 lands. **Mitigation:** documented in spec.md's Edge Cases as the intended state, not a bug.
