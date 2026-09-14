# Rebuild Backlog: Hotel-Management-System

**Path analyzed:** .
**Date generated:** 2026-09-11
**Source documents:** codebase-report.md, architecture.md, domain-model.md, functional-spec.md, module-map.md

<!--
  NOTE ON THIS COMMENT: never write a literal double-brace placeholder
  token inside this comment's own prose (not even to describe it) — the
  render step's template substitution is a dumb global string replace, and
  a token mentioned here would get overwritten by that token's rendered
  value along with the real placeholder below, corrupting this comment.
  Refer to placeholders by section name instead (e.g. "the status block
  below", "the Backlog section").

  The status block right after this comment is bash-computed, never
  agent-drafted — date, which optional inputs (decisions.md,
  clarifications.md, baseline/manifest.json, baseline/scenarios.md) were
  consumed vs. missing (with the command that produces each),
  Gate/Verification counts, and the single recommended next item to
  propose. This block, and every item's Gate:/Verification: field below,
  is recomputed from scratch on every run — never hand-maintained.

  MODULE GROUPING. The Backlog section below is two levels deep: one
  "## MOD-### — <Module Name>" heading per module from module-map.md, with
  that module's "### BL-0##" items beneath it. Modules are ordered by their
  own dependency rank from the map (foundations first), computed in bash by
  the same fixed-point pass that ranks items; a declared cycle is reported
  and no rank number is printed, because the number would be an artifact of
  the iteration cap rather than a dependency depth.

  A module is a MIGRATION/ACCEPTANCE unit — the "one flow at a time" slice a
  large legacy system is rebuilt and signed off in. BL items remain the
  BUILD units: the hierarchy is MOD-### -> BL-0## -> DR-### -> GM-###, and a
  module is NEVER collapsed into one giant BL item. Item granularity rules
  are exactly as they were — a module only groups items that already exist
  at capability-bullet granularity.

  Item order WITHIN a module is unchanged from before modules existed
  (dependency rank first — a hard constraint — then within the same rank:
  CLEAR+VERIFIABLE, then CLEAR+PENDING CAPTURE/NO BASELINE DATA/
  UNVERIFIABLE, then OPEN QUESTIONS, then BLOCKED). Two further top-level
  groups may appear after the modules: "## Unassigned — no module declared"
  (items with no **Module:** field, or one naming a MOD-### the map does not
  define — never folded into a real module by guesswork) and "## Struck"
  (tombstones, which belong to no module).

  Expected per-item sub-structure inside each module group — one entry per
  backlog item:

  ### BL-NNN — <Feature Title>

  **Module:** <the MOD-### from module-map.md this item belongs to. Declared
    by the planner agent, read mechanically by bash, and NEVER derived from
    the item's DR-### rules — deriving one would be a silent assignment, and
    a disagreement between this field and the map's own rule ownership is
    exactly what /specclaw:bf-baseline record reports as a WARN at record
    time. An item with no such field is rendered under "## Unassigned",
    never guessed into a module.>
  **Maps to capability:** <functional-spec.md capability name/quote>
  **Depends on:** <earlier items' BL-NNN IDs, or "None">
  **Acceptance basis (domain-model.md):**
  - <entity/business-rule/enumeration reference, quoted — cite a business
    rule's DR-NNN ID (from domain-model.md) directly wherever the
    acceptance basis rests on a numbered rule, e.g. "DR-007: ..."; this is
    the join key /specclaw:bf-clarify and /specclaw:bf-baseline key their own
    CQ-NNN/GM-NNN citations against, so the ID itself must be textually
    present, not just implied by the quoted prose>

  **Verification inputs needed:**
  - <golden-master capture, external-format/DLL/COM semantics, or other
    human-supplied input this item's fidelity check will need — never
    leave this field blank; if genuinely nothing beyond the acceptance
    criteria above applies, say so explicitly rather than omitting it>

  **Gate:** <bash-computed: BLOCKED — blocked by <CQ-NNN + one-line title,
    ...> | OPEN QUESTIONS — risk from unanswered, non-blocking: <CQ-NNN,
    ...> | CLEAR>
  **Verification:** <bash-computed: VERIFIABLE — fixtures: <GM-NNN (legacy
    commit sha), ...> | PENDING CAPTURE — scenarios designed, no recorded
    fixture yet: <GM-NNN, ...> | UNVERIFIABLE — acceptance must come from a
    stakeholder decision, not fixture comparison (see CQ-NNN) | NO BASELINE
    DATA — baseline not run (or not designed) for these rules>
  **UI fidelity:** <bash-computed, and present ONLY when this item renders a
    screen AND the UI fidelity policy (SQ-013, read mechanically from
    decisions.md) is decided FAITHFUL/THEME-ONLY or is undecided. Renders as:
    FAITHFUL — reproduce the layout structure and token values of: <SCR-###,
    ...>; token groups: <TK-###, ...> | THEME-ONLY — reproduce the token
    values of: <TK-###, ...>; screens for reference only: <SCR-###, ...> |
    ⚠ UI GROUNDING MISSING — <the decided policy, plus which .specclaw/ui/
    artifacts are absent, or the fact that this item cites no SCR-### at all>
    | UNDECIDED — <SQ-013 has no recorded decision>. The last two also
    contribute an OPEN QUESTIONS state to the Gate line above, naming SQ-013.
    Under a decided REINTERPRET policy this field never appears on any item
    and no warning is emitted anywhere — the zero-extra-work path for a
    project that does not need visual fidelity. Which items render a screen
    is the planner agent's judgment, delivered as a SCREEN-BEARING: directive
    and applied mechanically here; SCR-###/TK-### content itself belongs to
    /specclaw:bf-ui, never to this document. A cited SCR-### never implies
    visual equivalence has been proven — that is established by a named human
    signing ui-review.md against recorded screenshots, never by this backlog
    and never by fixture replay.>
  **Settled constraints (from decisions):** <optional — only present when a
    mechanical-adopt decision applies to this item; omit the field entirely
    otherwise, never render it empty>

  **Status notes (human-added):** <optional — anything a human types under
    this exact heading (e.g. "built and merged, PR #12") survives every
    future /specclaw:bf-rebuild-plan --refresh verbatim, byte for byte. Nothing
    else in this document offers that guarantee — this is the one place a
    human note is safe to leave.>

  If two or more functional-spec capabilities are merged into a single
  backlog item, the item must state why in a "Merge rationale:" line —
  merging is a judgment call, never silent. A revised item (its acceptance
  basis rewritten because a decision changed its shape) states so inline,
  e.g. a line reading "⟲ revised per CQ-005, 2026-08-01" placed right after
  the heading.

  PROVISIONAL marker: an item touched by an open pending question — either
  a direct DR-NNN/BL-NNN join to a CQ-NNN promoted from a PQ-NNN (bash-
  computed), or a prose-level match the planner agent found and directed
  via a PROVISIONAL: line (agent-judged, mechanically re-verified by bash
  the same way an UNVERIFIABLE: directive is) — carries its own line right
  after the heading: "⚠ PROVISIONAL — pending PQ-NNN/CQ-NNN (proposed
  default: <x>)". This is soft-block: the item is still fully drafted,
  sequenced, and gated/verified exactly as any other; the marker rides
  alongside Gate/Verification, not instead of them, and both this line and
  Gate/Verification are recomputed from scratch on every run — it clears
  automatically once decisions.md answers the underlying question, no
  manual cleanup.

  STUB-BACKED marker: an item built against a dependency-bypass stub (see
  templates/CONTRACT.md (m) and .specclaw/analysis/module-stubs.md) carries
  its own line right after the heading, alongside any PROVISIONAL marker:
  "⚠ STUB-BACKED — built against ST-001 (stub-interface, faking BL-014
  (MOD-005)). Any replay verdict for this item says so until the stub is
  retired."

  It is deliberately NOT folded into the Verification: line. Verification
  answers "is there a fixture for this?"; taint answers "was the thing under
  test real?" — orthogonal axes, and collapsing them would let a
  VERIFIABLE item read as fully proven when part of what it was checked
  against was a placeholder. Like PROVISIONAL, it is recomputed from the
  registry on every run and never persisted, so retiring a stub clears every
  consuming item's marker automatically with no manual cleanup.

  A stub is only ever created by a human choosing one at /specclaw:propose
  time. Nothing in this document creates, edits, or retires one.

  QUALITY REMEDIATION ITEMS. When /specclaw:bf-quality has measured the legacy
  tree, each module with at least one open hotspot at or above the configured
  severity floor also carries ONE bash-written item — never one per hotspot —
  headed "### BL-NNN — MOD-### quality remediation" and declaring
  "**Item type:** QUALITY-REMEDIATION". Absent that measurement the whole
  mechanism is inert and this document is exactly what it would have been
  before the mechanism existed, down to the byte.

  ONE PER MODULE. A large legacy tree registers hundreds of QI-###; an item
  each would bury the functional backlog. The module is already the migration
  and acceptance unit here, and it is also the finest grain the target-side
  measurement can be taken at.

  ITS ACCEPTANCE IS A MEASUREMENT, NEVER A LITERAL INSTRUCTION. The item's
  criterion is that the REBUILT module measures within the thresholds in
  config.yaml's `quality:` section and regresses on no dimension, evidenced by
  .specclaw/analysis/quality-delta.json (from /specclaw:bf-quality --target
  followed by --compare). It never asks anyone to change a named legacy source
  file: that file is not part of the target and will not exist there. The
  QI-### ids it lists are the evidence for WHY the item exists; the delta is
  the proof that it is done.

  A hotspot counts as retired at the grain the delta can actually carry —
  module × metric. A hotspot's identity names a legacy file and function, and
  neither survives into the rebuilt tree to be measured a second time, so a
  per-hotspot claim of retirement would be a claim nothing could check.

  ITS OWN VERIFICATION CHANNEL. "**Verification:** QUALITY-MEASURED" is a fifth
  value alongside VERIFIABLE / PENDING CAPTURE / UNVERIFIABLE / NO BASELINE
  DATA, and it is not one of them: those four all answer "is there a recorded
  legacy output to compare against?", and here that question does not apply.
  The item cites no DR-### and maps to no GM-###. /specclaw:bf-replay --item on
  one refuses cleanly, names the item type, and points at
  /specclaw:bf-quality --compare — it never reports NO BASELINE DATA, which
  would read as "somebody forgot to record a fixture".

  ITS OWN COMPLETION AXIS. "**Quality state:** BLOCKED | OPEN | DONE" is
  bash-computed from the delta and is a THIRD question, not a restatement of
  the two fields above it: Gate answers "can this start?", Verification "how
  would it ever be checked?", and this one "has it been checked, and did it
  pass?". It is recomputed every run, so it clears by regeneration alone.

  MECHANICALLY GATED BEHIND ITS OWN MODULE. A remediation item is BLOCKED
  until every functional item in its module carries a declared "BUILT:" line in
  its Status-notes block — the same narrow declared trigger stub retirement and
  item splits use, never a prose reading. You cannot measure the health of code
  that does not exist yet.

  WHAT PERSISTS. The body is regenerated in full on every run, so the ONE thing
  that could not be recomputed is kept: a dated "⊕ Added"/"⊖ Retired" ledger
  recording hotspots that appeared or stopped qualifying after the item was
  created. A re-measured quality.json APPENDS a new hotspot to the module's
  existing item and never creates a second one. Human Status notes survive
  verbatim, exactly as on every other item.

  A hotspot BELOW the severity floor generates no item. It is reported as an
  advisory count on its module's line in the Module Coverage Rollup below, so
  it stays visible without becoming something the rebuild must clear.

  THE LEVER FOR "WE ACCEPT THIS DEBT" IS THE FLOOR, NOT A STRIKE. Deferring a
  remediation item works normally and holds its id forever, like any other item.
  STRIKING one does not stick: a tombstone keeps only the id and the reason, so
  the module it belonged to is no longer recoverable from the document, and the
  next --refresh sees a measured module with no item and generates one (once —
  the new item is then permanent like any other). If a module's measured state
  is genuinely acceptable, raise `quality.remediation_severity_floor` or resolve
  the hotspots at source; both are re-measured facts rather than an edit to a
  generated document.

  BL-NNN IDs are permanent identifiers, not position — assigned once in
  dependency order on the first-ever run and never renumbered afterward.
  A later /specclaw:bf-rebuild-plan --refresh may append a genuinely new item
  (next free BL-NNN, dependency-placed correctly) or strike/defer an
  existing one, but an already-assigned ID is never reused, renumbered, or
  silently deleted — a struck item stays in the Backlog section as a
  one-line tombstone ("### BL-NNN — STRUCK — <reason>, <date>"); a deferred
  item moves in full to the Deferred section, out of the ready ordering.
  "Depends on:" always cites BL-NNN IDs, never bare position, for exactly
  this reason.
