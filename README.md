# erp_app

A multi-tenant Enterprise Resource Planning system for NGOs — HRM, Finance, Procurement & Logistics, and Programs — built once and reusable across many organizations. Backend: Laravel.

**Status:** Phase 0 (platform foundation) in progress — project scaffolded (Laravel 13, Livewire + Tailwind, Pest + Pint + Larastan, CI), multi-tenancy not yet built. See [`docs/build/00-build-plan.md`](docs/build/00-build-plan.md) for current progress.

## Running it locally

```
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm run build   # or `npm run dev` while working on frontend
php artisan serve
```

No Docker/database server needed — local dev runs on SQLite (see `docs/build/DECISIONS.md` D-012). Run `composer ci` before committing (Pint + Larastan + Pest).

## Start here

- [`docs/index.md`](docs/index.md) — the full plan: vision, architecture, all four modules, data model, roadmap.
- [`docs/build/00-build-plan.md`](docs/build/00-build-plan.md) — the step-by-step engineering checklist we're actually working from.
- [`docs/build/CHANGELOG.md`](docs/build/CHANGELOG.md) — dated log of what's been done.
- [`docs/build/DECISIONS.md`](docs/build/DECISIONS.md) / [`docs/build/QUESTIONS.md`](docs/build/QUESTIONS.md) — decisions made and open questions, with defaults.
- [`docs/README.md`](docs/README.md) — how the docs folder itself is organized.
