# Proposal: Cross-Cutting CSRF Protection Baseline (BL-001)

**Created:** 2026-09-14
**Status:** 🟡 Draft

## Problem

The legacy application has no CSRF protection on any state-changing form or handler anywhere (codebase-report.md § Risks/Tech-Debt). Every write-capability item in the rebuild backlog — across every module (Room, Customer, Booking, User) — depends on a uniform CSRF mechanism existing first, per **CQ-015**: "Should the rebuild add CSRF protection to state-changing forms, given the legacy app has none anywhere? ... Decision: Add CSRF tokens to every state-changing form/handler in the rebuild."

Without this in place first, every downstream write item (BL-007, BL-008, BL-013, BL-014, BL-015 onward) would either ship without CSRF protection or would each have to invent their own ad-hoc mechanism, producing inconsistent enforcement across modules. This item exists precisely to prevent that: build the mechanism once, centrally, before anything else needs it.

## Proposed Solution

Implement a single, centrally-integrated CSRF token mechanism in the Laravel rebuild:

- Mint a CSRF token into the session (Laravel's built-in `VerifyCsrfToken` middleware / `@csrf` Blade directive, since the target stack is Laravel Blade + Bootstrap per bootstrap-manifest.json).
- Every state-changing form (POST/PUT/PATCH/DELETE) routed through the admin front controller carries a hidden token field.
- Every such handler verifies the token before any business logic runs; a missing/invalid token is rejected before the handler's own logic executes.

This is a client-orchestrated precondition with no governing `DR-NNN` rule (domain-model.md has none for it) — it exists solely because of the CQ-015 security-architecture decision, not a legacy behavior being replicated.

## Scope

### In Scope
- CSRF token minting into the session on every admin page render.
- Token embedded in every state-changing form template used by later backlog items.
- Middleware/handler-level verification that rejects requests with a missing or invalid token, before any business logic runs.
- Dedicated new tests proving: token-present-and-valid → request proceeds; token-absent-or-invalid → request is rejected before any business logic runs.

### Out of Scope
- Any specific module's business logic (Customer, Room, Booking, User CRUD) — those are separate backlog items (BL-006 onward) that will consume this mechanism once it exists.
- Golden-master replay verification — no baseline scenario exists for this, since the legacy app has zero CSRF protection to replay (scenarios.md's 35 fixtures all pin the legacy app's as-shipped, CSRF-free behavior). New tests are authored directly against the rebuilt handlers instead.

## Impact

- **Files affected:** ~3–5 (estimated) — middleware/config wiring, a shared form-partial or Blade component for the token field, and its test(s).
- **Complexity:** small
- **Risk:** low

## Open Questions

None — BL-001 has no unmet dependencies (bypass-check: clean) and no golden-master fixture is expected to exist for it, per its own Verification Inputs Needed note in rebuild-backlog.md.

---

**To proceed:** Review this proposal and approve to begin planning.
