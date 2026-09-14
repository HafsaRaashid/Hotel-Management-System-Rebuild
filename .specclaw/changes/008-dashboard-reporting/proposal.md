# Proposal: Admin Dashboard & Reporting (MOD-007, BL-024)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

Legacy `admin/home.php` is the admin landing page: a read-only page of nine live counts/sums (Total Bookings, Available Rooms, Checked In, Checked Out, Total Rooms, Total Room Categories, Total Customers, Total Payment, Total Users), each rendered by its own `admin/counters/*.php` fragment querying its owning module's table directly. Right now login (`AuthController::login()`, change 007) redirects to `customers.index` because no dashboard exists — there is no single at-a-glance operational view.

## Proposed Solution

Add a `DashboardController@index` at `/` (or `/dashboard`) inside the existing `auth` middleware group, aggregating nine live counts/sums directly from the tables each already-built module owns:

- Total Bookings, Checked In, Checked Out — `Booking::status` enum (MOD-004, change 005)
- Total Payment — `SUM(bookings.price)` over checked-out bookings (MOD-004)
- Available Rooms, Total Rooms — `Room::status` enum (MOD-003, change 003)
- Total Room Categories — `RoomCategory::count()` (MOD-003)
- Total Customers — `Customer::count()` (MOD-005, change 002)
- Total Users — `User::count()` (MOD-001, change 007 — see Dependency Note below)

Redirect `AuthController::login()`'s success path from `customers.index` to the new dashboard route, and add a "Dashboard" link to `admin-nav.blade.php` as the first item.

### Dependency note on BL-012

`specclaw-bf-rebuild-collect bypass-check` flags `BL-012` (User List, MOD-002) as an unmet cross-module dependency of `BL-024`. Inspecting what BL-024 actually needs from the User entity (domain-model.md: "Total Users" is a plain `COUNT(*)` over the `users` table), the real technical dependency is the `users` table existing — not BL-012's List *screen*. That table and its `User` model were already built in change `007-admin-authentication` (`database/migrations/2026_09_14_000006_create_users_table.php`, `app/Models/User.php`) to seed the admin login. `BL-012`'s own scope (rendering a `name`/`username`/`type` list with UI fidelity) remains unbuilt and is not part of this change.

This is not a stub or a bypass: the Total Users counter reads real, persisted `users` rows through the real `User` model — nothing is faked, so no `ST-###` registry entry applies. It is the same pattern as BL-009 (public marketing) needing only RoomCategory *data*, not a UI dependency, made explicit here because bypass-check's module-level edge doesn't distinguish "needs the table" from "needs the screen."

## Scope

### In Scope
- `DashboardController@index`, one Blade view rendering all nine counters.
- Route added inside the existing `auth` middleware group.
- Login redirect changed from `customers.index` to the dashboard route.
- "Dashboard" link added to `admin-nav.blade.php` (first position).
- Feature tests asserting each of the nine counters against seeded fixture data (per BL-024's Verification inputs note: no golden-master scenario exists for read-only aggregates, so tests are authored directly).

### Out of Scope
- BL-012/BL-013/BL-014 (User List/Create/Edit/Delete screens) — MOD-002 remains unbuilt; this change reads the `users` table's count only.
- Any UI fidelity/theming work (SQ-013 gate — THEME-ONLY, required artifacts still missing per every item's UI fidelity note).
- Auto-refresh, charts, or date-range filtering — legacy `admin/home.php` is a static-per-page-load read, no such behavior is documented in domain-model.md or functional-spec.md.

## Impact

- **Files affected:** ~5 (estimated) — 1 controller, 1 view, 1 route addition, 1 nav partial edit, 1 test file.
- **Complexity:** small
- **Risk:** low — purely additive, read-only, no writes to any existing table.

## Open Questions

None blocking. UI fidelity gate (SQ-013) is a pre-existing, already-acknowledged gap shared by every item in the backlog, not specific to this change.

---

**To proceed:** Review this proposal and approve to begin planning.
