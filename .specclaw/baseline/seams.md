# Baseline Seams: Hotel-Management-System

**Date generated:** 2026-09-10
**Grounded in:** .specclaw/analysis/domain-model.md, plus codebase-report.md, architecture.md, functional-spec.md, module-map.md (all present and read directly), and direct reading of the legacy source (PHP files under `homepage/` and `admin/`, and `Data/myhotel.sql`) in this run.

<!--
  A seam is a place where the legacy app's behaviour can be observed as
  input -> output without driving its UI. Every candidate must be ranked
  into exactly one of these classes, and every seam entry must declare its
  `seam_layer` — the closed enum in templates/CONTRACT.md (i).
-->

## Architectural note that shapes every ranking below

This codebase has **no separate service/business-logic layer**. Every business
rule lives directly inside a top-level, HTTP-invoked PHP script under
`admin/*.php` or `homepage/*.php` (e.g. `admin/xuli_login.php`,
`homepage/connect.php`, `admin/xulicheckout.php`) — there are no classes, and
only one trivial helper function (`validate()` inside `admin/xuli_login.php`)
anywhere in the traced business logic. Confirmed by reading every handler file
cited below in full.

Because of this, the cheapest layer at which a rule is reachable **without**
going through a real HTTP transport is: **include the PHP script directly in
a harness process, with `$_POST`/`$_GET`/`$_SESSION` populated beforehand**,
and inspect the resulting database rows (and the `header()`/`exit()` call the
script would have made, via a testing shim that intercepts them). This
bypasses routing, real socket I/O, and any web-server middleware — none of
which exists in this app anyway — while exercising the *exact* same code path
a real HTTP request would run. Per `templates/CONTRACT.md` (i)'s definition
("A service/application-layer entry point, called in-process, with its own
arrange step"), this is classified **`service`**, not `http`: no real request/
response transport is measured, only the in-process business logic, which
happens to be the same file the HTTP server would have run.

`http` (a real request issued to a running web server) is therefore never
selected as this design's primary layer for any rule — the same rule is
always reachable one layer in, at lower cost and equal fidelity, by the
in-process technique above. The one exception considered was DR-013 (see
Pure Function class below), where an even cheaper extraction exists.

## Seam Ranking

### Pure Function seams (lowest cost, highest fidelity — capture first)

- **PF-1 — Days-of-stay computation.** `floor(abs(strtotime($dateout) - strtotime($datein)) / 86400)`, identically duplicated in `admin/manage_check_out.php:15-16`, `admin/manage_booking.php:12-13`, and `admin/manage_check_in.php:12-13`. A pure function of two date strings — no clock, no persistence, no session state. Backs DR-009.
  - **Seam layer:** pure-function
- **PF-2 — Room-type name → category id mapping (direct-input form).** `homepage/connect.php:32-38`: `if ($room_type == 'Single Room') { $room_id = 1; } elseif ($room_type == "Double Room") { $room_id = 2; } else { $room_id = 3; }`. Pure function of one string input, called before any DB access in this file. Backs DR-006.
  - **Seam layer:** pure-function
- **PF-3 — Checkout amount-due formula.** `admin/manage_check_out.php:34`: `$cat['price'] * $calc_days`. A pure multiplication of a unit price and a day count; both can be supplied directly as inputs without a DB arrange step when the seam is isolated at this exact expression. Backs DR-010.
  - **Seam layer:** pure-function
- **PF-4 — Edit-user Password-field value-selection expression.** `admin/manage_user.php:24`: `isset($get['password']) ? $get['id'] : ''`. This one-line ternary can be exercised directly with a fabricated `$get` array (`['password' => ..., 'id' => ...]` or unset), without rendering the surrounding HTML form or touching the database. This is cheaper and more deterministic than driving the real GET request (which would additionally require seeding a `users` row and parsing the rendered HTML `value="..."` attribute out of the page for no added fidelity on the rule itself — the rule lives entirely in this one expression). Backs DR-013.
  - **Seam layer:** pure-function

### Stateful service boundary seams (need a DB arrange step; captured via in-process script invocation per the architectural note above)

- **SV-1 — Login credential match.** `admin/xuli_login.php` (full file, 46 lines): validates non-empty username/password, queries `users`, requires `mysqli_num_rows === 1`, then rechecks with `===`, and sets `$_SESSION` on success. Backs DR-001, DR-002.
  - **Seam layer:** service
- **SV-2 — Booking reference-number uniqueness loop.** `homepage/connect.php:6-12`; `admin/xulicheckin.php:30-36`. Backs DR-003.
  - **Seam layer:** service
- **SV-3 — Customer-id uniqueness loop.** `homepage/connect.php:13-19`; `admin/xulicheckin.php:46-53`; `admin/xulicustomer.php:6-14`. Backs DR-004.
  - **Seam layer:** service
- **SV-4 — Customer dedup-by-phone.** `homepage/connect.php:48-55`; `admin/xulicheckin.php:71-78`. Backs DR-005.
  - **Seam layer:** service
- **SV-5 — Walk-in check-in category round-trip mapping.** `admin/xulicheckin.php:6-27`: looks up the chosen room's category **name** from the DB, then re-derives a category **id** from that name string using the same PF-2-shaped mapping. Requires a `rooms`/`room_categoricals` DB arrange, unlike PF-2. Backs DR-006.
  - **Seam layer:** service
- **SV-6 — Booking → check-in conversion composite write.** `admin/xulibooking.php` (full file, 22 lines): `UPDATE booking set status=1, room=?` then `UPDATE rooms set status=1`. Backs DR-007.
  - **Seam layer:** service
- **SV-7 — Walk-in check-in composite write.** `admin/xulicheckin.php` (full file, 80 lines): booking insert with `status=1`, room-status update, customer dedup/insert. Backs DR-008.
  - **Seam layer:** service
- **SV-8 — Checkout payment write (no server-side validation).** `admin/xulicheckout.php:12-16`: `UPDATE booking set status=2, price=?` accepts `$_POST['payment']` verbatim, with no comparison anywhere in this file against a computed amount due. Backs DR-010.
  - **Seam layer:** service
- **SV-9 — Checkout composite write.** `admin/xulicheckout.php` (full file, 31 lines): the three sequential `UPDATE`s (`booking`, `customers`, `rooms`). Backs DR-011.
  - **Seam layer:** service
- **SV-10 — User-management handlers with no server-side role check.** `admin/xuliuser.php` (full file) and `admin/delete_user.php` (full file) — both read in full this run; neither references `$_SESSION['login_type']` or any other role check. Backs DR-012.
  - **Seam layer:** service

### Data/persistence boundary seams

`Data/myhotel.sql` was read in full this run: it declares **no `FOREIGN KEY` constraint anywhere** (only `PRIMARY KEY`s and `AUTO_INCREMENT`s on all five tables). There is therefore no ORM/schema-level cascade, `SetNull`, or `Restrict` delete rule to rank in this codebase — that entire sub-class is empty by direct evidence, not by omission. What *is* a real, reachable persistence-boundary behaviour is the **absence** of any such rule: every `delete_*.php` handler issues a raw, unconditional `DELETE FROM <table> where id = <concatenated $id>` (e.g. `admin/delete_room.php:8`, `admin/delete_customer.php:7`) with no existence check on dependent rows anywhere in the codebase.

- **PS-1 — Room deletion has no referential-integrity check.** `admin/delete_room.php:8`. A room referenced by `booking.room` (set only at check-in, per DR-007/DR-008) can be deleted while that booking is still active; no numbered domain-model.md rule documents this because no FK exists to violate. Grounded in `Data/myhotel.sql`'s absence of `FOREIGN KEY` clauses plus this handler's unconditional `DELETE`.
  - **Seam layer:** persistence
- **PS-2 — Customer deletion has no referential-integrity check.** `admin/delete_customer.php:7`. Since `booking`↔`customers` is matched only by `phone` at the application level (no FK column exists at all — domain-model.md's Relationships section), deleting a customer whose phone matches an active booking has no cascading effect of any kind, silently.
  - **Seam layer:** persistence

### HTTP/API boundary

Not selected as the primary layer for any rule in this design — see the architectural note above. The one HTTP-shaped surface with its own defect already tracked outside domain-model.md's numbered rules is `admin/index.php`'s unwhitelisted `?page=` dynamic include (CQ-001, a DEFECT decision already made); it is not a numbered `DR-###` business rule and is not designed into a `scenarios.md` entry here for that reason (scenarios are derived from domain-model.md's numbered rules, plus reachable cascade/coexistence findings — the router itself is neither).

## Excluded: UI Automation

The front end (both `homepage/` and the Bootstrap-modal admin CRUD screens) is
excluded from the seam taxonomy entirely, for reasons anchored in decisions
already on record in `.specclaw/analysis/clarifications.md`, not just general
policy:

- **SQ-013 (decided, THEME-ONLY):** the rebuild's layout is explicitly
  "reinterpreted for the target platform" — only the colour palette/branding
  tokens are kept. A UI-automation test recorded against the legacy jQuery/
  Bootstrap/DataTables modal-CRUD DOM structure (`codebase-report.md`'s Tech
  Stack section: unversioned vendored jQuery, Bootstrap, DataTables,
  bootstrap-datepicker, plus live CDN assets with no Subresource Integrity)
  would assert against a DOM shape the rebuild is already decided not to
  reproduce structurally.
- **SQ-006 (UI framework/library) and SQ-008 (browser/device/OS matrix)** are
  both still open/unanswered in `clarifications.md` — there is no decided
  target UI stack yet to even design a UI-level replay test against.
- No accessibility contract (ARIA roles, semantic structure) exists anywhere
  in the analyzed markup to assert against even if the layout were preserved
  (functional-spec.md's UI Inventory notes none).

Every business rule in domain-model.md is independently reachable one layer
in (pure-function or service, per the ranking above), so nothing is lost by
excluding UI — it is strictly the highest-cost, lowest-fidelity option here,
exactly as the policy predicts.

## Capture Blockers (Determinism Audit)

**Checked and clear — no clock dependency found anywhere in the audited
business-rule code paths.** A repo-wide search this run
(`grep -rniE "date\(\)|time\(\)|NOW\(\)|CURDATE|CURRENT_TIMESTAMP|CURRENT_DATE"`
excluding vendored assets) returned zero matches. Every `date()`/`strtotime()`
call found while reading the handler files operates on an explicit,
caller-supplied date string (`$datein`, `$dateout`) — never on "now." The only
`date("Y-m-d")`/`date("H:i")` calls that *do* read the system clock are
default values pre-filling a check-in-date `<input>` in the **view** files
`admin/manage_booking.php:52,56` and `admin/manage_check_in.php:86,90` — these
affect only what a blank HTML form shows before a staff member picks a date;
no `xuli*.php` write path or business-rule computation ever depends on them.
**Recommendation 1 (injectable clock) is not needed anywhere in this design.**

1. **Unguarded, unseeded random-number generation for `ref_no`/`customer_id`
   (backs DR-003, DR-004).** `rand(0, 999999999)` (`homepage/connect.php:7`,
   `admin/xulicheckin.php:31`) and `rand(0, 99999999)`
   (`homepage/connect.php:15`, `admin/xulicheckin.php:48`,
   `admin/xulicustomer.php:9`) are drawn with no injectable RNG and no seed
   control anywhere in the codebase. Unlike a clock value, this cannot be
   "pinned to a captured instant" at all — the exact numeric value drawn is
   fundamentally different on every invocation, replay included.
   - **Mitigation: Option 2 (normalise out of comparison).** Record the
     generated `ref_no`/`customer_id` value in `normalized_fields`
     (canonical paths such as `booking_ref_no`, `customer_id`) and assert
     only the rule's actual invariants as booleans, per `CONTRACT.md` (k):
     `ref_no_is_new` (not equal to any pre-seeded value), `ref_no_in_range`
     (`0 <= n <= 999999999`) — never the literal drawn value.
   - The **collision-retry branch** (the loop runs more than once) also has
     no legacy-code injection point for forcing a specific draw. Exercising
     it deterministically at capture time will require the eventual harness
     to intercept `rand()` (e.g. a namespace-scoped override, since every
     call site invokes the unqualified global `rand()`) — this is a harness
     design constraint to carry into `--harness` mode, not an ambiguity
     needing a human decision.
   - This should also be raised as a `/specclaw:bf-clarify` TARGET-GAP
     candidate about whether the rebuild should replace `rand()` with a
     collision-resistant identifier scheme — note that `clarifications.md`'s
     CQ-009/CQ-010 already cover the adjacent "is this numeric range
     meaningful" question (both answered: adopt the range as-is), so a new
     question would only need to cover the *generation mechanism* itself,
     not the range.
2. **Auto-increment primary keys (`booking.id`, `customers.id`, `rooms.id`,
   `users.id`) — `Data/myhotel.sql:204,210,216,228`.** None of the audited
   scenarios compare these across independently-seeded databases directly,
   but any future scenario that would must apply the same **Option 2**
   treatment: normalise the raw id out of comparison, or list it in
   `normalized_fields` when it is genuinely part of the returned shape (e.g.
   a newly-inserted row's id), per `CONTRACT.md` (k).
3. **Checked and clear — no unstable ordering found among the seams selected
   for scenario design.** The one multi-row query feeding a designed
   scenario, the available-rooms dropdown in `admin/manage_booking.php:31`
   and `admin/manage_check_in.php:32`, carries an explicit
   `order by id asc`. No other multi-row `SELECT` feeds any scenario in
   `scenarios.md`.

## Recommended Seam

**Primary: in-process service-layer invocation** of the existing
`xuli*.php`/`delete_*.php` handler scripts — populate `$_POST`/`$_GET`/
`$_SESSION` directly, `include` the script under a harness that shims
`header()`/`exit()`, then inspect the resulting database rows. This is the
only layer beneath the HTTP surface that exists in this codebase (see the
architectural note above), and it covers every composite/stateful business
rule (DR-001 through DR-005, DR-007, DR-008, DR-010's write path, DR-011,
DR-012).

**Supplementary: pure-function extraction** for the four isolable
calculations that need no database arrange at all — DR-006's direct-input
mapping, DR-009's days-of-stay formula, DR-010's amount-due formula, and
DR-013's password-field ternary. These are strictly cheaper and should be
captured first.

No rule in this corpus requires `http` or UI automation as its primary seam.
Persistence-boundary seams (PS-1, PS-2) are recommended specifically to pin
the current *absence* of referential-integrity enforcement, since a rebuild
that legitimately adds real foreign keys (per the already-decided CQ-011) will
diverge from these two scenarios on purpose — that divergence should show up
as a sanctioned, expected change, not a silent gap in coverage.

This recommendation needs human confirmation before harness generation
(`--harness`) proceeds.
