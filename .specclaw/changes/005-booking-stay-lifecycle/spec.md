# Spec: Booking & Stay Lifecycle (MOD-004)

**Change:** 005-booking-stay-lifecycle
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements all 9 of MOD-004's functional backlog items (BL-015–BL-023), grounded in domain-model.md's `Booking` entity and rebuild-backlog.md's per-item acceptance bases. No `bookings` table, model, or route exists yet.

**Schema note:** the legacy `booking` table has two confusingly named columns — `room_id` actually stores the **category** id, and `room` stores the actual **room's** id, assigned only at check-in (domain-model.md: "`room_id`... misleadingly named... actually stores the room category id... `room`... the actual specific room's id"). No decision pins these physical names. This rebuild uses `category_id` and `room_id` respectively — the clear names, not the legacy's swapped ones — consistent with how change `003-room-rate-management` used `room_categories` instead of `room_categoricals`.

**Redundant field dropped:** the legacy `room_type` varchar column ("free-text copy of the selected category name... redundant with `room_id`/`room_categoricals.name`" per domain-model.md) is not recreated — the category name is available via the `category_id` relationship, and no decision requires keeping the redundant copy.

**New status value:** `bookings.status` gains a 4th value, `3 = cancelled` (legacy only had `0=booked, 1=check_in, 2=check_out`), implementing **CQ-014**'s decision to preserve cancelled bookings as queryable records instead of hard-deleting them.

## Requirements

### FR Group 1 — BL-015: Public Reservation Intake

- **FR-1:** A public booking form (GET) renders fields: `name`, `mail`, `phone`, `category_id` (select, populated live from `room_categories`), `adult`, `children`, `datein`, `dateout`, `message`.
- **FR-2:** Submitting the form (POST) creates a `Booking` row with `status=0`, a unique `ref_no` in `[0, 999999999]` (**DR-003**, regenerated on collision, same mechanism as `Customer::generateUniqueCustomerId()`; range per **CQ-009**).
- **FR-3 (DR-004/DR-005, CQ-010):** The submission creates/finds a `Customer` exactly like `CustomerController::store()` does (change 002) — generate a unique `customer_id` (range 0-99999999 per CQ-010) only if no existing customer shares the submitted `phone`; the booking's `customer_id` FK (**CQ-011**) is populated from that customer's internal `id` regardless of whether it was just created or found.
- **FR-4 (DR-006 as corrected by CQ-004):** `category_id` must reference a real `room_categories` row — validated via `exists:room_categories,id`, not the legacy's free-text room-type string with a silent Deluxe fallback for anything unrecognized.
- **FR-5 (CQ-012):** `phone` is validated with the same format rule as change 002 (`regex:/^[0-9]{7,15}$/`).
- **FR-6:** The form and its handler carry CSRF protection.

### FR Group 2 — BL-016: Pending Bookings List & Cancellation

- **FR-7:** A list view shows every `status=0` booking.
- **FR-8 (CQ-014):** Cancelling a pending booking sets `status=3` (cancelled) — the row is never deleted, so it remains queryable, unlike the legacy hard delete.

### FR Group 3 — BL-017: Booking → Check-In Conversion

- **FR-9 (DR-007):** Converting a pending booking assigns a specific room: sets `booking.status=1`, `booking.room_id=<chosen room's id>`, and that room's `status=1` (unavailable) — one composite write.
- **FR-10:** Only rooms whose `status=0` (available) and whose `category_id` matches the booking's own `category_id` may be assigned (the legacy has no such category-match constraint explicitly documented as a rule, but assigning a room from the wrong category would silently violate the guest's own selected/paid category — grounded in the Room/RoomCategory relationship itself, not a separate DR-### citation).

### FR Group 4 — BL-018: Walk-In Availability List

- **FR-11:** A list view shows every `status=0` (available) room.

### FR Group 5 — BL-019: Walk-In Check-In

- **FR-12 (DR-008):** Creates a `Booking` row directly with `status=1` (already checked in, skipping the "booked" stage) and sets the chosen room's `status=1`.
- **FR-13:** Same DR-003/DR-004/DR-005/DR-006(-as-corrected-by-CQ-004)/CQ-011/CQ-012 mechanisms as BL-015 (ref-no generation, customer dedup/creation, validated category selection, FK population, phone validation) — independently implemented for this handler, per module-map.md's note that the legacy app duplicates this logic per call site.
- **CQ-022 note:** the legacy connection-lifecycle crash this handler is documented to have does not apply here — Eloquent/Laravel has no manual database-connection lifecycle for this handler to mismanage, so the defect is structurally absent rather than requiring an explicit fix.

### FR Group 6 — BL-020: Check-In/Out List

- **FR-14:** A list view shows every `status=1` (checked-in) and `status=2` (checked-out) booking, with links to checkout (BL-021, status=1 rows only), edit date (BL-022, status=1 rows only), and view detail (BL-023, status=2 rows only).

### FR Group 7 — BL-021: Guest Check-Out & Billing

