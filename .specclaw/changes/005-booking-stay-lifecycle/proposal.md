# Proposal: Booking & Stay Lifecycle (MOD-004)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

MOD-004 owns the `Booking` entity and the hotel's central transactional workflow — reservation intake, walk-in check-in, converting a pending booking to a checked-in stay, checkout/billing, cancellation, and stay-editing — none of which have a rebuilt equivalent yet. It is the largest remaining module: 9 backlog items (BL-015 through BL-023), all of whose cross-module dependencies (BL-001 CSRF, BL-002 Room, BL-005 RoomCategory, BL-007 Customer) are now built.

## Proposed Solution

Implement all 9 of MOD-004's functional backlog items in one change, built internally in the module's own declared sequencing order (rebuild-backlog.md's Sequencing Rationale): intake → pending-list/cancel → check-in conversion + walk-in check-in (parallel path) → check-in/out list → checkout/billing → edit/view stay detail.

- **BL-015 — Public Reservation Intake:** the public booking form + its submission handler. DR-003 (unique ref-no), DR-004/DR-005 (customer id generation + phone dedup, reusing `Customer::generateUniqueCustomerId()` from change 002), DR-006 as corrected by **CQ-004** (reject an invalid category selection — implemented as a real FK-validated `category_id` select against `room_categories`, not legacy string-matching), CQ-011 (populate the new `customer_id` FK), CQ-012 (phone validation, reusing the established pattern).
- **BL-016 — Pending Bookings List & Cancellation:** list `status=0` bookings; cancellation sets a new `cancelled` status value (**CQ-014**) instead of the legacy hard delete, preserving the record.
- **BL-017 — Booking → Check-In Conversion:** DR-007 (assign a room, occupy it, flip booking to checked-in) as one composite write.
- **BL-018 — Walk-In Availability List:** available (`status=0`) rooms.
- **BL-019 — Walk-In Check-In:** DR-003/004/005/006/008 (same generation/dedup/validation as BL-015, plus DR-008's direct-to-checked-in insert). **CQ-022** (the legacy connection-lifecycle crash) does not apply to the rebuild — Eloquent has no manual connection lifecycle to mismanage, so the defect is structurally absent rather than needing a fix.
- **BL-020 — Check-In/Out List:** `status=1`/`status=2` bookings, with entry points into checkout/edit/view.
- **BL-021 — Guest Check-Out & Billing:** DR-009 (days-of-stay), DR-010 (amount due = price × days) with **CQ-005**'s server-side payment validation (the legacy accepts any client-submitted value), DR-011's three-write composite (close booking, bill customer via the CQ-011 FK not phone, free the room via id per **CQ-023** not name-text).
- **BL-022 — Edit In-Progress Stay's Checkout Date:** **CQ-016** (recompute and persist `days_of_stay`, which the legacy leaves stale).
- **BL-023 — View Completed Stay Detail:** read-only summary.

Functional-only, same convention as every prior change: `/specclaw:bf-ui` hasn't run, so no visual/theme fidelity.

## Scope

### In Scope
- `bookings` table, `Booking` model (with a `generateUniqueRefNo()` helper mirroring `Customer::generateUniqueCustomerId()`), and all 9 items' routes/controllers/views/tests.
- All named defect corrections (CQ-004, CQ-005, CQ-011, CQ-014, CQ-016, CQ-023) — the rebuild implements the decided behavior, never the legacy defect.
- Updating MOD-006's "Book Now" nav link from its placeholder plain `/book` string to the real route, now that BL-015 exists.

### Out of Scope
- BL-025 (MOD-004 quality remediation) — a measurement-only item driven by `/specclaw:bf-quality`, not part of the normal propose/build/verify lifecycle.
- Golden-master replay (`/specclaw:bf-replay`) — a separate phase not yet run in this project; acceptance rests on tests written directly against the documented rules, same approach every prior change used. Many `GM-NNN` fixtures exist for this module's rules (cited per-item in rebuild-backlog.md) but several pin **pre-fix** legacy defect behavior (GM-014, GM-015, GM-017, GM-023, GM-025) that must not be treated as parity targets — noted in spec.md per item.
- Any UI grouping/routing prefix decisions that belong to BL-011 (front controller), not yet built.

## Impact

- **Files affected:** ~30-35 (estimated) — 1 migration, 1 model, 4 controllers, ~8 views, routes, tests. The largest change so far by a wide margin.
- **Complexity:** large
- **Risk:** medium — real composite multi-write flows (DR-007, DR-011) and several corrected-vs-legacy-defect behaviors (CQ-004, CQ-005, CQ-014, CQ-016, CQ-023) that must each be implemented as the *decided* behavior, not the *observed* legacy one; mitigated by grounding every write path directly in its DR-###/CQ-### citation in spec.md.

## Open Questions

None blocking. `bypass-check` on all 9 items is clean — every cross-module dependency (BL-001, BL-002, BL-005, BL-007) resolves `ok-built`; the only unmet dependencies are same-module prerequisites satisfied by this change's own internal build order.

---

**To proceed:** Review this proposal and approve to begin planning.
