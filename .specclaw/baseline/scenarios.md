# Baseline Scenarios: Hotel-Management-System

**Date generated:** 2026-09-10
**Grounded in:** .specclaw/analysis/domain-model.md's numbered Business Rules, plus codebase-report.md, architecture.md, functional-spec.md, module-map.md, and direct reading of the legacy source in this run.

## Scenarios

### GM-001 — Login rejected: empty username (checked before password)

- **Seam:** SV-1 — Login credential match
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 1 (DR-001)
- **Arrange:** No `users` row needed. POST body: `username=""`, `password=""` (both present as keys, both empty after `trim`).
- **Act:** Invoke `admin/xuli_login.php` in-process.
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "username required", `threw: false`; redirect target carries `error=Username is required`; no SQL query is ever issued (proves username is checked before password, per the `if/else if` order at lines 15-18).
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-002 — Login rejected: empty password, non-empty username

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 1 (DR-001)
- **Arrange:** No `users` row needed. POST body: `username="Admin"`, `password=""`.
- **Act:** Invoke `admin/xuli_login.php` in-process.
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "password required", `threw: false`; redirect target carries `error=Password is required`.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-003 — Login succeeds with an exact username+password match

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 2 (DR-002)
- **Arrange:** Seed one `users` row: `username="Admin"`, `password="admin123"`, `type=1`.
- **Act:** Invoke `admin/xuli_login.php` with matching POST credentials.
- **Assert (shape):** `outcome: "OK"`, `error_code: null`, `threw: false`; session state set — `session_username`, `session_id`, `session_login_type` present in output and equal to the seeded row's `username`/`id`/`type`; redirect target is `index.php`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-004 — Login rejected: wrong password for an existing username

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 2 (DR-002)
- **Arrange:** Seed one `users` row: `username="Admin"`, `password="admin123"`.
- **Act:** Invoke `admin/xuli_login.php` with `username="Admin"`, `password="wrongpass"`.
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "incorrect credentials", `threw: false`; no session keys set.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-005 — Login rejected despite correct credentials: two accounts share the same username+password

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 2 (DR-002) ⚠ PROVISIONAL — pending PQ-007 (proposed default: enforce username uniqueness in the rebuild schema so this state cannot occur)
- **Arrange:** Seed two `users` rows with identical `username="Reception1"` and identical `password="reception1"` (schema has no `UNIQUE` constraint on `username` — `Data/myhotel.sql:143-149`).
- **Act:** Invoke `admin/xuli_login.php` with `username="Reception1"`, `password="reception1"`.
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "incorrect credentials" (same code as GM-004, even though the submitted credentials exactly match two rows), `threw: false` — because `mysqli_num_rows($result) === 1` fails when 2 rows match (`admin/xuli_login.php:24`).
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-006 — Booking reference number generated with no collision

- **Seam:** SV-2 — Booking reference-number uniqueness loop
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 3 (DR-003)
- **Arrange:** Empty `booking` table (or seeded with `ref_no` values guaranteed not to collide with the harness's fixed RNG stub's first draw).
- **Act:** Invoke `homepage/connect.php` with a full valid reservation POST body.
- **Assert (shape):** `outcome: "OK"`, `error_code: null`, `threw: false`; `ref_no_is_new: true`; `ref_no_in_range: true` (0–999999999); the literal `ref_no` value is listed in `normalized_fields` (`booking_ref_no`), never asserted directly, per the rand()-based non-determinism noted in seams.md.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-007 — Booking reference-number generation retries after a collision

- **Seam:** SV-2
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 3 (DR-003)
- **Arrange:** Seed one existing `booking` row with a known `ref_no`. Harness must intercept `rand()` (namespace-scoped override — see seams.md Capture Blockers) so its first draw equals the seeded `ref_no` and its second draw is a distinct value.
- **Act:** Invoke `homepage/connect.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `retry_occurred: true`; `final_ref_no_distinct_from_seeded: true` — booleans the seam itself can answer, per `CONTRACT.md` (k), never the raw regenerated value (listed in `normalized_fields` instead).
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-008 — Customer id generated via public booking with no collision

- **Seam:** SV-3 — Customer-id uniqueness loop
- **Seam layer:** service
- **Modules:** MOD-004, MOD-005
- **Business rules pinned:** rule 4 (DR-004)
- **Arrange:** Empty `customers` table (or seeded to avoid colliding with the harness's fixed first draw). New phone number not present in `customers`.
- **Act:** Invoke `homepage/connect.php` with a full valid reservation POST body.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `customer_id_is_new: true`; `customer_id_in_range: true` (0–99999999); literal value in `normalized_fields` (`customer_id`).
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-009 — Customer-id generation retries after a collision

- **Seam:** SV-3
- **Seam layer:** service
- **Modules:** MOD-004, MOD-005
- **Business rules pinned:** rule 4 (DR-004)
- **Arrange:** Seed one existing `customers` row with a known `customer_id`. Harness `rand()` override draws that value first, then a distinct value.
- **Act:** Invoke `homepage/connect.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `retry_occurred: true`; `final_customer_id_distinct_from_seeded: true`.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-010 — New phone number creates a new customer record

