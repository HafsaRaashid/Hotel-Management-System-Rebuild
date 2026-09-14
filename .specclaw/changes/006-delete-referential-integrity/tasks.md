# Tasks: Delete Referential Integrity (BL-004 + BL-008's deferred scope)

**Change:** 006-delete-referential-integrity
**Created:** 2026-09-14
**Total Tasks:** 4

## Summary

Two waves. Wave 1 implements both controllers' delete logic independently (different files, no shared dependency). Wave 2 tests both.

## Tasks

### Wave 1 — Implementation

- [x] `T1` — RoomController::destroy() + view + route (BL-004)
  - Files: `app/Http/Controllers/RoomController.php` (modify), `resources/views/rooms/index.blade.php` (modify), `routes/web.php` (modify)
  - Estimate: medium
  - Kind: impl
  - Depends: none
  - Notes: `destroy(Room $room)`: if `Booking::where('room_id', $room->id)->whereIn('status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])->exists()`, redirect back to `rooms.index` with `->with('error', 'This room is referenced by an active booking and cannot be deleted.')` (CQ-024, AC-2/AC-3); otherwise `$room->delete()` (CQ-021, AC-1) and redirect to `rooms.index`. View: add a `@csrf`/`@method('DELETE')` confirm form per row, same pattern as `customers/index.blade.php`, plus render the flash `error` if present. Route: change `Route::resource('rooms', RoomController::class)->except(['show', 'destroy'])` to `->except(['show'])`.

- [x] `T2` — CustomerController::destroy() referential check (BL-008 deferred scope)
  - Files: `app/Http/Controllers/CustomerController.php` (modify)
  - Estimate: small
  - Kind: impl
  - Depends: none
  - Notes: Add the same check before the existing `$customer->delete()`: if `Booking::where('customer_id', $customer->id)->whereIn('status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])->exists()`, redirect back to `customers.index` with `->with('error', 'This customer is referenced by an active booking and cannot be deleted.')` (AC-5/AC-6); otherwise proceed exactly as today (AC-4). Update the class docblock comment on `destroy()` — it currently states CQ-024 is deferred; that's no longer true.

### Wave 2 — Tests

- [x] `T3` — Room delete feature tests
  - Files: `tests/Feature/RoomManagementTest.php` (modify)
  - Estimate: medium
  - Kind: test
  - Depends: T1
  - Notes: AC-1 (unreferenced room deletes), AC-2 (status=0 or status=1 booking referencing the room blocks delete, room still exists), AC-3 (status=2/status=3 booking referencing the room does NOT block delete), CSRF half of AC-7 for the delete route. Seed `Booking` rows directly (same pattern as `tests/Feature/PendingBookingTest.php`/`StayLifecycleTest.php`) — no need to drive them through the full intake/checkout flow.

- [x] `T4` — Customer delete feature tests
  - Files: `tests/Feature/CustomerManagementTest.php` (modify)
  - Estimate: medium
  - Kind: test
  - Depends: T2
  - Notes: AC-4 (regression: unreferenced customer still deletes), AC-5 (active-booking reference blocks delete), AC-6 (checked-out/cancelled-only reference does not block delete). No new CSRF test needed — change 002's `test_destroy_without_csrf_token_is_rejected_before_handler_runs` already covers the customer-delete route and still applies unchanged.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
