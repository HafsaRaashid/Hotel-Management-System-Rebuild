# Learnings: 001-csrf-baseline

Build learnings, spec gaps, and patterns discovered.

**Categories:** spec_gap | design_gap | pattern | best_practice | agent_issue

---

## [L1] pattern — First-ever /specclaw lint run (vendor/bin/pint --test) su...

**When:** 2026-09-14 05:28 UTC
**Category:** pattern
**Priority:** low
**Status:** pending

### Detail
First-ever /specclaw lint run (vendor/bin/pint --test) surfaced 4 pre-existing style issues in the bootstrap scaffold, none touched by this change: bootstrap/providers.php and config/auth.php (fully_qualified_strict_types, single_line_after_imports), config/logging.php and public/index.php (no_unused_imports).

### Action
A later change (or a dedicated cleanup) should run 'vendor/bin/pint' (no --test) to auto-fix, or address manually — not blocking BL-001.

---
