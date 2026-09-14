# Design: Room & Rate Management (MOD-003)

**Change:** 003-room-rate-management
**Created:** 2026-09-14

## Technical Approach

Same shape as change `002-customer-management`: migrations → models → controllers → Blade views → routes → tests. Two entities this time (`RoomCategory`, `Room`), with `Room` FK-dependent on `RoomCategory`, so `RoomCategory`'s migration/model/seed lands first in the build order.

Routes again carry no `/admin` prefix (BL-011 not built — same reasoning as change 002's design.md).

## Architecture

```
routes/web.php ──resource routes──> RoomCategoryController ──Eloquent──> RoomCategory (room_categories table)
                │                                                              ▲
                └──resource routes──> RoomController ──Eloquent──> Room ───────┘ (category_id FK)
                                            │
                                            └──> resources/views/rooms/{index,create,edit}.blade.php
                                                 resources/views/room_categories/{index,create,edit}.blade.php
```

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/<ts1>_create_room_categories_table.php` | create | `room_categories`: `id`, `name`, `price` (unsigned integer), timestamps. Seeds the 3 legacy categories in `up()`. |
| `database/migrations/<ts2>_create_rooms_table.php` | create | `rooms`: `id`, `room` (string), `category_id` (foreign key → `room_categories.id`), `status` (unsigned tiny integer, default 0), timestamps. |
| `app/Models/RoomCategory.php` | create | `$fillable`: `name`, `price`. `hasMany(Room::class)`. |
| `app/Models/Room.php` | create | `$fillable`: `room`, `category_id`, `status`. `belongsTo(RoomCategory::class)`. |
| `app/Http/Controllers/RoomCategoryController.php` | create | Full CRUD (`index`, `create`, `store`, `edit`, `update`, `destroy`) — BL-005. |
| `app/Http/Controllers/RoomController.php` | create | `index` (with `?category_id=` filter, BL-002), `create`/`store`, `edit`/`update` (BL-003). No `destroy` — BL-004 held out. |
| `resources/views/room_categories/index.blade.php` | create | List + inline delete form (BL-005). |
| `resources/views/room_categories/create.blade.php` | create | Create form (BL-005). |
| `resources/views/room_categories/edit.blade.php` | create | Edit form (BL-005). |
| `resources/views/rooms/index.blade.php` | create | List + category filter select, no delete action (BL-002). |
| `resources/views/rooms/create.blade.php` | create | Create form: `room`, `category_id` select (live from `room_categories`), `status` select (BL-003). |
| `resources/views/rooms/edit.blade.php` | create | Same fields, pre-filled (BL-003). |
| `routes/web.php` | modify | Add `Route::resource('room-categories', RoomCategoryController::class);` and `Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);`. |
| `tests/Feature/RoomCategoryManagementTest.php` | create | AC-6, AC-7, AC-8, AC-9, and the category-write half of AC-10. |
| `tests/Feature/RoomManagementTest.php` | create | AC-1 through AC-5, and the room-write half of AC-10. |

## Data Model Changes

**`room_categories`:**

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | Auto-increment — the legacy app's seeded ids (1/2/3) are not pinned by any decision; Eloquent's own auto-increment is used, and the migration's seed rows land at 1/2/3 anyway since they're the first three inserts on an empty table. |
| `name` | string | |
| `price` | unsigned integer | Nightly rate. |
| `created_at`/`updated_at` | timestamps | |

**`rooms`:**

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `room` | string | Display name/number, e.g. `"Single_101"`. |
| `category_id` | unsigned bigint, FK → `room_categories.id` | `constrained()->cascadeOnDelete()` is **not** used — see spec.md's Edge Cases: no decision governs category-deletion referential integrity, and a hard FK cascade-delete would silently destroy rooms, which is a stronger, undecided behavior. A plain `foreignId()->constrained()` (restrict-on-delete, the Postgres/MySQL default) is used instead — it will simply block a category delete that a room still references, which is a safe default absent a decision either way. |
| `status` | unsigned tiny integer, default 0 | 0=Available, 1=Unavailable (domain-model.md Enumeration 2). |
| `created_at`/`updated_at` | timestamps | |

## API Changes

New routes:
- `GET /room-categories`, `GET /room-categories/create`, `POST /room-categories`, `GET /room-categories/{room_category}/edit`, `PUT /room-categories/{room_category}`, `DELETE /room-categories/{room_category}` (BL-005, full resource).
- `GET /rooms` (accepts `?category_id=`), `GET /rooms/create`, `POST /rooms`, `GET /rooms/{room}/edit`, `PUT /rooms/{room}` (BL-002/BL-003; no `DELETE /rooms/{room}` — BL-004 held out).

## Key Decisions

- **`room_categories` table name, not the legacy `room_categoricals`** — idiomatic Laravel pluralization, no decision pins the physical name (see spec.md's Notes).
- **RoomCategory CRUD lands before Room CRUD in the build order**, even though `rooms.category_id` is a real FK constraint, `category_id`'s select — live from `room_categories`, not hardcoded — needs real rows to reference. Building both in one change (rather than a decision-record note like BL-004's) means BL-002/BL-003 can go straight to the live-select version instead of a hardcoded-then-migrated-later placeholder.
- **`category_id` uses a restrict-on-delete FK, not cascade-delete.** Explained in the Data Model table above — no decision authorizes either the FK constraint's presence or its cascade behavior, so the safer, standard default (block rather than silently cascade) is used, and this is called out explicitly rather than picked silently.
- **No `destroy()` on `RoomController`** — BL-004 is held out per spec.md; there is no code path that deletes a room in this change at all, matching BL-008's AC-7 precedent (a genuine absence, not a stub).

## Risks & Mitigations

- **Risk:** A room category referenced by rooms cannot be deleted once rooms exist (restrict-on-delete FK) — no UI feedback is designed for this failure in BL-005's scope. **Mitigation:** low risk at this stage (BL-004/Room-create are the only room-producing paths, and this change's own tests don't leave rooms referencing a category under test when deleting it); worth revisiting once BL-004 or MOD-004 exist and rooms/categories accumulate real data.
- **Risk:** BL-004 (Room Delete) stays unbuilt indefinitely if MOD-004 is delayed. **Mitigation:** tracked explicitly in rebuild-backlog.md (a status note will be added, same as BL-008's), not silently dropped.
