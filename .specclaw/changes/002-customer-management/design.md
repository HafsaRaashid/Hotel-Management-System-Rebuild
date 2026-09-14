# Design: Customer Management (MOD-005)

**Change:** 002-customer-management
**Created:** 2026-09-14

## Technical Approach

Standard Laravel resource CRUD: a migration for the `customers` table, a `Customer` Eloquent model (owning DR-004's id-generation logic as a static helper), a `CustomerController` with the five resource actions this module needs (`index`, `create`, `store`, `edit`, `update`, `destroy` — no `show`, since neither the legacy app nor functional-spec.md's capability description has a customer detail page), plain Blade views (no modal — the legacy `admin/manage_customer.php` uses a JS modal, but that is a UI-fidelity concern out of scope for this functional-only change per `/specclaw:bf-ui` not having run), and `Route::resource()` wired into `routes/web.php`.

Routes are registered **without an `/admin` prefix** for now (e.g. `/customers`, not `/admin/customers`). No admin route grouping exists yet in this repo — that is BL-011's job (the front-controller/whitelist replacement, MOD-001, not yet built) — so inventing a prefix here would pre-empt a decision that belongs to a different backlog item. This mirrors how change `001-csrf-baseline` avoided adding a route of its own for the same reason. Noted as a follow-up: once BL-011 lands, these routes may need to move under whatever prefix/middleware group it establishes.

## Architecture

```
routes/web.php ──resource routes──> CustomerController ──Eloquent──> Customer model ──> customers table
                                            │
                                            └──> resources/views/customers/{index,create,edit}.blade.php
```

No new architectural component beyond standard MVC — this module owns exactly one entity and one controller.

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/<timestamp>_create_customers_table.php` | create | `customers` table: `id`, `customer_id` (unsigned integer, unique), `name`, `mail`, `phone`, `address` (all string), `charges` (unsigned integer, default 0), timestamps. |
| `app/Models/Customer.php` | create | Eloquent model; `$fillable` for `name`/`mail`/`phone`/`address`/`customer_id`/`charges`; static `generateUniqueCustomerId()` implementing DR-004's regenerate-until-unique loop (range 0-99999999). |
| `app/Http/Controllers/CustomerController.php` | create | `index` (BL-006), `create`/`store` (BL-007 create half), `edit`/`update` (BL-007 edit half), `destroy` (BL-008 — CQ-021 fix, no CQ-024 check per the item split). |
| `resources/views/customers/index.blade.php` | create | List view — BL-006's FR-1 fields. |
| `resources/views/customers/create.blade.php` | create | Create form — `name`/`mail`/`phone`/`address`, no `customer_id` field (server-generates it), `@csrf`. |
| `resources/views/customers/edit.blade.php` | create | Edit form — same fields plus a read-only display of the existing `customer_id` (never a submitted input, matching DR-004: the id is never user-editable), `@csrf`, `@method('PUT')`. |
| `routes/web.php` | modify | Add `Route::resource('customers', CustomerController::class)->except(['show']);`. |
| `tests/Feature/CustomerManagementTest.php` | create | Feature tests for AC-1 through AC-8. |

## Data Model Changes

New table `customers`:

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK, auto-increment | Internal identity — never exposed as `customer_id`. |
| `customer_id` | unsigned integer, unique | DR-004's public-facing random id, range 0-99999999. |
| `name` | string | |
| `mail` | string | Column named `mail` (not `email`) — matches domain-model.md's own field naming for this entity, for consistency with the documented schema. |
| `phone` | string | No DB-level unique constraint — DR-005's dedup check is documented as legacy application-level logic only ("only best-effort, at insert time"), and no decision (CQ-###) adds a DB constraint here; adding one would be behavior nobody decided. |
| `address` | string | |
| `charges` | unsigned integer, default 0 | Accumulates at checkout (MOD-004's job, not this change's) — created at 0, never written to by this change's own code. |
| `created_at`/`updated_at` | timestamps | Standard Eloquent. |

## API Changes

New routes (all via `Route::resource('customers', ...)`, no `/admin` prefix — see Technical Approach):
- `GET /customers` → `index` (BL-006)
- `GET /customers/create` → `create`, `POST /customers` → `store` (BL-007 create)
- `GET /customers/{customer}/edit` → `edit`, `PUT /customers/{customer}` → `update` (BL-007 edit)
- `DELETE /customers/{customer}` → `destroy` (BL-008)

## Key Decisions

- **No JS modal, no custom Blade component for the create/edit form.** Plain full-page forms — the legacy modal is a UI-fidelity detail, explicitly out of scope until `/specclaw:bf-ui` runs. A shared `_form.blade.php` partial is **not** introduced either: two small, near-identical forms (create/edit) are simpler than a partial with conditionals for the one field (`customer_id` display) that differs between them (decision-ladder: stop at the first rung that holds).
- **`customer_id` generation lives on the model**, not the controller, so BL-007's DR-004 logic has one authoritative implementation other future call sites (if any arise) could reuse — matching how domain-model.md itself notes this exact loop is independently duplicated three times in the legacy app (`homepage/connect.php`, `admin/xulicheckin.php`, `admin/xulicustomer.php`); this rebuild gives it one home instead.
- **DR-005's phone-dedup check applies to `store()` only, not `update()`** — the rule is specifically about *creating* a new record ("A new customer record is created only if..."), and nothing in the acceptance basis extends it to edits.
- **No FormRequest class for validation** — `store`/`update`'s validation rules are simple enough (4-5 fields, one with a format rule) to inline via `$request->validate([...])`; a dedicated FormRequest would be one more file for no behavioral benefit at this scale.
- **CQ-024 deferred, not implemented as a no-op stub** — see spec.md's `## Item Split` section. `destroy()` contains no reference to any `booking`/`Booking` construct at all, per AC-7.

## Risks & Mitigations

- **Risk:** CQ-012's phone format has no decided spec (see spec.md NFR-2's stated assumption: digits only, 7-15 chars). **Mitigation:** flagged explicitly in spec and design rather than silently guessed; easy to tighten later without touching unrelated code (one `regex` rule).
- **Risk:** Routes without an `/admin` prefix may need to move once BL-011 (front controller) exists. **Mitigation:** `Route::resource()` makes this a one-line change (add `->prefix('admin')` or wrap in a route group) when that item lands — no controller/view changes needed.
- **Risk:** BL-008's deferred CQ-024 scope could be forgotten. **Mitigation:** recorded in spec.md's `## Item Split`, in this design's Key Decisions, and as a status note on BL-008 in `rebuild-backlog.md` (added alongside this change) — three places a future reader or `/specclaw:bf-rebuild-plan` run would surface it.
