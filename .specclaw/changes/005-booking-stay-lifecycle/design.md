# Design: Booking & Stay Lifecycle (MOD-004)

**Change:** 005-booking-stay-lifecycle
**Created:** 2026-09-14

## Technical Approach

One `bookings` table/model, four controllers grouped by lifecycle stage (matching the legacy's own file groupings while consolidating each stage's actions into one controller, same granularity as `CustomerController`/`RoomController` in prior changes):

- **`BookingController`** (public) — BL-015: `create()`/`store()`.
- **`PendingBookingController`** (admin) — BL-016: `index()`/`destroy()` (cancel); BL-017: `convert()`.
- **`WalkInController`** (admin) — BL-018: `available()`; BL-019: `store()`.
- **`StayController`** (admin) — BL-020: `index()`; BL-021: `checkout()`; BL-022: `updateDate()`; BL-023: `show()`.

Routes carry no `/admin` prefix (BL-011 not built — same reasoning as every prior change). `BookingController`'s routes are the only genuinely public ones in this change.

## Architecture

```
Public:  GET/POST /book ──> BookingController ──creates──> Booking (status=0) + Customer (dedup)

Admin:   GET /bookings/pending ──> PendingBookingController@index (status=0)
         DELETE /bookings/pending/{booking} ──> @destroy (status=0 -> status=3, cancel)
         POST /bookings/pending/{booking}/convert ──> @convert (status=0 -> status=1, assigns Room)

         GET /walk-in ──> WalkInController@available (status=0 Rooms)
         POST /walk-in ──> @store (creates Booking status=1 directly + Customer dedup)

         GET /stays ──> StayController@index (status=1, status=2)
         POST /stays/{booking}/checkout ──> @checkout (status=1 -> status=2, bills Customer, frees Room)
         PUT /stays/{booking} ──> @updateDate (status=1, dateout + recomputed days_of_stay)
         GET /stays/{booking} ──> @show (status=2, read-only)

Booking belongsTo Customer, belongsTo RoomCategory (category_id), belongsTo Room (room_id, nullable until check-in)
```

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/<ts>_create_bookings_table.php` | create | See Data Model Changes. |
| `app/Models/Booking.php` | create | `belongsTo(Customer::class)`, `belongsTo(RoomCategory::class, 'category_id')`, `belongsTo(Room::class, 'room_id')`. Static `generateUniqueRefNo(): int` (range 0-999999999, same retry-loop pattern as `Customer::generateUniqueCustomerId()`). Constants or a small enum-like set of `STATUS_BOOKED=0`, `STATUS_CHECKED_IN=1`, `STATUS_CHECKED_OUT=2`, `STATUS_CANCELLED=3`. |
| `app/Http/Controllers/BookingController.php` | create | `create()`, `store()` — BL-015. |
| `app/Http/Controllers/PendingBookingController.php` | create | `index()`, `destroy()` — BL-016. `convert()` — BL-017. |
| `app/Http/Controllers/WalkInController.php` | create | `available()` — BL-018. `store()` — BL-019. |
| `app/Http/Controllers/StayController.php` | create | `index()` — BL-020. `checkout()` — BL-021. `updateDate()` — BL-022. `show()` — BL-023. |
| `resources/views/booking/create.blade.php` | create | Public booking form — BL-015. |
| `resources/views/bookings/pending.blade.php` | create | Pending list + cancel + convert forms — BL-016/BL-017. |
| `resources/views/bookings/convert.blade.php` | create | Room-assignment form (available same-category rooms) — BL-017. |
| `resources/views/walk-in/available.blade.php` | create | Available rooms list — BL-018. |
| `resources/views/walk-in/create.blade.php` | create | Walk-in check-in form — BL-019. |
| `resources/views/stays/index.blade.php` | create | Checked-in/out list — BL-020. |
| `resources/views/stays/checkout.blade.php` | create | Checkout/billing form — BL-021. |
| `resources/views/stays/edit.blade.php` | create | Edit checkout date — BL-022. |
| `resources/views/stays/show.blade.php` | create | Completed-stay detail — BL-023. |
| `resources/views/partials/marketing-nav.blade.php` | modify | "Book Now" → `route('booking.create')`, replacing the placeholder `/book` string (FR-23). |
| `routes/web.php` | modify | Add all routes above. |
| `tests/Feature/BookingIntakeTest.php` | create | AC-1, AC-2, AC-3, and the BL-015 CSRF half of AC-14. |
| `tests/Feature/PendingBookingTest.php` | create | AC-4, AC-5, and the BL-016/BL-017 CSRF halves of AC-14. |
| `tests/Feature/WalkInTest.php` | create | AC-6, AC-7, and the BL-019 CSRF half of AC-14. |
| `tests/Feature/StayLifecycleTest.php` | create | AC-8 through AC-13, and the BL-021/BL-022 CSRF halves of AC-14. |
| `tests/Feature/MarketingSiteTest.php` | modify | AC-15 — add a test that the "Book Now" link resolves (not a 404) and reaches the booking form. |

## Data Model Changes

**`bookings`:**

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `ref_no` | unsigned integer, unique | DR-003, range 0-999999999. |
| `customer_id` | unsigned bigint, FK → `customers.id` | CQ-011 — not nullable; every booking creates/finds a customer in the same transaction. |
| `name`, `mail`, `phone` | string | Denormalized guest contact, matching domain-model.md's documented Booking fields — kept for display parity even though `customer_id` now exists for the billing relationship. |
| `category_id` | unsigned bigint, FK → `room_categories.id` | Selected at booking/check-in time. Renamed from the legacy's misleadingly-named `room_id` (see spec.md's Schema note). |
| `room_id` | unsigned bigint, FK → `rooms.id`, **nullable** | The actual assigned room — null until BL-017/BL-019 assigns one. Renamed from the legacy's `room` column. |
| `adult`, `children` | unsigned integer | |
| `datein`, `dateout` | date | |
| `days_of_stay` | unsigned integer | |
| `status` | unsigned tiny integer, default 0 | 0=booked, 1=check_in, 2=check_out, 3=cancelled (new, CQ-014). |
| `message` | text, nullable | |
| `price` | unsigned integer, default 0 | Set at checkout (DR-010/DR-011). |
| `created_at`/`updated_at` | timestamps | |

No changes to `customers`, `rooms`, or `room_categories` — this change only adds FK columns pointing at them.

## API Changes

New routes (no `/admin` prefix — see Technical Approach):
- `GET /book`, `POST /book` (BL-015, public)
- `GET /bookings/pending` (BL-016 list), `DELETE /bookings/pending/{booking}` (BL-016 cancel), `GET /bookings/pending/{booking}/convert`, `POST /bookings/pending/{booking}/convert` (BL-017)
- `GET /walk-in` (BL-018), `GET /walk-in/create`, `POST /walk-in` (BL-019)
- `GET /stays` (BL-020), `GET /stays/{booking}/checkout`, `POST /stays/{booking}/checkout` (BL-021), `GET /stays/{booking}/edit`, `PUT /stays/{booking}` (BL-022), `GET /stays/{booking}` (BL-023)

## Key Decisions

- **`category_id`/`room_id` naming swap from the legacy's `room_id`/`room`** — see spec.md's Schema note; no decision pins the legacy names, and perpetuating a documented "misleadingly named" column would be pointless in a rebuild.
- **`room_type` varchar dropped** — documented as redundant in domain-model.md itself; the category name is one relationship away via `category_id`.
- **`status=3` (cancelled) added to the existing enum column**, not a separate boolean/soft-delete column — CQ-014 offers both as acceptable ("a 'cancelled' status value (or soft-delete/archive record)"); a 4th enum value is the smaller change and keeps every list's filter logic (`where('status', N)`) uniform.
- **CQ-022 (connection-lifecycle crash) requires no code** — it is a defect in the legacy's manual `mysqli` connection handling that has no equivalent in Eloquent/Laravel's connection pooling. Stated explicitly in spec.md rather than silently omitted, so a reader doesn't wonder where the "fix" is.
- **Room-category match enforced on BL-017's room assignment (FR-10)** — grounded in the Room/RoomCategory relationship itself (assigning a guest who booked and will be billed for a Single Room into a Deluxe room would silently break the price-by-category model DR-010 depends on), not a separate `DR-NNN` citation, following the same "no rule, state the basis explicitly" pattern BL-001/BL-011 used.
- **No FormRequest classes** — same reasoning as every prior controller: validation rule sets are small enough to inline.
- **DR-009's formula ported literally** (`floor(abs(strtotime($dateout) - strtotime($datein)) / 86400)`) via PHP's own `strtotime`/`floor`, since PHP is the rebuild's language too — no translation needed, and no decision changes the formula.

## Risks & Mitigations

- **Risk:** this is the largest change so far (9 items, ~35 files) — higher chance of an inconsistency slipping through. **Mitigation:** every write path is grounded directly in its DR-###/CQ-### citation in spec.md and cross-referenced here; tests are organized per lifecycle stage (4 test files) rather than one monolithic file, so a failure localizes to its stage.
- **Risk:** the composite writes (DR-007, DR-011) must each complete as a unit. **Mitigation:** wrap each in a `DB::transaction()` closure — not required by any decision, but a reasonable, minimal safeguard against a partial write (e.g. booking flipped to checked-out but the room left occupied) that costs one line per handler.
- **Risk:** BL-017's room-category-match constraint (FR-10) is this design's own addition, not a cited rule. **Mitigation:** called out explicitly above and in spec.md rather than presented as if a decision required it.
