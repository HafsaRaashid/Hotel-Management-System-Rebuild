# Decisions: Hotel-Management-System

**Date generated:** 2026-09-12
**Source:** .specclaw/analysis/clarifications.md

<!--
  This is the clean, pinnable decision record /specclaw:bf-clarify --resolve
  produces from clarifications.md's answered questions, swept across all
  three question families (CQ-NNN/SQ-NNN/UQ-NNN). Every entry below is a
  mechanical transcription of an already-answered question — the
  Answer/Decided by/Date fields are transcribed, never reinterpreted. Each
  entry carries a **Family:** line (Extracted | Standard bank | Custom
  (per-repo)) derived mechanically from the question's ID prefix, so a
  reader can tell at a glance whether a decision came from this repo's own
  code, the plugin's standard bank, or a per-repo custom question.
  Re-running --resolve is idempotent: it always reflects the current state
  of clarifications.md's answered blocks, replacing this file's prior
  content wholesale (the prior version is archived, never lost).

  Pin this file — add `.specclaw/analysis/decisions.md` to config.yaml's
  `context.pin` (raise `max_lines` accordingly) and `git add` it, so every
  downstream /specclaw:propose, /specclaw:plan, and /specclaw:build cites
  these decisions as grounding instead of re-deriving them. Discovery
  enumerates via `git ls-files` — an untracked file is invisible to it.
-->

## Decisions

### CQ-001 — Admin router's unwhitelisted dynamic include: preserve or fix?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Treat as DEFECT — rebuild should whitelist against the known page set.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-001 (bf-architecture-analyst, Trigger T4) — L3 "Front Controller & Layout" component (admin/index.php), L4 Front Controller routing diagram (admin/index.php)

### CQ-002 — Plaintext password storage/comparison: preserve or require hashing?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Require schema migration (widen/replace the password column) plus password_hash/password_verify.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-002 (bf-architecture-analyst, Trigger T4) — L3 "Authentication" component (admin/login.php, admin/xuli_login.php), Data layer / `users` table schema

### CQ-003 — User Management handlers: add a real server-side admin-only check?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Add a server-side admin-only (type == 1) check to both handlers.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-003 (bf-domain-analyst, Trigger T4) — DR-012, MOD-002 (User Account Management — `admin/xuliuser.php`, `admin/delete_user.php`)

### CQ-004 — Unrecognized/unselected room-type value: reject or keep silent Deluxe default?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Treat as a defect — require an explicit, validated room_type selection and reject the submission otherwise.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-004 (bf-domain-analyst, Trigger T4) — DR-006, MOD-004 (Booking & Stay Lifecycle — `homepage/connect.php`, `admin/xulicheckin.php`)

### CQ-005 — Checkout payment amount: validate server-side against amount due?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Treat as a defect — add server-side validation matching the client-side min (payment >= computed amount due).
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-005 (bf-domain-analyst, Trigger T4) — DR-010, MOD-004 (Booking & Stay Lifecycle — `admin/manage_check_out.php`, `admin/xulicheckout.php`)

### CQ-006 — manage_user.php password-field pre-fill showing the user's id: copy-paste bug?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Copy-paste defect — leave the Password field blank on edit; do not pre-fill it with the user's id or any other unrelated value.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Promoted from PQ-006 (bf-domain-analyst, Trigger T2) — DR-013, MOD-002 (User Account Management — `admin/manage_user.php`)

### CQ-007 — Should the rebuild add an admin screen to manage room categories/pricing, which the legacy app never had?

- **Type:** SCOPE
- **Family:** Extracted
- **Decision:** Add a full CRUD admin screen for room categories (create/edit/delete tiers, live price editing).
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** domain-model.md § RoomCategory entity (Named Gap); functional-spec.md § Named Gaps — citing `admin/manage_room.php:32-34`, `admin/manage_check_in.php`, `homepage/book.php:24-26`

### CQ-008 — Should public-facing room pricing be sourced live from the same pricing table the admin/checkout side uses, instead of hardcoded marketing text?

- **Type:** DECISION
- **Family:** Extracted
- **Decision:** Drive public pricing display from the same live pricing data the admin/checkout path uses.
- **Decided by:** Hafsa
- **Date:** 2026-09-10
- **Source:** functional-spec.md § Named Gaps ("Public-site room pricing is not sourced from the data model") — citing `homepage/index.php`, `homepage/room.php`

### CQ-009 — DR-003: Is there a reason booking reference numbers are drawn from the range 0–999999999, or can the rebuild choose a different scheme?

