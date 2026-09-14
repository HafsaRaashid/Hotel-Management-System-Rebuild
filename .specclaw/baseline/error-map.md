# Error Map: Hotel-Management-System

**Date created:** 2026-09-10
**Grounded in:** the legacy application's own source -- every entry cites the
line that raises the condition it names.

<!--
  THIS FILE IS PER-PROJECT DATA. It lives in the target repo at
  .specclaw/baseline/error-map.md and belongs to that project alone. The
  specclaw plugin ships this skeleton and nothing else -- no code tables, no
  framework exception names, no mapping between them. If any specclaw command
  ever READS OR MATCHES ON a specific code or exception name, that is a bug:
  the plugin's only interest in this file is that a heading exists for each
  code a fixture asserts. (Prose elsewhere may name a code to illustrate the
  format; an illustration a command never reads is documentation, a lookup a
  command consults is a dictionary. Only the second is forbidden.)

  APPEND-AND-AMEND, NEVER REGENERATE. Codes are permanent once assigned, on
  the same terms as GM-/DR-/CQ-/BL- ids: a code already cited by a captured
  fixture must keep meaning exactly what it meant at capture time. A new
  condition gets a new code appended; an existing entry is only ever amended
  to fill in its Rebuild source or to correct a citation.

  ASK, DON'T GUESS. An error neither agent can confidently map does NOT get a
  plausible-looking code. The agent appends a pending question
  (.specclaw/analysis/pending-questions.md, triggers T2/T3/T4), leaves
  error_code null on the affected fixture or actual result, and marks the
  scenario PROVISIONAL.
-->

## Codes

### USERNAME_REQUIRED

- **Condition:** Login was submitted with an empty (or whitespace-only,
  post-`trim()`) username. Checked strictly before password, per the
  `if`/`else if` order.
- **Legacy source:** `admin/xuli_login.php:15-17`
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** not an exception -- a `header("Location:
  login.php?error=Username is required")` redirect followed by `exit()`.
  No throw anywhere on this path.
- **Pinned by:** GM-001

### PASSWORD_REQUIRED

- **Condition:** Login was submitted with a non-empty username but an empty
  (or whitespace-only) password.
- **Legacy source:** `admin/xuli_login.php:18-20`
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** not an exception -- `header("Location:
  login.php?error=Password is required")` followed by `exit()`.
- **Pinned by:** GM-002

### MISSING_CREDENTIALS

- **Condition:** The login POST body carried neither a `username` nor a
  `password` key at all (distinct from `USERNAME_REQUIRED`/
  `PASSWORD_REQUIRED`, where the keys are present but empty after
  validation). The outer `if (isset($username) && isset($password))` guard
  is false, so validation never runs and no `?error=` message is ever
  attached.
- **Legacy source:** `admin/xuli_login.php:42-44`
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** not an exception -- a bare `header("Location:
  login.php")` (no query string) followed by `exit()`.
- **Pinned by:** GM-032

### INCORRECT_CREDENTIALS

- **Condition:** The submitted username/password did not resolve to exactly
  one matching `users` row under the app's own equality rule: the SQL query
  must return exactly one row (`mysqli_num_rows($result) === 1`), and the
  fetched row must then also satisfy a case-sensitive PHP `===` recheck
  against both `username` and `password`. This single code covers three
  distinguishable legacy paths that all reach the identical `header()`
  call with the identical message, so the app itself draws no distinction
  between them:
  1. No row matches at all (wrong password for an existing username).
  2. More than one row matches an ambiguous username+password pair (no
     `UNIQUE` constraint on `users.username` -- see PQ-007/CQ-019).
  3. Exactly one row matches the SQL's own comparison, but the row fails
     the stricter, case-sensitive `===` recheck (see PQ-008/CQ-020, the
     collation-vs.-recheck case-sensitivity conflict).
- **Legacy source:** `admin/xuli_login.php:32-34` (recheck failure branch)
  and `admin/xuli_login.php:36-38` (num_rows !== 1 branch) -- both branches
  raise the identical redirect, which is exactly why one code, not three,
  is correct here: the source itself draws no distinction, so inventing
  three separate codes would assert a distinction the legacy app never
  makes.
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** not an exception -- `header("Location:
  login.php?error=Incorrect username or password")` followed by `exit()`.
- **Pinned by:** GM-004, GM-005, GM-031

### DELETE_STATEMENT_PARAM_COUNT_MISMATCH

- **Condition:** Every `delete_*.php` handler builds its DELETE statement by
  concatenating the id directly into the SQL string (no `?` placeholder),
  then still calls `bind_param('i', $id)` on the resulting zero-parameter
  prepared statement. Actually discovered by running this harness against a
  real PHP 8.1 interpreter (not from reading the source alone): `bind_param()`
  throws `ArgumentCountError: The number of variables must match the number
  of parameters in the prepared statement` the instant it runs, so `execute()`
  is never reached and the row is **never deleted** -- the opposite of what a
  source-only reading of "an unconditional `DELETE`" would suggest. This
  affects `admin/delete_room.php`, `admin/delete_customer.php`, and
  `admin/delete_user.php` identically; no scenario in this harness exercises
  `admin/xulideleteroom.php`, which is a distinct file with its own (correct,
  placeholder-based) DELETE and is not part of this scenario corpus.
- **Legacy source:** `admin/delete_room.php:8-9`, `admin/delete_customer.php:7-8`,
  `admin/delete_user.php:8-9`
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** `ArgumentCountError: The number of variables must
  match the number of parameters in the prepared statement`
- **Pinned by:** GM-027, GM-034, GM-035

### CHECKIN_CUSTOMER_INSERT_ON_CLOSED_CONNECTION

- **Condition:** `admin/xulicheckin.php` closes its own `$con` mysqli
  connection (`$con->close()`) immediately after the room-status UPDATE,
  then -- only on the branch where the submitted phone number is genuinely
  new (`!mysqli_num_rows($checked)`) -- tries to `$con->prepare(...)` again
  on that already-closed connection to insert the new `customers` row.
  Actually discovered by running this harness against a real PHP 8.1
  interpreter: this throws `Error: mysqli object is already closed`,
  uncaught, so the walk-in check-in handler crashes before reaching its own
  final `header()` redirect whenever it needs to create a new customer --
  the common case for a first-time walk-in guest. The booking INSERT and
  the room-status UPDATE both run (and succeed) *before* this point in the
  file, so those two writes are unaffected; only the customer-creation step
  is lost.
- **Legacy source:** `admin/xulicheckin.php:69` (`$con->close()`),
  `admin/xulicheckin.php:73` (the closed-connection reuse)
- **Rebuild source:** not yet mapped
- **Raised as (legacy):** `Error: mysqli object is already closed`
- **Pinned by:** GM-015, GM-017

## Unmapped Conditions

None -- every error condition exercised by this harness's 35 scenarios is
mapped above. One error path exists in the source but is never exercised by
any designed scenario, so it is noted here for completeness rather than
given a code: every `xuli*.php`/`delete_*.php` handler's
`die('Connection Failed: ' . $con->connect_error)` branch (e.g.
`admin/xulibooking.php:6`), reached only when the hardcoded
`localhost`/`root`/`` (empty password) `/myhotel` connection itself fails.
No scenario arranges a failed DB connection (doing so would test the
harness's own environment, not a business rule), so no fixture ever needs
this code; if a future scenario does, it must get one here before it can
record a non-null `error_code` for it.
