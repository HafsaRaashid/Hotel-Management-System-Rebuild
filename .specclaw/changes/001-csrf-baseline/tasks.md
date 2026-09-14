# Tasks: Cross-Cutting CSRF Protection Baseline (BL-001)

**Change:** 001-csrf-baseline
**Created:** 2026-09-14
**Total Tasks:** 2

## Summary

Two small tasks, one wave (no dependency between them, no wave to serialize on). Proves Laravel's already-registered `ValidateCsrfToken` middleware behaves per AC-1/AC-2/AC-3 via a feature test, and records the `@csrf`-on-every-write-form convention in `.specclaw/context.md` (AC-4) so every later backlog item inherits it.

## Tasks

### Wave 1 — CSRF proof + convention record

- [x] `T1` — Feature test proving CSRF is enforced end-to-end
  - Files: `tests/Feature/CsrfProtectionTest.php` (create)
  - Estimate: small
  - Kind: test
  - Depends: none
  - Notes: Register a temporary `Route::post('/__csrf-baseline-test', fn () => response('ok'))->middleware('web')` inline in the test (setUp or per-test). Three cases: (1) POST with no `_token` → assert HTTP 419, response never reaches the closure (use a flag/log the closure sets, assert it was never set); (2) POST with an invalid `_token` value → assert HTTP 419 same way; (3) GET the route first is not needed — instead obtain a valid token via `$this->app['session']->token()` or by rendering a view with `@csrf` and extracting it, then POST with that token → assert HTTP 200 and that the closure ran. Use Laravel's `Illuminate\Foundation\Testing\RefreshDatabase`/`WithoutMiddleware` NOT applied here — the whole point is testing the real middleware stack, so no middleware should be disabled or mocked.

### Wave 1 — (continued, independent of T1)

- [x] `T2` — Document the CSRF convention in project context
  - Files: `.specclaw/context.md` (create)
  - Estimate: small
  - Kind: docs
  - Depends: none
  - Notes: State plainly: "Every state-changing Blade form (POST/PUT/PATCH/DELETE) must include `@csrf`. Enforced by Laravel's default `ValidateCsrfToken` middleware in the `web` group (see `bootstrap/app.php`) — a request without a valid token is rejected with HTTP 419 before any handler logic runs. Established by change `001-csrf-baseline` (BL-001)." This is read automatically by `/specclaw:plan` for every later change, per the plan skill's step 3 ("Also read `.specclaw/context.md` if it exists").

---

## Legend

- `[ ]` Pending
- `[~]` In Progress
- `[x]` Complete
- `[!]` Failed
