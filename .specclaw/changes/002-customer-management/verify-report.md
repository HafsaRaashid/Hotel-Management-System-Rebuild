# Verification Report: 002-customer-management

**Verified:** 2026-09-14
**Model:** claude-sonnet-5
**Verdict:** PASS

## Acceptance Criteria

### AC-1 (BL-006)
Quotes:
- Spec: "GET the customer list route returns all seeded customers' `name`, `mail`, `phone`, `address`, `customer_id`, `charges` values, visible in the response body."
- Code (`resources/views/customers/index.blade.php` lines 34-39): `<td>{{ $customer->customer_id }}</td> ... <td>{{ $customer->name }}</td> ... <td>{{ $customer->mail }}</td> ... <td>{{ $customer->phone }}</td> ... <td>{{ $customer->address }}</td> ... <td>{{ $customer->charges }}</td>`
- Code (`app/Http/Controllers/CustomerController.php` lines 12-17): `public function index(): View { return view('customers.index', ['customers' => Customer::all()]); }`
- Test: `test_list_shows_all_customer_fields` — `$response->assertSee($customer->customer_id); ... assertSee('Jane Doe'); ... assertSee('jane@example.com'); ... assertSee('5551234567'); ... assertSee('123 Main St'); ... assertSee('42');` — PASS in test output ("✓ list shows all customer fields")

- ✅ **AC-1:** GET the customer list route returns all seeded customers' fields visible in the response body — index view renders all six fields for every customer; test confirms all fields visible.

### AC-2 (BL-007, DR-004)
Quotes:
- Spec: "Creating a customer assigns a `customer_id` in `[0, 99999999]`; when an existing `customer_id` is seeded to collide, the handler regenerates until unique."
- Code (`app/Models/Customer.php` lines 21-28): `do { $candidate = random_int(0, 99999999); } while (static::where('customer_id', $candidate)->exists()); return $candidate;`
- Test: `test_create_assigns_unique_customer_ids_across_many_creates` — creates 50 customers, asserts `assertGreaterThanOrEqual(0, $id)`, `assertLessThanOrEqual(99999999, $id)`, `assertCount(50, array_unique($ids), ...)` — PASS ("✓ create assigns unique customer ids across many creates")

- ✅ **AC-2:** customer_id assigned in [0, 99999999], regenerated until unique via `do/while` loop querying existing rows; test proves uniqueness across 50 creates and bound compliance.

### AC-3 (BL-007, DR-005)
Quotes:
- Spec: "Creating a customer whose `phone` matches an existing customer's `phone` does not insert a second row."
- Code (`CustomerController.php` lines 28-33): `if (Customer::where('phone', $validated['phone'])->exists()) { return redirect()->route('customers.create')->withInput()->with('error', ...); }`
- Test: `test_create_skips_insert_when_phone_already_exists` — `assertSame(1, Customer::count(), 'No second row should be inserted for a duplicate phone.')` — PASS ("✓ create skips insert when phone already exists")

- ✅ **AC-3:** Duplicate-phone submission is intercepted before insert and redirected with an error; test confirms count stays at 1.

### AC-4 (BL-007, CQ-012)
Quotes:
- Spec: "Submitting a non-numeric or out-of-length `phone` value is rejected with a validation error; a well-formed one is accepted."
- Code (`CustomerController.php` line 82): `'phone' => ['required', 'regex:/^[0-9]{7,15}$/'],`
- Test: `test_create_rejects_invalid_phone_format` — posts `phone => 'not-a-phone'`, asserts `assertSessionHasErrors('phone')` and `assertSame(0, Customer::count())` — PASS; `test_create_accepts_valid_phone` — posts `phone => '5559998888'`, asserts redirect and `assertSame(1, Customer::count())` — PASS

- ✅ **AC-4:** Regex `^[0-9]{7,15}$` rejects non-numeric/out-of-length phone and accepts well-formed 7-15 digit phone; both directions covered by passing tests.

### AC-5 (BL-007)
Quotes:
- Spec: "Editing an existing customer updates `name`/`mail`/`phone`/`address` without changing its `customer_id`."
- Code (`CustomerController.php` lines 48-53): `public function update(Request $request, Customer $customer): RedirectResponse { $customer->update($this->validated($request)); return redirect()->route('customers.index'); }` — `validated()` only returns name/mail/phone/address keys (lines 76-84), never customer_id.
- Test: `test_edit_updates_fields_without_changing_customer_id` — asserts `name`, `mail`, `address` updated and `assertSame($originalCustomerId, $customer->customer_id)` — PASS ("✓ edit updates fields without changing customer id")

- ✅ **AC-5:** `update()` only writes the four editable fields (validated() excludes customer_id from its rule set), so customer_id is structurally unchangeable via this path; test confirms.
  - ⚠️ Edge case: update() does not re-check phone-uniqueness against other customers (spec explicitly scopes DR-005's dedup check to create only, per the controller's own docblock comment, lines 70-73) — this is a documented, intentional scope limit, not a defect.

### AC-6 (BL-008, CQ-021)
Quotes:
- Spec: "Deleting a customer removes its row from the `customers` table (not a silent no-op)."
- Code (`CustomerController.php` lines 63-68): `public function destroy(Customer $customer): RedirectResponse { $customer->delete(); return redirect()->route('customers.index'); }`
- Test: `test_delete_removes_customer` — `assertDatabaseMissing('customers', ['id' => $customer->id])` — PASS ("✓ delete removes customer")

