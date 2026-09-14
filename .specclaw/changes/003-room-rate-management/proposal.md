# Proposal: Room & Rate Management (MOD-003)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

MOD-003 owns `Room` and `RoomCategory` — no rebuilt equivalent exists yet for `admin/rooms.php` (list/filter), `admin/manage_room.php` (create/edit), or room-category management. Per rebuild-backlog.md's sequencing rationale, MOD-003 is one of the two modules with no module dependency (alongside MOD-005, already built), so its CRUD should exist before MOD-004 (Booking, which needs an available room to assign) and MOD-006 (Public Marketing, which reads live category pricing per CQ-008).

RoomCategory management (BL-005) is **new scope**, not a legacy capability: the legacy app has no admin screen for it at all (functional-spec.md's Named Gap), and CQ-007 decided to add one.

## Proposed Solution

Implement three of MOD-003's four backlog items, in dependency order:

- **BL-002 — Room List & Category Filter:** read-only list of rooms, filterable by category (select populated live from `room_categoricals`), rendering each room's `status` (Available/Unavailable).
- **BL-003 — Room Create/Edit:** create/edit form for `room` (name/number), `category` (select), `status` (Available/Unavailable). CSRF-protected (BL-001, built).
- **BL-005 — Room Category Management (CRUD):** new admin screen for `room_categoricals` — create/edit/delete category rows (`name`, `price`), closing the Named Gap per CQ-007. CSRF-protected.

**BL-004 (Room Delete) is explicitly held out of this change.** Its own acceptance basis states the CQ-021 delete-fix and CQ-024's referential-integrity check (against active bookings) "must ship together... not delete alone deferred to an undefined later point" — but no `booking` table exists yet (MOD-004 unbuilt), so that check cannot be built against anything real. Unlike BL-008 (Customer Delete), this item's own wording rules out a partial/deferred build, so it stays out of this change entirely rather than being force-fit via an item-split. Decided with the operator (2026-09-14).

Functional-only, same convention as changes 001/002: `/specclaw:bf-ui` hasn't run, so no visual/theme fidelity work — BL-002/BL-003/BL-005 (all screen-bearing) stay gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md.

## Scope

### In Scope
- Room list/filter route+controller+view (BL-002).
- Room create/edit route+controller+view (BL-003): `room`, `category_id` (FK), `status` fields.
- RoomCategory CRUD route+controller+views (BL-005): `name`, `price` fields — genuinely new capability, no legacy behavior to replicate.
- Tests for all three items' acceptance criteria.

### Out of Scope
- **BL-004 (Room Delete)** — held out entirely; needs MOD-004's `booking` table to honestly satisfy its own acceptance basis. Revisit once MOD-004 exists.
- Visual/theme fidelity — deferred until `/specclaw:bf-ui` runs.
- MOD-006 (Public Marketing Site) — separate change; will read this module's RoomCategory pricing once built, but that's its own item (BL-009).
- Making BL-002/BL-003's `category` select source live options from BL-005's new table instead of the legacy's hardcoded three — rebuild-backlog.md flags this as "a forward-looking consistency point, not a separate DR-NNN-governed rule." Included here anyway since BL-005 lands in the same change (no reason to ship BL-002/BL-003 against hardcoded options when the live table exists in the same change) — see design.md.

## Impact

- **Files affected:** ~14-18 (estimated) — 2 migrations, 2 models, 2 controllers, ~6 views, tests.
- **Complexity:** medium
- **Risk:** low — BL-002/BL-003 rest on documented entity fields; BL-005 is new scope grounded directly in CQ-007's decision and the RoomCategory entity's documented shape.

## Open Questions

None blocking. `bypass-check` on BL-002/BL-003/BL-005 is clean (BL-001 resolves `ok-built`). BL-004 is out of scope per the decision above, not an open dependency to elicit.

---

**To proceed:** Review this proposal and approve to begin planning.