- **Seam:** SV-4 — Customer dedup-by-phone
- **Seam layer:** service
- **Modules:** MOD-004, MOD-005 (cross-module — DR-005 is co-owned: MOD-004 triggers the dedup from check-in, MOD-005 runs the same guard in `admin/xulicustomer.php:29`)
- **Business rules pinned:** rule 5 (DR-005)
- **Arrange:** Empty `customers` table. Reservation POST body carries a phone number not present in `customers`.
- **Act:** Invoke `homepage/connect.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `customer_row_created: true`; `existing_customer_reused: false`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-011 — Existing phone number reuses the existing customer, no duplicate created

- **Seam:** SV-4
- **Seam layer:** service
- **Modules:** MOD-004, MOD-005 (cross-module — DR-005 is co-owned: MOD-004 triggers the dedup from check-in, MOD-005 runs the same guard in `admin/xulicustomer.php:29`)
- **Business rules pinned:** rule 5 (DR-005)
- **Arrange:** Seed one `customers` row with phone `"918393892"`. Reservation POST body carries the same phone.
- **Act:** Invoke `homepage/connect.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `customer_row_created: false`; `existing_customer_reused: true`; exactly one `customers` row with that phone exists after the call.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-012 — Room type "Single Room" maps to category id 1

- **Seam:** PF-2 — Room-type name → category id mapping (direct-input form)
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 6 (DR-006) ⚠ PROVISIONAL — pending CQ-004 (decision on record: require an explicit, validated room_type selection and reject the submission otherwise — this scenario still pins the legacy mapping as it exists today)
- **Arrange:** Input string `"Single Room"`.
- **Act:** Evaluate the mapping expression (`homepage/connect.php:32-38`) directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.room_id: 1`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-013 — Room type "Double Room" maps to category id 2

- **Seam:** PF-2
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 6 (DR-006) ⚠ PROVISIONAL — pending CQ-004
- **Arrange:** Input string `"Double Room"`.
- **Act:** Evaluate the mapping expression directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.room_id: 2`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-014 — Unrecognized/unselected room type silently maps to Deluxe (3)

- **Seam:** PF-2
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 6 (DR-006) ⚠ PROVISIONAL — pending CQ-004 (decision on record: this exact behavior is the one being replaced — the rebuild should reject instead of defaulting)
- **Arrange:** Input string `"Type of Rooms"` — the public form's placeholder `<option>` text, which carries no `value` attribute (`homepage/book.php:22-27`), so an unselected submission yields this literal string.
- **Act:** Evaluate the mapping expression directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.room_id: 3` — the most expensive category, silently, with no rejection.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-015 — Walk-in check-in category round-trip mapping agrees for a legitimate Single Room choice

- **Seam:** SV-5 — Walk-in check-in category round-trip mapping
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 6 (DR-006) ⚠ PROVISIONAL — pending CQ-004
- **Arrange:** Seed `room_categoricals` (the 3 standard rows) and one `rooms` row with `category_id=1` ("Single Room"). POST body's `rid` selects that room.
- **Act:** Invoke `admin/xulicheckin.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.room_id: 1` — confirms the DB-round-trip mapping (category-id → name → re-derived id) agrees with PF-2's direct-input mapping for a legitimate selection.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-016 — Booking → check-in conversion occupies the assigned room

- **Seam:** SV-6 — Booking → check-in conversion composite write
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 7 (DR-007)
- **Arrange:** Seed one pending `booking` row (`status=0`, `room=0`) and one available `rooms` row (`status=0`).
- **Act:** Invoke `admin/xulibooking.php` with that booking's `id` and the room's `id` as `rid`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.booking_status: 1`; `output.booking_room_id` equals the chosen room's id; `output.room_status: 1`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-017 — Walk-in check-in creates an already-checked-in booking

