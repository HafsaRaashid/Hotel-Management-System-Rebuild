# Spec: Delete Referential Integrity (BL-004 + BL-008's deferred scope)

**Change:** 006-delete-referential-integrity
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Closes two gaps that both needed the `bookings` table (change `005-booking-stay-lifecycle`), which now exists:

- **BL-004 (Room Delete):** held out of change 003 entirely — implemented here in full.
- **BL-008's deferred scope:** `CustomerController::destroy()` already deletes (CQ-021, shipped in change 002) but has no referential-integrity check — added here.

**"Active" booking, per CQ-024's own wording** ("not checked-out/cancelled"): `status` 0 (booked) or 1 (checked-in). A `status=2` (checked-out) or `status=3` (cancelled) booking does not block a delete.

## Requirements

### Functional Requirements

- **FR-1 (BL-004, CQ-021):** `DELETE /rooms/{room}` actually deletes the row.
- **FR-2 (BL-004, CQ-024):** Deleting a room referenced by an active booking (`bookings.room_id = room.id` AND `status IN (0, 1)`) is rejected, not silently permitted.
- **FR-3 (BL-008 deferred, CQ-024):** Deleting a customer referenced by an active booking (`bookings.customer_id = customer.id` AND `status IN (0, 1)`) is rejected.
- **FR-4:** Both checks query `bookings` via the id-based FKs (`room_id`, `customer_id`) that already exist — no phone/name-text matching, consistent with CQ-011/CQ-023's id-based design used throughout change 005.
- **FR-5:** Both delete routes remain CSRF-protected.

## Acceptance Criteria

- **AC-1 (FR-1):** Deleting a room with no bookings referencing it removes the row.
- **AC-2 (FR-2):** Deleting a room referenced by a `status=0` or `status=1` booking is rejected; the room row still exists afterward.
- **AC-3 (FR-2, CQ-024's own scope):** Deleting a room referenced only by a `status=2` (checked-out) or `status=3` (cancelled) booking still succeeds — CQ-024 says "active," not "ever referenced."
- **AC-4 (FR-3):** Deleting a customer with no active-booking reference removes the row (already true — regression check for CQ-021).
- **AC-5 (FR-3):** Deleting a customer referenced by a `status=0` or `status=1` booking is rejected; the customer row still exists afterward.
- **AC-6 (FR-3, symmetric to AC-3):** Deleting a customer referenced only by a checked-out or cancelled booking still succeeds.
- **AC-7 (FR-5):** POSTing (as `DELETE`) to either route without a valid CSRF token is rejected with HTTP 419, before any handler logic runs.

## Edge Cases

- **A room/customer referenced by multiple bookings, some active and some not:** rejected if *any* qualifying booking is active — `exists()` on the filtered query, not a count comparison.

## Dependencies

- **BL-001 (CSRF), BL-003 (Room Create/Edit), BL-007 (Customer Create/Edit):** built and verified.
- **`bookings` table (change `005-booking-stay-lifecycle`):** built and verified — the real, previously-undeclared dependency both items were waiting on.

## Notes

`AC-7`'s CSRF proof for the room delete route is new coverage (BL-004 never shipped before); the customer delete route's CSRF proof already exists in `tests/Feature/CustomerManagementTest.php` from change 002 and is not repeated here.

**Mid-build finding (documented, not silently patched):** change 005's `bookings` migration left `room_id`/`customer_id`'s foreign keys at their default RESTRICT-on-delete. That is *stricter* than CQ-024 — it blocks deleting a room/customer referenced by *any* booking at all, including a checked-out/cancelled one, contradicting AC-3/AC-6 as originally written. Fixed via a new migration (not editing change 005's already-merged one) relaxing both FKs to `nullOnDelete()` — `room_id` was already nullable; `customer_id` is made nullable too. AC-3 and AC-6 now hold exactly as originally specified.

**Post-verify finding, fixed in the same change:** the first verify pass (PARTIAL) correctly identified that the FK relax above removed an implicit safety net — `StayController::checkout()` dereferences `$booking->customer` with no guard that the booking is still `status=checked_in`. Before this change, `customer_id`'s RESTRICT FK made that dereference unconditionally safe forever; after the relax, a customer referenced only by a checked-out/cancelled booking can legally be deleted (AC-6), so a *replayed* checkout on that now-customer-less booking would null-dereference (HTTP 500) instead of failing gracefully. Fixed: both `showCheckout()` and `checkout()` now reject (redirect with a flash error) any booking that isn't `status=checked_in`, before touching any relation. Regression-tested directly (a checked-out booking with `customer_id=null` posted to checkout is rejected, not crashed).
