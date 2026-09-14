# Spec: Admin Dashboard & Reporting (MOD-007, BL-024)

**Change:** 008-dashboard-reporting
**Status:** 🟡 Draft

## Functional Requirements

- **FR-1:** A dashboard route, reachable only by an authenticated session (inside the existing `auth` middleware group, BL-011), renders nine live counts/sums matching legacy `admin/home.php`'s nine `admin/counters/*.php` fragments:
  1. Total Bookings — `Booking::count()`
  2. Checked In — `Booking::where('status', Booking::STATUS_CHECKED_IN)->count()`
  3. Checked Out — `Booking::where('status', Booking::STATUS_CHECKED_OUT)->count()`
  4. Total Payment — `Booking::where('status', Booking::STATUS_CHECKED_OUT)->sum('price')`
  5. Available Rooms — `Room::where('status', 0)->count()`
  6. Total Rooms — `Room::count()`
  7. Total Room Categories — `RoomCategory::count()`
  8. Total Customers — `Customer::count()`
  9. Total Users — `User::count()`
- **FR-2:** The dashboard is entirely read-only — no form input, no state-changing action, matching functional-spec.md's "No form input" note on `admin/home.php`.
- **FR-3:** `AuthController::login()`'s success redirect changes from `route('customers.index')` to the new dashboard route, so a fresh login lands on the operational overview first (matching the legacy's login → `index.php` → `home.php` default landing).
- **FR-4:** `admin-nav.blade.php` gains a "Dashboard" link as the first nav item, so every already-built admin screen can navigate back to it.

## Non-Functional Requirements

- **NFR-1:** No new tables, migrations, or writes — every counter is a read against a table an earlier change already created (`bookings`, `rooms`, `room_categories`, `customers`, `users`).
- **NFR-2:** No UI fidelity work — SQ-013 (THEME-ONLY) required artifacts (`ui/ui-inventory.md`, `design-tokens.json`, etc.) remain absent for every item in the backlog; this change is functional-only, matching every prior change's own NFR on this point.

## Dependency Note (not a bypass)

BL-024's declared dependency on BL-012 (User List, MOD-002) is a module-level abstraction, not a real requirement of this item's own acceptance basis. domain-model.md's "Total Users" counter is `COUNT(*)` over the `users` table — data BL-012's own scope (rendering a list *screen*) is not, and that table/model already exist (`app/Models/User.php`, `database/migrations/2026_09_14_000006_create_users_table.php`, change `007-admin-authentication`). Nothing here is faked or stubbed: `User::count()` reads real persisted rows through the real model. `BL-012` itself remains unbuilt and is not implemented by this change.

## Acceptance Criteria

- **AC-1:** Visiting the dashboard route while unauthenticated redirects to `/login` (inherits BL-011's `auth` middleware — regression coverage, not new behavior).
- **AC-2:** Visiting the dashboard route while authenticated renders 200 and displays all nine counter labels and values.
- **AC-3:** Total Bookings equals the count of all rows in `bookings` regardless of status.
- **AC-4:** Checked In / Checked Out counts exactly match bookings with `status = Booking::STATUS_CHECKED_IN` / `STATUS_CHECKED_OUT` respectively — a booking in `STATUS_BOOKED` or `STATUS_CANCELLED` is excluded from both.
- **AC-5:** Total Payment equals the sum of `price` over checked-out bookings only (bookings still pending or checked-in have no finalized `price` yet, per change 005's `StayController::checkout()`).
- **AC-6:** Available Rooms counts only rooms with `status = 0`; Total Rooms counts every room regardless of status.
- **AC-7:** Total Room Categories, Total Customers, and Total Users each equal a plain `count()` over their respective table.
- **AC-8:** After a successful login, the response redirects to the dashboard route, not `customers.index`.
- **AC-9:** `admin-nav.blade.php`'s rendered output contains a link to the dashboard route on every existing admin page (regression: assert on an already-built screen, e.g. `customers.index`).
- **AC-10:** Zero writes occur anywhere in the request — a `DatabaseTransactions`-style before/after row-count assertion across all five read tables shows no change.

## Edge Cases

- **No bookings/rooms/customers/categories/users yet (fresh install):** every counter renders `0`, not an error — `count()`/`sum()` over an empty relation return `0`/`null`; `sum()` must be coerced to `0` when `null` so the view doesn't render a blank cell.
- **A pending/checked-in booking's `price`** (the `bookings.price` column defaults to `0`, is never nullable, and is only ever written by `StayController::checkout()`): must be excluded from the Total Payment sum by the `status = STATUS_CHECKED_OUT` filter in AC-5 regardless of its value — proven by forcing a non-zero `price` on a not-yet-checked-out booking in the test and asserting it is not summed.