- **Seam:** SV-7 — Walk-in check-in composite write
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 8 (DR-008)
- **Arrange:** Seed `room_categoricals` and one available `rooms` row (`status=0`). No pre-existing `booking` row is needed — this flow creates one directly.
- **Act:** Invoke `admin/xulicheckin.php` with a full walk-in POST body.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.booking_created: true`; `output.booking_status: 1` (never `0` — there is no intermediate "booked" stage for a walk-in); `output.room_status: 1`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-018 — Days-of-stay: representative multi-day stay

- **Seam:** PF-1 — Days-of-stay computation
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 9 (DR-009)
- **Arrange:** `datein="2026-01-01"`, `dateout="2026-01-04"`.
- **Act:** Evaluate the formula (`admin/manage_check_out.php:15-16`) directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.days: 3`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-019 — Days-of-stay: 0-day boundary (same calendar day, different time-of-day)

- **Seam:** PF-1
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 9 (DR-009)
- **Arrange:** `datein="2026-01-01 10:00:00"`, `dateout="2026-01-01 14:00:00"` (a 4-hour difference, floored to 0 whole days).
- **Act:** Evaluate the formula directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.days: 0` — the 0% boundary of this computed value.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-020 — Days-of-stay: reversed date order still yields a positive count via abs()

- **Seam:** PF-1
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 9 (DR-009)
- **Arrange:** `datein="2026-01-04"`, `dateout="2026-01-01"` (check-out entered earlier than check-in).
- **Act:** Evaluate the formula directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.days: 3` — identical magnitude to GM-018 despite the reversed order, because `abs()` discards sign.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-021 — Checkout amount-due formula: representative multi-day case