- ✅ **AC-6:** `destroy()` calls Eloquent `$customer->delete()`, an actual row deletion (fixing the legacy bind_param no-op); test confirms row is gone from the table.

### AC-7 (BL-008, deferred scope)
Quotes:
- Spec: "No code path in this change queries a `booking`/`bookings` table or references a `Booking` model."
- Code: `grep -i booking app/` matches only `app/Http/Controllers/CustomerController.php`, and inspection shows the only occurrences are inside docblock comments (lines 56-61, 70-73) explaining the deferral — no `Booking` class reference, no `booking` table query anywhere in executable code.
- Test: `test_controller_source_contains_no_booking_reference` — strips comments via `token_get_all()`/`T_COMMENT`/`T_DOC_COMMENT` filtering, then `assertStringNotContainsStringIgnoringCase('booking', $codeOnly, ...)` — PASS ("✓ controller source contains no booking reference")

- ✅ **AC-7:** The word "booking" appears only inside comments explaining the deferral; the dedicated test strips comments before asserting absence, directly verifying no live code path references booking.

### AC-8 (FR-7)
Quotes:
- Spec: "POSTing to the create, edit, or delete routes without a valid CSRF token is rejected with HTTP 419, before any handler logic runs."
- Code (views): `@csrf` present in `create.blade.php` line 24, `edit.blade.php` line 30, and the delete form in `index.blade.php` line 43.
- Test: `test_store_without_csrf_token_is_rejected_before_handler_runs` — `$this->app['env'] = 'production'; ... assertStatus(419); ... assertSame(0, Customer::count())` — PASS; `test_destroy_without_csrf_token_is_rejected_before_handler_runs` — `assertStatus(419); ... assertDatabaseHas('customers', ['id' => $customer->id])` — PASS
- Also covered by `CsrfProtectionTest`: "✓ post without token is rejected before handler runs", "✓ post with invalid token is rejected before handler runs", "✓ post with valid token reaches handler" (established in prerequisite change 001-csrf-baseline, which this change's routes inherit via the global `VerifyCsrfToken` middleware — no route-specific CSRF exemption exists in `routes/web.php`).

- ✅ **AC-8:** Both store and destroy are proven to return 419 and leave the database unmodified when CSRF token is absent (in production env, bypassing the testing-env CSRF exemption); no edit-route-specific CSRF test exists but `update()` shares the same global middleware and `@csrf`/`@method('PUT')` directives are present in the edit form.
  - ⚠️ Edge case: no dedicated test asserts 419 specifically for the `update` (edit/PUT) route — coverage is via `store` and `destroy` plus the general `CsrfProtectionTest`, not an update-specific case. This is a minor test-coverage gap, not an AC failure, since the CSRF middleware is applied globally and not route-specific.

## Test Results

```
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true                                                    0.55s

   PASS  Tests\Feature\CsrfProtectionTest
  ✓ post without token is rejected before handler runs                  12.05s
  ✓ post with invalid token is rejected before handler runs              0.44s
  ✓ post with valid token reaches handler                                0.45s

   PASS  Tests\Feature\CustomerManagementTest
  ✓ list shows all customer fields                                       5.72s
  ✓ create assigns unique customer ids across many creates               1.48s
  ✓ create skips insert when phone already exists                        0.50s
  ✓ create rejects invalid phone format                                  0.41s
  ✓ create accepts valid phone                                           0.37s
  ✓ edit updates fields without changing customer id                     0.53s
  ✓ delete removes customer                                              0.43s
  ✓ controller source contains no booking reference                      0.38s
  ✓ store without csrf token is rejected before handler runs             0.46s
  ✓ destroy without csrf token is rejected before handler runs           0.58s

   PASS  Tests\Feature\HealthCheckTest
  ✓ the health check endpoint answers                                    0.45s
  ✓ the shell route renders                                              0.41s

  Tests:    16 passed (343 assertions)
  Duration: 31.54s
```

All tests pass, including all 10 tests in `CustomerManagementTest` covering every acceptance criterion. This output reflects the run made after the line-ending normalization fix (commit `b5af764`), so it is current.

## Issues Found

1. **No dedicated CSRF test for the `update` (PUT) route** — `AC-8` names create, edit, and delete routes explicitly, but `CustomerManagementTest` only has CSRF-specific tests for `store` and `destroy`; `update`'s CSRF enforcement is only indirectly covered by the shared global middleware and the generic `CsrfProtectionTest`. **Fix:** add a `test_update_without_csrf_token_is_rejected_before_handler_runs` test mirroring the existing store/destroy CSRF tests, for symmetry and explicit coverage (low priority — the global `VerifyCsrfToken` middleware with no route exemption makes this effectively already proven, but explicit test coverage would close the gap).
2. **Lint failures on 4 files** — confirmed via `git log --oneline --all -- bootstrap/providers.php config/auth.php config/logging.php public/index.php`, which shows only commit `e53b6f5` ("chore: track the Laravel foundation scaffold and specclaw project records") touches these files — a pre-existing scaffold commit predating both `001-csrf-baseline` and `002-customer-management`'s own work (`c38fecd` onward, `1fbaf7c` onward respectively). No commit from this change touches any of the 4 flagged files. Consistent with the spec's claim that these are pre-existing, out-of-scope, and already accepted in `001-csrf-baseline`'s PASS verdict. Not a blocking issue for this change.

## Summary

**Passed:** 8/8 criteria
**Failed:** 0/8 criteria
**Verdict:** PASS
