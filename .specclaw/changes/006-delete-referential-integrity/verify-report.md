# Verification Report: 006-delete-referential-integrity

**Verified:** 2026-09-14
**Model:** claude-sonnet-5
**Verdict:** PASS

## History

A first verify pass returned **PARTIAL**: all 7 acceptance criteria passed on their own tests, but the agent found a real, separate safety gap — this change's own FK relax (making `bookings.customer_id` nullable with `nullOnDelete()`, required for AC-6 to hold) removed an implicit safety net `StayController::checkout()` depended on. Before this change, `customer_id`'s RESTRICT FK made `$booking->customer` unconditionally safe; after the relax, a customer referenced only by a checked-out/cancelled booking can legally be deleted, so a replayed/double-submitted checkout on that now-customer-less booking would null-dereference (HTTP 500) instead of failing gracefully.

Fixed in commit `8303c8f`: both `showCheckout()` and `checkout()` now reject (redirect with a flash error) any booking that isn't `status=checked_in`, before touching any relation. Two regression tests added. A second, independent verify agent re-checked everything from scratch (not trusting the first report) and confirmed the fix is real, correctly placed, and tested.

## Prior Finding Remediation

Closed. Independently confirmed by reading the diff (`git show 8303c8f`) and the current controller source:

- `StayController::showCheckout()` begins with the status guard, before any line touching `$booking->category`, `$booking->customer`, or `$booking->room`.
- `StayController::checkout()` has the identical guard before any relation access; `$booking->customer->increment(...)` sits well after the guard.
- `grep -rn '\->customer\b' app/` still returns exactly one file, `StayController.php` — the dereference now sits textually after both guards.
- `test_checkout_on_already_checked_out_booking_with_null_customer_is_rejected_not_crashed` creates a booking with `status => Booking::STATUS_CHECKED_OUT` and `customer_id => null`, POSTs to `stays.checkout`, and asserts a redirect with a session error flash — not a 500 — plus that the booking's status is unchanged. `test_checkout_show_on_non_checked_in_booking_redirects_instead_of_rendering` proves the same for the GET path. Both pass.

## Acceptance Criteria

- ✅ **AC-1 (FR-1):** Deleting a room with no bookings referencing it removes the row — `RoomController::destroy()` deletes unconditionally when no active booking exists; test passes.
- ✅ **AC-2 (FR-2):** Deleting a room referenced by a `status=0`/`status=1` booking is rejected, row survives — test passes.
- ✅ **AC-3 (FR-2):** Deleting a room referenced only by a checked-out/cancelled booking succeeds — the FK-relax migration sets `room_id` to `nullOnDelete()`, matching the app-level check; test passes.
- ✅ **AC-4 (FR-3):** Deleting an unreferenced customer removes the row — test passes.
- ✅ **AC-5 (FR-3):** Deleting a customer referenced by an active booking is rejected, row survives — test passes.
- ✅ **AC-6 (FR-3):** Deleting a customer referenced only by a checked-out/cancelled booking succeeds — `customer_id` relaxed to `nullOnDelete()`; test passes.
- ✅ **AC-7 (FR-5):** CSRF-missing POST (as DELETE) to either route rejected with 419 before handler logic — both room and customer CSRF tests pass, each asserting the row is still present afterward.

## Test Results

```
Tests:    68 passed (528 assertions)
Duration: 18.52s
```
All suites green, including `StayLifecycleTest` with the two new regression tests, `RoomManagementTest` (11 tests), and `CustomerManagementTest` (11 tests).

Lint (`vendor/bin/pint --test`): flags only `bootstrap/providers.php`, `config/auth.php`, `config/logging.php`, `public/index.php`. `git log --oneline --all -- <those 4 files>` shows only the original scaffold commit `e53b6f5` — none of this change's 10 commits touch any of them. Pre-existing, out of scope, not blocking.

## Issues Found

No issues found.

## Summary

**Passed:** 7/7 criteria
**Failed:** 0/7 criteria
**Prior finding closed:** yes
**Verdict:** PASS