- **Type:** MECHANICAL
- **Family:** Extracted
- **Decision:** Adopt the same range as-is (0–999999999) for behavioral/format parity.
- **Decided by:** Hafsa
- **Date:** 2026-09-10
- **Source:** domain-model.md § Business Rules, DR-003 — `homepage/connect.php:6-12`, `admin/xulicheckin.php:30-36`

### CQ-010 — DR-004: Is there a reason customer ids are drawn from the range 0–99999999, or can the rebuild choose a different scheme?

- **Type:** MECHANICAL
- **Family:** Extracted
- **Decision:** Adopt the same range as-is (0–99999999).
- **Decided by:** Hafsa
- **Date:** 2026-09-10
- **Source:** domain-model.md § Business Rules, DR-004 — `homepage/connect.php:13-19`, `admin/xulicheckin.php:46-53`, `admin/xulicustomer.php:6-14`

### CQ-011 — Should the rebuild introduce an explicit Customer↔Booking foreign key, instead of matching by phone number at the application level?

- **Type:** DECISION
- **Family:** Extracted
- **Decision:** Introduce an explicit customer_id foreign key on booking, populated at booking/check-in time; stop matching by phone.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** domain-model.md § Relationships — `CUSTOMERS }o--o{ BOOKING` narrative, citing `homepage/connect.php:48`, `admin/xulicheckin.php:71`, `admin/xulicheckout.php:17`

### CQ-012 — Should the rebuild add real phone-number format validation, given the legacy app has none anywhere?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Add real phone-number format validation (client- and server-side).
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** functional-spec.md § Named Gaps ("The `phone` field's `type="Phone"` is not a valid HTML5 input type") — `homepage/book.php:17`, `admin/manage_check_in.php:58`

### CQ-013 — Should the rebuild implement a real room-availability search, matching the decorative form the legacy homepage never wired up, or drop it?

- **Type:** SCOPE
- **Family:** Extracted
- **Decision:** Drop it — unimplemented decorative markup with no evidenced product intent.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** functional-spec.md § UI Inventory (`homepage/index.php` row) — "Contains a second, unused `<form action="/action_page.php" target="_blank">` search-availability widget (date text ×2, number ×2) that posts nowhere in-app"

### CQ-014 — Should booking cancellation remain a hard delete, or should the rebuild preserve a record for audit purposes?

- **Type:** DECISION
- **Family:** Extracted
- **Decision:** Add a "cancelled" status value (or soft-delete/archive record) so cancelled bookings remain queryable.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** functional-spec.md § Named Gaps ("Booking cancellation (`admin/delete_booking.php`) is a hard delete")

### CQ-015 — Should the rebuild add CSRF protection to state-changing forms, given the legacy app has none anywhere?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Add CSRF tokens to every state-changing form/handler in the rebuild.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** codebase-report.md § Risks/Tech-Debt ("No CSRF protection on any state-changing form")

### CQ-016 — Does editing a checked-in stay's checkout date need to recompute the stored `days_of_stay` value, given it currently doesn't?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Treat as a defect — recompute and persist days_of_stay whenever dateout (or datein) changes.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** domain-model.md § Booking entity ("`days_of_stay` — stated length of stay (not always recomputed consistently — see DR-009)") and § DR-009; functional-spec.md § "Edit an in-progress stay's checkout date" workflow (`admin/xulieditcheckin.php`)

### CQ-019 — Duplicate username+password rows: enforce username uniqueness, or preserve the legacy "exactly one row" login lockout?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Add a `UNIQUE` constraint on `username` in the rebuild schema, and drop the "exactly one row" check as unreachable. Note the `users` table's collation is case-insensitive (`utf8mb4_vietnamese_ci`), so this constraint also makes `"Admin"` and `"admin"` the same row — which is consistent with, and does not conflict with, CQ-020's case-sensitive *comparison* decision.
- **Decided by:** Hafsa
- **Date:** 2026-09-10
- **Source:** Promoted from PQ-007 (bf-baseline-designer, design mode, Trigger T4) — DR-002, MOD-001 (`admin/xuli_login.php`), GM-005

### CQ-020 — Login username case-sensitivity: the DB collation and the app's own recheck disagree

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Preserve case-sensitive username matching for parity — the rebuild requires an exact-case username match, as the legacy `===` recheck enforces today. Passwords are out of scope here and follow CQ-002's `password_verify` hashing decision. GM-031 therefore replays as a MATCH and needs no sanctioned divergence. This may be relaxed to case-insensitive later without breaking the golden master, since loosening only admits logins that are currently rejected.
- **Decided by:** Hafsa
- **Date:** 2026-09-10
- **Source:** Promoted from PQ-008 (bf-baseline-designer, design mode, Trigger T2) — DR-002, MOD-001 (`admin/xuli_login.php`), GM-031

