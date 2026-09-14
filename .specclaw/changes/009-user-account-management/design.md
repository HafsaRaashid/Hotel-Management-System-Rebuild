# Design: MOD-002 — User Account Management

**Change:** 009-user-account-management
**Created:** 2026-09-14

## Technical Approach

Add one resource-style `UserController` (`index`, `create`, `store`, `edit`, `update`, `destroy`) registered under the existing `auth` middleware group in `routes/web.php`, exactly mirroring the pattern already established by `CustomerController`/`RoomCategoryController`. Reuses `App\Models\User` and its migration verbatim from `007-admin-authentication` — no schema or model changes.

The DR-012 handler-side admin-only check (CQ-003) is a small private guard called at the top of `store`, `update`, and `destroy` only — matching `rebuild-backlog.md`'s explicit scoping, which puts no server-side gate on `index` (BL-012) beyond the pre-existing `auth` middleware.

## Architecture

No new architectural component — this is one more admin CRUD controller/view set added to the existing Laravel MVC admin back office, following the identical shape as `MOD-005` (Customer Management) and `MOD-003` (Room & Rate Management).

## Grounding sources

- `app/Http/Controllers/CustomerController.php` — structural template for index/create/store/edit/update/destroy, private validation-helper style, and the `redirect()->route(...)->with('error', ...)` pattern for rejections.
- `resources/views/customers/index.blade.php`, `create.blade.php`, `edit.blade.php` — Blade structure (`@extends('layouts.app')`, `@include('partials.admin-nav')`, Bootstrap 5 markup, `old()` binding, disabled/read-only field pattern).
- `app/Models/User.php` — confirms `password` already casts to `'hashed'` (Laravel auto-hashes on assignment) and `username` is schema-`unique` — no extra hashing or uniqueness code needed beyond validation rules.
- `routes/web.php` — the `auth`-middleware group every admin resource route is added inside; comment convention (`/* MOD-### - ... (BL-###). */`) preceding each block.

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/UserController.php` | Create | `index`/`create`/`store`/`edit`/`update`/`destroy`; private `ensureAdmin()` guard on store/update/destroy; private `validated()` helper |
| `routes/web.php` | Modify | Add `Route::resource('users', UserController::class)->except(['show'])` inside the existing `auth` group, with a `MOD-002 - User Account Management (BL-012/BL-013/BL-014)` comment matching the file's existing block style |
| `resources/views/users/index.blade.php` | Create | List table: Name, Username, Type (Admin/Staff), Edit/Delete actions |
| `resources/views/users/create.blade.php` | Create | Create form: name, username, password, type (select) |
| `resources/views/users/edit.blade.php` | Create | Edit form: password field always blank; name/username/type pre-filled via `old(..., $user->x)` |
| `tests/Feature/UserManagementTest.php` | Create | Feature tests for AC-1 through AC-9 |

## Data Model Changes

None. Reuses the `users` table/migration and `App\Models\User` created by `007-admin-authentication` exactly as-is.

## API Changes

New routes, all inside the existing `Route::middleware('auth')->group(...)` block in `routes/web.php`:

| Method | URI | Action | Name |
|---|---|---|---|
| GET | `/users` | index | `users.index` |
| GET | `/users/create` | create | `users.create` |
| POST | `/users` | store | `users.store` |
| GET | `/users/{user}/edit` | edit | `users.edit` |
| PUT/PATCH | `/users/{user}` | update | `users.update` |
| DELETE | `/users/{user}` | destroy | `users.destroy` |

## Key Decisions

- **Admin-only check as a private controller method, not a Policy/Middleware/Gate.** Only three actions in one controller need it (store/update/destroy), and no second consumer exists yet — introducing a `Policy`/route-middleware layer now would be speculative (Rule 2: simplicity first). The check: `abort_unless($request->user()->type === User::TYPE_ADMIN, 403)`, added as a small `private function ensureAdmin(Request $request): void` called first in each of those three actions. If a second module later needs the same check, it's a one-line extraction into a Gate at that point — not before.
- **`User::TYPE_ADMIN = 1` / `User::TYPE_STAFF = 2` constants added to the model**, mirroring the existing `Booking::STATUS_*` convention, so the controller/tests/views never hard-code the magic numbers `1`/`2`.
- **Password handling on edit:** the `update()` validation rule for `password` is `nullable|min:8`; when the submitted value is blank/absent, it is excluded from the update payload entirely (so the existing hash is untouched) rather than written as an empty string. On `create`, `password` is `required|min:8`. Both rely on the model's existing `hashed` cast to store the hash — no manual `Hash::make` call needed anywhere in this controller.
- **List view (`index`) carries no admin-only gate.** `rebuild-backlog.md`'s BL-012 entry explicitly scopes DR-012's enforcement to the create/update/delete handlers only ("the visibility half is BL-011's concern; this item is the list rendering itself"). Adding a gate here would be scope not asked for by the backlog item.
- **No "Users" nav link added.** Confirmed out of scope in `proposal.md`; the screen is reachable at `/users` by direct navigation only until a follow-up change adds the link.
- **No self-deletion/self-demotion guard.** `domain-model.md` documents no such rule for `User` ("carries no relationship edge to any other entity"); inventing one would be unrequested scope.

## Risks & Mitigations

- **Risk:** a future module might need the same admin-only check, and a second copy-pasted `ensureAdmin()` would drift. **Mitigation:** the guard is a single small private method with one obvious extraction point (a `Gate::define('admin-only', ...)`) — deferred until there's a second real caller, per Rule 2.
- **Risk:** leaving the "Users" screen off the nav means it's reachable only by typing `/users` directly. **Mitigation:** this was an explicit, documented scope decision (proposal.md), not an oversight; the security-relevant fix (server-side admin check) ships regardless of nav visibility.
- **Risk:** no UI-fidelity grounding artifacts exist (`/specclaw:bf-ui` not run), so visual parity with any target design system can't be verified. **Mitigation:** documented as an accepted, pre-existing gap (NFR-2), consistent with change `002-customer-management`'s own precedent — not something this change is expected to close.
