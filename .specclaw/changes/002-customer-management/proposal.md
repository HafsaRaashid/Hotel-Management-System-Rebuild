# Proposal: Customer Management (MOD-005)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

The legacy application's `admin/customers.php` (list), `admin/manage_customer.php` (create/edit modal), and `admin/delete_customer.php` (delete) capabilities have no rebuilt equivalent yet. MOD-005 owns the `Customer` entity — "a running profile of every guest who has ever booked or checked in, deduplicated by phone number, that accumulates lifetime spend" (domain-model.md) — and per rebuild-backlog.md's own sequencing rationale, MOD-005 is one of the two modules (alongside MOD-003) with no module dependency, so its CRUD should be built early: MOD-004 (Booking) reads/writes Customer as a side effect once it exists.

## Proposed Solution

Implement the three MOD-005 backlog items in their declared build order (List → Create/Edit → Delete — each is the prerequisite screen the next one is reached from, per the legacy UI and rebuild-backlog.md's own sequencing):

- **BL-006 — Customer List:** a read-only list view rendering `name`, `mail`, `phone`, `address`, `customer_id`, and cumulative `charges` for every customer.
- **BL-007 — Customer Create/Edit:** a create/edit form (`name`, `cus_id`/Customer Id — hidden on create — `mail`, `phone`, `address`), enforcing **DR-004** (customer ids are randomly generated and unique) and **DR-005** (a new customer record is created only if no existing customer shares the same phone number), with CSRF protection via the now-built BL-001 baseline.
- **BL-008 — Customer Delete:** a delete handler, CSRF-protected via BL-001.

This change builds **functional-only** — business logic, routes, controllers, views, and their own tests — with **no visual/theme fidelity work**. `/specclaw:bf-ui` has not been run yet, so no UI inventory, design tokens, or screen references exist to build against. BL-006 and BL-007 (both screen-bearing) will therefore stay gated `OPEN QUESTIONS — UI fidelity` in rebuild-backlog.md until that phase runs and a later change reconciles visual fidelity; that gap is tracked, not silently dropped.

## Scope

### In Scope
- Customer list route/controller/view (BL-006), rendering all six fields including the read-only `charges` total.
- Customer create/edit route/controller/view (BL-007): DR-004 unique-id generation loop, DR-005 phone-dedup check before insert, `@csrf` on the form (per BL-001's established convention in `.specclaw/context.md`), CQ-011/CQ-012 field-validation requirements (phone format validation — a legacy gap this rebuild closes).
- Customer delete handler (BL-008), CSRF-protected, actually executing the delete (per **CQ-021**'s bind_param-defect fix requirement) and enforcing the referential-integrity check **CQ-024** requires (reject deleting a customer referenced by an active booking).
- Tests for all three items' acceptance criteria (DR-004, DR-005, CQ-011/012/021/024 as applicable).

### Out of Scope
- Visual/theme fidelity (colors, layout pixel-parity, Bootstrap component styling beyond functional Blade + Bootstrap 5 defaults) — deferred until `/specclaw:bf-ui` runs.
- MOD-006 (Public Marketing Site) — separate change, and it has its own unmet dependency on MOD-003 (Room/RoomCategory), not addressed here.
- Any MOD-004 (Booking) integration beyond what BL-007/BL-008's own acceptance basis requires — Booking's own "creates/updates customer record as a side effect" behavior (BL-015) is a separate, later backlog item.

## Impact

- **Files affected:** ~10-14 (estimated) — routes, 1 controller, 2-3 Blade views/partials, tests for each item.
- **Complexity:** medium
- **Risk:** low — no legacy behavior to replay (per-item verification notes below), acceptance rests on documented business rules (DR-004, DR-005) and decisions (CQ-011, CQ-012, CQ-021, CQ-024), not on speculative design.

## Open Questions

None blocking. Dependency check (`bypass-check`) on BL-006/BL-007/BL-008 is clean: BL-001 (CSRF baseline) now carries a declared `BUILT:` note (change `001-csrf-baseline`, verify PASS) and resolves as `ok-built` for both BL-007 and BL-008. The remaining same-module dependencies (BL-006 before BL-007, BL-007 before BL-008) are this change's own internal build order, not a cross-change wait.

---

**To proceed:** Review this proposal and approve to begin planning.
