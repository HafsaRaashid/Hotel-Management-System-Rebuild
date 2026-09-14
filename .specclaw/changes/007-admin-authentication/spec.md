# Spec: Admin Authentication & Access Control (MOD-001)

**Change:** 007-admin-authentication
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements BL-010 (Admin Login & Logout) and BL-011 (Front Controller & Session Gate). Creates the `users` table and `User` model — `config/auth.php`'s own comment already anticipated this, noting the provider model was "deliberately not yet backed by a model file... created by a future backlog item as part of building the actual domain entity."

## Requirements

### FR Group 1 — BL-010: Login/Logout

- **FR-1 (DR-001):** Submitting the login form with an empty `username` or empty `password` is rejected with a validation error before any credential query runs.
- **FR-2 (DR-002, CQ-002):** A valid `username`+`password` pair is checked via `Hash::check()` against the stored (hashed) password — never plaintext comparison.
- **FR-3 (CQ-019):** `username` is `UNIQUE` at the schema level (case-insensitive collation, matching the legacy's own `utf8mb4_vietnamese_ci`) — the legacy's "exactly one row" ambiguous-match check becomes structurally unreachable and is not reimplemented.
- **FR-4 (CQ-020):** Login additionally re-checks the submitted `username` against the matched user's stored `username` with an exact-case (`===`) comparison, mirroring the legacy's own recheck — since the DB-level lookup is case-insensitive (FR-3's collation) but login itself must not be.
- **FR-5:** On success, the user is logged in via Laravel's session guard (`Auth::login()`); on failure (empty fields, no match, wrong case, wrong password), a single generic rejection message is shown — not different messages for "user not found" vs "wrong password" (the legacy itself gives one generic message: "Incorrect username or password").
- **FR-6:** Logout ends the session and redirects to the login form.
- **FR-7:** The login form and logout action carry CSRF protection.

### FR Group 2 — BL-011: Front Controller & Session Gate

- **FR-8 (session guard):** Every existing admin route (customers, rooms, room-categories, bookings/pending, walk-in, stays) requires an authenticated session — an unauthenticated request redirects to the login form.
- **FR-9 (CQ-001, satisfied structurally, not by new code):** the legacy's unwhitelisted dynamic `include $page . '.php'` pattern has no equivalent anywhere in this rebuild — Laravel's router is itself an explicit, compiled whitelist of named routes. No additional whitelist mechanism is built; this FR exists to record that CQ-001 is closed, not to add code.
- **FR-10 (shared nav):** every admin screen renders a shared nav linking to every other admin screen that currently exists (Customers, Rooms, Room Categories, Pending Bookings, Walk-In, Stays) plus a logout control and the logged-in user's name.

## Acceptance Criteria

- **AC-1 (FR-1):** Submitting the login form with a blank `username` or blank `password` re-renders the form with a validation error; no query runs (verified indirectly: no session is established).
- **AC-2 (FR-2/FR-3):** Submitting the seeded admin's exact `username`+`password` logs in successfully and reaches an admin page.
- **AC-3 (FR-2):** Submitting the correct `username` with a wrong `password` is rejected.
- **AC-4 (FR-4):** Submitting the correct `username` in the wrong case (e.g. seeded `admin`, submitted `Admin`) with the correct password is rejected, even though the DB lookup alone would match it.
- **AC-5 (FR-6):** After logging in, visiting the logout route ends the session — a subsequent request to any admin route redirects to login again.
- **AC-6 (FR-8):** Each of the following, requested without an authenticated session, redirects to the login route rather than rendering: `/customers`, `/rooms`, `/room-categories`, `/bookings/pending`, `/walk-in`, `/stays`.
- **AC-7 (FR-8, regression):** Each of the same routes, requested **with** an authenticated session, renders normally (200) — the existing feature tests for every prior admin change must still pass once they authenticate first.
- **AC-8 (public routes unaffected):** `/`, `/rooms-overview`, `/services`, `/food`, `GET /book`, `POST /book` all remain reachable **without** authentication.
- **AC-9 (FR-10):** An authenticated admin page's response body contains links to every other admin screen's index route.
- **AC-10 (FR-7):** POSTing to the login route without a valid CSRF token is rejected with HTTP 419, before any handler logic runs.

## Edge Cases

- **A logged-in session hitting the login page again:** out of scope for this change's acceptance criteria — no decision governs this, and the legacy itself has no special handling (a logged-in user can still see the login form). Not tested either way.

## Dependencies

- **BL-001 (CSRF):** built and verified. Satisfied — `ok-built`.
- **BL-010 before BL-011:** same-module build order within this change — the session gate needs a working login to test against.

## Notes

**Seeded admin user:** since there is no UI yet to create a `User` row (MOD-002 unbuilt) and no legacy seed data documented anywhere in this project's analysis to replicate for parity, one admin account is seeded directly in the migration (same pattern as change 003's RoomCategory seed): `username: admin`, `password: password123` (hashed via `Hash::make()`), `type: 1` (admin). **These are development-only placeholder credentials, not a security decision** — documented here and in `.specclaw/context.md` so they're never mistaken for something chosen deliberately for production.

**DR-012's role-visibility half is not implemented** — there is no "Users" nav item to hide yet (MOD-002 unbuilt), so there is nothing this change can correctly gate. Tracked, not silently dropped — a status note on BL-011 will flag it as partial, same treatment as BL-009's "Book Now" placeholder pattern.
