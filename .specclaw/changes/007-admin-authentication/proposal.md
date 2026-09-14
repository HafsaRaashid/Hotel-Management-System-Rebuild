# Proposal: Admin Authentication & Access Control (MOD-001)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

Every admin screen built so far (customers, rooms, room categories, pending bookings, walk-in check-in, stays) is completely unauthenticated — anyone can reach any URL directly. There is no login, no session gate, and no shared admin navigation tying the screens together. MOD-001 (BL-010 Login/Logout, BL-011 Front Controller & Session Gate) is the module that closes this — BL-001 (CSRF) is already built.

## Proposed Solution

- **BL-010 — Admin Login & Logout:** a login form (`username`/`password`) and session-based authentication using Laravel's built-in `Auth` system (session guard, already wired in `config/auth.php` — that file's own comment notes `App\Models\User` was deliberately left uncreated, "owned by MOD-002... created by a future backlog item"). DR-001 (both fields required), DR-002 (exact match), CQ-002 (`password_hash`/`Hash::check`, not plaintext), CQ-019 (`UNIQUE` constraint on `username`, case-insensitive collation), CQ-020 (case-sensitive comparison re-check in code, mirroring the legacy's own `===` recheck, since DB collation alone is case-insensitive).
- **BL-011 — Front Controller & Session Gate:** an `auth` middleware group wrapping every existing admin route (customers, rooms, room-categories, bookings/pending, walk-in, stays) — unauthenticated requests redirect to login. CQ-001 ("whitelist against the known page set," replacing the legacy's unsanitized dynamic `include`) is structurally satisfied for free: Laravel's router is itself an explicit whitelist, nothing like the legacy's `include $page . '.php'` pattern exists in this rebuild at all.

**The `User` entity (migration + model) is built here, not deferred to MOD-002** — login cannot function without it, and `config/auth.php`'s own comment already anticipated a backlog item would need to create it. MOD-002's own items (BL-012/013/014, User CRUD) will reuse this same model, exactly as MOD-003's Room items reused BL-005's RoomCategory.

**A shared admin nav is added**, since none exists yet (the user asked "where's the admin home page with the nav bar" earlier in this session) — one partial, linking to every admin screen that currently exists. DR-012's "Users" nav-visibility-by-role rule is **not** implemented yet: there is no "Users" screen to link to (MOD-002 unbuilt), so there is nothing to hide. Documented explicitly, not silently dropped — same treatment as BL-009's "Book Now" placeholder.

**One admin user is seeded** (via the migration, same pattern as change 003's RoomCategory seed) since there is no UI yet to create one and no legacy seed data to replicate for parity — documented credentials, not a silent default.

## Scope

### In Scope
- `users` migration + `User` model (Authenticatable).
- Login form + handler, logout.
- `auth` middleware applied to every existing admin route.
- Shared admin nav partial, included on every existing admin screen.
- One seeded admin user.
- Tests: DR-001/DR-002/CQ-002/CQ-019/CQ-020 for login; middleware-redirect proof for every previously-open admin route.

### Out of Scope
- MOD-002 (User CRUD screens, BL-012/013/014) — separate module, reuses this change's `User` model.
- DR-012's role-based nav-visibility half (hiding "Users" from non-admins) — nothing to hide yet; revisit when MOD-002 exists.
- The public marketing pages and the public booking form (`/`, `/rooms-overview`, `/services`, `/food`, `/book`) stay unauthenticated — they're public by design, never in scope for this gate.

## Impact

- **Files affected:** ~20 (1 migration, 1 model, 1 controller, 1 login view, 1 nav partial, routes, 6 existing view edits, 2-3 test files).
- **Complexity:** medium-large
- **Risk:** medium — this is the first change that *removes* access from routes that previously had none; every existing admin feature test must keep passing once its routes sit behind the new middleware (tests will need to authenticate first).

## Open Questions

None blocking. `bypass-check` is clean for both items — BL-001 resolves `ok-built`; BL-011's only unmet dependency is BL-010, satisfied by this change's own build order.

---

**To proceed:** Review this proposal and approve to begin planning.