-->

**Date:** 2026-09-11
**Inputs consumed:**
- decisions.md: present
- clarifications.md: present
- baseline/manifest.json: present
- baseline/scenarios.md: present

**Module map:** CONFIRMED by Hafsa, 09-09-2026 — 7 active module(s)

> ⚠ **Module dependency cycle.** module dependency ranking did not converge after 30 passes — module-map.md's 'Depends on' fields describe a cycle. Modules are still grouped and rendered, but their order below is not a valid dependency order until the cycle is broken.

**Recommended next module to build:** none — not because any module is unready, but because there is no defensible order to recommend one from.
- **Why:** module dependency order is undefined — module-map.md's 'Depends on' fields describe a cycle (see the warning above). Break the cycle, then re-run --refresh.

**UI fidelity policy:** THEME-ONLY (SQ-013)
- .specclaw/ui/ui-inventory.md: missing — run /specclaw:bf-ui
- .specclaw/ui/design-tokens.json: missing — run /specclaw:bf-ui
- .specclaw/ui/screens/: missing — a human must capture screenshots per screenshot-checklist.md
- .specclaw/ui/ui-manifest.json: missing — run /specclaw:bf-ui --record
- Screen-bearing items: 19, of which 19 lack UI grounding

> ⚠ **WARNING — UI fidelity policy THEME-ONLY is decided, but the artifacts it requires do not exist:** .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json. Every screen-bearing item above is held at OPEN QUESTIONS as a result. Run `/specclaw:bf-ui` (and `--record`, after a human captures the screenshots) — this backlog cannot state a UI acceptance basis without them, and it will not pretend to.

**Gate counts:** CLEAR: 5, OPEN QUESTIONS: 19, BLOCKED: 1 (of 25 active items; 0 struck, 0 deferred)
**Verification counts:** VERIFIABLE: 11, PENDING CAPTURE: 0, UNVERIFIABLE: 0, NO BASELINE DATA: 13
**Provisional (pending a decision):** 0 item(s) — independent of Gate/Verification; see each item's own marker

**Quality remediation:** 1 item(s) on the QUALITY-MEASURED channel, 0 computed DONE from `quality-delta.json`. Severity floor: HIGH (`config.yaml` `quality.remediation_severity_floor`). These cite no DR-### and map to no GM-### — see each item's own **Quality state:** line.

**Recommended next item to propose:** BL-001 — Cross-Cutting CSRF Protection Baseline

## Backlog

## MOD-003 — Room & Rate Management

_Depends on: none. module dependency rank undefined — module-map.md describes a dependency cycle. 4 active item(s)._

### BL-002 — Room List & Category Filter

**Module:** MOD-003 — owns Room and RoomCategory per module-map.md ("Owns (entities): Room, RoomCategory").
**Maps to capability:** "Manage rooms — list at `admin/rooms.php` (filter by category, a select/combo populated live from `room_categoricals`)..." (functional-spec.md Capabilities).
**Depends on:** None.
**Acceptance basis (domain-model.md):**
- Room entity: "`status` (`int(2)`) — occupancy enum, see Enumerations" and Enumeration 2, "`rooms.status` ... values `0`, `1` ... tracks whether a physical room can currently be assigned to a new stay." The list/filter view must render this status and the room's category-derived name.
- RoomCategory entity: "a small, fixed set of room pricing tiers ('Single', 'Double', 'Deluxe')... rooms are classified under" — the category filter select is populated live from this table (functional-spec.md: "a select/combo populated live from `room_categoricals`").
**Verification inputs needed:**
- No `GM-NNN` scenario in `scenarios.md` targets `admin/rooms.php`'s list/filter view directly — all 35 captured scenarios exercise write-path handlers (`xuli*.php`/`delete_*.php`) or pure-function seams, not this read-only list page. A new capture (or a direct acceptance-criteria-only build, since the rendering logic is simple and fully evidenced by functional-spec.md/domain-model.md) is needed before this item can reach VERIFIABLE.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `003-room-rate-management`, verify-report.md verdict PASS (10/10 acceptance criteria across BL-002/003/005) 2026-09-14, merged to `main`. Functional-only (no UI fidelity — still gated above).

---

### BL-003 — Room Create/Edit

**Module:** MOD-003.
**Maps to capability:** "...create/edit via `admin/manage_room.php`'s modal form... Create/edit fields: `room` — text input (room name/number); `category` — select/combo, hardcoded options `Single Room` (1) / `Double Room` (2) / `Deluxe Room` (3); `status` — select/combo, options `Available` (0) / `Unavailable` (1)" (functional-spec.md Capabilities). Note: functional-spec.md flags that "despite the target file's name" `admin/xulideleteroom.php` is this capability's create/update handler, not a delete handler (architecture.md L3 Room Management component narrative).
**Depends on:** BL-001 (CSRF token required on this state-changing form).
**Acceptance basis (domain-model.md):**
- Room entity fields `room` (display name) and `category_id` (FK into RoomCategory) — "a single physical, individually numbered hotel room belonging to one pricing category."
- Enumeration 2, `rooms.status` values `0`/`1` as above — the create/edit form's `status` select must persist one of these two values.
- Once CQ-007's Room Category CRUD screen (BL-005) exists, this item's `category` select should read live options rather than the legacy's hardcoded three, per the same live-data principle CQ-008 applies to public pricing — noted here as a forward-looking consistency point, not a separate `DR-NNN`-governed rule.
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/manage_room.php`/`admin/xulideleteroom.php`'s create/update write path — none of the 35 captured fixtures seed or assert against a room-create/edit call. A new capture is needed to pin exact field-to-column mapping before VERIFIABLE status is reachable.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `003-room-rate-management`, verify-report.md verdict PASS 2026-09-14, merged to `main`. The forward-looking consistency point above is already satisfied: the `category` select reads live from `room_categories` (BL-005, built in the same change), never hardcoded.

---

### BL-005 — Room Category Management (CRUD)

**Module:** MOD-003 — RoomCategory is owned by MOD-003 per module-map.md, "included in this module's ownership as the closest-fitting entity home, even though no live management screen for it exists."
**Maps to capability:** No functional-spec.md capability bullet describes this screen — it is new scope. Source: functional-spec.md's Named Gaps, "No admin screen manages `room_categoricals`... If a fourth category were ever needed, no traced code path in this run creates one," and decisions.md's **CQ-007**: "Add a full CRUD admin screen for room categories (create/edit/delete tiers, live price editing)."
**Depends on:** BL-001.
**Acceptance basis (domain-model.md):**
- RoomCategory entity: "`id` — primary key (seeded as exactly 1=Single Room, 2=Double Room, 3=Deluxe Room)... `name` (`varchar(20)`)... `price` (`int(30)`) — nightly rate for the category." The new CRUD screen must create/edit/delete rows shaped exactly like this entity.
- Named Gap cross-reference: "Named Gap: no admin screen was found anywhere under `admin/` that creates, edits, or deletes rows in `room_categoricals`... every reference to it in the codebase... is a hardcoded `<option>` list of exactly the three seeded categories, never a query-driven CRUD form." This item exists specifically to close that gap per CQ-007's decision.
**Verification inputs needed:**
- This is a wholly new capability with no legacy behavior to capture as a golden master (there is no code path in the legacy app that creates/edits/deletes a category, per the Named Gap above) — `manifest.json` correctly has no fixture for it and cannot get one from the legacy source. Acceptance must rest on the acceptance-basis quotes above and on functional/unit tests written against the new rebuilt screen itself, not on baseline replay.
- Once this screen exists, BL-002/BL-003's hardcoded three-option category selects (and BL-015/BL-019's booking-side category selects) should source their options from this same live table — a forward dependency worth flagging for build sequencing even though it does not change this item's own acceptance criteria.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `003-room-rate-management`, verify-report.md verdict PASS 2026-09-14, merged to `main`. Migration seeds the 3 legacy categories (Single/Double/Deluxe at 99/149/199), matching the static prices `homepage/index.php`/`homepage/room.php` currently hardcode — see BL-009's own note on this once MOD-006 is built.

---

### BL-004 — Room Delete

**Module:** MOD-003.
**Maps to capability:** "...delete via a confirm modal posting to `admin/delete_room.php`" (functional-spec.md Capabilities, "Manage rooms" bullet).
**Depends on:** BL-001, BL-003.
**Acceptance basis (domain-model.md):**
- No `DR-NNN` rule numbers this handler (domain-model.md's 13 business rules do not include one for room deletion) — grounding instead in `scenarios.md`'s persistence-layer scenario **GM-034 (seam PS-1)**: "Room deletion has no referential-integrity check," and in `decisions.md`'s **CQ-021** ("Fix the bind_param defect so delete_room.php/delete_customer.php/delete_user.php actually delete") and **CQ-024** ("Add a referential-integrity check to both delete handlers — reject (or require confirmation for) deleting a room/customer still referenced by an active (not checked-out/cancelled) booking").
- domain-model.md's Relationships narrative: "`ROOMS ||--o{ BOOKING` — `booking.room` is set to a specific `rooms.id` value only at check-in time" — this is the relationship CQ-024's referential-integrity check must consult before permitting a delete.
- Per the collect-step's flagged interaction: CQ-021's fix makes CQ-024's gap immediately live — this item must ship both the delete fix and the referential-integrity check together, not delete alone deferred to an undefined later point.
**Verification inputs needed:**
- **GM-034** is currently `VERIFIABLE` in manifest.json but describes the **pre-CQ-021-fix** legacy behaviour (`outcome: REJECTED`, `error_code: DELETE_STATEMENT_PARAM_COUNT_MISMATCH` — the `bind_param` defect silently no-ops the delete before referential integrity even becomes relevant). Once CQ-021's fix lands, this fixture's seam changes shape entirely and must be re-captured against the fixed handler; the rebuild must not claim behavioral parity with GM-034 as currently recorded, per the Fidelity Discipline note above.
- No fixture anywhere in `manifest.json` captures the *decided* CQ-024 rejection path (delete blocked when an active booking still references the room) — this is new decided behavior with no legacy precedent to replay, so a new golden-master-style scenario must be authored and captured against the rebuilt handler, not against legacy source.
**Gate:** CLEAR
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `006-delete-referential-integrity`, verify-report.md verdict PASS (7/7 acceptance criteria) 2026-09-14, merged to `main`. Shipped exactly as this item's acceptance basis required — CQ-021's delete fix and CQ-024's referential-integrity check together, in one change, once MOD-004's `bookings` table existed. Previously held out of change `003-room-rate-management`; see that change's spec.md "Item Held Out" section for the original reasoning.

## MOD-005 — Customer Management

_Depends on: none. module dependency rank undefined — module-map.md describes a dependency cycle. 3 active item(s)._

### BL-006 — Customer List

**Module:** MOD-005 — owns Customer per module-map.md.
**Maps to capability:** "Manage customers — list at `admin/customers.php`..." (functional-spec.md Capabilities).
**Depends on:** None.
**Acceptance basis (domain-model.md):**
- Customer entity: "A running profile of every guest who has ever booked or checked in, deduplicated by phone number, that accumulates lifetime spend," with fields `name`, `mail`, `phone`, `address`, `customer_id`, `charges`. The list view must surface these fields, including the cumulative `charges` total ("Confirmed incremented (never replaced) at checkout").
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/customers.php`'s list view directly. New capture (or acceptance-criteria-only build, given the page's read-only simplicity) is needed.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `002-customer-management`, verify-report.md verdict PASS (8/8 acceptance criteria across BL-006/007/008) 2026-09-14, merged to `main`. Functional-only (no UI fidelity — still gated above).

