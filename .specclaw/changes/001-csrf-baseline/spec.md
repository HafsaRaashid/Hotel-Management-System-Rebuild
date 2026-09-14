# Spec: Cross-Cutting CSRF Protection Baseline (BL-001)

**Change:** 001-csrf-baseline
**Created:** 2026-09-14
**Status:** 🟡 Draft

## Overview

The legacy application has no CSRF protection on any state-changing form or handler anywhere (codebase-report.md § Risks/Tech-Debt). Per **CQ-015**, the rebuild must add CSRF tokens to every state-changing form/handler, uniformly. This is the one item every later write-capability item (Customer, Room, Booking, User) depends on, so it is built first, on its own, before any of them.

**Codebase finding (this change is grounded in, not invented):** the target stack is Laravel 12 (`composer.json`: `laravel/framework: ^12.0`). Laravel's own `web` middleware group already registers `\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class` by default (`vendor/laravel/framework/src/Illuminate/Foundation/Configuration/Middleware.php:490`), and `bootstrap/app.php`'s `->withMiddleware()` callback (lines 18-23) is currently empty — it does not remove or alter this default. So CSRF verification is **already wired in** for any route declared through `routes/web.php`. What does not yet exist is proof that it actually works end-to-end, and a documented convention that every future write-form must carry Laravel's `@csrf` Blade directive. That is what this change adds.

## Requirements

### Functional Requirements

- **FR-1:** Every state-changing request (POST/PUT/PATCH/DELETE) routed through the `web` middleware group is rejected before any controller/handler logic runs, if it carries no CSRF token or an invalid one.
- **FR-2:** A request carrying a valid CSRF token proceeds to its handler normally.
- **FR-3:** The project's coding convention — every state-changing Blade form must include the `@csrf` directive — is documented in `.specclaw/context.md` so every later backlog item (BL-007, BL-008, BL-013, BL-014, BL-015 onward) inherits it without re-deciding it.

### Non-Functional Requirements

- **NFR-1:** No new middleware package or hand-rolled token mechanism — use Laravel's built-in `ValidateCsrfToken` exactly as the framework provides it, per the decision-ladder default (framework already solves this; do not reinvent it).
- **NFR-2:** The proof must exercise a real HTTP request/response cycle through the actual `web` middleware stack (a feature test using Laravel's HTTP testing helpers), not a unit test that mocks the middleware away.

## Acceptance Criteria

- **AC-1:** A POST request to a `web`-routed endpoint with no `_token` field is rejected with HTTP 419 ("Page Expired"), and the request never reaches the target route's closure/controller action.
- **AC-2:** A POST request to a `web`-routed endpoint with an invalid/mismatched `_token` value is rejected with HTTP 419, same as AC-1.
- **AC-3:** A POST request to a `web`-routed endpoint with a valid CSRF token (obtained from a prior GET to a page rendering `@csrf`) succeeds and reaches the target route's closure/controller action.
- **AC-4:** `.specclaw/context.md` documents the `@csrf`-on-every-write-form convention, citing this change.

## Edge Cases

- **GET requests are never subject to CSRF verification** — `ValidateCsrfToken` only inspects state-changing verbs; this is Laravel's own default behavior and needs no extra handling here.
- **A route added later without `@csrf` in its form** will still be blocked by the middleware (fails safe, HTTP 419) — it is a UX/completeness bug for that future item to catch via its own tests, not a gap in this change's mechanism.

## Dependencies

None. `bypass-check` on BL-001 reported a clean dependency graph (no unmet dependencies).

## Notes

No golden-master fixture exists or can exist for this item: `scenarios.md`'s 35 captured fixtures all pin the legacy app's *as-shipped* behavior, which has zero CSRF protection anywhere — there is nothing to replay as a baseline. Acceptance here rests entirely on new tests written directly against the rebuilt (Laravel) behavior, per rebuild-backlog.md's own "Verification inputs needed" note for BL-001.
