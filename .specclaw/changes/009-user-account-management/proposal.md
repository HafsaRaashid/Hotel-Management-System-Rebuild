# Proposal: MOD-002 — User Account Management

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

MOD-002 (User Account Management) is the last unbuilt module that owns a live admin screen. The legacy app lets an authenticated session create, edit, and delete staff/admin login accounts (`admin/users.php`, `admin/manage_user.php`, `admin/xuliuser.php`, `admin/delete_user.php`), but with two defects the rebuild must not carry forward:

- **DR-012 — Admin-only "Users" access is enforced only in the UI, not on the server.** `admin/sidebar.php` hides the nav link for non-admin (`login_type != 1`) sessions, but the create/update/delete handlers themselves (`admin/xuliuser.php`, `admin/delete_user.php`) run no equivalent check — any authenticated session, staff or admin, can reach them directly. Decided by **CQ-003**: add a real server-side admin-only (`type == 1`) check to both handlers.
- **DR-013 — The edit-user form's Password field pre-fills the user's `id`, not their password.** `admin/manage_user.php:24` is a copy-paste defect. Decided by **CQ-006**: leave the field blank on edit instead.

Change `007-admin-authentication` already built the `User` model/migration (needed for login) and the front-controller session gate (BL-011), but explicitly deferred all of MOD-002's own screens. Until this module exists, there is no way for an admin to manage staff accounts in the rebuilt system at all.

## Proposed Solution

Implement the three MOD-002 backlog items as one cohesive module, in dependency order within this same change (List → Create/Edit → Delete), reusing the `User` Eloquent model and migration from `007-admin-authentication`:

- **BL-012 — User List:** admin-only route/view rendering every account's `name`, `username`, and `type` (Admin/Staff), reachable only through an authenticated session (BL-011's `auth` middleware).
- **BL-013 — User Create/Edit:** create/edit form (`name`, `username`, `password`, `type`). Password field renders **blank on edit** (DR-013 fix, CQ-006) — never pre-filled with the id or any other value. Any password written on create goes through `Hash::make` (per CQ-002, already established by `007-admin-authentication`'s auth build). Both create and update actions add a **server-side `type == 1` (admin-only) check** (DR-012 fix, CQ-003) — a non-admin session gets rejected, not just hidden from nav.
- **BL-014 — User Delete:** delete action, same server-side admin-only check (DR-012/CQ-003) applied to the delete handler. Per CQ-021, delete must actually execute (no bind_param-style no-op). User carries no relationship edge to any other entity (domain-model.md), so no referential-integrity guard is needed here (unlike Room/Customer delete under CQ-024).

All three routes sit behind the existing `auth` middleware group from BL-011, and all state-changing forms use the CSRF baseline from BL-001 (both already built and confirmed `ok-built` by the dependency check).

Once this ships, `007-admin-authentication`'s deferred DR-012 nav-visibility half becomes unblockable: a follow-up (or this change, if the user prefers) can add the "Users" link to `admin-nav.blade.php` gated on `type == 1`, since there is now a screen to link to.

## Scope

### In Scope
- User list screen (BL-012).
- User create/edit form, with the DR-013 blank-password-on-edit fix and the DR-012 server-side admin-only check on create/update (BL-013).
- User delete action, with the DR-012 server-side admin-only check and a real (non-no-op) delete (BL-014).
- Server-side `type == 1` authorization check on all three write-adjacent handlers (create, update, delete) — the handler half of DR-012.

### Out of Scope
- Adding the "Users" nav link to `admin-nav.blade.php` (DR-012's nav-visibility half) — left to a follow-up change or a deliberate add-on here if the user wants it bundled; not required for this module's own acceptance basis.
- Any change to the `User` model/migration itself — already built in `007-admin-authentication`, reused as-is.
- UI visual fidelity capture — SQ-013 decided THEME-ONLY, but `.specclaw/ui/` grounding artifacts (ui-inventory.md, design-tokens.json, screens/, ui-manifest.json) are absent; see Open Questions.

## Impact

- **Files affected:** ~8-10 (estimated) — routes, controller(s), 2 Blade views (list, create/edit form), form requests/validation, feature tests.
- **Complexity:** small
- **Risk:** low — reuses an already-built model and auth middleware; the only behavioral changes from legacy are two already-decided defect fixes (DR-012, DR-013), both narrowly scoped.

## Open Questions

- BL-012 and BL-013 both carry an **OPEN QUESTIONS — UI fidelity** gate: SQ-013 decided THEME-ONLY visual parity, but the required grounding artifacts (`.specclaw/ui/ui-inventory.md`, `design-tokens.json`, `screens/`, `ui-manifest.json`) don't exist yet — `/specclaw:bf-ui` has not been run. Should this change proceed on functional/theme-consistent styling only, or should `/specclaw:bf-ui` run first to ground the UI fidelity gate before `/specclaw:plan`?
- Should the DR-012 nav-visibility half (adding "Users" to `admin-nav.blade.php`) be bundled into this change now that the screen will exist, or tracked as a separate small follow-up?
