# Tasks: Room & Rate Management (MOD-003)

**Change:** 003-room-rate-management
**Created:** 2026-09-14
**Total Tasks:** 9

## Summary

Three waves. Wave 1 builds RoomCategory's data layer (migration + seed + model), since Room's FK and live category select depend on it. Wave 2 builds RoomCategory's controller/views (BL-005) and Room's data layer (migration + model) in parallel — independent of each other. Wave 3 builds Room's controller/views (BL-002/BL-003), routes, and tests.

## Tasks

### Wave 1 — RoomCategory data layer

- [x] `T1` — RoomCategory migration (with seed)
  - Files: `database/migrations/2026_09_14_000002_create_room_categories_table.php` (create)
  - Estimate: small
  - Kind: migration
  - Depends: none
  - Notes: Columns per design.md: `name` (string), `price` (unsigned integer), timestamps. In `up()`, after `Schema::create`, insert the 3 seed rows: `['name' => 'Single Room', 'price' => 99]`, `['name' => 'Double Room', 'price' => 149]`, `['name' => 'Deluxe Room', 'price' => 199]` (AC-9) — use `DB::table('room_categories')->insert(...)`, not the Eloquent model (avoid a model-migration ordering dependency).

- [x] `T2` — RoomCategory model
  - Files: `app/Models/RoomCategory.php` (create)
  - Estimate: small
  - Kind: impl
  - Depends: T1
  - Notes: `$fillable = ['name', 'price']`. `hasMany(Room::class)` relation (Room model doesn't exist yet at this point in the build — fine, PHP resolves the class reference at call time, not at file-write time).

### Wave 2 — RoomCategory controller/views, Room data layer (independent of each other)

- [x] `T3` — RoomCategoryController + views (BL-005)
  - Files: `app/Http/Controllers/RoomCategoryController.php` (create), `resources/views/room_categories/index.blade.php` (create), `resources/views/room_categories/create.blade.php` (create), `resources/views/room_categories/edit.blade.php` (create)
  - Estimate: medium
  - Kind: impl
  - Depends: T2
  - Notes: Full resource CRUD (`index`, `create`, `store`, `edit`, `update`, `destroy`). Validation: `name` required string, `price` required integer min:0. `@csrf` on create/edit forms and the index view's inline delete forms (AC-10). AC-6/AC-7/AC-8.

- [x] `T4` — Room migration
  - Files: `database/migrations/2026_09_14_000003_create_rooms_table.php` (create)
  - Estimate: small
  - Kind: migration
  - Depends: T1
  - Notes: Columns per design.md: `room` (string), `category_id` (`$table->foreignId('category_id')->constrained('room_categories')` — restrict-on-delete, no `cascadeOnDelete()`), `status` (`$table->unsignedTinyInteger('status')->default(0)`), timestamps.

### Wave 3 — Room controller/views, routes, tests

- [x] `T5` — Room model
  - Files: `app/Models/Room.php` (create)
  - Estimate: small
  - Kind: impl
  - Depends: T4
  - Notes: `$fillable = ['room', 'category_id', 'status']`. `belongsTo(RoomCategory::class, 'category_id')`.

- [x] `T6` — RoomController (index, create/store, edit/update)
  - Files: `app/Http/Controllers/RoomController.php` (create), `resources/views/rooms/index.blade.php` (create), `resources/views/rooms/create.blade.php` (create), `resources/views/rooms/edit.blade.php` (create)
  - Estimate: medium
  - Kind: impl
  - Depends: T5, T3
  - Notes: `index($request)` — accepts optional `category_id` query param, filters `Room::query()` when present, passes `$categories = RoomCategory::all()` to the view for both the filter select and (in create/edit) the form select (AC-1, AC-2). `create()`/`store()`/`edit()`/`update()` validate `room` required string, `category_id` required `exists:room_categories,id` (AC-4), `status` required `in:0,1`. No `destroy()` (design.md's Key Decisions — BL-004 held out). `@csrf` on create/edit forms (AC-10).

- [x] `T7` — Routes
  - Files: `routes/web.php` (modify)
  - Estimate: small
  - Kind: config
  - Depends: T3, T6
  - Notes: `Route::resource('room-categories', RoomCategoryController::class);` and `Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);`.

- [x] `T8` — RoomCategory feature tests
  - Files: `tests/Feature/RoomCategoryManagementTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T7
  - Notes: AC-6, AC-7, AC-8, AC-9 (assert exactly 3 seeded rows with the right name/price after migration — can assert directly against `RoomCategory::all()` in a fresh `RefreshDatabase` test), and CSRF tests for category store/update/destroy (AC-10 category half) — same `$this->app['env'] = 'production'` pattern as `CsrfProtectionTest`/`CustomerManagementTest`.

- [x] `T9` — Room feature tests
  - Files: `tests/Feature/RoomManagementTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T7
  - Notes: AC-1 through AC-5, and CSRF tests for room store/update (AC-10 room half).

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
