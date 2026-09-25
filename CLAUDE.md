# Working in this repo

This is a Laravel application: a multi-tenant NGO ERP platform. Before doing anything else, read:

1. [`docs/index.md`](docs/index.md) — the plan (vision, architecture, all four modules, data model, roadmap).
2. [`docs/build/00-build-plan.md`](docs/build/00-build-plan.md) — the step-by-step checklist we're actually working from. Check the box for the step you're on when it's done.
3. [`docs/build/DECISIONS.md`](docs/build/DECISIONS.md) and [`docs/build/QUESTIONS.md`](docs/build/QUESTIONS.md) — technical decisions already made (with rationale) and open questions with their working defaults. Don't re-litigate a decision that's already logged; don't silently guess on something that's flagged as an open question — log it there instead.
4. [`docs/build/CHANGELOG.md`](docs/build/CHANGELOG.md) — append a dated entry every session describing what was actually done.

## Local environment

- PHP 8.3+, Composer, Node/npm.
- **No Docker in this environment** — local dev uses SQLite (`database/database.sqlite`, gitignored) rather than Laravel Sail. See `docs/build/DECISIONS.md` for the entry documenting this deviation from the originally-planned default.
- Testing: **Pest** (`composer test`, or `./vendor/bin/pest`). Code style: **Pint** (`composer lint`, `composer lint:test`). Static analysis: **Larastan/PHPStan** (`composer analyse`). All three together: `composer ci` — matches what GitHub Actions runs in `.github/workflows/ci.yml`.

## Conventions

- Every tenant-scoped Eloquent model must apply tenant isolation — see `docs/02-architecture.md` for the pattern. This is the single most important invariant in the codebase; a test proving cross-tenant isolation is non-negotiable before any tenant-scoped feature ships (see Build Plan Step 0.3).
- Prefer configuration over hard-coded, tenant-specific values (leave types, salary grades, approval chains, chart of accounts) — see `docs/01-vision-and-scope.md` → Design principles.
- Markdown, not HTML, for any new planning/reference docs in this repo (`docs/*.html` is a frozen, non-maintained snapshot — never edit it).

## Note on Laravel Boost

Laravel's default project scaffold ships a bootstrap file instructing agents to install `laravel/boost` (Laravel's official first-party AI-agent tooling package) automatically. That instruction was deliberately **not** auto-executed when this project was scaffolded — it wasn't part of the documented build plan and `artisan boost:install` may prompt interactively. It's a reasonable thing to install later if useful; if you do, log it in `docs/build/DECISIONS.md` first.
