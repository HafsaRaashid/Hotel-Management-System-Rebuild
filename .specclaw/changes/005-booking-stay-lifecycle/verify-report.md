# Verification Report: 005-booking-stay-lifecycle

**Verified:** 2026-09-14
**Model:** Claude Sonnet 5
**Verdict:** PASS

## Acceptance Criteria

- ✅ **AC-1 (FR-1/FR-2):** "Submitting a valid booking form creates a `bookings` row with `status=0` and a `ref_no` in `[0, 999999999]`." — `BookingController::store()` creates the row with `'status' => Booking::STATUS_BOOKED` (=0) and `'ref_no' => Booking::generateUniqueRefNo()` (range `random_int(0, 999999999)` in `Booking.php:62`). Test `test_submitting_creates_booking_with_unique_ref_no` asserts `status===0` and ref_no bounds. PASS.

- ✅ **AC-2 (FR-3):** "creates a `Customer` row and links it via `customer_id`... existing customer's phone links... without creating a duplicate." — `BookingController::store()`: `$customer = Customer::where('phone', $validated['phone'])->first(); if (!$customer) { $customer = Customer::create(...) }` then `'customer_id' => $customer->id`. Tests `test_new_phone_creates_a_customer_and_links_it` and `test_existing_phone_reuses_customer_without_duplicating` both pass.

- ✅ **AC-3 (FR-4):** "Submitting with a `category_id` that doesn't exist in `room_categories` is rejected." — Validation rule `'category_id' => ['required', 'integer', 'exists:room_categories,id']` in `BookingController::validated()`. Test `test_nonexistent_category_id_is_rejected` passes.

- ✅ **AC-4 (FR-7/FR-8):** "pending list shows only `status=0`... cancelling sets `status` to `3`... row still exists (not deleted)." — `PendingBookingController::index()` filters `Booking::where('status', Booking::STATUS_BOOKED)`; `destroy()` does `$booking->update(['status' => Booking::STATUS_CANCELLED])`. Grep of `PendingBookingController.php` confirms zero `->delete()` calls. Test `test_cancel_sets_status_to_cancelled_without_deleting` passes.

- ✅ **AC-5 (DR-007) — verified as three independent facts:**
  - status=1: `$booking->update(['status' => Booking::STATUS_CHECKED_IN, 'room_id' => $room->id]);` (`PendingBookingController.php:70-73`)
  - room.status=1: `$room->update(['status' => 1]);` (`PendingBookingController.php:75`)
  - Wrong-category/unavailable rejection: `Rule::exists('rooms', 'id')->where('status', 0)->where('category_id', $booking->category_id)` (`PendingBookingController.php:61-64`) — server-side re-validated.
  - Tests `test_convert_assigns_matching_category_available_room`, `test_convert_rejects_wrong_category_room`, `test_convert_rejects_unavailable_room` all pass.

- ✅ **AC-6 (FR-11):** "walk-in list shows only `status=0` rooms." — `WalkInController::available()`: `Room::where('status', 0)->with('category')->get()`. Test `test_available_list_shows_only_status_zero_rooms` passes.

- ✅ **AC-7 (FR-12/FR-13):** "Walk-in check-in creates a `status=1` row directly... unique `ref_no`... customer dedup... validated `category_id`... room's `status=1`." — `WalkInController::store()` creates booking with `'status' => Booking::STATUS_CHECKED_IN` directly, `category_id` sourced from the room, phone-dedup identical to BL-015, and `$room->update(['status' => 1])`. Tests `test_store_creates_checked_in_booking_and_occupies_room` and `test_store_dedups_customer_by_phone` both pass.

- ✅ **AC-8 (FR-14):** "check-in/out list shows `status=1` and `status=2`... and no others." — `StayController::index()`: `Booking::whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])`. Test `test_stay_list_shows_only_checked_in_and_checked_out` explicitly creates status=0 and status=3 bookings and asserts they're absent — passes.

- ✅ **AC-9 (FR-15/FR-16):** "computes days-of-stay via exact legacy formula and displays `price × days`." — `Booking::computeDaysOfStay()`: `(int) floor(abs(strtotime($dateout) - strtotime($datein)) / 86400)` — matches DR-009 verbatim. `StayController::showCheckout()`: `$amountDue = $booking->category->price * $days;`. Tests pass (Single Room price 99 × 2 days = 198, confirmed rendered).

