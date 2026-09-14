# Tasks: Booking & Stay Lifecycle (MOD-004)

**Change:** 005-booking-stay-lifecycle
**Created:** 2026-09-14
**Total Tasks:** 16

## Summary

Nine waves, following the module's own dependency chain: data layer → intake (BL-015) + walk-in availability (BL-018) → pending list (BL-016) + walk-in check-in (BL-019) + nav update → conversion (BL-017) + intake tests → check-in/out list (BL-020) + walk-in tests → checkout (BL-021) + edit-date (BL-022) + pending/conversion tests → view detail (BL-023) → stay-lifecycle tests. Several tasks modify the same controller file as an earlier task in a later wave (e.g. `PendingBookingController`, `StayController`) — that's expected; each adds one more method.

## Tasks

### Wave 1 — Data layer

- [x] `T1` — Bookings migration
  - Files: `database/migrations/2026_09_14_000004_create_bookings_table.php` (create)
  - Estimate: medium
  - Kind: migration
  - Depends: none
  - Notes: Columns per design.md's Data Model Changes: `ref_no` (unsigned integer, unique), `customer_id` (`foreignId()->constrained('customers')`), `name`/`mail`/`phone` (string), `category_id` (`foreignId()->constrained('room_categories')`), `room_id` (`foreignId()->nullable()->constrained('rooms')`), `adult`/`children` (unsigned integer), `datein`/`dateout` (date), `days_of_stay` (unsigned integer), `status` (unsigned tiny integer, default 0), `message` (text, nullable), `price` (unsigned integer, default 0), timestamps.

### Wave 2 — Model

- [x] `T2` — Booking model
  - Files: `app/Models/Booking.php` (create)
  - Estimate: small
  - Kind: impl
  - Depends: T1
  - Notes: `$fillable` for every column except `id`/timestamps. `belongsTo(Customer::class)`, `belongsTo(RoomCategory::class, 'category_id')`, `belongsTo(Room::class, 'room_id')`. Constants `STATUS_BOOKED=0`, `STATUS_CHECKED_IN=1`, `STATUS_CHECKED_OUT=2`, `STATUS_CANCELLED=3`. Static `generateUniqueRefNo(): int` — `do { $candidate = random_int(0, 999999999); } while (static::where('ref_no', $candidate)->exists());` (DR-003/CQ-009, mirrors `Customer::generateUniqueCustomerId()`).

### Wave 3 — BL-015 (intake) + BL-018 (walk-in availability), independent of each other

- [x] `T3` — BookingController (create/store) + view + route (BL-015)
  - Files: `app/Http/Controllers/BookingController.php` (create), `resources/views/booking/create.blade.php` (create), `routes/web.php` (modify — add `GET/POST /book`)
  - Estimate: large
  - Kind: impl
  - Depends: T2
  - Notes: `create()` passes `RoomCategory::all()` for the select. `store()`: validate `name`/`mail` (string/email), `phone` (regex, CQ-012), `category_id` (`exists:room_categories,id`, FR-4/CQ-004), `adult`/`children` (integer), `datein`/`dateout` (date), `message` (nullable string). Inside a `DB::transaction()`: find-or-create the `Customer` by phone (reuse `CustomerController::store()`'s dedup pattern — `Customer::where('phone', ...)->first()` or `Customer::create([..., 'customer_id' => Customer::generateUniqueCustomerId()])`), then `Booking::create([..., 'ref_no' => Booking::generateUniqueRefNo(), 'customer_id' => $customer->id, 'status' => Booking::STATUS_BOOKED])`. `@csrf` on the form.

- [x] `T4` — WalkInController available() + view + route (BL-018)
  - Files: `app/Http/Controllers/WalkInController.php` (create), `resources/views/walk-in/available.blade.php` (create), `routes/web.php` (modify — add `GET /walk-in`)
  - Estimate: small
  - Kind: impl
  - Depends: T2
  - Notes: `available()` — `Room::where('status', 0)->with('category')->get()`, rendered as a list (reuse the rendering pattern from `RoomController::index()`, change 003).

### Wave 4 — BL-016 (pending list/cancel), BL-019 (walk-in check-in), nav update

- [x] `T5` — PendingBookingController index()/destroy() + view + route (BL-016)
  - Files: `app/Http/Controllers/PendingBookingController.php` (create), `resources/views/bookings/pending.blade.php` (create), `routes/web.php` (modify — add `GET /bookings/pending`, `DELETE /bookings/pending/{booking}`)
  - Estimate: medium
  - Kind: impl
  - Depends: T3
  - Notes: `index()` — `Booking::where('status', Booking::STATUS_BOOKED)->get()`. `destroy(Booking $booking)` — `$booking->update(['status' => Booking::STATUS_CANCELLED]);` (CQ-014 — never `delete()`). `@csrf` on the cancel form.

- [x] `T6` — WalkInController store() + view + route (BL-019)
  - Files: `app/Http/Controllers/WalkInController.php` (modify — add `store()`), `resources/views/walk-in/create.blade.php` (create), `routes/web.php` (modify — add `GET /walk-in/create`, `POST /walk-in`)
  - Estimate: large
  - Kind: impl
  - Depends: T4
  - Notes: Same validation/dedup/ref-no pattern as `BookingController::store()` (T3), but additionally requires a specific available `room_id` (of the matching category) at submission time, and creates the `Booking` with `status=STATUS_CHECKED_IN` directly (DR-008) and sets that room's `status=1` — inside one `DB::transaction()`. No CQ-022 handling needed (design.md's Key Decisions — moot in Eloquent).

