# Open Questions

Things that need your input. Each has the default we're proceeding under until you answer — so silence doesn't block progress, but nothing gets permanently locked in without a chance for you to weigh in first. Answered questions move to the bottom under "Resolved" with the answer recorded, rather than being deleted.

**Status values:** `Open` · `Resolved`

---

### Q2

**Deployment/hosting target: AWS specifically, or hosting-agnostic (any S3-compatible cloud / VPS)?**

- **Why it matters:** affects whether we lean on AWS-specific services (SES, RDS-specific features) or keep everything portable.
- **Options:** (a) AWS, as all three vendor-facing source documents recommend. (b) Stay cloud-agnostic — standard Laravel deployment (e.g. Forge, Vapor, or plain VPS + Docker) against any S3-compatible storage.
- **Default in use:** (b) — architecture documented as "not locked to AWS-only services" in [`../02-architecture.md`](../02-architecture.md); can layer AWS-specific optimizations later without re-architecting.
- **Status:** Open.

### Q3

**Which donor report template to build first in Finance (Phase 2)?**

- **Why it matters:** Step 2.9 says "start with one format end-to-end before generalizing" — need to know which one to build against real requirements rather than a guess.
- **Options:** USAID-style (SF-425-like), UN agency format, EU format, or WARDI's actual current donor mix if that's known.
- **Default in use:** none yet — this step is far enough out (Phase 2) that we'll ask again when we get closer rather than guessing now.
- **Status:** Open.

### Q5

**Git workflow: direct commits to `main`, or feature branches + PRs per build-plan step?**

- **Why it matters:** affects how we structure commits as we work through the build plan.
- **Options:** (a) Direct to `main` — simplest, fine for a two-person team (you + me) on a private repo. (b) A branch per phase or per step, merged via PR — more overhead, gives a review checkpoint before each merge.
- **Default in use:** (a) direct to `main`, by observed practice — every commit so far (planning docs, Phase 0 Step 0.1/0.2) has gone straight to `main` without objection. Flagging explicitly now that real application code has started landing: say the word if you'd rather switch to a branch-per-step/PR flow before Phase 0 continues further.
- **Status:** Open.

### Q7

**Do you have an actual pilot NGO in mind for Phase 1 UAT, or should we build against synthetic/demo data only until one is identified?**

- **Why it matters:** [`../09-roadmap.md`](../09-roadmap.md)'s testing gate calls for "real users from a pilot tenant" exercising real workflows before go-live. If WARDI itself (or another specific NGO) is the intended first real tenant, their actual org structure, leave policy, and salary grades would make Phase 1 far more realistic to build against than invented demo data.
- **Default in use:** proceed with realistic-but-synthetic demo data modeled on WARDI's public ToR details (six regions, health/FSL/education/protection/WASH sectors) until told otherwise.
- **Status:** Open.

---

## Resolved

### Q1

**Frontend stack: Livewire + Blade + Tailwind, or Inertia.js + Vue/React?** *(Resolved 2026-09-25)*

- **Answer:** confirmed — Livewire + Blade + Tailwind CSS. The user approved proceeding on this default; built in Build Plan Step 0.2 (`resources/views/components/layouts/app.blade.php`, `app/Livewire/SystemStatus.php`).

### Q4

**Local development environment: Laravel Sail (Docker), Valet, Herd, or plain local PHP/MySQL?** *(Resolved 2026-09-25 — answer differs from the originally-proposed default)*

- **Answer:** none of the above — **SQLite** via plain `php artisan serve`. The originally-documented default was Sail, but checking the actual build machine at the start of Phase 0 found no Docker installed. Laravel 13's own skeleton already defaults new projects to SQLite with zero configuration, which fully covers Phase 0's needs without installing anything extra. See [`DECISIONS.md#d-012`](DECISIONS.md#d-012) for the full reasoning. Revisit for staging/production, which target real MySQL/PostgreSQL regardless.

### Q6

**Package manager for the JS side: npm, pnpm, or yarn?** *(Resolved 2026-09-25)*

- **Answer:** npm, as defaulted — no objection raised, low-stakes choice. `package-lock.json` is the committed lockfile.

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`DECISIONS.md`](DECISIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
