# Open Questions

Things that need your input. Each has the default we're proceeding under until you answer — so silence doesn't block progress, but nothing gets permanently locked in without a chance for you to weigh in first. Answered questions move to the bottom under "Resolved" with the answer recorded, rather than being deleted.

**Status values:** `Open` · `Resolved`

---

### Q1

**Frontend stack: Livewire + Blade + Tailwind, or Inertia.js + Vue/React?**

- **Why it matters:** determines how Step 0.2 is built and the general shape of every UI screen after it. Expensive to change mid-build.
- **Options:** (a) Livewire + Blade + Tailwind — one language, simpler for a small team, good fit for the form/dashboard-heavy reference UI. (b) Inertia + Vue — more familiar to devs coming from a Vue background, nicer for highly interactive components. (c) Inertia + React — same trade-off as (b) with React instead.
- **Default in use:** (a) Livewire + Blade + Tailwind — see [`DECISIONS.md#d-003`](DECISIONS.md#d-003).
- **Status:** Open.

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

### Q4

**Local development environment: Laravel Sail (Docker), Valet, Herd, or plain local PHP/MySQL?**

- **Why it matters:** affects Step 0.1 setup instructions and how reproducible the dev environment is across machines.
- **Options:** (a) Laravel Sail — Docker-based, most portable, works the same on any machine. (b) Valet/Herd — macOS-native, faster local iteration, less portable. (c) Plain local PHP/MySQL install.
- **Default in use:** (a) Sail — most portable given this is a Google-Drive-synced folder on macOS and the setup should work identically if the project ever moves machines.
- **Status:** Open.

### Q5

**Git workflow: direct commits to `main`, or feature branches + PRs per build-plan step?**

- **Why it matters:** affects how we structure commits as we work through the build plan.
- **Options:** (a) Direct to `main` — simplest, fine for a two-person team (you + me) on a private repo. (b) A branch per phase or per step, merged via PR — more overhead, gives a review checkpoint before each merge.
- **Default in use:** undecided — will ask before Phase 0 coding starts, since it's cheap to set a convention now and expensive to change habits later.
- **Status:** Open.

### Q6

**Package manager for the JS side: npm, pnpm, or yarn?**

- **Why it matters:** minor, but worth fixing once rather than mixing lockfiles.
- **Default in use:** npm — Laravel's own scaffolding defaults to it; no reason cited yet to deviate.
- **Status:** Open (low priority — will just proceed with npm unless you object).

### Q7

**Do you have an actual pilot NGO in mind for Phase 1 UAT, or should we build against synthetic/demo data only until one is identified?**

- **Why it matters:** [`../09-roadmap.md`](../09-roadmap.md)'s testing gate calls for "real users from a pilot tenant" exercising real workflows before go-live. If WARDI itself (or another specific NGO) is the intended first real tenant, their actual org structure, leave policy, and salary grades would make Phase 1 far more realistic to build against than invented demo data.
- **Default in use:** proceed with realistic-but-synthetic demo data modeled on WARDI's public ToR details (six regions, health/FSL/education/protection/WASH sectors) until told otherwise.
- **Status:** Open.

---

## Resolved

*(none yet — answers will be recorded here as questions get resolved)*

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`DECISIONS.md`](DECISIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
