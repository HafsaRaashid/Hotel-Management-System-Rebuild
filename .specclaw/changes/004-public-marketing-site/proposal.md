# Proposal: Public Marketing Site (MOD-006)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

MOD-006 owns four public, read-only marketing pages — `homepage/index.php`, `homepage/room.php`, `homepage/service.php`, `homepage/food.php` — none of which have a rebuilt equivalent yet. Per **CQ-008**, the rebuild must "drive public pricing display from the same live pricing data the admin/checkout path uses," replacing the legacy's hardcoded `$99`/`$149`/`$199` static text. That live data (RoomCategory) now exists — built and verified in change `003-room-rate-management`, seeded with exactly those three legacy prices.

## Proposed Solution

Implement **BL-009 — Browse Marketing Content**, MOD-006's single backlog item: four read-only pages (Home, Room Details, Services, Food & Drinks), reachable from a shared nav, with the Home and Room Details pages querying `RoomCategory::all()` for live pricing instead of hardcoding it.

Per BL-009's own acceptance basis, this is "a shape-changing revision of the legacy capability, not a client-orchestrated multi-command flow — no `DR-NNN` rule governs marketing-page rendering." No golden-master fixture exists or is expected (the legacy pages issue no database query at all — pure static markup — so there is no legacy behavior to replay; verification is a direct assertion that rendered prices match `room_categories.price` at request time).

Functional-only, same convention as every prior change: `/specclaw:bf-ui` hasn't run, so no visual/theme fidelity — BL-009 stays gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md.

## Scope

### In Scope
- Four public routes/views: home, room details, services, food & drinks — no form input, no auth (public pages).
- Home and Room Details pages render each RoomCategory's `name` and `price`, queried live.
- Shared nav (Home / Room / Services / Foods / Book Now) reachable from all four pages, matching the legacy's `homepage/Header.php` nav structure.
- Services and Food & Drinks pages: static content (no entity data involved per their capability description — no named gap or decision requires them to be data-driven).

### Out of Scope
- The "Book Now" nav link's destination — `homepage/book.php` is MOD-004's own item (BL-015, Public Reservation Intake), not built yet. The nav link will exist but its target route will 404 or be a placeholder until BL-015 exists — see design.md.
- Visual/theme fidelity — deferred until `/specclaw:bf-ui` runs.
- The decorative, never-wired-up availability-search widget on the legacy homepage — already decided (**CQ-013**): "Drop it — unimplemented decorative markup with no evidenced product intent."

## Impact

- **Files affected:** ~7-9 (estimated) — 1 controller, 4 views, a shared nav partial, routes, tests.
- **Complexity:** small-medium
- **Risk:** low — no business rule governs this item; acceptance rests directly on CQ-008's decision and RoomCategory's already-verified data.

## Open Questions

None blocking. `bypass-check` on BL-009 is clean (BL-002 resolves `ok-built`; BL-005 confirmed built via its own status note).

---

**To proceed:** Review this proposal and approve to begin planning.