---

### BL-007 — Customer Create/Edit

**Module:** MOD-005.
**Maps to capability:** "...create/edit via `admin/manage_customer.php`'s modal form... Create/edit fields: `name` (text input), `cus_id`/Customer Id (text input, shown only when editing an existing customer — hidden field otherwise, per DR-004), `mail` (text input...), `phone` (text input), `address` (text input)" (functional-spec.md Capabilities).
**Depends on:** BL-001, BL-006.
**Acceptance basis (domain-model.md):**
- **DR-004 — Customer ids are randomly generated and unique** — "`admin/xulicustomer.php:6-14`: `$cus_id = rand(0, 99999999)` is regenerated in a loop until no existing `customers.customer_id` matches it." Confirmed independently implemented (not shared code) by module-map.md: "this module's own create-customer handler, `admin/xulicustomer.php`, independently regenerates a unique `customer_id` the same way MOD-004's flows do" — co-owned with MOD-004, placed here because this item is primarily about MOD-005's own Customer-entity CRUD handler.
- **DR-005 — A new customer record is created only if no existing customer shares the same phone number** — "`admin/xulicustomer.php:71-78`" is not the exact citation (that line range is `xulicheckin.php`'s); the correct citation for this handler is module-map.md's own evidence: "`admin/xulicustomer.php:29` runs its own phone-match check before INSERT, the same guard MOD-004's `admin/xulicheckin.php:71` applies" — co-owned with MOD-004 for the same reason as DR-004 above.
- Per **CQ-011** ("Introduce an explicit `customer_id` foreign key on `booking`... stop matching by phone"): this decision concerns `booking`'s relationship to `customers.id` (the internal primary key), not `customers.customer_id` (the random public-facing identifier this item's DR-004 governs) — the two identifiers must not be conflated when this item's fields are implemented.
- Per **CQ-012** ("Add real phone-number format validation (client- and server-side)"): the `phone` field here must be validated, closing the gap functional-spec.md's Named Gaps flagged ("no numeric/format validation of the phone value was found anywhere along any traced write path").
**Verification inputs needed:**
- **GM-033** (`VERIFIABLE`) covers this handler's DR-004 customer-id generation loop directly: "confirms this third, independently-coded call site (`admin/xulicustomer.php:6-14`) implements DR-004 identically to the booking-triggered call sites exercised in GM-008/GM-009."
- No `GM-NNN` scenario exercises this handler's own DR-005 phone-dedup check (`admin/xulicustomer.php:29`) — GM-010/GM-011 exercise DR-005 only via `homepage/connect.php`. This is a real coverage gap: the dedup guard is independently coded in this handler per module-map.md's own evidence, so GM-010/GM-011 passing does not prove this handler's copy behaves the same way. A new scenario targeting `admin/xulicustomer.php`'s dedup branch is needed before this item's DR-005 citation can be marked VERIFIABLE with confidence.
- CQ-011's FK design has no existing fixture asserting `booking.customer_id` population (it doesn't exist in the legacy schema) — not directly this item's concern (that's BL-015/BL-017/BL-019's), but noted here since this item's Customer entity is the FK's target.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-008 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-009 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-010 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-011 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-033 (c77a6e7fdb549db426c1cb410bc32c31dd236045)
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `002-customer-management`, verify-report.md verdict PASS 2026-09-14, merged to `main`. DR-004/DR-005/CQ-012 implemented and tested; golden-master fixtures (GM-008/009/010/011/033) not yet replayed against the rebuild — `/specclaw:bf-replay` has not run in this project.

---

### BL-008 — Customer Delete

