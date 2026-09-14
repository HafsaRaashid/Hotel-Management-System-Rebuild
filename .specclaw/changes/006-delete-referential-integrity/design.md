# Design: Delete Referential Integrity (BL-004 + BL-008's deferred scope)

**Change:** 006-delete-referential-integrity
**Created:** 2026-09-14

## Technical Approach

Both deletes follow the same shape: check `Booking::where(<fk>, $model->id)->whereIn('status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])->exists()` before deleting; if true, redirect back with a flash error (same pattern `CustomerController::store()` already uses for the duplicate-phone case) instead of deleting.

## Architecture

```
RoomController::destroy()      ──checks──> Booking (room_id = room.id, status in [0,1])
CustomerController::destroy()  ──checks──> Booking (customer_id = customer.id, status in [0,1])
```

No new files beyond tests and the two controller methods/edits, plus a delete control added to `rooms/index.blade.php` (which currently has none, since BL-004 never existed until now).

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/RoomController.php` | modify | Add `destroy(Room $room)`. |
| `app/Http/Controllers/CustomerController.php` | modify | Add the CQ-024 check to the existing `destroy(Customer $customer)`. |
| `resources/views/rooms/index.blade.php` | modify | Add a delete confirm form per row (same pattern as `customers/index.blade.php`), since BL-004 didn't exist when this view was built. |
| `routes/web.php` | modify | `Route::resource('rooms', RoomController::class)->except(['show'])` — drop `'destroy'` from the exclusion list now that it exists. |
| `tests/Feature/RoomManagementTest.php` | modify | AC-1, AC-2, AC-3, and the room-delete half of AC-7. |
| `tests/Feature/CustomerManagementTest.php` | modify | AC-4, AC-5, AC-6 (AC-7's customer half already covered by change 002's existing CSRF test — not repeated). |

## Data Model Changes

None — both checks query the existing `bookings` table.

## API Changes

`DELETE /rooms/{room}` — new (BL-004). `DELETE /customers/{customer}` — unchanged route, modified behavior.

## Key Decisions

- **"Active" = `status` 0 or 1**, grounded directly in CQ-024's own wording ("not checked-out/cancelled"), not a separate interpretation.
- **`exists()`, not a count comparison** — the check only needs a yes/no answer; counting would be wasted work and an easy place for an off-by-one mistake to hide.
- **Rejection via redirect+flash, not an exception** — matches the existing `CustomerController::store()` duplicate-phone pattern already in this codebase, rather than introducing a new error-handling shape for one more case.
- **`RoomController::destroy()` is new, not a modification of an existing no-op** — unlike `CustomerController::destroy()` (which already deleted, per CQ-021, and only needs the CQ-024 check added), BL-004 never shipped at all, so this is a full new method plus the missing delete UI control and route.

## Risks & Mitigations

- **Risk:** the room-delete UI control never existed before (BL-004 was held out) — adding it now to `rooms/index.blade.php` is this change's only view edit. **Mitigation:** copies the exact same confirm-form pattern already proven in `customers/index.blade.php`, not a new design.
- **Risk (found mid-build, fixed, documented in spec.md's Notes):** `bookings.room_id`/`customer_id`'s default RESTRICT foreign keys (from change 005) blocked deleting a room/customer referenced by *any* booking, not just active ones — stricter than CQ-024. **Mitigation:** `database/migrations/2026_09_14_000005_null_bookings_room_and_customer_on_delete.php` relaxes both to `nullOnDelete()`. `customer_id` becoming nullable is safe because `StayController::checkout()` — the only place `$booking->customer` is dereferenced — only runs on an active booking, and this change's own check guarantees an active booking's customer was never deleted.