- [x] `T12` — Update marketing nav's Book Now link (FR-23)
  - Files: `resources/views/partials/marketing-nav.blade.php` (modify)
  - Estimate: small
  - Kind: impl
  - Depends: T3
  - Notes: Replace `href="/book"` with `href="{{ route('booking.create') }}"`, now that T3 registers that route.

### Wave 5 — BL-017 (conversion), intake tests

- [x] `T7` — PendingBookingController convert() + view + route (BL-017)
  - Files: `app/Http/Controllers/PendingBookingController.php` (modify — add `showConvert()`/`convert()`), `resources/views/bookings/convert.blade.php` (create), `routes/web.php` (modify — add `GET/POST /bookings/pending/{booking}/convert`)
  - Estimate: medium
  - Kind: impl
  - Depends: T5
  - Notes: `showConvert(Booking $booking)` lists `Room::where('status', 0)->where('category_id', $booking->category_id)->get()` (FR-10 — same-category constraint). `convert(Request $request, Booking $booking)` validates the chosen `room_id` is `exists:rooms,id` AND still available+same-category (re-check server-side, not just what the GET listed) — inside a `DB::transaction()`: `$booking->update(['status' => Booking::STATUS_CHECKED_IN, 'room_id' => $room->id]); $room->update(['status' => 1]);` (DR-007).

- [x] `T13` — Booking intake feature tests (BL-015)
  - Files: `tests/Feature/BookingIntakeTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T3
  - Notes: AC-1, AC-2 (new-phone creates customer; existing-phone reuses it, no duplicate), AC-3 (bad `category_id` rejected), CSRF half of AC-14 for `/book` POST — same `$this->app['env'] = 'production'` pattern as every prior CSRF test.

### Wave 6 — BL-020 (check-in/out list), walk-in tests

- [x] `T8` — StayController index() + view + route (BL-020)
  - Files: `app/Http/Controllers/StayController.php` (create), `resources/views/stays/index.blade.php` (create), `routes/web.php` (modify — add `GET /stays`)
  - Estimate: medium
  - Kind: impl
  - Depends: T6, T7
  - Notes: `index()` — `Booking::whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])->get()`. View links: checkout/edit for `status=1` rows, view for `status=2` rows.

- [x] `T15` — Walk-in feature tests (BL-018/BL-019)
  - Files: `tests/Feature/WalkInTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T4, T6
  - Notes: AC-6 (available list filters status=0), AC-7 (walk-in creates status=1 booking directly, unique ref_no, customer dedup, room status flips to 1), CSRF half of AC-14 for `/walk-in` POST.

