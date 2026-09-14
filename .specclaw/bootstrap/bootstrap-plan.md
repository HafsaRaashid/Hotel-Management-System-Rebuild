# Target Foundation Plan: Hotel-Management-System

**Date:** 2026-09-12
**Repo:** the NEW (rebuild) repository
**Written by:** `bf-bootstrap-architect`, inside `/specclaw:bf-bootstrap`

## Resolved Target Stack

| Part | Resolved as | Decision | Source |
|---|---|---|---|
| Target platform | Web application | SQ-001 | `.specclaw/analysis/decisions.md` |
| Backend language/framework | PHP + Laravel (including Laravel's own Eloquent ORM / data-access approach) | SQ-014 | `.specclaw/analysis/decisions.md` |
| Frontend | Server-rendered Laravel Blade views, Bootstrap retained for the admin CRUD modal pattern; CDN-loaded, no separate frontend build pipeline | SQ-006 | `.specclaw/analysis/decisions.md` |
| Database engine | MySQL/MariaDB, retained from the legacy app | SQ-002 | `.specclaw/analysis/decisions.md` |
| Hosting/deployment model | Cloud-hosted, single-tenant | SQ-003 | `.specclaw/analysis/decisions.md` |
| Auth approach | Real authentication/authorization required, sized to the target platform — a **boundary** only in this stage (`config/auth.php`'s guard/provider structure); no login/logout logic is implemented here | SQ-004 | `.specclaw/analysis/decisions.md` |
| UI fidelity policy | THEME-ONLY (keep colour palette/branding tokens; layout reinterpreted) — but `.specclaw/ui/design-tokens.json` and `ui-inventory.md` do not exist in this repo, so no token *value* is imported (see "UI Token Plumbing" below) | SQ-013 | `.specclaw/analysis/decisions.md` |
| Operational baseline (shape only) | Standard backups/logging/monitoring/CI-CD from day one — informs the logging-channel structure (`config/logging.php`) this stage ships, not a working integration | SQ-011 | `.specclaw/analysis/decisions.md` |
| Production data | All existing production data is migrated eventually — not this stage's job; informs why the migrations mechanism must run cleanly against the same MySQL/MariaDB engine | SQ-005 | `.specclaw/analysis/decisions.md` |

No required decision was found undetermined. `SQ-006` was the one part of the stack the legacy repo's own `target-architecture.md` had marked `PROVISIONAL(SQ-006)` at blueprint time; `decisions.md` (dated 2026-09-12, later than the blueprint) resolves it, so this run treats it as settled.

## Structure

A single Laravel application (no separate frontend/API split — SQ-014, SQ-006), laid out per Laravel's own convention:

```
app/
  Http/Controllers/Controller.php   base controller every future controller extends
  Providers/AppServiceProvider.php  DI/composition root
bootstrap/
  app.php                          composition root: routing paths, middleware, exceptions
  providers.php                    registered service providers
config/                            app, auth, cache, database, filesystems, logging, mail, queue,
                                    services, session — framework-standard config surface
database/
  migrations/                      migration mechanism (framework tables only — see Boundaries)
  seeders/DatabaseSeeder.php        empty; a backlog item adds its own seeder
public/
  index.php                        HTTP entry point
  css/theme.css                    theme mechanism (see UI Token Plumbing)
resources/views/
  layouts/app.blade.php             the one shared layout every screen extends
  shell.blade.php                   placeholder root view (no capability content)
routes/
  web.php                           one shell route, no capability route
  console.php                       empty
tests/
  TestCase.php, Unit/, Feature/     PHPUnit, one trivial test per suite
```

This is the framework's own default project shape (Laravel 11+, the current minimal `bootstrap/app.php`-centric skeleton), so a Laravel developer should find nothing surprising in it. Layer boundaries: `routes/` may only reference `app/Http/Controllers`; controllers may depend on Eloquent models and the container; nothing outside `config/` may read the environment directly (`env()` calls are confined to config files, per Laravel convention).

## Boundaries

- **Frontend → API boundary:** does not exist by design. SQ-006 decided server-rendered Blade views with no separate JS framework, and SQ-014 decided a single Laravel application (no separate API surface for a JS client to call) — Blade views are rendered directly by the same controllers that would otherwise expose an API. The `api-client` pillar is therefore recorded `absent-by-decision`, not skipped by omission.
- **API/route → domain boundary:** `routes/web.php` → controllers extending `app/Http/Controllers/Controller.php`. No domain entity/controller exists yet; each module's own backlog item adds its controller(s) here.
- **Domain → persistence boundary:** Laravel's own Eloquent ORM / query builder, configured in `config/database.php` against the retained MySQL/MariaDB connection (SQ-002). No Eloquent model exists yet for any domain entity (User, Room, RoomCategory, Booking, Customer) — each is created by the backlog item that owns it (see "Not In Scope").
- **Auth boundary:** `config/auth.php` declares the guard (`web`, session-driver) and provider (`users`, Eloquent) shape SQ-004 requires, and intentionally references `App\Models\User` even though no such class exists yet. This is inert: nothing in this scaffold invokes the `auth` guard, so the missing class is never resolved. The first backlog item to touch authentication must add the model, its migration, and the actual login/logout logic together.
- **Error-handling conventions:** `bootstrap/app.php`'s `withExceptions()` closure shapes how an unhandled exception is rendered for a JSON-expecting caller (a `{"error": {"message", "status"}}` envelope) and left to Laravel's own default HTML error pages otherwise. No business rule decides anything in this closure — it is a convention every future controller inherits for free.
- **Logging:** `config/logging.php` is the framework's stock channel structure (`stack` → `single` file today), sized so a later operational item (SQ-011) can add a hosted channel without restructuring anything.

## Testing Approach

- **Backend (`test-backend`, present):** PHPUnit, configured via `phpunit.xml`. `tests/Unit/ExampleTest.php` and `tests/Feature/HealthCheckTest.php` are trivial proofs that the runner executes and that the HTTP kernel/routing/health-check wiring works end to end — neither asserts any domain/capability behaviour.
- **Frontend (`test-frontend`, absent-by-decision):** there is no separate frontend test runner. SQ-006 decided server-rendered Blade views with no separate JS framework or build step; Blade output is exercised through the same PHPUnit feature tests (e.g. `HealthCheckTest::test_the_shell_route_renders` asserts on rendered HTML), so a second, JS-side test project would test nothing a feature test cannot already cover.
- Every future backlog item's replay/acceptance tests are expected to live under `tests/Feature/` alongside `HealthCheckTest.php`, one test class per capability.

## Local Development Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# Point DB_* at a real MySQL/MariaDB instance (SQ-002) — see .env.example.
# No secret is committed: .env is git-ignored, .env.example carries only
# placeholders.

php artisan migrate      # runs the framework-mechanism migrations only —
                          # see database/migrations/, no domain table yet

php artisan serve         # http://127.0.0.1:8000/  (shell)
                          # http://127.0.0.1:8000/up (health check)

php artisan test          # runs the PHPUnit suite (test-backend)
```

## Smoke Verification

| Check | Status | Command / reason |
|---|---|---|
| `api-build` | declared | `composer install --no-interaction --prefer-dist` |
| `api-start` | declared | `php artisan serve --port=8000 & pid=$!; sleep 2; curl -sf http://127.0.0.1:8000/up; kill $pid` — starts, proves it answered, stops |
| `db-connect` | declared | `php artisan db:show` |
| `migrations-infra` | declared | `php artisan migrate --force` (runs the two framework-mechanism migrations only) |
| `test-backend` | declared | `php artisan test` |
| `frontend-build` | SKIPPED | SQ-006 decided no separate frontend build — Bootstrap is CDN-loaded directly in `resources/views/layouts/app.blade.php`, nothing to compile |
| `frontend-start` | SKIPPED | No separate frontend process exists; the same Laravel process serves Blade views (see `api-start`) |
| `test-frontend` | SKIPPED | No separate frontend test runner — see "Testing Approach" |
| `frontend-to-api` | SKIPPED | No frontend/API boundary exists (see "Boundaries") — there is nothing to prove connects |

This agent has no PHP/Composer toolchain available in its own execution environment (verified: `php`/`composer` are absent from `PATH`), so these commands are declared, not executed, by this agent. The orchestrating skill's own `smoke` step runs them where a PHP toolchain is available.

## UI Token Plumbing

SQ-013 decided THEME-ONLY (keep the legacy colour palette/branding tokens, reinterpret layout). However `.specclaw/ui/design-tokens.json` and `.specclaw/ui/ui-inventory.md` do not exist in this repo — the `bf-ui` workstream has never run against the legacy app here. Per the artifacts-absent rule, this stage creates the theme **mechanism only**:

- `resources/views/layouts/app.blade.php` links `public/css/theme.css`, a CSS custom-properties file, alongside CDN Bootstrap.
- `public/css/theme.css` declares the `:root` structure with every property commented out and unset — no `TK-###` value is imported, because none exists to import.
- `ui_tokens_imported: []`, `ui_tokens_skipped_reason`: artifacts absent (see declaration).

No screen's layout structure is reproduced here, and no `TK-###` group scoped to a specific `SCR-###` is or ever will be imported by this stage — those remain a named human's per-screen sign-off in `ui-review.md`, once `bf-ui` has run and a screen-bearing item is under review.

## Not In Scope — and who owns it instead

- **CSRF protection mechanism** → `BL-001` (Cross-Cutting CSRF Protection Baseline)
- **Admin login/logout, session establishment** → `BL-010` (Admin Login & Logout)
- **Front-controller route whitelisting, session/nav-visibility gate, the `App\Models\User` auth-guard target class** → `BL-011` (Admin Front Controller Routing & Session Access Gate)
- **User entity, its migration, User list/create/edit/delete screens, admin-only server-side check** → `BL-012`, `BL-013`, `BL-014`
- **Room / RoomCategory entities and migrations, Room list/create/edit/delete, Room Category CRUD** → `BL-002`, `BL-003`, `BL-004`, `BL-005`
- **Customer entity and migration, Customer list/create/edit/delete** → `BL-006`, `BL-007`, `BL-008`
- **Public marketing content (Home, Room Details, Services, Food & Drinks), live pricing display** → `BL-009`
- **Booking entity and migration, public reservation intake, walk-in check-in, booking→check-in conversion, checkout/billing, cancellation, stay-detail/edit views** → `BL-015` through `BL-023`, `BL-025`
- **Dashboard & Reporting aggregate counts** → `BL-024`
- **Any capability endpoint, capability screen, or placeholder named after one of the above (including a "temporary" login page or a room list)** → none created; each is exclusively the backlog item named above.

No domain entity, no domain table/migration, and no auth flow was created by this stage — `app/Models/` does not exist yet, and `database/migrations/` contains only the two framework-mechanism tables (`cache`, `jobs`) described in "Boundaries."

## Open Risks

- `config/auth.php` references `App\Models\User` before that class exists (see "Boundaries" — Auth boundary). This is inert today, but the first item that touches authentication (`BL-010`/`BL-011`/`BL-012`) must create the model and its migration together, not assume either already exists.
- SQ-008 (browser/device/OS matrix) and SQ-010 (non-functional targets) remain undecided per `decisions.md`; nothing in this scaffold depends on either, but they will eventually bear on asset-delivery/caching strategy and sizing.
- SQ-011's operational baseline (backups, monitoring, CI/CD) is decided in principle but not yet given a concrete provider/mechanism; `config/logging.php`'s channel structure is sized to accept one without restructuring, but no monitoring integration exists today.
- `module-map.md`'s MOD-001/MOD-002 mutual-dependency cycle is carried forward unresolved (as `target-architecture.md` itself notes) — it does not affect this foundation, since neither module's domain code exists yet, but whoever builds `BL-010`/`BL-011`/`BL-012` first will need to resolve build order some other way.
- This agent's own execution environment has no PHP/Composer toolchain, so every smoke command above is declared, never executed, by this stage — the actual `composer install` / `php artisan migrate` / `php artisan test` run has not happened yet and may surface an issue this plan could not catch (e.g. a typo in a config file only PHP's parser would reject).
