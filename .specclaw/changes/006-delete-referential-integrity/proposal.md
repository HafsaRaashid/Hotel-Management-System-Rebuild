# Proposal: Delete Referential Integrity (BL-004 + BL-008's deferred scope)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

Two backlog items were left incomplete specifically because they needed the `bookings` table, which didn't exist yet:

- **BL-004 (Room Delete, MOD-003)** was held out of change `003-room-rate-management` entirely — its own acceptance basis requires shipping CQ-021's delete-fix and CQ-024's referential-integrity check *together*, and the check needs `bookings`.
- **BL-008 (Customer Delete, MOD-005)** shipped in change `002-customer-management` with CQ-021's delete-fix only — CQ-024's referential-integrity check was explicitly deferred (documented in that change's spec.md and a status note on BL-008), again because `bookings` didn't exist.

Change `005-booking-stay-lifecycle` created the `bookings` table. Both gaps are now closeable.

## Proposed Solution

- **BL-004 (Room Delete):** implement in full — CQ-021 (delete actually deletes) and CQ-024 (reject deleting a room referenced by an active booking) together, exactly as the item's own acceptance basis requires. "Active" means `status` 0 (booked) or 1 (checked-in) — a `status=2` (checked-out) or `status=3` (cancelled) booking no longer occupies anything.
- **BL-008's deferred scope:** add the same CQ-024 check to `CustomerController::destroy()`, which currently has none (per change 002's explicit Item Split). Same "active booking" definition, checked against the customer's own bookings via the `customer_id` FK.

Both checks query `bookings` directly — CQ-011's FK-based design (not phone-matching) applies to both from day one, since `bookings.customer_id` and `bookings.room_id` already exist.

## Scope

### In Scope
- `RoomController::destroy()` — new method (didn't exist before), CQ-021 + CQ-024.
- `CustomerController::destroy()` — modified, adds CQ-024 only (CQ-021 already works).
- Tests proving: an unreferenced room/customer deletes cleanly; a room/customer referenced by an active (status 0/1) booking is rejected; a room/customer referenced only by a checked-out or cancelled booking still deletes cleanly (CQ-024's own wording — "active" — is not "ever referenced").

### Out of Scope
- Any change to `bookings` itself, or to the booking lifecycle — this change only adds read-only referential checks against it.
- UI fidelity — same as every prior change, `/specclaw:bf-ui` hasn't run.

## Impact

- **Files affected:** ~6 (2 controllers, 1 new view for room delete's confirm action if needed, 2-3 test files).
- **Complexity:** small
- **Risk:** low — both checks are straightforward existence queries against a table whose schema is already fixed and tested.

## Open Questions

None blocking. `bypass-check` is clean for both items (all declared dependencies `ok-built`); the `bookings`-table dependency was real but undeclared in the backlog, same class of gap found and resolved for BL-009/MOD-006 earlier — now resolved by change 005's existence.

---

**To proceed:** Review this proposal and approve to begin planning.