- **FR-15 (DR-009):** Days of stay is computed as `floor(abs(strtotime($dateout) - strtotime($datein)) / 86400)` — the exact legacy formula, adopted as-is (no decision changes it).
- **FR-16 (DR-010):** Amount due = category `price` × days of stay, displayed to staff.
- **FR-17 (CQ-005):** The submitted `payment` amount is validated **server-side** to be `>=` the computed amount due — the legacy only enforces this via an HTML `min` attribute, trivially bypassable; this item must reject a lower value server-side.
- **FR-18 (DR-011, composite write):** Checkout does three things atomically: (a) `booking.status=2`, `booking.price=<payment>`; (b) increments the customer's `charges` by `<payment>`, keyed by the booking's `customer_id` FK (**CQ-011**), not by phone; (c) frees the room — `rooms.status=0` — matched by the room's **id** (**CQ-023**), not the legacy's room-name text match.

### FR Group 8 — BL-022: Edit In-Progress Stay's Checkout Date

- **FR-19:** Updates a `status=1` booking's `dateout`.
- **FR-20 (CQ-016):** Recomputes and persists `days_of_stay` from the new `dateout` (and current `datein`) — the legacy writes `dateout` alone and leaves `days_of_stay` stale.

### FR Group 9 — BL-023: View Completed Stay Detail

- **FR-21:** A read-only view of a `status=2` booking's `room`, category, price, `ref_no`, guest name/phone, dates, days, and total amount.

### FR Group 10 — Cross-cutting

- **FR-22:** Every write route (BL-015 store, BL-016 cancel, BL-017 convert, BL-019 store, BL-021 checkout, BL-022 update) carries CSRF protection.
- **FR-23:** MOD-006's "Book Now" nav link (`resources/views/partials/marketing-nav.blade.php`) is updated from its placeholder plain `/book` string to the real route, now that BL-015 exists.

## Acceptance Criteria

- **AC-1 (FR-1/FR-2):** Submitting a valid booking form creates a `bookings` row with `status=0` and a `ref_no` in `[0, 999999999]`.
- **AC-2 (FR-3):** Submitting with a new phone number creates a `Customer` row and links it via `customer_id`; submitting with an existing customer's phone links to that existing customer without creating a duplicate.
- **AC-3 (FR-4):** Submitting with a `category_id` that doesn't exist in `room_categories` is rejected with a validation error.
- **AC-4 (FR-7/FR-8):** The pending list shows only `status=0` bookings; cancelling one sets its `status` to `3` and it disappears from the pending list, but the row still exists in the database (not deleted).
- **AC-5 (FR-9/FR-10):** Converting a pending booking with an available same-category room sets `status=1`, `room_id=<that room>`, and that room's `status=1`; attempting to assign a room from a different category or an unavailable room is rejected.
- **AC-6 (FR-11):** The walk-in list shows only `status=0` rooms.
- **AC-7 (FR-12/FR-13):** Walk-in check-in creates a `bookings` row with `status=1` directly (no `status=0` stage), a unique `ref_no`, correct customer dedup/creation, a validated `category_id`, and sets the chosen room's `status=1`.
- **AC-8 (FR-14):** The check-in/out list shows `status=1` and `status=2` bookings and no others (not `status=0` or `status=3`).
- **AC-9 (FR-15/FR-16):** Checkout computes days-of-stay via the exact legacy formula and displays `price × days` as the amount due.
- **AC-10 (FR-17):** Submitting a `payment` below the computed amount due is rejected server-side (not just relying on client-side `min`); a payment at or above the amount due succeeds.
- **AC-11 (FR-18):** A successful checkout sets `booking.status=2` and `booking.price` to the payment, increments the linked customer's `charges` by that payment, and sets the assigned room's `status=0` — all three, verified independently.
- **AC-12 (FR-19/FR-20):** Editing a stay's `dateout` updates both `dateout` and a freshly recomputed `days_of_stay` (not the old value).
- **AC-13 (FR-21):** The completed-stay view renders `ref_no`, guest name/phone, dates, days, and price for a `status=2` booking.
- **AC-14 (FR-22):** POSTing to any of the six write routes without a valid CSRF token is rejected with HTTP 419, before any handler logic runs.
- **AC-15 (FR-23):** `marketing-nav.blade.php`'s "Book Now" link resolves to a real route (not a 404), and following it reaches the booking form.

## Edge Cases

- **`ref_no`/`customer_id` collision on first draw:** the generation loops must retry, not fail — same pattern already proven in `Customer::generateUniqueCustomerId()` and its tests (change 002).
- **No available room in the requested category (BL-017):** rejected with a validation error (AC-5) rather than silently assigning a wrong-category or unavailable room.
- **Cancelled (`status=3`) bookings:** excluded from every list in this change (pending, check-in/out) — they exist only as a queryable historical record, per CQ-014's intent.

## Dependencies

- **BL-001 (CSRF), BL-002 (Room), BL-005 (RoomCategory), BL-007 (Customer):** all built and verified. Every cross-module dependency resolves `ok-built` per `bypass-check`.
- **Internal same-module order** (this change's own build order, not a cross-change wait): BL-015 before BL-016 before BL-017; BL-018 before BL-019; BL-017 and BL-019 before BL-020; BL-020 before BL-021 and BL-022; BL-021 before BL-023.

## Notes

Several `GM-NNN` fixtures cited per-item in rebuild-backlog.md pin **pre-fix legacy defect behavior** and must not be treated as parity targets for this change: GM-014 (DR-006's silent-Deluxe defect, replaced by CQ-004), GM-015/GM-017 (the CQ-022 connection-crash, moot here), GM-023 (the CQ-005 unvalidated-payment gap), GM-025 (the CQ-023 name-match defect). No golden-master replay is performed as part of this change's own verification (same approach as every prior change) — acceptance rests on tests written directly against the documented rules and decisions above.
