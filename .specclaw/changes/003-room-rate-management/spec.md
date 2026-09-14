# Spec: Room & Rate Management (MOD-003)

**Change:** 003-room-rate-management
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements MOD-003's `Room` and `RoomCategory` entities and three of its four backlog items — Room List & Category Filter (BL-002), Room Create/Edit (BL-003), Room Category Management (BL-005) — grounded in domain-model.md and functional-spec.md.

**BL-004 (Room Delete) is out of scope for this change** — see `## Item Held Out` below.

## Item Held Out — BL-004 (Room Delete)

BL-004's own acceptance basis states: "CQ-021's fix makes CQ-024's gap immediately live — this item must ship both the delete fix and the referential-integrity check together, not delete alone deferred to an undefined later point." CQ-024's check requires querying the `booking` table, which does not exist (MOD-004 unbuilt).

Decided with the operator (2026-09-14): unlike BL-008 (Customer Delete), this item's own wording rules out a partial/deferred build, so **BL-004 is held out of this change entirely** rather than force-fit via an item-split. It will be proposed as its own change once MOD-004 creates the `booking` table. Nothing in this change adds a delete route/handler for rooms.

## Requirements

### Functional Requirements

- **FR-1 (BL-002):** A list view renders every room's `room` (name/number), category name, and `status` (Available/Unavailable), filterable by category via a select populated live from `room_categories`.
- **FR-2 (BL-003):** A create/edit form accepts `room` (text), `category_id` (select, populated live from `room_categories` — not hardcoded, since BL-005 lands in this same change), `status` (select: Available/Unavailable).
- **FR-3 (BL-005):** A CRUD screen (list/create/edit/delete) for `room_categories`, fields `name` and `price`. New capability — no legacy screen exists (functional-spec.md's Named Gap; CQ-007's decision).
- **FR-4:** Both create/edit forms (Room, RoomCategory) and RoomCategory's delete carry a CSRF token per the established convention (`.specclaw/context.md`).
- **FR-5 (seed data, legacy parity):** The three legacy-seeded categories are seeded on migration: Single Room ($99), Double Room ($149), Deluxe Room ($199) — the exact static prices `homepage/index.php`/`homepage/room.php` currently hardcode (per rebuild-backlog.md's BL-009 citation), preserved here as real, editable starting data instead of literal text.

### Non-Functional Requirements

- **NFR-1:** No golden-master replay performed as part of this change's own verification. BL-002/BL-003 have no `GM-NNN` fixture (rebuild-backlog.md: "no scenario... targets `admin/rooms.php`'s list/filter view directly" / "`admin/manage_room.php`... none of the 35 captured fixtures seed or assert against a room-create/edit call"). BL-005 has none by definition (new capability, no legacy behavior to replay). Acceptance rests on tests against the documented entity shape and CQ-007's decision, same approach as changes 001/002.
- **NFR-2:** No visual/theme fidelity work — `/specclaw:bf-ui` has not run; BL-002/BL-003/BL-005 (all screen-bearing) stay gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md.

## Acceptance Criteria

- **AC-1 (BL-002):** GET the room list route returns every seeded room's `room`, category name, and `status`, visible in the response body.
- **AC-2 (BL-002):** GET the room list route with a category filter returns only rooms in that category.
- **AC-3 (BL-003):** Creating a room with a valid `category_id` persists `room`, `category_id`, `status`.
- **AC-4 (BL-003):** Creating a room with a `category_id` that does not exist in `room_categories` is rejected with a validation error (not silently accepted, unlike the legacy hardcoded-select which could never actually submit an invalid id).
- **AC-5 (BL-003):** Editing an existing room updates `room`/`category_id`/`status`.
- **AC-6 (BL-005):** Creating a room category persists `name` and `price`.
- **AC-7 (BL-005):** Editing a room category updates `name`/`price`.
- **AC-8 (BL-005):** Deleting a room category removes its row.
- **AC-9 (FR-5):** After migration, `room_categories` contains exactly three rows: Single Room/99, Double Room/149, Deluxe Room/199.
- **AC-10 (FR-4):** POSTing to any of the five write routes (room store/update, category store/update/destroy) without a valid CSRF token is rejected with HTTP 419, before any handler logic runs.

## Edge Cases

- **Empty `rooms` table, non-empty `room_categories`:** the room list renders with zero rows, category filter select still populates from the three seeded categories.
- **Deleting a room category that rooms still reference:** out of scope for this change's acceptance criteria — no `DR-NNN`/`CQ-###` in this item's cited acceptance basis governs category-deletion referential integrity (unlike BL-004/BL-008's booking-reference concern, which is entity-to-entity; RoomCategory-to-Room integrity was never raised in any decision). Left unhandled here rather than inventing a rule nobody decided (decision-ladder: don't build validation for a scenario no acceptance basis names). Deleting the category referenced by an existing room will leave that room's `category_id` pointing at a nonexistent row — the same "no referential-integrity check" pattern the legacy app has everywhere else (GM-034/GM-035), so this is consistent with the rebuild's documented baseline behavior elsewhere, not a new gap this change introduces.

## Dependencies

- **BL-001 (CSRF baseline):** built and verified. Satisfied — `ok-built` per `bypass-check` for BL-003 and BL-005.
- **BL-005 → BL-002/BL-003:** RoomCategory CRUD is built first (or at least its migration/model/seed) so Room's `category_id` select and validation can reference real `room_categories` rows from day one — see design.md's build order.

## Notes

RoomCategory table is named `room_categories` (idiomatic Laravel plural of the `RoomCategory` model), not the legacy `room_categoricals` — no decision pins the physical table name, and there is no reason to preserve the legacy misspelling in a rebuild. Room's foreign key still points at the correct target regardless of name.