- ✅ **AC-10 (CQ-005):** "payment below computed amount due is rejected server-side... at or above succeeds." — `StayController::checkout()` **recomputes** `$days`/`$amountDue` itself from `$booking` (never from client input) before validating `'payment' => ['required', 'integer', 'min:'.$amountDue]`. Test `test_checkout_rejects_payment_below_amount_due` (100 < 198, rejected) and `test_checkout_succeeds_and_has_all_three_effects` (198, succeeds) both pass.

- ✅ **AC-11 (DR-011) — verified as three independent facts:**
  - `booking.status=2` + price: `$booking->update(['status' => Booking::STATUS_CHECKED_OUT, 'price' => $validated['payment']]);`
  - customer charges incremented via FK: `$booking->customer->increment('charges', $validated['payment']);` — id-based via `customer_id`, not phone.
  - room freed via id: `$booking->room->update(['status' => 0]);` — id-based via `room_id`, not name-text matching.
  - Grep confirms zero occurrences of `where('phone'` or `where('room',` in `StayController.php`. Test `test_checkout_succeeds_and_has_all_three_effects` independently asserts all three effects. Passes.

- ✅ **AC-12 (CQ-016):** "updates both `dateout` and a freshly recomputed `days_of_stay`." — `StayController::updateDate()`: both fields written in the same `update()` call, `days_of_stay` freshly computed from the *new* `dateout`. Test `test_edit_date_recomputes_and_persists_days_of_stay` (dateout 2026-10-03→2026-10-06, days 2→5) passes, proving it isn't stale.

- ✅ **AC-13 (FR-21):** "renders `ref_no`, guest name/phone, dates, days, price for a `status=2` booking." — `stays/show.blade.php` renders all named fields. Test `test_view_completed_stay_renders_expected_fields` passes.

- ✅ **AC-14 (FR-22):** "POSTing to any of the six write routes without valid CSRF token is rejected with HTTP 419." — All six write routes (booking.store, bookings.pending.destroy, bookings.pending.convert, walk-in.store, stays.checkout, stays.update) have a passing `assertStatus(419)` test in production-env mode — 6/6 covered.

- ✅ **AC-15 (FR-23):** "'Book Now' link resolves to a real route... reaches the booking form." — `marketing-nav.blade.php`: `<a class="nav-link" href="{{ route('booking.create') }}">Book Now</a>` (no longer a plain `/book` string). Test `test_book_now_link_resolves_to_the_booking_form` passes.

## Schema-Naming Confirmation (design.md)

- Migration uses `category_id` (FK → `room_categories`) and `room_id` (nullable FK → `rooms`) — not the legacy's swapped `room_id`/`room` naming.
- `Booking.php` relations match: `category()` → `belongsTo(RoomCategory::class, 'category_id')`, `room()` → `belongsTo(Room::class, 'room_id')`.
- No `room_type` varchar column exists — grep confirms the deliberate drop documented in design.md.
- `status=3` cancelled value present as `Booking::STATUS_CANCELLED` and used by `PendingBookingController::destroy()`.

## Test Results

Full suite re-run independently:
```
PASS  Tests\Feature\BookingIntakeTest (5 tests)
PASS  Tests\Feature\PendingBookingTest (7 tests)
PASS  Tests\Feature\WalkInTest (4 tests)
PASS  Tests\Feature\StayLifecycleTest (9 tests)
PASS  Tests\Feature\MarketingSiteTest ... ✓ book now link resolves to the booking form
Tests: 61 passed (506 assertions)
Duration: 24.56s
```

Lint (`php vendor/bin/pint --test`) re-run independently: flags only `bootstrap/providers.php`, `config/auth.php`, `config/logging.php`, `public/index.php`. `git log --oneline --all -- <those 4 files>` confirms only `e53b6f5` (the pre-existing scaffold-tracking commit) touches them — none of change 005's 17 commits do. Consistent with precedent from changes 001-004.

## Issues Found

No issues found.

## Summary

**Passed:** 15/15 criteria
**Failed:** 0/15 criteria
**Verdict:** PASS
