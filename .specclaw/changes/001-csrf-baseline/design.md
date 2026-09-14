# Design: Cross-Cutting CSRF Protection Baseline (BL-001)

**Change:** 001-csrf-baseline
**Created:** 2026-09-14

## Technical Approach

Laravel 12's default `web` middleware group already includes `ValidateCsrfToken` (`vendor/laravel/framework/.../Configuration/Middleware.php:490`), and `bootstrap/app.php`'s `withMiddleware()` callback does not remove it. There is no missing mechanism to build — the task is to **prove it, in a real HTTP round-trip, and to record the convention** every later write-form must follow.

Since no capability route exists yet (routes/web.php intentionally carries only the foundation's `shell` view route — see its own header comment: "every backlog item replaces or extends it, one item at a time"), this change does not add a production route of its own. Adding a throwaway POST route to `routes/web.php` would misrepresent it as a capability, which it is not.

Instead, the feature test registers its own temporary route inline (`Route::post(...)` inside the test), which is Laravel's standard idiom for exercising middleware/framework behavior in isolation — the route exists only for the duration of the test process and never ships. This keeps the proof honest: it verifies the *actual* `web` middleware stack (registered once, globally, in `bootstrap/app.php`), not a mock of it.

## Architecture

No new components. This change touches only:
1. A feature test exercising the existing, already-registered `ValidateCsrfToken` middleware.
2. A documentation update (`.specclaw/context.md`) recording the `@csrf`-on-every-write-form convention for every subsequent backlog item to follow.

## File Changes Map

| File | Action | Description |
|------|--------|-------------|
| `tests/Feature/CsrfProtectionTest.php` | create | Registers a temporary `Route::post()` inside the test and asserts: no token → 419, invalid token → 419, valid token → 200/reaches handler. |
| `.specclaw/context.md` | create | Project-level convention doc; records "every state-changing Blade form must include `@csrf`" and cites this change as the source. |

## Data Model Changes

None.

## API Changes

None — no production route or endpoint is added by this change.

## Key Decisions

- **Do not hand-roll a CSRF mechanism or wrapper Blade component.** Laravel's built-in `@csrf` directive and `ValidateCsrfToken` middleware already satisfy CQ-015 exactly; adding a custom abstraction on top would be pure indirection (leanforge decision-ladder: stop at the first rung that already holds).
- **Do not add a route to `routes/web.php` for this proof.** The route file's own header states every route it carries is a capability from a specific backlog item; a CSRF proof route belongs to none of them. The inline test route keeps `routes/web.php` accurate.
- **No golden-master fixture is used or expected.** Per rebuild-backlog.md's own note for BL-001, the legacy app has zero CSRF protection to replay — acceptance rests entirely on the new tests described above.

## Risks & Mitigations

- **Risk:** A later backlog item forgets `@csrf` on a new write-form. **Mitigation:** the framework fails safe regardless (missing/invalid token → 419 even without the form remembering to render), and `.specclaw/context.md` records the convention so later planning/build phases carry it forward automatically (per `/specclaw:plan`'s own instruction to read `context.md` before generating spec/design/tasks).
- **Risk:** `SESSION_DRIVER`/`APP_KEY` misconfiguration could make token generation/validation silently fail in a way tests don't catch locally vs. in CI. **Mitigation:** out of scope for this change — covered by the existing `health-check` pillar and standard Laravel environment setup, not a CSRF-specific concern.
