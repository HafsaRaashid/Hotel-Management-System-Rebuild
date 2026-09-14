# Spec: MOD-002 — User Account Management

**Change:** 009-user-account-management
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

Implements MOD-002 (BL-012 User List, BL-013 User Create/Edit, BL-014 User Delete): the admin screens for managing staff/admin login accounts, reusing the `User` Eloquent model and `users` migration already built in change `007-admin-authentication`. Fixes two legacy defects along the way: DR-013 (edit form's Password field must render blank, never pre-filled with the id) and the handler half of DR-012 (create/update/delete must enforce a real server-side admin-only check — today any authenticated session, staff or admin, can reach these handlers).

## Requirements

### Functional Requirements

- **FR-1 (BL-012):** An authenticated session can view a list of every user account, showing `name`, `username`, and `type` (rendered distinguishably as Admin/Staff, per `users.type` Enumeration 3).
- **FR-2 (BL-013):** An authenticated admin session can create a new user account (`name`, `username`, `password`, `type`). `username` must be unique (schema-enforced, per the existing migration). The password is stored hashed via the `User` model's existing `hashed` cast.
- **FR-3 (BL-013, DR-013 fix):** The edit form's Password field always renders blank — never pre-filled with the user's `id` or any other stored value. Submitting the edit form with the password field left blank leaves the stored password unchanged; submitting a new value replaces it (hashed).
- **FR-4 (BL-014, CQ-021):** An authenticated admin session can delete a user account, and the row is actually removed — not a silent no-op.
- **FR-5 (DR-012 handler half, CQ-003):** The create (`store`), update, and delete (`destroy`) actions reject any authenticated session whose `type` is not `1` (Admin) with an HTTP 403 response, before any write occurs. Per BL-012's own scoping in `rebuild-backlog.md`, the **list view (`index`) carries no additional server-side check beyond the existing `auth` middleware** — DR-012's nav-visibility half stays BL-011/007's territory and is not re-litigated here.
- **FR-6 (BL-001):** Every state-changing form (create, edit/update, delete) includes Laravel's `@csrf` token, per the established CSRF baseline.

### Non-Functional Requirements

- **NFR-1:** No changes to the `User` model or the `users` table/migration — both are reused exactly as built in `007-admin-authentication`.
- **NFR-2 (UI fidelity):** SQ-013 decided THEME-ONLY visual parity, but `.specclaw/ui/` grounding artifacts (`ui-inventory.md`, `design-tokens.json`, `screens/`, `ui-manifest.json`) don't exist — `/specclaw:bf-ui` has not run. This change proceeds on functional, theme-consistent (Bootstrap 5) styling only, matching the same accepted gap in change `002-customer-management` (its NFR-3).
- **NFR-3:** Adding a "Users" link to `resources/views/partials/admin-nav.blade.php` is explicitly out of scope for this change (see Scope, proposal.md's Out of Scope).

## Acceptance Criteria

Each criterion must pass for the change to be considered complete.

- **AC-1:** `GET /users` (admin session) renders every user's `name`, `username`, and a distinguishable Admin/Staff label for `type`.
- **AC-2:** `POST /users` (admin session) with valid, unique data creates the user; submitting a duplicate `username` is rejected with a validation error and no row is inserted.
- **AC-3:** `POST /users` (authenticated, non-admin `type=2` session) is rejected with HTTP 403; no user is created.
- **AC-4:** `GET /users/{user}/edit` renders the Password field blank — the response never contains the user's `id`, current password hash, or any other value in that field.
- **AC-5:** `PUT /users/{user}` (admin session) submitted with a blank password leaves the stored password hash unchanged (verifiable via `Hash::check` against the original password); submitted with a new password, the new password verifies via `Hash::check` and the old one no longer does.
- **AC-6:** `PUT /users/{user}` (authenticated, non-admin session) is rejected with HTTP 403; no field on the target user changes.
- **AC-7:** `DELETE /users/{user}` (admin session) actually removes the row (`assertDatabaseMissing`).
- **AC-8:** `DELETE /users/{user}` (authenticated, non-admin session) is rejected with HTTP 403; the row remains (`assertDatabaseHas`).
- **AC-9:** `POST`/`PUT`/`DELETE` to any of the above routes without a CSRF token, under a production-profile test env, is rejected with HTTP 419 before any handler logic runs; no data changes.

## Edge Cases

- **Duplicate username on create:** rejected by validation (backed by the schema's own `unique` constraint) — no insert occurs. Covered by AC-2.
- **Editing a user's own account, including changing one's own `type` from Admin to Staff, or deleting the sole remaining admin account:** no rule in `domain-model.md` restricts either — none is invented here (Rule 1: assumptions stated explicitly rather than picked silently). If the user wants a "last admin" or "can't demote self" guard, that is new scope beyond this backlog item's documented acceptance basis and should be raised as its own decision.
- **Blank password on edit submitted alongside other changed fields (name/username/type):** only the password is left unchanged; `name`/`username`/`type` still update normally (FR-3).

## Dependencies

- **BL-001 — CSRF Protection Baseline:** built (`001-csrf-baseline`, PASS). Reused as-is via `@csrf` on every form.
- **BL-011 — Admin Front Controller Routing & Session Access Gate:** built (`007-admin-authentication`, PASS). The `users.*` routes are added inside the existing `auth`-middleware route group.
- **BL-012 — User List:** implemented first, within this same change, before BL-013/BL-014 — satisfies the same-module ordering `rebuild-backlog.md` records ("MOD-002... Depends on: MOD-001").

## Notes

- The two open questions raised at proposal time were resolved as: (1) proceed without running `/specclaw:bf-ui` first — functional/theme-consistent styling only (NFR-2); (2) the "Users" nav-link addition (DR-012's other half) stays a separate follow-up, not bundled here (NFR-3).
- No `## Bypassed Dependencies`, `## Item Split`, or `## Resumed From Split` sections — `proposal.md` carries none of those; every acceptance criterion above is a plain, unlabelled real-behavior check.
