# Project Context

_Last updated: 2026-09-14_

## Architecture Overview

<!-- High-level system description: key components, entry points, data flow. -->

_Not yet documented._

## Coding Style & Conventions

<!-- Language version, formatting rules, naming conventions, comment policy. -->

_Not yet documented._

## Key Patterns

<!-- Reusable patterns used across the codebase — auth, error handling, data access, logging, etc. -->

- **Every state-changing Blade form must include `@csrf`.** POST/PUT/PATCH/DELETE requests are rejected with HTTP 419 before any handler logic runs unless they carry a valid CSRF token. This is enforced by Laravel's default `ValidateCsrfToken` middleware in the `web` group (see `bootstrap/app.php`) — no custom middleware or token mechanism was added; the framework default already satisfies **CQ-015**. Established by change `001-csrf-baseline` (BL-001); proven end-to-end in `tests/Feature/CsrfProtectionTest.php`.

## Technology Decisions

<!-- Why specific libraries/frameworks were chosen; version pins and why; migration paths. -->

_Not yet documented._

## Constraints

<!-- What NOT to do — banned patterns, deprecated APIs, performance floors, security rules. -->

_Not yet documented._

## Recent Decisions

<!-- Last 5 significant decisions from merged changes. Updated automatically on each PR merge. -->

- **2026-09-14 — CSRF protection baseline (BL-001):** Adopted Laravel's built-in `ValidateCsrfToken` middleware (already default in the `web` group) rather than a hand-rolled mechanism, per CQ-015. No production route was added for this change — the proof uses a test-only inline route. See `.specclaw/changes/001-csrf-baseline/`.
