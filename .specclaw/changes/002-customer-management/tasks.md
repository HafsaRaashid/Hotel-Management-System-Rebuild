# Tasks: Customer Management (MOD-005)

**Change:** 002-customer-management
**Created:** 2026-09-14
**Total Tasks:** 6

## Summary

Two waves. Wave 1 builds the data layer (migration + model) everything else needs. Wave 2 builds the controller, views, routes, and tests on top of it — BL-006 (List) is implemented before BL-007 (Create/Edit) before BL-008 (Delete) inside the single controller task, matching the module's own declared build order.

## Tasks

### Wave 1 — Data layer

- [x] `T1` — Customers migration
  - Files: `database/migrations/<timestamp>_create_customers_table.php` (create)
  - Estimate: small
  - Kind: migration
  - Depends: none
  - Notes: Columns per design.md's Data Model Changes table: `customer_id` (unsigned integer, unique), `name`, `mail`, `phone`, `address` (string), `charges` (unsigned integer, default 0), timestamps. No DB-level unique constraint on `phone` (see design.md's Key Decisions — DR-005's dedup is application-level only, no decision adds a DB constraint).

- [x] `T2` — Customer model
  - Files: `app/Models/Customer.php` (create)
  - Estimate: small
  - Kind: impl
  - Depends: T1
  - Notes: `$fillable` for `name`, `mail`, `phone`, `address`, `customer_id`, `charges`. Static `generateUniqueCustomerId(): int` implementing DR-004: `rand(0, 99999999)` regenerated in a loop while `static::where('customer_id', $candidate)->exists()`.

### Wave 2 — Controller, views, routes, tests

- [x] `T3` — CustomerController (index, create/store, edit/update)
  - Files: `app/Http/Controllers/CustomerController.php` (create), `resources/views/customers/index.blade.php` (create), `resources/views/customers/create.blade.php` (create), `resources/views/customers/edit.blade.php` (create)
  - Estimate: medium
  - Kind: impl
  - Depends: T2
  - Notes: `index()` — BL-006, list all customers (AC-1). `create()`/`store()` — BL-007 create half: validate `name`, `mail`, `phone` (format per spec.md NFR-2: digits only, 7-15 chars), `address`; before insert, check `Customer::where('phone', ...)->exists()` and skip insert if so (DR-005, AC-3); otherwise create with `Customer::generateUniqueCustomerId()` for `customer_id` (DR-004, AC-2). `edit()`/`update()` — BL-007 edit half: same fields, no phone-dedup re-check (design.md's Key Decisions), `customer_id` never accepted from the request (AC-5). Views: `@csrf` on both forms (AC-8), edit view displays `customer_id` read-only (never an input field).

- [x] `T4` — CustomerController destroy (BL-008)
  - Files: `app/Http/Controllers/CustomerController.php` (modify — add `destroy()`)
  - Estimate: small
  - Kind: impl
  - Depends: T3
  - Notes: `destroy($customer)` deletes the row (CQ-021 — must actually delete, AC-6). **Do not** add any check against a `booking`/`Booking` construct — CQ-024 is explicitly deferred per spec.md's Item Split (AC-7 requires this absence, not a stub).

- [x] `T5` — Routes
  - Files: `routes/web.php` (modify)
  - Estimate: small
  - Kind: config
  - Depends: T3, T4
  - Notes: `Route::resource('customers', CustomerController::class)->except(['show']);`. No `/admin` prefix (design.md's Technical Approach — that's BL-011's job, not built yet).

- [ ] `T6` — Feature tests
  - Files: `tests/Feature/CustomerManagementTest.php` (create)
  - Estimate: medium
  - Kind: test
  - Depends: T5
  - Notes: One test per AC-1 through AC-8 in spec.md. AC-8 (CSRF) follows the same `$this->app['env'] = 'production'` override pattern `tests/Feature/CsrfProtectionTest.php` (change `001-csrf-baseline`) established, since `APP_ENV=testing` bypasses `ValidateCsrfToken` by default. AC-7 (CQ-024 absence) is a static check: assert `CustomerController.php`'s source contains no reference to `Booking`/`booking` (e.g. `Str::contains` on the file's contents, or a `grep`-equivalent PHP assertion) — a genuine absence check, not a behavioral one.

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
