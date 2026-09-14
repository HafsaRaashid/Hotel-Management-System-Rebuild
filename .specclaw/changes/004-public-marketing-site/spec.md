# Spec: Public Marketing Site (MOD-006)

**Change:** 004-public-marketing-site
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements BL-009, MOD-006's only backlog item: four public, read-only marketing pages (Home, Room Details, Services, Food & Drinks), reachable from a shared nav, matching legacy `homepage/index.php`/`room.php`/`service.php`/`food.php`. Per **CQ-008**, Home and Room Details render live `RoomCategory` pricing instead of the legacy's hardcoded `$99`/`$149`/`$199`.

This change also replaces the foundation's placeholder `shell` route at `/` — its own header comment states it is "expected to be replaced by the Public Marketing Site module's own backlog item once it is built."

## Requirements

### Functional Requirements

- **FR-1:** A home page at `/` renders each `RoomCategory`'s `name` and `price`, live from the database (CQ-008).
- **FR-2:** A room details page renders the same live category data (CQ-008 applies identically — functional-spec.md's Named Gap names both `homepage/index.php` and `homepage/room.php` as hardcoding the same three prices).
- **FR-3:** A services page and a food & drinks page render static content — no entity data is involved in either (no named gap or decision requires them to be data-driven; their capability description states "No form input" and neither is cited against any entity).
- **FR-4:** All four pages share a nav with links: Home, Room, Services, Foods, Book Now — matching the legacy's `homepage/Header.php` nav structure.
- **FR-5:** The nav's "Book Now" link points at `/book` as a plain URL, not a named route — `homepage/book.php`'s rebuild equivalent is BL-015 (MOD-004, Public Reservation Intake), not built yet. Using a plain string avoids a `RouteNotFoundException` on every page render; the link will 404 until BL-015 lands, which is honest (a real destination that doesn't exist yet) rather than broken (a route helper call that crashes the page).

### Non-Functional Requirements

- **NFR-1:** No golden-master replay. Per BL-009's own "Verification inputs needed" note, no `GM-NNN` scenario exists for any MOD-006 page — the legacy pages issue no database query at all (module-map.md: "no database query in any of them; prices/content are static markup"), so there is no legacy behavior to replay. Verification is a direct assertion that rendered prices match `room_categories.price` at request time.
- **NFR-2:** No visual/theme fidelity work — `/specclaw:bf-ui` has not run; BL-009 stays gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md.
- **NFR-3:** The decorative, never-wired-up availability-search widget from the legacy homepage is not rebuilt — already decided (**CQ-013**: "Drop it — unimplemented decorative markup with no evidenced product intent").

## Acceptance Criteria

- **AC-1 (FR-1):** GET `/` returns 200 and the response body contains every seeded `RoomCategory`'s `name` and `price` (e.g. "Single Room" and "99").
- **AC-2 (FR-2):** GET the room details route returns 200 and the response body contains every seeded `RoomCategory`'s `name` and `price`.
- **AC-3 (FR-1/FR-2, live-data proof):** Changing a `RoomCategory`'s `price` in the database and re-requesting `/` (or the room details route) reflects the new price — proving the value is queried live, not cached/hardcoded.
- **AC-4 (FR-3):** GET the services route and GET the food & drinks route both return 200.
- **AC-5 (FR-4):** All four pages' response bodies contain the nav links' text (Home, Room, Services, Foods, Book Now).
- **AC-6 (FR-5):** The "Book Now" link's `href` is `/book` (a literal path, confirmed by inspecting the rendered HTML — not asserting the route resolves, since it deliberately does not yet).

## Edge Cases

- **Zero `RoomCategory` rows:** cannot occur in practice (the migration in change `003-room-rate-management` always seeds exactly three), but the view iterates `RoomCategory::all()` with a `@foreach`, which renders nothing (no error) if the table were ever empty.
- **`/book` visited before BL-015 exists:** 404s. This is the intended, documented state per FR-5 — not a defect to handle here.

## Dependencies

- **BL-002 (Room List & Category Filter):** built and verified (change `003-room-rate-management`, PASS). Satisfied — `ok-built` per `bypass-check`.
- **BL-005 (Room Category Management):** built and verified (same change). `bypass-check` only surfaced BL-002 in its dependencies array (a parser limitation on BL-009's multi-citation `Depends on:` line), but BL-005's own status note in rebuild-backlog.md independently confirms `BUILT: change 003-room-rate-management`.

## Notes

Neither `RoomCategory` nor `Room` gets a new column or behavior change from this item — BL-009 only reads `RoomCategory::all()`. No model changes are part of this change.