- **Seam:** PF-3 — Checkout amount-due formula
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 10 (DR-010) ⚠ PROVISIONAL — pending CQ-005 (decision on record: enforce server-side that payment ≥ computed amount due — this scenario pins the computation itself, which the decision does not change)
- **Arrange:** `price=99` (Single Room's seeded rate), `days=3`.
- **Act:** Evaluate the formula (`admin/manage_check_out.php:34`) directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.amount_due: 297`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-022 — Checkout amount-due formula: 0-day boundary

- **Seam:** PF-3
- **Seam layer:** pure-function
- **Modules:** MOD-004
- **Business rules pinned:** rule 10 (DR-010) ⚠ PROVISIONAL — pending CQ-005
- **Arrange:** `price=199` (Deluxe Room's seeded rate), `days=0`.
- **Act:** Evaluate the formula directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.amount_due: 0` — the 0% boundary of this computed value.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-023 — Checkout accepts an unvalidated payment below the computed amount due

- **Seam:** SV-8 — Checkout payment write (no server-side validation)
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 10 (DR-010) ⚠ PROVISIONAL — pending CQ-005 (decision on record: add server-side validation matching the client-side `min` — this scenario pins the exact legacy gap the decision closes)
- **Arrange:** Seed a checked-in `booking` row whose computed amount due (per PF-3) is `297`. POST body's `payment` is `50` — below the computed amount, and below the HTML `min` attribute that is the *only* enforcement present client-side.
- **Act:** Invoke `admin/xulicheckout.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false` — accepted with no rejection; `output.booking_price: 50` (verbatim staff input, not the computed `297`).
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-024 — Full checkout composite write: bill, accrue charges, free the room

- **Seam:** SV-9 — Checkout composite write
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 11 (DR-011)
- **Arrange:** Seed a checked-in `booking` row (`status=1`), the matching `customers` row (matched by phone, `charges=0`), and the assigned `rooms` row (`status=1`).
- **Act:** Invoke `admin/xulicheckout.php` with `payment=297`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.booking_status: 2`; `output.booking_price: 297`; `output.customer_charges: 297` (incremented from `0`, per `charges = charges + ?`); `output.room_status: 0`.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-025 — Checkout frees the room by display-name text match, not room id

- **Seam:** SV-9
- **Seam layer:** service
- **Modules:** MOD-004
- **Business rules pinned:** rule 11 (DR-011)
- **Arrange:** Seed two `rooms` rows with **different** `id` values but where the hidden `room` POST field (the room's display-name text, e.g. `"Single_101"`) is deliberately set to a value that matches one specific row's `room` text column, not the booking's numeric room id.
- **Act:** Invoke `admin/xulicheckout.php`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; exactly the `rooms` row whose `room` **text** column equals the posted value has `status: 0` afterward — proving the `UPDATE rooms set status=0 where room = ?` (`admin/xulicheckout.php:22`) matches by name text, unlike every other room-status write in the app, which matches by `id`.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-026 — Non-admin (staff) session can create/update a user account

- **Seam:** SV-10 — User-management handlers with no server-side role check
- **Seam layer:** service
- **Modules:** MOD-001, MOD-002
- **Business rules pinned:** rule 12 (DR-012) ⚠ PROVISIONAL — pending CQ-003 (decision on record: add a server-side admin-only (type==1) check — this scenario pins the exact legacy gap the decision closes)
- **Arrange:** `$_SESSION['login_type'] = 2` (staff, non-admin) — the UI would hide the "Users" nav link for this session (`admin/sidebar.php:16`), but the handler is invoked directly.
- **Act:** Invoke `admin/xuliuser.php` with a valid create-user POST body.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; a new `users` row is created despite the acting session lacking `type=1` — no role check anywhere in this handler.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-027 — Non-admin (staff) session can delete a user account

- **Seam:** SV-10
- **Seam layer:** service
- **Modules:** MOD-001, MOD-002
- **Business rules pinned:** rule 12 (DR-012) ⚠ PROVISIONAL — pending CQ-003
- **Arrange:** `$_SESSION['login_type'] = 2`. Seed a target `users` row (including, as a further edge, one with `type=1` — deleting an admin account).
- **Act:** Invoke `admin/delete_user.php` with the target's `id`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; the target `users` row is gone, regardless of the acting session's role and regardless of the target's own role.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-028 — Admin session performing the same user create/update succeeds (contrast case)

- **Seam:** SV-10
- **Seam layer:** service
- **Modules:** MOD-001, MOD-002
- **Business rules pinned:** rule 12 (DR-012) ⚠ PROVISIONAL — pending CQ-003
- **Arrange:** `$_SESSION['login_type'] = 1` (admin).
- **Act:** Invoke `admin/xuliuser.php` with a valid create-user POST body.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; a new `users` row is created — the legitimate, intended-use path, included to contrast against GM-026/GM-027's edge case (today, both sessions succeed identically, since no check distinguishes them).
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-029 — Edit-user form pre-fills the Password field with the user's id, not their password

- **Seam:** PF-4 — Edit-user Password-field value-selection expression
- **Seam layer:** pure-function
- **Modules:** MOD-002
- **Business rules pinned:** rule 13 (DR-013) ⚠ PROVISIONAL — pending CQ-006 (decision on record: leave the field blank on edit instead — this scenario pins the exact legacy defect the decision closes)
- **Arrange:** `$get = ['id' => 7, 'password' => 'reception1']` (simulating an existing user row fetched for edit).
- **Act:** Evaluate the expression (`admin/manage_user.php:24`) directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.password_field_value: "7"` — the user's `id`, not their actual password `"reception1"`.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-030 — Create-user form's Password field renders empty (contrast case)

- **Seam:** PF-4
- **Seam layer:** pure-function
- **Modules:** MOD-002
- **Business rules pinned:** rule 13 (DR-013) ⚠ PROVISIONAL — pending CQ-006
- **Arrange:** `$get` unset entirely (simulating a fresh create-user form with no `id` in the query string).
- **Act:** Evaluate the expression directly.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `output.password_field_value: ""` — confirms the `isset($get['password'])` guard is false only in the create path, contrasting GM-029's edit-path edge case.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-031 — Login rejected despite a case-insensitive DB match: case-differing credentials

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 2 (DR-002) ⚠ PROVISIONAL — pending PQ-008 (proposed default: preserve as-is — the app-level `===` recheck is what every login actually experiences)
- **Arrange:** Seed one `users` row: `username="Admin"`, `password="admin123"` (table collation `utf8mb4_vietnamese_ci`, case-insensitive — `Data/myhotel.sql:143-149`).
- **Act:** Invoke `admin/xuli_login.php` with `username="admin"`, `password="admin123"` (username differs only in case).
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "incorrect credentials", `threw: false` — even though the SQL `WHERE` clause matches this row case-insensitively (`mysqli_num_rows === 1`), the PHP `===` recheck at line 26 fails on case and rejects the login.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-032 — POST missing username/password keys entirely redirects with no error message

- **Seam:** SV-1
- **Seam layer:** service
- **Modules:** MOD-001
- **Business rules pinned:** rule 1 (DR-001)
- **Arrange:** No `users` row needed. POST body contains neither a `username` nor a `password` key at all (distinct from GM-001/GM-002, where the keys are present but empty).
- **Act:** Invoke `admin/xuli_login.php`.
- **Assert (shape):** `outcome: "REJECTED"`, `error_code` naming "missing credentials" (distinct from GM-001/GM-002's "required" codes — see `admin/xuli_login.php:42-45`'s bare `header("Location: login.php")`, no `?error=` query string at all), `threw: false`.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-033 — Direct customer creation (admin screen) generates a unique customer id via the same rule

- **Seam:** SV-3
- **Seam layer:** service
- **Modules:** MOD-004, MOD-005
- **Business rules pinned:** rule 4 (DR-004)
- **Arrange:** Empty `customers` table (or seeded to avoid colliding with the harness's fixed first draw). No `id` in the POST body (create, not edit).
- **Act:** Invoke `admin/xulicustomer.php` (the handler behind the Customer Management "create" screen, `admin/manage_customer.php`) with a valid create-customer POST body.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; `customer_id_is_new: true`; `customer_id_in_range: true` — confirms this third, independently-coded call site (`admin/xulicustomer.php:6-14`) implements DR-004 identically to the booking-triggered call sites exercised in GM-008/GM-009.
- **Kind:** boundary
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-034 — Deleting a room referenced by an active booking succeeds unconditionally

- **Seam:** PS-1 — Room deletion has no referential-integrity check
- **Seam layer:** persistence
- **Modules:** MOD-003 (derived from module-map.md's Services/routes listing for `admin/delete_room.php` — this scenario pins no domain-model.md `DR-###` rule, so there is no rule-ownership entry to derive a module tag from directly)
- **Business rules pinned:** no numbered rule — grounded in `Data/myhotel.sql`'s complete absence of `FOREIGN KEY` constraints (confirmed by reading the full schema dump this run) and `admin/delete_room.php:8`'s unconditional `DELETE`
- **Arrange:** Seed a `rooms` row (`id=6`) and a checked-in `booking` row whose `room` column equals `6`.
- **Act:** Invoke `admin/delete_room.php` with `id=6`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; the `rooms` row is gone; the `booking` row is unchanged — its `room` column still holds `6`, now an orphaned reference to a nonexistent row; no error is raised anywhere.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

### GM-035 — Deleting a customer referenced by an active (phone-matched) booking has no cascading effect

- **Seam:** PS-2 — Customer deletion has no referential-integrity check
- **Seam layer:** persistence
- **Modules:** MOD-005 (derived from module-map.md's Services/routes listing for `admin/delete_customer.php` — no domain-model.md `DR-###` rule is pinned by this scenario)
- **Business rules pinned:** no numbered rule — grounded in domain-model.md's Relationships section (no `customer_id` FK column exists anywhere in `booking`; matching is phone-only, application-level) and `admin/delete_customer.php:7`'s unconditional `DELETE`
- **Arrange:** Seed a `customers` row with phone `"918393892"` and an active `booking` row with the same phone.
- **Act:** Invoke `admin/delete_customer.php` with that customer's `id`.
- **Assert (shape):** `outcome: "OK"`, `threw: false`; the `customers` row is gone; the `booking` row is completely unchanged (its own `name`/`mail`/`phone` columns are a denormalized copy, not a reference); no error, no update to `booking`, nothing links the two after deletion beyond the now-stale phone string.
- **Kind:** edge case
- **Verifies backlog item:** not yet backlog-linked — rebuild-backlog.md does not exist yet

## No Legacy Behaviour Exists

- **A `room_categoricals` row beyond the three seeded categories (ids 1–3), or a `booking.room_id`/`rooms.category_id` value outside `{1, 2, 3}`.** No admin screen anywhere under `admin/` creates, edits, or deletes a `room_categoricals` row (confirmed by reading every file that references it — `admin/manage_room.php:32-34`, `admin/manage_check_in.php`, `homepage/book.php:24-26` are all hardcoded 3-option lists), and DR-006's mapping function only ever produces `1`, `2`, or `3` (the catch-all `else` branch hardcodes `3`, never anything else). No code path can create a fourth category or write a category id outside this set. This is also functional-spec.md's own Named Gap, now decided by CQ-007 (add a full CRUD admin screen in the rebuild) — worth a `/specclaw:bf-clarify` SCOPE cross-reference for anyone re-reading this document, though CQ-007 already covers it.
- **A `booking` row with `status=0` (pending) that already has a non-zero `room` value.** Every write path that sets `booking.room` to a specific room id (`admin/xulibooking.php:8`, `admin/xulicheckin.php:59`) sets `status=1` in the same statement. No traced code path assigns a specific room to a booking while leaving it in the pending stage.
- **A `customers.charges` value that ever decreases.** The only write to this column anywhere in the codebase is `admin/xulicheckout.php:17`'s `charges = charges + ?`. No refund, credit, or reset code path exists anywhere in the analyzed source, and `architecture.md`'s System Context confirms no external payment-processor integration exists that could trigger one.
- **A `users.type` value other than `1` or `2` reachable through the admin UI.** `admin/manage_user.php:28-30`'s User Type `<select>` offers only `value="1"` (Admin) and `value="2"` (User); `admin/xuliuser.php:7,14` writes `$_POST['type']` with no range validation, so a raw POST bypassing the form *could* set `type` to any integer — but no code anywhere branches on any value other than `== 1` (`admin/sidebar.php:16`), so a `type=3` (or any non-`1` value) is behaviorally indistinguishable from `type=2` everywhere in the app. There is no distinct legacy behaviour to capture beyond what the `type=2` scenarios (GM-026, GM-027) already pin.

## Rule Coverage Check

- **DR-001** (login requires username and password) — covered by GM-001, GM-002, GM-032.
- **DR-002** (login requires exact single-row match) — covered by GM-003, GM-004, GM-005, GM-031.
- **DR-003** (booking ref_no random and unique) — covered by GM-006, GM-007.
- **DR-004** (customer id random and unique) — covered by GM-008, GM-009, GM-033.
- **DR-005** (customer created only if no existing phone match) — covered by GM-010, GM-011.
- **DR-006** (room-type name maps to category id, defaulting to Deluxe) — covered by GM-012, GM-013, GM-014, GM-015. ⚠ PROVISIONAL (see below).
- **DR-007** (booking → check-in occupies the room) — covered by GM-016.
- **DR-008** (walk-in check-in creates an already-checked-in booking) — covered by GM-017.
- **DR-009** (days of stay = whole-day difference) — covered by GM-018, GM-019, GM-020.
- **DR-010** (checkout amount computed, but recorded charge is unvalidated) — covered by GM-021, GM-022, GM-023. ⚠ PROVISIONAL (see below).
- **DR-011** (checkout composite: bill, accrue, free room) — covered by GM-024, GM-025.
- **DR-012** (admin-only Users access enforced only in UI) — covered by GM-026, GM-027, GM-028. ⚠ PROVISIONAL (see below).
- **DR-013** (edit-user Password field shows id instead of password) — covered by GM-029, GM-030. ⚠ PROVISIONAL (see below).

All 13 of domain-model.md's numbered business rules are covered by at least one scenario. No rule was excluded from coverage.

Additional scenarios beyond the numbered rules: GM-034 and GM-035 (persistence-boundary findings — absence of referential-integrity enforcement, cited against "no numbered rule" per their own entries above).

### Provisional pending decision

- **DR-006** — GM-012, GM-013, GM-014, GM-015 — blocked on **CQ-004** (decision already on record: reject unrecognized/unselected room types instead of defaulting to Deluxe; these scenarios remain provisional because `domain-model.md` itself has not yet been regenerated to drop its own `⚠ PROVISIONAL` marker on this rule).
- **DR-010** — GM-021, GM-022, GM-023 — blocked on **CQ-005** (decision already on record: enforce server-side that payment ≥ computed amount due).
- **DR-012** — GM-026, GM-027, GM-028 — blocked on **CQ-003** (decision already on record: add a server-side admin-only check).
- **DR-013** — GM-029, GM-030 — blocked on **CQ-006** (decision already on record: leave the Password field blank on edit).
- **DR-002** — GM-005 — blocked on **PQ-007** (newly raised this run; not yet promoted or decided).
- **DR-002** — GM-031 — blocked on **PQ-008** (newly raised this run; not yet promoted or decided).

Note on CQ-003/CQ-004/CQ-005/CQ-006: each already has a recorded **Answer**/**Decided by**/**Date** in `clarifications.md` and an entry in `decisions.md`. They remain marked provisional here specifically because `domain-model.md`'s own rule text (DR-006, DR-010, DR-012, DR-013) still carries its own `⚠ PROVISIONAL` marker pending a fresh `bf-domain-analyst` run — this baseline captures the legacy app's *current, as-shipped* behaviour regardless of what the rebuild has since decided to do differently, per this agent's mandate never to substitute a decided future behaviour for what the code actually does today.
