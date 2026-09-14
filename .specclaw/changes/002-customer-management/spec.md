# Spec: Customer Management (MOD-005)

**Change:** 002-customer-management
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements MOD-005's three backlog items — Customer List (BL-006), Customer Create/Edit (BL-007), Customer Delete (BL-008) — grounded in domain-model.md's `Customer` entity and the legacy capabilities at `admin/customers.php`, `admin/manage_customer.php`, and `admin/delete_customer.php`.

No `customers` table, model, or admin routes exist yet in this repo (`database/migrations/` holds only the framework's `cache`/`jobs` tables) — this change creates them from scratch, following the conventions `.specclaw/context.md` already records (every state-changing form/handler gets `@csrf`, per change `001-csrf-baseline`).

## Item Split — BL-008 (Customer Delete)

BL-008's own acceptance basis (rebuild-backlog.md) requires **CQ-024**'s referential-integrity check — reject deleting a customer still referenced by an active booking — implemented "querying `booking` by phone... unless/until CQ-011's new FK column supersedes it." **No `booking` table exists in this repo** (MOD-004 is unbuilt), so that check cannot be implemented against anything real.

Decided with the operator (2026-09-14): **implement the delete itself now** (CQ-021's fix — make delete actually delete, unlike the legacy bind_param no-op), **defer CQ-024's referential-integrity check** until MOD-004 creates the `booking` table.

- **Implemented now:** CSRF-protected `DELETE`/POST handler that actually removes the `customers` row (CQ-021).
- **Deferred:** CQ-024's check against active bookings — genuinely absent from this change, not partially wired. Attaches to `CustomerController::destroy()` (or equivalent) once a `Booking` model/table exists — the seam is a single guard clause querying bookings by the deleted customer's `id` (or, once CQ-011 lands, `booking.customer_id`) before the delete proceeds.
- **Blocked until:** MOD-004 (Booking) creates the `booking` table — no single BL-### id is cited here because BL-008 itself does not declare this dependency (a gap this spec is recording, not inheriting).

**Note on tooling:** this is recorded here in prose rather than via specclaw's `IS-###` split registry (`specclaw-bf-rebuild-collect split-append`), because that command mechanically requires the item to cite at least one `DR-###` rule to partition against (`split-append: ... acceptance basis cites no DR-### rule, so there is nothing to partition`), and BL-008 legitimately cites none — its basis is CQ-021/CQ-024/GM-035, by the backlog's own design ("No `DR-NNN` rule numbers this handler"). Forcing a fabricated `DR-###` citation onto the backlog item just to satisfy the tool would corrupt its evidentiary record, so this deferral is tracked here and in rebuild-backlog.md's status notes for BL-008 instead.

## Requirements

### Functional Requirements

- **FR-1 (BL-006):** A list view renders every customer's `name`, `mail`, `phone`, `address`, `customer_id`, and cumulative `charges`.
- **FR-2 (BL-007):** A create/edit form accepts `name`, `mail`, `phone`, `address` (and, when editing, displays the existing `customer_id` as a read-only/hidden field per DR-004 — never user-editable, exactly as the legacy form treats it).
- **FR-3 (BL-007, DR-004):** On create, `customer_id` is generated as a random integer in the range 0-99999999, regenerated in a loop until no existing `customers.customer_id` matches it.
- **FR-4 (BL-007, DR-005):** On create, if a customer already exists with the same `phone`, no new row is inserted (the legacy behavior this rule documents — "the code checks... and skips the insert if a row already exists").
- **FR-5 (BL-007, CQ-012):** The `phone` field is validated, client-side and server-side, against a real format check (see NFR-2 for the assumed format, since the source decision does not pin one).
- **FR-6 (BL-008, CQ-021):** A delete request actually removes the `customers` row (fixing the legacy bind_param defect that made `admin/delete_customer.php`'s delete a silent no-op).
- **FR-7:** All three write-capable forms/handlers (create, edit, delete) carry a CSRF token per the established convention (`.specclaw/context.md`).

### Non-Functional Requirements

- **NFR-1:** No golden-master replay is performed as part of this change's own verification (fixtures GM-008/GM-009/GM-010/GM-011/GM-033 exist per rebuild-backlog.md but replaying them is `/specclaw:bf-replay`'s job, a separate phase not yet run in this project — see bf-status). Acceptance here rests on tests written directly against the documented rules (DR-004, DR-005) and decisions (CQ-012, CQ-021), the same approach change `001-csrf-baseline` used.
- **NFR-2 (phone format — stated assumption, not sourced from a decision):** decisions.md's CQ-012 says only "Add real phone-number format validation (client- and server-side)" with no format specified. Assumption: digits only, 7-15 characters (a generic international-friendly phone length), enforced via Laravel's `regex` validation rule server-side and an HTML5 `pattern`/`inputmode="numeric"` attribute client-side. Flagging this explicitly per Rule 1 (Think Before Coding) rather than picking silently — if a specific format is required (e.g. a country-specific mask), say so and this will be revised.
- **NFR-3:** No visual/theme fidelity work (Bootstrap defaults only) — `/specclaw:bf-ui` has not run; BL-006/BL-007 stay gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md until it does.

## Acceptance Criteria

- **AC-1 (BL-006):** GET the customer list route returns all seeded customers' `name`, `mail`, `phone`, `address`, `customer_id`, `charges` values, visible in the response body.
- **AC-2 (BL-007, DR-004):** Creating a customer assigns a `customer_id` in `[0, 99999999]`; when an existing `customer_id` is seeded to collide, the handler regenerates until unique (tested by seeding a colliding value and asserting the persisted row's `customer_id` differs from it).
- **AC-3 (BL-007, DR-005):** Creating a customer whose `phone` matches an existing customer's `phone` does not insert a second row — the customers table's row count is unchanged after the request.
- **AC-4 (BL-007, CQ-012):** Submitting a non-numeric or out-of-length `phone` value is rejected with a validation error; a well-formed one is accepted.
- **AC-5 (BL-007):** Editing an existing customer updates `name`/`mail`/`phone`/`address` without changing its `customer_id`.
- **AC-6 (BL-008, CQ-021):** Deleting a customer removes its row from the `customers` table (not a silent no-op).
- **AC-7 (BL-008, deferred scope):** No code path in this change queries a `booking`/`bookings` table or references a `Booking` model — confirming CQ-024's check is genuinely absent, not half-wired.
- **AC-8 (FR-7):** POSTing to the create, edit, or delete routes without a valid CSRF token is rejected with HTTP 419 (the same mechanism change `001-csrf-baseline` proved), before any handler logic runs.

## Edge Cases

- **Empty `customers` table:** the list view renders with zero rows, no error.
- **`customer_id` collision on the very first draw:** the generation loop must retry, not fail (AC-2 tests this directly by seeding a collision).
- **Deleting a customer that does not exist:** out of scope for this change's acceptance criteria — the legacy handler's behavior for a missing id was not part of BL-008's cited acceptance basis (GM-035, CQ-021, CQ-024); if a 404/redirect convention is needed it will follow Laravel's normal route-model-binding behavior without a bespoke rule.

## Dependencies

- **BL-001 (CSRF baseline):** built and verified (change `001-csrf-baseline`, PASS). Satisfied — `ok-built` per `bypass-check`.
- **BL-006 → BL-007 → BL-008:** same-module build order within this change (List, then Create/Edit, then Delete) — not a cross-change wait.
- **MOD-004 (Booking/`booking` table):** unmet, undeclared in BL-008's own `Depends on:` field. Handled via the item-split above, not a bypass.

## Notes

Per rebuild-backlog.md, no `GM-NNN` scenario exists for BL-006's list view or BL-007's own DR-005 dedup branch (`admin/xulicustomer.php:29` specifically — GM-010/GM-011 exercise DR-005 only via `homepage/connect.php`, a different call site). This is a real, pre-existing coverage gap in the baseline, not something this change introduces or is expected to close; new tests here are written directly against this rebuild's own code, per the same rebuild-backlog.md guidance BL-001 followed.
