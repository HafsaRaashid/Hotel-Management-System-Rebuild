# Tasks: MOD-002 — User Account Management

**Change:** 009-user-account-management
**Created:** 2026-09-14
**Total Tasks:** 6

## Summary

3 waves: backend (controller + routes), views, tests. Mirrors the existing `CustomerController`/`customers.*` shape. No dependency bypass, no item split — every task builds real behavior against the already-built `User` model.

## Tasks

### Wave 1 — Controller & Routes

- [x] `T1` — Add `User::TYPE_ADMIN`/`User::TYPE_STAFF` constants
  - Files: `app/Models/User.php`
  - Estimate: small
  - Kind: impl
  - Notes: Mirrors `Booking::STATUS_*` convention. No other change to the model.

- [x] `T2` — Create `UserController` (index/create/store/edit/update/destroy)
  - Files: `app/Http/Controllers/UserController.php`
  - Estimate: medium
  - Kind: impl
  - Depends: T1
  - Notes: Per design.md — private `ensureAdmin()` guard (`abort_unless($request->user()->type === User::TYPE_ADMIN, 403)`) called first in `store`/`update`/`destroy` only, not `index`/`create`/`edit`. Validation: `password` `required|min:8` on create, `nullable|min:8` on update (strip from payload when blank so the existing hash is untouched). `username` unique (ignoring the current user on update). `type` `required|in:1,2`.

- [x] `T3` — Register `users` resource routes
  - Files: `routes/web.php`
  - Estimate: small
  - Kind: impl
  - Depends: T2
  - Notes: `Route::resource('users', UserController::class)->except(['show'])` inside the existing `auth` group, with a `MOD-002 - User Account Management (BL-012/BL-013/BL-014)` comment matching the file's existing block-comment style.

### Wave 2 — Views

- [x] `T4` — Create `users/index.blade.php`, `users/create.blade.php`, `users/edit.blade.php`
  - Files: `resources/views/users/index.blade.php`, `resources/views/users/create.blade.php`, `resources/views/users/edit.blade.php`
  - Estimate: medium
  - Kind: impl
  - Depends: T3
  - Notes: Mirror `resources/views/customers/*.blade.php` structure/markup exactly (layout, `@include('partials.admin-nav')`, Bootstrap 5 classes, `@csrf`/`@method`, error list, `old()` binding). Edit form's password field must NOT bind `old('password')` to any stored value — always render blank (DR-013). Type field is a `<select>` with Admin(1)/Staff(2) options. List table shows Name/Username/Type (render type as the word "Admin"/"Staff", not the raw integer).

### Wave 3 — Tests

- [x] `T5` — Write `tests/Feature/UserManagementTest.php`
  - Files: `tests/Feature/UserManagementTest.php`
  - Estimate: medium
  - Kind: test
  - Depends: T4
  - Notes: Cover AC-1 through AC-8 from spec.md. `setUp()` `actingAs(User::first())` (seeded admin), mirroring `CustomerManagementTest`'s pattern. For the non-admin-rejection tests (AC-3/AC-6/AC-8), create and `actingAs` a `type => 2` user. For AC-5 (password), assert via `Hash::check()` against the refreshed user, not by inspecting the raw column.

- [x] `T6` — Add CSRF-rejection tests for `users.store`/`update`/`destroy`
  - Files: `tests/Feature/UserManagementTest.php`
  - Estimate: small
  - Kind: test
  - Depends: T5
  - Notes: AC-9. Mirror `CustomerManagementTest::test_store_without_csrf_token_is_rejected_before_handler_runs` / `test_destroy_without_csrf_token_is_rejected_before_handler_runs` — set `$this->app['env'] = 'production'`, assert HTTP 419, assert no data changed.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