### Wave 7 — BL-021 (checkout), BL-022 (edit date), pending/conversion tests

- [x] `T9` — StayController checkout() + view + route (BL-021)
  - Files: `app/Http/Controllers/StayController.php` (modify — add `showCheckout()`/`checkout()`), `resources/views/stays/checkout.blade.php` (create), `routes/web.php` (modify — add `GET/POST /stays/{booking}/checkout`)
  - Estimate: large
  - Kind: impl
  - Depends: T8
  - Notes: `showCheckout(Booking $booking)` computes `days = floor(abs(strtotime($booking->dateout) - strtotime($booking->datein)) / 86400)` (DR-009) and `amountDue = $booking->category->price * $days` (DR-010), passes both to the view. `checkout(Request $request, Booking $booking)` re-derives the same `$days`/`$amountDue` server-side (never trusts a client-submitted amount) and validates `payment` (`required`, `integer`, `min:' . $amountDue` — CQ-005). Inside a `DB::transaction()`: `$booking->update(['status' => Booking::STATUS_CHECKED_OUT, 'price' => $payment]); $booking->customer->increment('charges', $payment); $booking->room->update(['status' => 0]);` (DR-011, CQ-011 via the `customer` relation, CQ-023 via the `room` relation — both already id-based, not name-text).

- [x] `T10` — StayController updateDate() + view + route (BL-022)
  - Files: `app/Http/Controllers/StayController.php` (modify — add `edit()`/`updateDate()`), `resources/views/stays/edit.blade.php` (create), `routes/web.php` (modify — add `GET /stays/{booking}/edit`, `PUT /stays/{booking}`)
  - Estimate: medium
  - Kind: impl
  - Depends: T8
  - Notes: `updateDate(Request $request, Booking $booking)` validates the new `dateout` (`required`, `date`, `after_or_equal:` the booking's own `datein`), recomputes `days_of_stay` with the same DR-009 formula using the new `dateout`, and persists both in one `update()` call (CQ-016 — the legacy writes `dateout` alone and leaves `days_of_stay` stale; this must not repeat that).

- [x] `T14` — Pending/conversion feature tests (BL-016/BL-017)
  - Files: `tests/Feature/PendingBookingTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T5, T7
  - Notes: AC-4 (pending list filters status=0; cancel sets status=3, row still exists), AC-5 (conversion assigns matching-category available room, sets status=1 + room status=1; wrong-category or unavailable room rejected), CSRF halves of AC-14 for cancel and convert POSTs.

### Wave 8 — BL-023 (view detail)

- [x] `T11` — StayController show() + view + route (BL-023)
  - Files: `app/Http/Controllers/StayController.php` (modify — add `show()`), `resources/views/stays/show.blade.php` (create), `routes/web.php` (modify — add `GET /stays/{booking}`)
  - Estimate: small
  - Kind: impl
  - Depends: T9
  - Notes: Read-only: room, category, price, `ref_no`, guest name/phone, dates, days, amount — all already on the `Booking` row or its relations by the time a booking reaches `status=2`.

### Wave 9 — Stay-lifecycle tests

- [x] `T16` — Stay lifecycle feature tests (BL-020/BL-021/BL-022/BL-023)
  - Files: `tests/Feature/StayLifecycleTest.php` (create)
  - Estimate: large
  - Kind: test
  - Depends: T8, T9, T10, T11
  - Notes: AC-8 (list shows only status 1/2), AC-9/AC-10/AC-11 (checkout: days/amount computed correctly, payment-below-due rejected server-side, successful checkout's three effects verified independently — booking status+price, customer charges incremented, room freed), AC-12 (edit date recomputes+persists days_of_stay), AC-13 (view-detail renders the right fields), CSRF halves of AC-14 for checkout and edit-date. Also AC-15 (nav "Book Now" resolves) belongs in `tests/Feature/MarketingSiteTest.php` (modify, not a new file here) — depends on T12; add it as part of this task's scope since T12 has no test task of its own.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