### CQ-021 — Should the rebuild preserve the delete_*.php handlers' current defective behaviour, or fix the bind_param defect that makes every "delete" a silent no-op?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Fix the bind_param defect so delete_room.php/delete_customer.php/delete_user.php actually delete. No evidence the no-op was ever intended; consistent with this project's pattern of fixing rather than preserving broken legacy behaviour (CQ-002, CQ-014, CQ-019).
- **Decided by:** Hafsa
- **Date:** 2026-09-11
- **Source:** Promoted from PQ-009 (bf-baseline-designer (harness mode, GM-027/GM-034/GM-035), Trigger T4) — DR-012 (via GM-027), PS-1 (via GM-034), PS-2 (via GM-035), MOD-002, MOD-003, MOD-005

### CQ-022 — Should the rebuild preserve walk-in check-in's crash-on-new-customer defect, or fix the closed-connection reuse bug?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Fix the connection-lifecycle bug — move `$con->close()` to the end of admin/xulicheckin.php, after the customer-insert branch, so walk-in check-in for a new customer completes and redirects instead of crashing.
- **Decided by:** Hafsa
- **Date:** 2026-09-11
- **Source:** Promoted from PQ-010 (bf-baseline-designer (harness mode, GM-015/GM-017), Trigger T4) — DR-006 (via GM-015), DR-008 (via GM-017), MOD-004

### CQ-023 — Checkout's room-free write matches by room display-name text, not room id: preserve or fix?

- **Type:** DEFECT
- **Family:** Extracted
- **Decision:** Fix — match by room id throughout, including in the checkout composite write, making this write path consistent with every other room-status write in the app (DR-007, DR-008).
- **Decided by:** Hafsa
- **Date:** 2026-09-11
- **Source:** functional-spec.md § "Guest Check-Out & Billing" workflow, step 3 — `admin/xulicheckout.php:22`; cross-referenced by `.specclaw/baseline/scenarios.md` GM-025 and `.specclaw/baseline/seams.md` SV-9

### CQ-024 — Should the rebuild add referential-integrity protection against deleting a room or customer still referenced by an active booking?

- **Type:** DECISION
- **Family:** Extracted
- **Decision:** Add a referential-integrity check to both delete handlers — reject (or require confirmation for) deleting a room/customer still referenced by an active (not checked-out/cancelled) booking.
- **Decided by:** Hafsa
- **Date:** 2026-09-11
- **Source:** domain-model.md § Relationships (`ROOMS ||--o{ BOOKING` and `CUSTOMERS }o--o{ BOOKING` narratives) and `Data/myhotel.sql` (no `FOREIGN KEY` constraint declared anywhere in the schema); cross-referenced by `.specclaw/baseline/seams.md` § "Data/persistence boundary seams" (PS-1, PS-2) and `.specclaw/baseline/scenarios.md` GM-034/GM-035

### SQ-001 — Target platform

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Web application.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-002 — Database engine and hosting

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Keep the legacy database engine as-is (MySQL/MariaDB).
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-003 — Hosting/deployment model

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Cloud-hosted, single-tenant.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-004 — Authentication/authorization approach

- **Type:** TARGET-GAP
- **Family:** Standard bank
- **Decision:** Add real authentication/authorization, sized to the target platform (covers CQ-002 and CQ-003 together).
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-005 — Existing production data

- **Type:** SCOPE
- **Family:** Standard bank
- **Decision:** Migrate all existing production data.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-006 — UI framework / component library

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Laravel Blade views with Bootstrap retained for the admin CRUD modal pattern. Keeps the modal-based list+form interaction staff already know, lowest-risk path from raw PHP, no separate frontend build or API layer needed for the admin side. Consistent with SQ-013's THEME-ONLY policy (keep the look, rebuild the plumbing).
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-008 — Browser/device/OS support matrix

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Not yet decided — defer to an implementation-time ADR.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-010 — Non-functional targets

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Not yet known — defer to a later capacity-planning pass.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-011 — Operational requirements