**Module:** MOD-005.
**Maps to capability:** "...delete via confirm modal → `admin/delete_customer.php`" (functional-spec.md Capabilities, "Manage customers" bullet).
**Depends on:** BL-001, BL-007.
**Acceptance basis (domain-model.md):**
- No `DR-NNN` rule numbers this handler — grounding in **GM-035 (seam PS-2)**: "Customer deletion has no referential-integrity check," and in **CQ-021**/**CQ-024** (see BL-004's identical citations — the same two decisions apply symmetrically to Room and Customer deletion).
- domain-model.md's Relationships narrative: "`CUSTOMERS }o--o{ BOOKING` — there is no `customer_id`/`customers.id` foreign-key column anywhere in `booking`. Every join... is done at the application level by matching `phone` values" — today's absence of a real FK is exactly why CQ-024's check must be application-level logic (querying `booking` by phone) unless/until CQ-011's new FK column supersedes it, at which point this item's referential-integrity check should query by the new FK instead of by phone.
**Verification inputs needed:**
- **GM-035** is currently `VERIFIABLE` in manifest.json but, identically to BL-004's GM-034, describes the **pre-CQ-021-fix** behaviour (`error_code: DELETE_STATEMENT_PARAM_COUNT_MISMATCH`). It must be re-captured once CQ-021's fix lands — the rebuild must not claim parity with GM-035 as currently recorded.
- No fixture captures the decided CQ-024 rejection path for customer deletion — new capture needed against the rebuilt handler, not legacy source.
- If BL-015/BL-017/BL-019 land CQ-011's FK before this item is built, this item's referential-integrity check should be re-specified against `booking.customer_id` rather than phone-matching — flagged here as a build-sequencing dependency, not a change to this item's DR/CQ citations.
**Gate:** CLEAR
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `002-customer-management` (CQ-021 delete fix, verify PASS 2026-09-14) + change `006-delete-referential-integrity` (CQ-024 referential-integrity check, closing the deferral below, verify PASS 2026-09-14), both merged to `main`. Fully built as of change 006.
⚠ Historical record, now resolved: CQ-024's check was deferred when this item first shipped in change `002-customer-management` (recorded in prose, not via IS-###, since this item cites no `DR-###` rule for `split-append` to partition against — see that change's spec.md) because no `booking` table existed yet (MOD-004 unbuilt). Change `005-booking-stay-lifecycle` created it; change `006-delete-referential-integrity` closed the deferral — the check now queries `bookings.customer_id` (the CQ-011 FK), exactly as this item's own acceptance basis anticipated, not by phone.

## MOD-006 — Public Marketing Site

_Depends on: none. module dependency rank undefined — module-map.md describes a dependency cycle. 1 active item(s)._

### BL-009 — Browse Marketing Content (Home, Room Details, Services, Food & Drinks)

**Module:** MOD-006 — owns these four public read-only pages per module-map.md.
**Maps to capability:** "Browse marketing content — `homepage/index.php`, `homepage/room.php`, `homepage/service.php`, `homepage/food.php`. Read-only pages reachable from `homepage/Header.php`'s nav (`Home`, `Room`, `Services`, `Foods`, `Book Now`). No form input." (functional-spec.md Capabilities).
**Depends on:** BL-002 (Room/RoomCategory read access), BL-005 (RoomCategory CRUD — the live pricing source this item must read from once it exists).
**Acceptance basis (domain-model.md):**
- RoomCategory entity: `price` (`int(30)`) — nightly rate for the category — is the field this item's pages must display, per **CQ-008**'s decision: "Drive public pricing display from the same live pricing data the admin/checkout path uses," replacing functional-spec.md's Named Gap finding that "`homepage/index.php` and `homepage/room.php` display `$99`/`$149`/`$199` as static literal text, not queried from `room_categoricals.price`."
- This is a shape-changing revision of the legacy capability, not a client-orchestrated multi-command flow — no `DR-NNN` rule governs marketing-page rendering; the acceptance basis rests entirely on the RoomCategory entity fields and the CQ-008 decision quoted above, per rubric step 2's instruction to say so explicitly rather than force an adjacent-rule citation.
**Verification inputs needed:**
- No `GM-NNN` scenario exists for any MOD-006 page — `scenarios.md`'s Rule Coverage Check confirms all 35 fixtures are tagged to MOD-001/002/003/004/005/007, never MOD-006, since these pages issue no database query in the legacy app at all (module-map.md: "no database query in any of them; prices/content are static markup"). Verification for this item must be a direct assertion that the rendered price matches `room_categoricals.price` at request time, not a golden-master replay (there is no legacy query behavior to replay).
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

**Status notes (human-added):**
BUILT: change `004-public-marketing-site`, verify-report.md verdict PASS (6/6 acceptance criteria) 2026-09-14, merged to `main`. CQ-008 satisfied (live RoomCategory pricing, not hardcoded). "Book Now" nav link is a plain `/book` href (BL-015/MOD-004 not built yet) — 404s until that item lands, documented as intended. Functional-only (no UI fidelity — still gated above).

## MOD-004 — Booking & Stay Lifecycle

_Depends on: MOD-003. module dependency rank undefined — module-map.md describes a dependency cycle. 10 active item(s)._

### BL-017 — Booking → Check-In Conversion

**Module:** MOD-004.
**Maps to capability:** "...with a 'Check In' button that opens `admin/manage_booking.php` in a modal (ajax-loaded)... Submitting 'Check In' triggers the Booking → Check-In Conversion workflow below." (functional-spec.md Capabilities, "View/convert pending bookings" bullet). Covers the named **Booking → Check-In Conversion** workflow (functional-spec.md Workflows).
**Depends on:** BL-001, BL-002 (room availability read), BL-016.
**Acceptance basis (domain-model.md):**
- **DR-007 — Converting a pending booking to checked-in occupies the assigned room** — "`admin/xulibooking.php:8-19`: sets `booking.status=1` and `booking.room=<chosen room id>`, then sets that room's `status=1` (unavailable)."
- **DR-009 — Days of stay is computed** (shared pure function) — "`floor(abs(strtotime($dateout) - strtotime($datein)) / 86400)`," used to pre-compute the `days` field shown in this workflow's modal form per functional-spec.md: "`days` (numeric input, pre-computed via DR-009)."
- The workflow's own two-call sequence is the client-orchestrated basis beyond DR-007 alone: "a staff member's single 'Check In' click... triggers two distinct backend calls in fixed sequence... What is lost if step 2 were omitted: the room would remain flagged `Available`... allowing it to be double-booked."
**Verification inputs needed:**
- **DR-007**: GM-016 (`VERIFIABLE`) — asserts both the booking-status/room-assignment write and the room-status write in one invocation.
- Because `admin/xulibooking.php` performs both writes inside one server-side handler call triggered by one form submission (not two separate client-orchestrated requests), GM-016 does exercise this composite write end-to-end, not just one isolated step — recorded explicitly per rubric step 4(c).
- **DR-009** itself is pinned in isolation by GM-018/GM-019/GM-020 (all `VERIFIABLE`, shared with BL-021/BL-022) — no scenario asserts that this specific form's `days` field is actually populated from that formula at render time; a light-touch new capture may be warranted to close that specific gap.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-016 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-018 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-019 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-020 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. DR-007 satisfied as one composite write (booking status + room assignment + room status), with a room-category-match constraint (design.md FR-10) enforced server-side.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-018 — Walk-In Availability List

**Module:** MOD-004.
**Maps to capability:** "Walk-in check-in — list of available rooms at `admin/check_in.php`..." (functional-spec.md Capabilities).
**Depends on:** BL-002.
**Acceptance basis (domain-model.md):**
- Enumeration 2, `rooms.status` values `0`/`1` — this list filters to available (`status=0`) rooms.
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/check_in.php`'s list view directly. New capture or acceptance-criteria-only build needed.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-015 — Public Reservation Intake

**Module:** MOD-004 — owns Booking; "creates/updates customer record as a side effect" per module-map.md's cross-reference to MOD-005.
**Maps to capability:** "Submit a room reservation — exposed by `homepage/book.php`'s 'Submit' button, posting to `homepage/connect.php`. Fields...: `name`, `mail`, `phone`..., `room_type`..., `adult`, `children`, `datein`, `dateout`, `days_of_stay`, `message`. This single 'Submit' action triggers a multi-step backend sequence — see the Public Reservation Intake workflow below." (functional-spec.md Capabilities). Also covers the named **Public Reservation Intake** workflow (functional-spec.md Workflows — the branching ref-number/customer-id/room-type/dedup diagram).
**Depends on:** BL-001, BL-005 (category validation source), BL-007 (customer dedup logic parity with MOD-005's own handler).
**Acceptance basis (domain-model.md):**
- **DR-003 — Booking reference numbers are randomly generated and unique** — "`$ref = rand(0, 999999999)` is regenerated in a loop until no existing `booking.ref_no` matches it." Per **CQ-009**: "Adopt the same range as-is (0–999999999) for behavioral/format parity."
- **DR-004 — Customer ids are randomly generated and unique** — same mechanism as BL-007, triggered here from `homepage/connect.php:13-19`. Per **CQ-010**: "Adopt the same range as-is (0–99999999)."
- **DR-005 — A new customer record is created only if no existing customer shares the same phone number** — "`homepage/connect.php:48-55`: before inserting into `customers`, the code checks `SELECT * FROM customers where phone = '$phone'` and skips the insert if a row already exists."
- **DR-006 — Room-type name maps to a fixed category id, defaulting to Deluxe for anything unrecognized** — "`homepage/connect.php:32-38`: `'Single Room'` → 1, `'Double Room'` → 2, any other string... → 3 (Deluxe)." Per **CQ-004**: "Treat as a defect — require an explicit, validated room_type selection and reject the submission otherwise" — this item must implement the corrected (reject-on-unrecognized) behavior, not the legacy silent-Deluxe-default.
- Per **CQ-011**: "Introduce an explicit `customer_id` foreign key on `booking`, populated at booking/check-in time; stop matching by phone" — this item's booking-insert step must populate the new FK column (referencing `customers.id`, not the DR-004 public `customer_id`) instead of relying on phone-matching at checkout time.
- Per **CQ-012**: "Add real phone-number format validation" — closes the Named Gap on the `phone` field's non-standard `type="Phone"` markup.
- The workflow narrative's own composite-flow framing is the client-orchestrated-sequence basis for this item beyond any single `DR-NNN`: "What is lost if the customer-insert step were skipped entirely: a returning guest recognized only by phone would never gain a `customers` row on their first booking" — recorded explicitly per rubric step 2, since this sequencing (ref-no loop → customer-id loop → category branch → booking insert → dedup branch → customer insert) is enforced by this one PHP script's own procedural order, not by a separate client-orchestrated multi-request flow (the whole sequence runs inside one `connect.php` invocation per one form submit).
**Verification inputs needed:**
- **DR-003**: GM-006 (no collision, `VERIFIABLE`), GM-007 (retry after collision, `VERIFIABLE`).
- **DR-004**: GM-008 (`VERIFIABLE`), GM-009 (`VERIFIABLE`).
- **DR-005**: GM-010 (`VERIFIABLE`), GM-011 (`VERIFIABLE`).
- **DR-006**: GM-012/GM-013 (legitimate mappings, `PROVISIONAL` pending CQ-004, already decided), GM-014 (the exact defect CQ-004 replaces — "Unrecognized/unselected room type silently maps to Deluxe (3)" — must not be treated as a parity target; a new fixture asserting rejection is needed).
- Because this whole sequence executes inside one server-side script per one form submission (no separate client-orchestrated network calls chain these steps), the existing GM-006 through GM-014 fixtures do exercise the full composite sequence end-to-end within their own scope, not merely isolated backend steps — however, none of them yet assert the CQ-011 FK population (`booking.customer_id`), since that column does not exist in the legacy schema captured by these fixtures. A new scenario asserting the FK is correctly populated is needed before CQ-011's design can be marked VERIFIABLE for this item.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-006 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-007 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-008 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-009 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-010 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-011 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-012 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-013 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-014 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-015 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-033 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. DR-003/004/005/006(-as-corrected-by-CQ-004)/CQ-009/CQ-010/CQ-011/CQ-012 all implemented and tested. Golden-master fixtures (GM-006 through GM-015/GM-033) not yet replayed against the rebuild — /specclaw:bf-replay has not run in this project; several (GM-014) pin the pre-CQ-004-fix legacy defect and must not be treated as parity targets.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-019 — Walk-In Check-In

**Module:** MOD-004.
**Maps to capability:** "...'Check-in' button opens `admin/manage_check_in.php` in a modal. Fields: `name`..., `rid`/Room..., `mail`..., `phone`..., `adult`/`children`..., `date_in`..., `date_in_time`..., `days`..., `message`.... Submitting triggers the Walk-In Check-In workflow below (a Composite-Flow Rule case)." (functional-spec.md Capabilities, "Walk-in check-in" bullet). Covers the named **Walk-In Check-In** workflow (functional-spec.md Workflows).
**Depends on:** BL-001, BL-018, BL-007.
**Acceptance basis (domain-model.md):**
- **DR-003** (ref-no generation, same mechanism as BL-015, this call site at `admin/xulicheckin.php:30-36`).
- **DR-004** (customer-id generation, same mechanism as BL-007/BL-015, this call site at `admin/xulicheckin.php:46-53`).
- **DR-005** (phone dedup, this call site at `admin/xulicheckin.php:71-78`).
- **DR-006** (room-type mapping, this call site at `admin/xulicheckin.php:21-27`) — per **CQ-004**, same corrected (reject-on-unrecognized) behavior as BL-015.
- **DR-008 — Walk-in check-in creates an already-checked-in booking and occupies the room** — "`admin/xulicheckin.php:59-69`: inserts a new `booking` row directly with `status=1` (skipping the 'booked' stage entirely) and sets the chosen room's `status=1`."
- Per **CQ-022**: "Fix the connection-lifecycle bug — move `$con->close()` to the end of `admin/xulicheckin.php`, after the customer-insert branch, so walk-in check-in for a new customer completes and redirects instead of crashing" — closes the defect that today crashes this exact handler for any genuinely new guest.
- Per **CQ-011**, this item's booking-insert must also populate the new `customer_id` FK, same as BL-015.
- The workflow's own multi-step sequence is the client-orchestrated basis beyond any single rule: "What is lost if the customer dedup/insert were omitted: repeat walk-in guests would never accumulate a `customers.charges` history."
**Verification inputs needed:**
- **DR-006** round-trip: GM-015 (`PROVISIONAL` pending CQ-004, already decided).
- **DR-008**: GM-017 (`PROVISIONAL` pending CQ-004 — note: GM-017's own `business_rules_pinned` cites DR-008, but manifest.json's `provisional_ref` for it is empty/`VERIFIABLE`-adjacent; scenarios.md's Provisional table lists only DR-006 as CQ-004-blocked for GM-015/017's shared seam SV-5/SV-7 lineage — treat GM-017 per its own manifest status).
- **Both GM-015 and GM-017 currently record `outcome: REJECTED`, `error_code: CHECKIN_CUSTOMER_INSERT_ON_CLOSED_CONNECTION`, `threw: true`** — this is the pre-CQ-022-fix crash, not the corrected behavior. Both fixtures must be re-captured once CQ-022's fix lands; the rebuild must not claim parity with either as currently recorded.
- **DR-004/DR-005 within this specific handler are not independently exercised by any `GM-NNN` scenario** — GM-008/009/010/011 exercise these rules only via `homepage/connect.php` (BL-015), and GM-033 only via `admin/xulicustomer.php` (BL-007); GM-015/017's own assertions (per scenarios.md) focus on the category round-trip and booking-creation outcomes, not on asserting the customer-id/dedup outcomes explicitly for this third, independently-coded call site. A dedicated new scenario for this handler's DR-004/DR-005 behavior is needed for the same reason flagged in BL-007.
- Because the crash occurs partway through one server-side script (not across separate client-orchestrated requests), fixing CQ-022 only requires reordering statements within this one handler — no separate frontend-orchestration step is missing here, so this item's gap is a backend defect, not a composite-flow omission, and existing fixtures (once re-captured) will fully exercise it end-to-end.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-006 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-007 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-008 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-009 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-010 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-011 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-012 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-013 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-014 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-015 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-017 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-033 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. DR-003/004/005/006/008 and CQ-011/CQ-012 implemented (independently from BL-015, per module-map.md's note that the legacy duplicates this logic per call site). CQ-022's connection-lifecycle crash does not apply in Eloquent — see design.md's Key Decisions.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-016 — Pending Bookings List & Cancellation

**Module:** MOD-004.
**Maps to capability:** "View/convert pending bookings — list at `admin/booked.php` (bookings with `status=0`)... and a 'Cancel' button (confirm modal → `admin/delete_booking.php`, a hard delete)." (functional-spec.md Capabilities).
**Depends on:** BL-001, BL-015.
**Acceptance basis (domain-model.md):**
- Booking entity `status` enumeration: "`0=booked, 1=check_in, 2=check_out`" — this list filters to `status=0`.
- No `DR-NNN` rule numbers cancellation itself; per **CQ-014**: "Add a 'cancelled' status value (or soft-delete/archive record) so cancelled bookings remain queryable" — replaces functional-spec.md's Named Gap finding, "Booking cancellation (`admin/delete_booking.php`) is a hard delete... a cancelled booking leaves no historical trace anywhere in the data model." This item must implement the decided audit-preserving cancellation, not the legacy hard delete.
**Verification inputs needed:**
- No `GM-NNN` scenario in `scenarios.md` targets `admin/booked.php`'s list view or `admin/delete_booking.php`'s cancellation handler at all — this is a real baseline gap (unlike BL-004/BL-008's delete handlers, no PS-seam scenario was captured for booking cancellation). Recommend capturing the legacy hard-delete behavior as a reference point before building the CQ-014 replacement, so the new status/soft-delete write can be checked against a known "what used to happen" baseline rather than being built with zero baseline evidence at all.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. CQ-014 satisfied — cancellation sets a new cancelled status value, never deletes the row.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-020 — Check-In/Out List

**Module:** MOD-004.
**Maps to capability:** "Check out a guest / record payment — list of checked-in stays at `admin/check_out.php`..." (functional-spec.md Capabilities). Also covers the read-only "Edit"/"View" button entry points into BL-021/BL-022/BL-023 from this same list.
**Depends on:** BL-017, BL-019.
**Acceptance basis (domain-model.md):**
- Booking entity `status` enumeration values `1`/`2` — this list shows checked-in (`status=1`) and checked-out (`status=2`) rows, per functional-spec.md's "checked-in/out list."
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/check_out.php`'s list view directly. New capture or acceptance-criteria-only build needed.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-021 — Guest Check-Out & Billing

**Module:** MOD-004.
**Maps to capability:** "...ajax-loads `admin/manage_check_out.php` into a modal showing read-only room/category/price/reference/dates/computed-amount, plus one editable field: `payment`.... Submitting 'Payment & Check Out' triggers the Guest Check-Out & Billing workflow below (a Composite-Flow Rule case)." (functional-spec.md Capabilities). Covers the named **Guest Check-Out & Billing** workflow (functional-spec.md Workflows).
**Depends on:** BL-001, BL-020.
**Acceptance basis (domain-model.md):**
- **DR-009** (days-of-stay, shared formula) — used here to compute the amount due, per DR-010 below.
- **DR-010 — Checkout amount due is computed as category price × days of stay, but the charge actually recorded is unvalidated staff input** — "`admin/manage_check_out.php:34-37` computes and displays `$cat['price'] * $calc_days`, enforced only via the HTML `min` attribute... `admin/xulicheckout.php:3-16` writes whatever value arrives in `$_POST['payment']` straight into `booking.price` with no server-side comparison." Per **CQ-005**: "Treat as a defect — add server-side validation matching the client-side `min` (payment ≥ computed amount due)" — this item must implement server-side enforcement, not the legacy unvalidated write.
- **DR-011 — Checkout closes the booking, bills the customer, and frees the room in one composite operation** — "`UPDATE booking set status=2, price=?`, then `UPDATE customers set charges = charges + ? where phone=?`, then `UPDATE rooms set status=0 where room=?`."
- Per **CQ-023**: "Fix — match by room id throughout, including in the checkout composite write" — replaces the third write's legacy name-text match domain-model.md/functional-spec.md flag as an outlier: "this call keys off `rooms.room` (the text name) rather than `rooms.id`, unlike every other room-status write in the codebase."
- Per **CQ-011**, once `booking.customer_id` exists, the second write (`UPDATE customers set charges = charges + ?`) should key off that FK, not phone matching, for the same reason cited in BL-015/BL-008.
- The workflow's own three-call composite sequence is the client-orchestrated basis beyond any single rule: "What is lost if step 2 were omitted: the customer's lifetime `charges` total... silently understates their real spend... What is lost if step 3 were omitted: the room would remain flagged `Unavailable` indefinitely."
**Verification inputs needed:**
- **DR-009**: GM-018/019/020 (all `VERIFIABLE`).
- **DR-010**: GM-021/022 (formula itself, `PROVISIONAL` pending CQ-005, already decided — the computation is unaffected by the decision, only the enforcement is) and GM-023 (`PROVISIONAL` pending CQ-005 — this one *is* the exact gap CQ-005 closes: "Checkout accepts an unvalidated payment below the computed amount due" — must not be treated as a parity target; a new fixture asserting rejection is needed).
- **DR-011**: GM-024 (`VERIFIABLE`, full composite write) and GM-025 (`VERIFIABLE`, but pins the **pre-CQ-023-fix** name-match behavior — "proving the `UPDATE rooms set status=0 where room = ?`... matches by name text, unlike every other room-status write in the app" — must be re-captured once CQ-023's fix lands; the rebuild must not claim parity with GM-025 as currently recorded).
- Because `admin/xulicheckout.php` performs all three writes inside one server-side handler call triggered by one "Payment & Check Out" click (not three separate client-orchestrated requests), GM-024 does exercise this composite write end-to-end — however, note that GM-024's assertions were captured against the *legacy* room-matching mechanism; once CQ-023 changes that mechanism, GM-024 itself may also need re-capture to confirm the id-based match still produces the same net outcome (room freed) even though the matching key changed.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-018 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-019 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-020 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-021 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-022 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-023 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-024 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-025 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. CQ-005 (server-side payment validation, recomputed not client-trusted), CQ-011 (charges billed via customer_id FK), CQ-023 (room freed via id, not name-text) all satisfied and independently tested.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-022 — Edit In-Progress Stay's Checkout Date

**Module:** MOD-004.
**Maps to capability:** "Edit an in-progress stay's checkout date — 'Edit' button on a checked-in row in `admin/check_out.php` opens `admin/edit_check_in.php`, which posts a single `date_out`/`date_out_time` change to `admin/xulieditcheckin.php` (one backend call, `UPDATE booking set dateout=?`— not a composite flow)." (functional-spec.md Capabilities).
**Depends on:** BL-001, BL-020.
**Acceptance basis (domain-model.md):**
- **DR-009** (days-of-stay formula) — domain-model.md's Booking entity note: "`days_of_stay` — stated length of stay (not always recomputed consistently — see DR-009)," and DR-009 itself: "`admin/xulieditcheckin.php`" is the one handler that changes `dateout` after check-in but "runs only `UPDATE booking set dateout=?`, never touching the stored `days_of_stay` column."
- Per **CQ-016**: "Treat as a defect — recompute and persist `days_of_stay` whenever `dateout` (or `datein`) changes" — this item must implement the recompute-and-persist behavior, not the legacy stale-column write.
**Verification inputs needed:**
- No `GM-NNN` scenario in `scenarios.md` targets `admin/xulieditcheckin.php` directly — only the shared DR-009 pure-function formula is captured in isolation (GM-018/019/020), not this specific handler's failure to apply it to the stored column. A new scenario capturing "edit checkout date → `days_of_stay` recomputed and persisted" is needed before this item can be marked VERIFIABLE; there is no existing fixture proving even the legacy (non-recomputing) behavior for direct comparison.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-018 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-019 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-020 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS 2026-09-14, merged to `main`. CQ-016 satisfied — days_of_stay is recomputed and persisted alongside dateout in one update() call, not left stale.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-023 — View Completed Stay Detail

**Module:** MOD-004.
**Maps to capability:** "View a completed stay's detail — 'View' button on a checked-out row opens `admin/view_check_out.php`, a read-only summary (room, category, price, reference no., guest name, phone, dates, days, total amount). No input." (functional-spec.md Capabilities).
**Depends on:** BL-021.
**Acceptance basis (domain-model.md):**
- Booking entity fields `ref_no`, `name`, `mail`, `phone`, `datein`, `dateout`, `days_of_stay`, `price` — this read-only view surfaces exactly these fields for a `status=2` (checked-out) booking.
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/view_check_out.php` directly. Given its purely read-only, no-input nature, acceptance can rest on the entity-field quote above without a dedicated golden-master capture, though a light-touch rendering test is still recommended.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `005-booking-stay-lifecycle`, verify-report.md verdict PASS (15/15 acceptance criteria across BL-015 through BL-023) 2026-09-14, merged to `main`. Read-only view; renders ref_no/name/phone/dates/days/price directly from the booking row and its relations.
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-025 — MOD-004 quality remediation

**Module:** MOD-004
**Item type:** QUALITY-REMEDIATION — generated from measured hotspots in `.specclaw/analysis/quality.json`, not from a functional capability. Bash writes this item in full on every run; only the dated ledger below and any human status note survive a `--refresh`.
**Maps to capability:** none — this item carries no capability bullet and is counted in no coverage line. It exists so that the rebuilt module's measured health is an acceptance condition rather than a hope.
**Depends on:** BL-015, BL-016, BL-017, BL-018, BL-019, BL-020, BL-021, BL-022, BL-023
**Acceptance basis (.specclaw/analysis/quality.json, classified against `config.yaml`'s `quality:` thresholds at a `remediation_severity_floor` of HIGH):**
- QI-114 — duplication, module-wide, measured 70.4, HIGH

**Acceptance criterion — MEASURED, never literal:** the REBUILT MOD-004 measures within the configured thresholds on every dimension listed above and regresses on none, as evidenced by `.specclaw/analysis/quality-delta.json`. The `QI-###` ids above are the evidence for WHY this item exists; the delta is the proof that it is done. Nothing here asks anyone to change the legacy implementation: that implementation is not part of the target, and the files named above will not exist there.

**Grain of the proof:** a listed hotspot counts as retired when the delta's entry for its module and its metric measures below the HIGH floor and is not classified `regressed`. Module × metric is the finest grain the delta can carry — a hotspot's identity names a legacy file and function, and neither exists in the rebuilt tree to be measured a second time.

**Verification inputs needed:**
- A measured target snapshot and a comparison: `/specclaw:bf-quality --target <path to the rebuilt tree>`, then `/specclaw:bf-quality --compare`. Nothing else — this item needs no golden-master capture and no stakeholder decision.
**Gate:** BLOCKED — quality: awaiting functional items — no declared `BUILT:` note yet on BL-015, BL-016, BL-017, BL-018, BL-019, BL-020, BL-021, BL-022, BL-023
**Verification:** QUALITY-MEASURED — cites no DR-### rule and maps to no GM-### fixture. Acceptance is a measurement, not a recorded output: `/specclaw:bf-quality --target <rebuilt tree>` then `/specclaw:bf-quality --compare`. `/specclaw:bf-replay --item BL-025` refuses this item by design.
**Quality state:** BLOCKED — nothing to measure yet; MOD-004's functional items come first.

## MOD-001 — Authentication & Access Control

_Depends on: MOD-002. module dependency rank undefined — module-map.md describes a dependency cycle. 3 active item(s)._

### BL-001 — Cross-Cutting CSRF Protection Baseline

**Module:** MOD-001 — no capability bullet or entity in domain-model.md owns this cross-cutting security mechanism; it is placed in MOD-001 because that module owns the Front Controller & Layout / session infrastructure (per module-map.md's MOD-001 Services/routes: `admin/index.php`, `admin/sidebar.php`, `admin/header.php`) that a CSRF token mechanism must integrate with (token minted into the session, verified on every state-changing POST routed through the front controller), and every other module's write-form items below depend on it.
**Maps to capability:** No functional-spec.md capability bullet describes this — it exists solely because of a decided cross-cutting security requirement (see Acceptance basis).
**Depends on:** None.
**Acceptance basis (domain-model.md):**
- No `DR-NNN` rule governs this — CSRF protection is not a legacy business rule but a decided security-architecture addition. Grounding instead in decisions.md: "CQ-015 — Should the rebuild add CSRF protection to state-changing forms, given the legacy app has none anywhere? ... Decision: Add CSRF tokens to every state-changing form/handler in the rebuild," sourced from codebase-report.md § Risks/Tech-Debt: "No CSRF protection on any state-changing form." This is a client-orchestrated precondition (a hidden token field on every form, validated before any handler's own business logic runs) that no `DR-NNN` rule enforces — recorded explicitly per rubric step 2's guidance for frontend-only sequencing with no governing rule.
**Verification inputs needed:**
- No golden-master fixture exists or can exist for this item: `scenarios.md`'s 35 captured scenarios all pin the legacy app's *actual, as-shipped* behavior, which has zero CSRF protection anywhere (codebase-report.md), so there is nothing to replay as a baseline for what protection should look like. This item needs dedicated new security tests (token-present-and-valid → request proceeds; token-absent-or-invalid → request is rejected before any business logic runs) captured against the rebuilt handlers themselves, not against the legacy app.
- Because every other write-item below depends on this item's token mechanism being present in its own form, and none of the 35 captured GM fixtures exercise HTTP-layer token validation (they invoke handlers in-process, bypassing the web-request layer entirely per scenarios.md's own Act descriptions, e.g. "Invoke `admin/xuli_login.php` in-process"), no existing fixture can ever detect a missing or broken CSRF check on any downstream item — this is a client/HTTP-orchestration gap analogous to rubric step 4(c)'s composite-flow caveat, just at the transport layer instead of the business layer.
**Gate:** CLEAR
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules

**Status notes (human-added):**
BUILT: change `001-csrf-baseline`, verify-report.md verdict PASS (4/4 acceptance criteria) 2026-09-14, merged to `main` (commits `179f127`, `c38fecd`). Proven in `tests/Feature/CsrfProtectionTest.php`.

---

### BL-010 — Admin Login & Logout

⚠ Merge rationale: "Log in" and "Log out" are two trivially small, tightly-coupled functional-spec.md capability bullets sharing one session lifecycle (login establishes `$_SESSION[...]`, logout tears it down) and one module/component (MOD-001 Authentication, per architecture.md's L3 component list). Merged into a single item per rubric step 1.
**Module:** MOD-001 — owns Authentication & Access Control.
**Maps to capability:** "Log in — `admin/login.php`'s 'Login' button, posting to `admin/xuli_login.php`. Fields: `username` (text input), `password` (password input...). See the Admin Login workflow..." and "Log out — `admin/logout.php`, reached via the sidebar's username link; clears `$_SESSION['username']` and redirects to login." (functional-spec.md Capabilities). Also covers the named **Admin Login** workflow (functional-spec.md Workflows — the branching validation/credential-outcome flowchart).
**Depends on:** BL-001 (CSRF token required on the login form).
**Acceptance basis (domain-model.md):**
- **DR-001 — Login requires both a username and a password** — "submitting the login form with an empty username or empty password redirects back with 'Username is required' / 'Password is required' before any query runs."
- **DR-002 — Login requires an exact single-row username+password match** — "`SELECT * FROM users WHERE username='$uname' AND password='$pass'` must return exactly one row... and that row's `username`/`password` must match exactly, or the login is rejected."
- Per **CQ-002**: "Require schema migration (widen/replace the `password` column) plus `password_hash`/`password_verify`" — replaces the plaintext comparison domain-model.md flags: "`admin/xuli_login.php:22,26`... `$row['password'] === $pass` directly... too narrow to ever hold a real password hash."
- Per **CQ-019**: "Add a `UNIQUE` constraint on `username`... and drop the 'exactly one row' check as unreachable" — resolves the ambiguous-match state DR-002's "exactly one row" check exists to guard against.
- Per **CQ-020**: "Preserve case-sensitive username matching for parity — the rebuild requires an exact-case username match, as the legacy `===` recheck enforces today."
- User entity's `type` field (`int(2)`, default `2`, "1 = admin, 2 = staff") is set into session state (`$_SESSION['login_type']`) on successful login, per the Admin Login workflow's final step, "Set `$_SESSION[username, id, login_type]`; redirect to index.php."
**Verification inputs needed:**
- **DR-001**: GM-001 (empty username), GM-002 (empty password), GM-032 (both keys missing entirely) — all `VERIFIABLE`.
- **DR-002**: GM-003 (exact match succeeds), GM-004 (wrong password rejected) — both `VERIFIABLE`; GM-005 (duplicate username+password rows) is `PROVISIONAL` in manifest.json pending PQ-007, but PQ-007 has since been promoted to and decided as CQ-019 in decisions.md — `scenarios.md` itself has not yet been regenerated to drop this marker, so this item's build should treat GM-005 as settled by CQ-019 even though its manifest status has not yet caught up.
- **DR-002 case-sensitivity**: GM-031 is likewise `PROVISIONAL` pending PQ-008, since promoted to and decided as CQ-020 — same stale-marker caveat as GM-005 above.
- No golden-master fixture exists for CQ-002's password-hashing behavior — hashing is a decided security improvement with no legacy behavior to replay (the legacy path is plaintext comparison end to end); new tests must be authored directly against the rebuilt `password_hash`/`password_verify` path.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-001 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-002 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-003 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-004 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-005 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-031 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-032 (c77a6e7fdb549db426c1cb410bc32c31dd236045)
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-011 — Admin Front Controller Routing & Session Access Gate

**Module:** MOD-001.
**Maps to capability:** No standalone functional-spec.md capability bullet names this directly — it is the routing/access-control infrastructure every other admin capability bullet below depends on to be reachable at all. Grounded instead in architecture.md's L3/L4 components: "Front Controller & Layout (`admin/index.php`, `admin/header.php`, `admin/sidebar.php`) is the aggregation point: `index.php` does `include 'header.php'; include 'sidebar.php';` then `include $page . '.php'`, so every other admin component is reached only through this component."
**Depends on:** BL-010 (a session must be establishable via Login before this gate has anything to check).
**Acceptance basis (domain-model.md):**
- **DR-012 — Admin-only "Users" access is enforced only in the UI, not on the server** (visibility half) — "`admin/sidebar.php:16-18` hides the 'Users' nav link unless `$_SESSION['login_type'] == 1`." This item owns the nav-visibility half of DR-012; module-map.md notes DR-012 is "co-owned with MOD-002: MOD-001 owns the visibility-gate rule's enforcement point, MOD-002 owns the handlers the rule says should — but currently don't — check role server-side" (see BL-013/BL-014 for the handler-side half).
- Per **CQ-001**: "Treat as DEFECT — rebuild should whitelist against the known page set," replacing architecture.md's flagged defect: "`admin/index.php` builds an `include` path directly from an unsanitized GET value with no whitelist... `filter_input` with no filter constant applies no sanitization."
- Session-guard logic architecture.md L4 documents directly: "the session guard `if (isset($_SESSION['id']) && isset($_SESSION['username'])){ ... } else { header('Location: login.php'); exit(); }` wraps the entire nav render and is the sole gate that indirectly protects `index.php`'s later `include $page . '.php'`."
**Verification inputs needed:**
- No `GM-NNN` scenario in `scenarios.md` models the front-controller routing/whitelist behavior or the sidebar's session-guard/nav-visibility rendering directly — the DR-012-tagged scenarios (GM-026/027/028) exercise the *handler-side* bypass (see BL-013/BL-014), not this item's UI-visibility/routing concern. This item's acceptance rests on the architecture.md quotes above and new tests written against the rebuilt router; no baseline replay is possible for the routing behavior since `scenarios.md` never modeled it as a seam.
**Gate:** CLEAR
**Verification:** VERIFIABLE — fixtures: GM-026 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-027 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-028 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

## MOD-002 — User Account Management

_Depends on: MOD-001. module dependency rank undefined — module-map.md describes a dependency cycle. 3 active item(s)._

### BL-012 — User List

**Module:** MOD-002 — owns User per module-map.md.
**Maps to capability:** "Manage user accounts — list at `admin/users.php`..." (functional-spec.md Capabilities). "This capability is visible only to sessions with `login_type == 1` (DR-012) but not server-side enforced" — the visibility half is BL-011's concern; this item is the list rendering itself.
**Depends on:** BL-011 (session gate must exist to reach this screen at all — module-map.md: "MOD-002... Depends on: MOD-001 (a session must exist to reach these screens at all, per the front-controller's session gate)").
**Acceptance basis (domain-model.md):**
- User entity: "A staff/admin account used to authenticate into the back-office," fields `id`, `name`, `username`, `password`, `type` — the list view must render `name`/`username`/`type` for every account.
- Enumeration 3, `users.type` values `1`/`2` ("1 = admin, 2 = staff") — the list should render the role distinguishably.
**Verification inputs needed:**
- No `GM-NNN` scenario targets `admin/users.php`'s list view directly. New capture or acceptance-criteria-only build needed.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-026 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-027 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-028 (c77a6e7fdb549db426c1cb410bc32c31dd236045)
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-013 — User Create/Edit

**Module:** MOD-002.
**Maps to capability:** "...create/edit via `admin/manage_user.php`'s modal form... Create/edit fields: `name` (text input), `username` (text input), `password` (password input — but see DR-013...), `type`/User Type (select/combo, options `Admin` (1) / `User` (2))" (functional-spec.md Capabilities).
**Depends on:** BL-001, BL-011, BL-012.
**Acceptance basis (domain-model.md):**
- **DR-013 — The edit-user form's Password field displays the user's id instead of their password** — "`admin/manage_user.php:24`: `value="<?php echo isset($get['password']) ? $get['id'] : '' ?>"`." Per **CQ-006**'s decision: "Copy-paste defect — leave the Password field blank on edit; do not pre-fill it with the user's id or any other unrelated value" — this item must implement the corrected (blank-on-edit) behavior, not the legacy defect.
- **DR-012 — Admin-only "Users" access is enforced only in the UI, not on the server** (handler half) — "`admin/xuliuser.php` and `admin/delete_user.php`, read in full, contain no equivalent check, so any authenticated session (admin or staff) can reach those handlers directly." Per **CQ-003**: "Add a server-side admin-only (type == 1) check to both handlers" — this item's create/update handler must implement that check; co-owned with MOD-001 per module-map.md, placed here because this item is primarily about MOD-002's own handler.
- Once CQ-002 lands (see BL-010), any password value this form ever does write (on create) must go through `password_hash`, never stored as plaintext — a consequence of BL-010's decision that this item's create path must also honor.
**Verification inputs needed:**
- **DR-013**: GM-029 (edit form pre-fills id as password — the legacy defect) and GM-030 (create form renders blank, contrast case) — both `PROVISIONAL` in manifest.json pending CQ-006, which is already decided; `scenarios.md` has not been regenerated to drop the marker. GM-029 pins the *defect being replaced* — it must not be treated as a target for behavioral parity; only GM-030's blank-field behavior should carry forward, and a new fixture asserting "edit also renders blank" (not just create) is needed since GM-029 as captured proves the opposite.
- **DR-012 create path**: GM-026 (non-admin session can create/update a user — the gap) and GM-028 (admin session, contrast case) — both `PROVISIONAL` pending CQ-003, already decided. GM-026 pins the defect being closed; a new fixture asserting the corrected rejection (non-admin session → create blocked) is needed once the server-side check is built, since no current fixture captures that outcome.
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** VERIFIABLE — fixtures: GM-026 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-027 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-028 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-029 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-030 (c77a6e7fdb549db426c1cb410bc32c31dd236045)
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui

---

### BL-014 — User Delete

**Module:** MOD-002.
**Maps to capability:** "...delete via confirm modal → `admin/delete_user.php`" (functional-spec.md Capabilities, "Manage user accounts" bullet).
**Depends on:** BL-001, BL-011, BL-013.
**Acceptance basis (domain-model.md):**
- **DR-012** (handler half, delete specifically) — "`admin/delete_user.php`... contain[s] no equivalent check" — per **CQ-003**, this handler too must gain the server-side `type == 1` check.
- Per **CQ-021**: "Fix the bind_param defect so delete_room.php/delete_customer.php/delete_user.php actually delete" — this item's delete must actually execute, unlike today.
- Unlike BL-004/BL-008, **CQ-024**'s referential-integrity decision names only Room and Customer ("reject... deleting a room/customer still referenced by an active... booking") — User has no relationship edge to any other entity (domain-model.md Relationships: "`User` carries no relationship edge to any other entity"), so no referential-integrity check applies to this item.
**Verification inputs needed:**
- **GM-027** (`PROVISIONAL` pending CQ-003, already decided) pins the current handler-bypass gap ("Non-admin (staff) session can delete a user account") — needs a new fixture asserting rejection once the server-side check is built.
- **GM-027**'s `outcome`/`error_code` (`DELETE_STATEMENT_PARAM_COUNT_MISMATCH`) also reflects the **pre-CQ-021-fix** bind_param defect, identically to GM-034/GM-035 (BL-004/BL-008) — must be re-captured once that fix lands; the rebuild must not claim parity with GM-027 as currently recorded on either the CQ-003 or the CQ-021 dimension.
**Gate:** CLEAR
**Verification:** VERIFIABLE — fixtures: GM-026 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-027 (c77a6e7fdb549db426c1cb410bc32c31dd236045), GM-028 (c77a6e7fdb549db426c1cb410bc32c31dd236045)

## MOD-007 — Dashboard & Reporting

_Depends on: MOD-002, MOD-003, MOD-004, MOD-005. module dependency rank undefined — module-map.md describes a dependency cycle. 1 active item(s)._

### BL-024 — Admin Dashboard & Reporting

**Module:** MOD-007 — owns the Dashboard per module-map.md; "References (not owned): Booking (MOD-004)... Room (MOD-003)... RoomCategory (MOD-003)... Customer (MOD-005)... User (MOD-002)."
**Maps to capability:** "View dashboard — `admin/home.php`, a read-only page of nine live counts/sums, each rendered by its own `admin/counters/*.php` fragment (Total Bookings, Available Rooms, Checked In, Checked Out, Total Rooms, Total Room Categories, Total Customers, Total Payment, Total Users). No form input." (functional-spec.md Capabilities).
**Depends on:** BL-012, BL-002, BL-005, BL-006, BL-016, BL-017, BL-019, BL-021.
**Acceptance basis (domain-model.md):**
- Booking entity `status` enumeration (drives Total Bookings/Checked In/Checked Out/Total Payment counts, per architecture.md's L3 narrative: "`admin/counters/booking_count.php`'s `'SELECT * FROM booking WHERE status = 0'`" style queries, confirmed in module-map.md's MOD-007 Evidence).
- Room entity `status` enumeration (Available Rooms, Total Rooms counts).
- RoomCategory entity (Total Room Categories count).
- Customer entity (Total Customers count).
- User entity (Total Users count).
- No single `DR-NNN` rule governs aggregate counting itself — each counter simply reads its owning module's entity/enumeration state as already governed by that module's own rules cited in BL-002 through BL-021 above; this item's acceptance basis is the aggregation of those, not a new rule of its own.
**Verification inputs needed:**
- No `GM-NNN` scenario in `scenarios.md` models any of the nine `admin/counters/*.php` fragments — confirmed by scenarios.md's Rule Coverage Check, which tags all 35 fixtures to MOD-001 through MOD-005/MOD-007's *write* paths, never to a read-only aggregate count. Verification for this item should be a direct count/sum assertion against seeded fixture data for each of the nine metrics, not a golden-master replay (no seam was ever defined for counting).
**Gate:** OPEN QUESTIONS — UI fidelity: SQ-013 decided THEME-ONLY, required artifacts missing
**Verification:** NO BASELINE DATA — baseline has been run, but no scenario in scenarios.md cites this item's rules
**UI fidelity:** ⚠ UI GROUNDING MISSING — THEME-ONLY decided (SQ-013) but these artifacts are absent: .specclaw/ui/ui-inventory.md, .specclaw/ui/design-tokens.json, .specclaw/ui/screens/, .specclaw/ui/ui-manifest.json — run /specclaw:bf-ui



## Deferred

None.

## Sequencing Rationale

The draft orders items so that every `Depends on` reference points to an earlier item, and groups by the module-map.md dependency graph with foundational cross-cutting infrastructure placed first:

1. **BL-001 (CSRF Baseline)** comes first because it is the one item every other state-changing item below cites as a dependency — CQ-015 decided this as a uniform requirement across every state-changing form/handler pair in every module, so it must exist conceptually before any write-capability item can be considered complete.
2. **MOD-003 (BL-002–BL-005)** and **MOD-005 (BL-006–BL-008)** come next because module-map.md declares neither has a `Depends on` entry — both own entities (Room/RoomCategory, Customer) that MOD-004's booking flows read or write as a side effect, so their CRUD must be specified first. Room List precedes Room Create/Edit/Delete because the list is how staff reach the create/edit/delete actions in the legacy UI; the same ordering applies to Customer.
3. **BL-009 (Public Marketing)** follows immediately because CQ-008 makes it depend on RoomCategory (BL-005) and Room (BL-002) for its live pricing source — it has no own business rule but a data dependency the module-map's own "References (not owned)" note for MOD-006 does not capture (module-map explicitly found no live query from MOD-006, but CQ-008 changes that going forward).
4. **MOD-001 (BL-010–BL-011)** then **MOD-002 (BL-012–BL-014)** reflect the module-map's documented co-dependency: "MOD-001 -->|reads users| MOD-002" and "MOD-002... Depends on: MOD-001 (a session must exist to reach these screens at all)." Login (BL-010) is sequenced before the Front Controller/Session Gate (BL-011) because a session must be establishable before there is anything for the gate to check; User Management (BL-012–014) follows because its screens are only reachable once the gate (BL-011) exists.
5. **MOD-004 (BL-015–BL-023)** comes after MOD-003/MOD-005/MOD-001 because module-map.md declares "MOD-004... Depends on: MOD-003 (needs an available room to assign), MOD-005 (creates/updates the matching customer record as a side effect)" — and because CQ-011's new Customer FK and CQ-015's CSRF baseline must exist first. Within MOD-004, the item order follows the guest's own lifecycle: intake (BL-015) → pending-list/cancel (BL-016) → check-in conversion (BL-017) and walk-in intake (BL-018–019, in parallel with the booking-conversion path since they are alternative entry points to the same "checked-in" state) → check-in/out list (BL-020) → checkout/billing (BL-021) → the two smaller in-progress-stay items that read from the same list (BL-022, BL-023).
6. **BL-024 (Dashboard)** is last because module-map.md declares "MOD-007... Depends on: MOD-002, MOD-003, MOD-004, MOD-005 (reads each module's owned table for a count/sum)" — every counter it renders reads state that items 2–23 above are responsible for producing correctly first.

## Coverage Check

<!--
  Capability-bullet coverage, authored by the planner agent (bash never
  writes prose it cannot verify against the source documents) and carried
  by bash: this run's draft wins, otherwise the prior file's section is
  preserved verbatim, otherwise a line saying plainly that it is absent.

  Each bullet is accounted for on its own line, in this countable form so
  that the per-module rollup below can be computed mechanically rather than
  asserted:

    - **MOD-002** — "<capability bullet, quoted>" -> BL-014
    - **MOD-002** — "<capability bullet, quoted>" -> EXCLUDED: <reason>
    - **MOD-002** — "<capability bullet, quoted>" -> ORPHAN

  Granularity is unchanged — this is still one line per individual
  capability bullet (and per distinct clause of a compound bullet), never
  one line per module. The "### Module Coverage Rollup" subsection is
  bash-computed by counting these lines per module and is re-derived from
  scratch every run; a prior run's copy is dropped before the new one is
  appended, exactly as the UI Screen Coverage subsection is. When no line
  matches the countable form, the rollup says it is not computable rather
  than reporting 0/0 — which would read as "nothing to cover."

  Under a --module scoped run, only the scoped module's lines are replaced;
  every other module's accounting is preserved by line-level surgery.
-->

- **MOD-006** — "Browse marketing content — `homepage/index.php`, `homepage/room.php`, `homepage/service.php`, `homepage/food.php`. Read-only pages..." → BL-009
- **MOD-004** — "Submit a room reservation — exposed by `homepage/book.php`'s 'Submit' button..." → BL-015
- **MOD-004** — Workflow "Public Reservation Intake" → BL-015
- **MOD-001** — "Log in — `admin/login.php`'s 'Login' button..." → BL-010
- **MOD-001** — "Log out — `admin/logout.php`..." → BL-010 (merged with Log in; see BL-010's Merge rationale)
- **MOD-001** — Workflow "Admin Login" → BL-010
- **MOD-007** — "View dashboard — `admin/home.php`..." → BL-024
- **MOD-003** — "Manage rooms — list at `admin/rooms.php` (filter by category...)" → BL-002
- **MOD-003** — "Manage rooms — ...create/edit via `admin/manage_room.php`'s modal form..." → BL-003
- **MOD-003** — "Manage rooms — ...delete via a confirm modal posting to `admin/delete_room.php`." → BL-004
- **MOD-004** — "View/convert pending bookings — list at `admin/booked.php`... 'Cancel' button (confirm modal → `admin/delete_booking.php`...)" → BL-016
- **MOD-004** — "View/convert pending bookings — ...'Check In' button that opens `admin/manage_booking.php`..." → BL-017
- **MOD-004** — Workflow "Booking → Check-In Conversion" → BL-017
- **MOD-004** — "Walk-in check-in — list of available rooms at `admin/check_in.php`..." → BL-018
- **MOD-004** — "Walk-in check-in — ...'Check-in' button opens `admin/manage_check_in.php`..." → BL-019
- **MOD-004** — Workflow "Walk-In Check-In" → BL-019
- **MOD-004** — "Check out a guest / record payment — list of checked-in stays at `admin/check_out.php`..." → BL-020
- **MOD-004** — "Check out a guest / record payment — ...ajax-loads `admin/manage_check_out.php`..." → BL-021
- **MOD-004** — Workflow "Guest Check-Out & Billing" → BL-021
- **MOD-004** — "Edit an in-progress stay's checkout date — ... `admin/edit_check_in.php` ... `admin/xulieditcheckin.php`..." → BL-022
- **MOD-004** — "View a completed stay's detail — ... `admin/view_check_out.php`..." → BL-023
- **MOD-005** — "Manage customers — list at `admin/customers.php`..." → BL-006
- **MOD-005** — "Manage customers — ...create/edit via `admin/manage_customer.php`'s modal form..." → BL-007
- **MOD-005** — "Manage customers — ...delete via confirm modal → `admin/delete_customer.php`." → BL-008
- **MOD-002** — "Manage user accounts — list at `admin/users.php`..." → BL-012
- **MOD-002** — "Manage user accounts — ...create/edit via `admin/manage_user.php`'s modal form..." → BL-013
- **MOD-002** — "Manage user accounts — ...delete via confirm modal → `admin/delete_user.php`." → BL-014

**Orphaned:** none.

**Additional items not tied to a functional-spec.md capability bullet** (recorded here for transparency, not as coverage gaps — none of these represent an uncovered legacy behavior; they are new scope or cross-cutting infrastructure decided in decisions.md): BL-001 (CSRF Protection Baseline, CQ-015), BL-005 (Room Category Management CRUD, CQ-007), BL-011 (Front Controller Routing & Session Access Gate, architecture.md L3/L4 + CQ-001, not itself a functional-spec.md bullet).

**Confirmed:** no backlog item was drafted for any of the four "No Legacy Behaviour Exists" entries in scenarios.md (a fourth room category / out-of-`{1,2,3}` category id; a pending booking with a non-zero room already assigned; a decreasing `customers.charges` value; a `users.type` value reachable through the admin UI other than `1`/`2`) — nothing implements what was never reachable in the legacy app, per the collect-step's own instruction.

### Open Questions Blocking Readiness

None — no open questions touch any item's acceptance basis. All 22 extracted `CQ-NNN` questions and all blocking `SQ-NNN` questions (SQ-001 through SQ-005, SQ-013, SQ-014) are answered in `decisions.md`. The 7 remaining open questions (SQ-006, SQ-008, SQ-010, SQ-011, SQ-012, UQ-001, UQ-002) are all marked `Blocking: no` in `clarifications.md` and none of their Finding/Why-it-matters text was cited in any item's acceptance basis above — SQ-006 (UI framework), SQ-008 (browser matrix), SQ-010 (non-functional scale targets), and SQ-011 (operational tooling) concern implementation/infrastructure choices orthogonal to the business-rule acceptance criteria captured here; SQ-012 (blanket fidelity default) is moot because every legacy behavior flagged as a possible defect in this codebase already has its own specific, answered `CQ-NNN` (CQ-001 through CQ-006, CQ-016, CQ-019 through CQ-023) rather than falling back to the blanket default; UQ-001 (offline mode) and UQ-002 (mobile app) describe capabilities with no corresponding legacy behavior or backlog item at all.

### Module Coverage Rollup

_Bash-computed by counting the per-module capability-coverage lines above. Bullet granularity is unchanged — every bullet is still accounted for individually; this only rolls those counts up per module._

- **MOD-003 — Room & Rate Management:** 3/3 capability bullets covered; 0 excluded; 0 orphaned; quality PASS (0 open QI)
- **MOD-005 — Customer Management:** 3/3 capability bullets covered; 0 excluded; 0 orphaned; quality PASS (0 open QI)
- **MOD-006 — Public Marketing Site:** 1/1 capability bullets covered; 0 excluded; 0 orphaned; quality WARN (0 open QI)
- **MOD-004 — Booking & Stay Lifecycle:** 13/13 capability bullets covered; 0 excluded; 0 orphaned; quality HIGH (1 open QI)
- **MOD-001 — Authentication & Access Control:** 3/3 capability bullets covered; 0 excluded; 0 orphaned; quality PASS (0 open QI)
- **MOD-002 — User Account Management:** 3/3 capability bullets covered; 0 excluded; 0 orphaned; quality PASS (0 open QI)
- **MOD-007 — Dashboard & Reporting:** 1/1 capability bullets covered; 0 excluded; 0 orphaned

Modules measured HIGH on at least one code-quality dimension warrant extra rebuild attention — their legacy implementation is the hardest to read, so it is the easiest to reproduce a bug from or to under-scope: MOD-004, MOD-UNASSIGNED.

## Stub Retirement

<!--
  Bash-computed every run from .specclaw/analysis/module-stubs.md, never
  agent-narrated. For every ACTIVE or RETIRING dependency-bypass stub
  (templates/CONTRACT.md (m)): is the thing it substitutes built yet, and if
  so, exactly what does it take to retire it?

  THE TRIGGER IS A DECLARED SIGNAL, NOT PROSE. A stub becomes "ready to
  retire" only when the item it substitutes carries a line beginning "BUILT:"
  inside its own "**Status notes (human-added):**" block — e.g.
  "BUILT: PR #42, merged 2026-08-10". Free text is not parsed: specclaw
  records no built state of its own, and reading "done last week" as a
  completion signal would be exactly the guess the bypass mechanism exists
  to prevent. When a stub substitutes a whole MOD-###, EVERY active item of
  that module must carry the signal — a module is not built because one of
  its items is.

  WHO DOES WHAT. Retirement is a human/Claude handoff, and each step below
  names its actor. In short: a human decides the stub is gone and removes
  the code; Claude re-runs the replays and, only on a clean run, flips the
  registry entry to RETIRED citing that run id; a human decides what to do
  with a FAIL. Claude never removes stub code on its own initiative and
  never retires an entry on an unclean run.

  The three-state flow (ACTIVE -> RETIRING -> RETIRED) exists because with
  only two states the run that PROVES a stub is gone is itself stamped
  tainted, and flipping to RETIRED first leaves a failing re-replay falsely
  marked retired. See CONTRACT.md (m.4).

  This section changes no Gate, no Verification, and no ordering. It is a
  work list.
-->

_No active dependency bypass stubs. Every item is being built on the real modules it depends on._

## Item Splits

<!--
  Bash-computed every run from .specclaw/analysis/item-splits.md, never
  agent-narrated (templates/CONTRACT.md (o)). Which backlog items are
  PARTIALLY BUILT, what each is still missing, and — once every blocked-until
  item carries a declared "BUILT:" note — the exact steps to resume.

  A SPLIT IS NOT A STUB. Nothing was faked, so nothing is tainted and there is
  nothing to retire. What a split puts in question is whether the ITEM IS
  FINISHED, which is why it gets its own section rather than a row in Stub
  Retirement.

  THE SAME DECLARED TRIGGER as stub retirement: an entry becomes
  READY-TO-RESUME only when every id in its "Blocked until" list carries a
  literal "BUILT:" line in that item's own Status-notes block. Prose is never
  parsed. Unlike stub retirement, that transition is WRITTEN by bash here (a
  single Status-line rewrite, one direction only) — it is a pure function of
  declared data, so there is no human judgement to defer to, and a stale
  ACTIVE would be indistinguishable from "nobody got round to it".

  COMPLETE is a handoff, not a computation: it needs a clean
  /specclaw:bf-replay --item BL-### run to cite, and split-update refuses it
  straight from ACTIVE.

  This section changes no Gate, no Verification, and no ordering. It is a
  work list.
-->

_No item splits. Every backlog item is being built whole._

## Change Report

<!--
  Populated only by /specclaw:bf-rebuild-plan --refresh — bash-computed by
  diffing this run's fresh Gate/Verification against the prior file's own
  stored Gate:/Verification: lines, never agent-narrated. On a first-ever
  run this section reads "Not applicable."
-->

Not applicable — this is the first-ever run.