- **Type:** SCOPE
- **Family:** Standard bank
- **Decision:** Add standard backups/logging/monitoring/CI-CD from day one. The legacy app holds real business data (customer billing history, bookings) and its total absence of any operational tooling is a gap the rebuild should not silently inherit.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-012 — Fidelity default

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** Default to faithful reproduction of legacy behaviour unless a specific CQ says otherwise. Safer default for a fidelity-focused rebuild; every deliberate fix is already tracked as its own named CQ, so nothing changes silently.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-013 — UI fidelity policy

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** THEME-ONLY — keep the colour palette/branding tokens; layout is reinterpreted for the target platform.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### SQ-014 — Target backend stack

- **Type:** DECISION
- **Family:** Standard bank
- **Decision:** PHP + a modern framework (Laravel), including its data-access/ORM approach.
- **Decided by:** Hafsa
- **Date:** 2026-09-09
- **Source:** Standard bank v2 (references/clarify-standard-questions.md)

### UQ-001 — Should offline mode be supported?

- **Type:** SCOPE
- **Family:** Custom (per-repo)
- **Decision:** No — always-online is fine for this rebuild. No legacy behaviour requires offline support.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** .specclaw/analysis/custom-questions.md — "Should offline mode be supported?"

### UQ-002 — Do we need a mobile app eventually?

- **Type:** DECISION
- **Family:** Custom (per-repo)
- **Decision:** No — web-responsive is enough. No mobile client exists today and nothing in the analysis suggests one is needed; a responsive Blade UI (per SQ-006) covers staff on tablets/phones.
- **Decided by:** Hafsa
- **Date:** 2026-09-12
- **Source:** .specclaw/analysis/custom-questions.md — "Do we need a mobile app eventually?"

## ADR Promotion Candidates

- **CQ-001** — Admin Router Whitelisting Strategy — Closes an unsanitized dynamic-include vulnerability by fixing the routing architecture for the whole admin front controller, not just one page.
- **CQ-002** — Password Storage and Hashing — A schema-plus-authentication-path change (widen password column, adopt password_hash/password_verify) affecting the entire login architecture and every future credential-handling code path.
- **CQ-011** — Customer-Booking Relationship via Explicit Foreign Key — A relational data-model decision replacing phone-based application-level matching, affecting every booking/check-in/checkout code path and the schema itself.
- **CQ-014** — Booking Cancellation Audit Trail — Changes the Booking entity's lifecycle model from hard-delete to a status/soft-delete scheme, a durable data-model and auditability decision affecting reporting and future queries.
- **CQ-015** — CSRF Protection Baseline — A cross-cutting security-architecture decision applied uniformly to every state-changing form/handler pair across both containers.
- **CQ-019** — Username Uniqueness as a Schema Invariant — Makes the login identifier unique at the schema level and retires the runtime exactly-one-row check, a durable data-model decision on the credential-lookup path that pairs with CQ-002 and SQ-004.
- **CQ-024** — Referential Integrity on Room and Customer Deletion — A durable data-integrity decision governing deletion behavior across Room and Customer, directly paired with CQ-011's explicit foreign key and triggered by CQ-021 making delete functional again — belongs in the same schema-integrity ADR as CQ-011 rather than standing alone.
- **SQ-001** — Target Platform: Web Application — Foundational technology-stack decision that every other architectural choice (hosting, backend stack, UI framework) is conditioned on.
- **SQ-002** — Database Engine and Hosting: Retain MySQL/MariaDB — Foundational persistence-layer decision shaping schema migration, backup, and scaling choices for the whole system.
- **SQ-003** — Hosting and Deployment Model: Cloud-Hosted, Single-Tenant — Foundational infrastructure decision determining cost, scaling, and operational ownership for the entire rebuild.
- **SQ-004** — Authentication and Authorization Model — Foundational security-architecture decision replacing the legacy's plaintext/no-server-side-role-check model across the whole back office.
- **SQ-006** — Target Frontend: Laravel Blade with Bootstrap Retained — Foundational UI-stack decision determining the rendering technology and component library for the entire rebuild, required before any screen-bearing scaffold or item can be built; pairs with SQ-014 (Laravel backend) and SQ-013 (THEME-ONLY fidelity).
- **SQ-011** — Operational Baseline: Backups, Logging, Monitoring and CI/CD from Day One — A durable, cross-cutting operational-architecture decision covering every environment and deployment the rebuild ships, replacing the legacy app's total absence of any of this.
- **SQ-013** — UI Fidelity Policy: Theme-Only — A durable design-direction decision governing how closely both the public site and admin panel must match legacy branding, gating downstream UI workstream activation.
- **SQ-014** — Target Backend Stack: PHP + Laravel — Foundational technology-stack decision determining language, framework, and data-access approach for the entire rebuild, required before scaffolding can begin.


## Outstanding Questions

All questions in clarifications.md have been answered.
