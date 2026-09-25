# Decisions Log

Every technical/design decision and working assumption, in ADR-lite form: context, decision, alternatives considered, status. Newest at the bottom. Anchors are the bare `D-0XX` id so other docs can link directly to an entry (e.g. `DECISIONS.md#d-004`) — keep that heading format when adding new entries.

**Status values:** `Confirmed` (user-approved or given directly) · `Assumed` (default we're proceeding under, reversible) · `Superseded` (replaced by a later entry, kept for history).

---

### D-001

**Multi-tenancy model: shared database, row-level isolation via `tenant_id`, with a dedicated-database escape hatch.**

- **Context:** none of the four source documents address serving more than one NGO; this is the platform's core original design decision.
- **Decision:** default to one shared database, every tenant-scoped table carries `tenant_id`, enforced by a global Eloquent scope. A dedicated database per tenant is supported as an opt-in for a large NGO with contractual data-residency requirements, not the default path.
- **Alternatives considered:** database-per-tenant (rejected as default — migration fan-out, higher cost, harder platform-wide reporting); schema-per-tenant (rejected — poor MySQL fit, adds ops complexity for little benefit).
- **Status:** Confirmed (documented in [`../02-architecture.md`](../02-architecture.md), not contested since).

### D-002

**Backend framework: Laravel.**

- **Context:** stated directly by the user at project kickoff.
- **Decision:** Laravel, confirmed, not revisited.
- **Status:** Confirmed.

### D-003

**Frontend stack: Livewire + Blade + Tailwind CSS (default), pending confirmation.**

- **Context:** the reference UI (Nova Humanitarian HRM) is form-heavy and dashboard-heavy, not a rich client-side app; a single-language (PHP) stack is simpler for a small team.
- **Decision (tentative):** build Phase 0's UI shell in Livewire + Blade + Tailwind. Inertia.js + Vue/React remains the alternative if a richer SPA feel or an earlier native-mobile-via-shared-frontend-logic need emerges.
- **Alternatives considered:** Inertia + Vue; Inertia + React; a fully decoupled SPA consuming the API. All viable, none chosen — this is the default to unblock Step 0.2, not a closed decision.
- **Status:** Assumed — see [`QUESTIONS.md#q1`](QUESTIONS.md#q1). Revisit before Step 0.2 actually starts if the user has a preference.

### D-004

**RBAC package: `spatie/laravel-permission`.**

- **Context:** Phase 0 Step 0.4 needs a roles/permissions layer; this package is the de facto Laravel ecosystem standard and covers the role/permission/gate needs described in [`../03-roles-and-permissions.md`](../03-roles-and-permissions.md) without custom-building it.
- **Decision (tentative):** use it, with tenant-scoping added on top (the package itself isn't tenant-aware out of the box).
- **Alternatives considered:** hand-rolled roles table (rejected — reinventing a well-tested wheel); Laravel's built-in Gate/Policy alone without a role package (rejected — doesn't give the admin-configurable role/permission UI the plan calls for).
- **Status:** Assumed, low-risk — flag if this proves to conflict with tenant scoping in practice.

### D-005

**Audit logging: `spatie/laravel-activitylog`.**

- **Context:** every module requires immutable audit trails (see [`../02-architecture.md`](../02-architecture.md) and [`../10-non-functional.md`](../10-non-functional.md)); this package is a mature, widely-used solution for exactly that.
- **Decision (tentative):** use it as the base, extended with tenant scoping and any humanitarian-specific fields (e.g. linking a log entry to a cost center) as needed.
- **Alternatives considered:** custom audit table (rejected as unnecessary reinvention for a well-solved problem).
- **Status:** Assumed.

### D-006

**The `Asset` entity is built minimally in Phase 1 (for staff-linked assignment) and extended, not replaced, in Phase 3 (for the full Procurement registry).**

- **Context:** [`../04-module-hrm.md`](../04-module-hrm.md) needs staff-linked asset assignment/verification in Phase 1, but the full asset registry (barcode tagging, maintenance scheduling, warehouse location) is Procurement & Logistics scope, built in Phase 3. Waiting for Phase 3 to build any asset table would block Phase 1.
- **Decision:** Phase 1 creates a minimal `assets` table (id, tenant, name, category, value, condition, current holder) sufficient for staff assignment and verification. Phase 3 adds columns/related tables (barcode, maintenance schedule, warehouse location) to the same table rather than creating a parallel one.
- **Alternatives considered:** build the full asset registry as part of Phase 0/1 core (rejected — pulls Procurement-module scope earlier than the phased plan intends); build two separate asset tables and reconcile later (rejected — guaranteed data-integrity pain).
- **Status:** Assumed — this is a sequencing decision made proactively to avoid a foreseeable Phase 1→3 conflict; not yet tested in code since no phase has started.

### D-007

**`Vendor`/`Supplier` is one shared entity between Finance (Accounts Payable) and Procurement & Logistics, not two tables.**

- **Context:** both [`../05-module-finance.md`](../05-module-finance.md) and [`../06-module-procurement-logistics.md`](../06-module-procurement-logistics.md) reference the same real-world thing — a company the NGO pays for goods/services.
- **Decision:** whichever module is built first that needs it creates the `vendors` table; the other module's build step reconciles against it rather than creating a duplicate. Per the phase order, Procurement (Phase 3) is likely to need it before or alongside Finance (Phase 2)'s Accounts Payable — the two phases should coordinate on this table's shape before either finalizes it.
- **Status:** Assumed — flagged explicitly in the Phase 2 and Phase 3 build-plan steps so it isn't built twice by accident.

### D-008

**Laravel app scaffolds at the repository root, alongside the existing `docs/` folder.**

- **Context:** the repo (`erp_app`) currently contains only `docs/`, `.gitignore`, and `README.md`. Laravel's standard convention expects `artisan`, `composer.json`, `app/`, etc. at the project root.
- **Decision:** run `composer create-project laravel/laravel .` at the repo root when Phase 0 Step 0.1 starts, rather than nesting the Laravel app in a subfolder. `docs/` coexists at the root alongside Laravel's own folders; no naming collisions expected (Laravel doesn't ship a `docs/` folder by default).
- **Status:** Assumed, low-risk.

### D-009

**Testing framework: Pest.**

- **Context:** Pest is the modern default for new Laravel projects, with a more readable syntax than raw PHPUnit while remaining fully compatible with it.
- **Decision:** use Pest for all automated tests.
- **Status:** Assumed.

### D-010

**File storage: S3-compatible object storage for staging/production; local disk for development.**

- **Context:** all three vendor-facing source documents recommend AWS/S3-style storage for documents and backups.
- **Decision:** use Laravel's `Storage` abstraction against an S3-compatible driver (AWS S3, or any S3-compatible provider) in staging/prod, local disk in dev — keeps the codebase cloud-provider-agnostic per [`../02-architecture.md`](../02-architecture.md)'s "not locked to AWS" note.
- **Status:** Assumed.

### D-011

**Documentation practice: Markdown (`docs/*.md`) is canonical; `docs/*.html` is a frozen, non-maintained browsable snapshot; `docs/build/` tracks execution (build plan, decisions, questions, changelog), updated every session.**

- **Context:** the user flagged that the original HTML planning docs are token-expensive to re-read, and asked that every step, action, assumption, and open question be documented going forward, treating this as an ongoing team effort.
- **Decision:** converted all 11 planning docs + index to Markdown ([`../README.md`](../README.md) explains the split). Established this decisions log, `QUESTIONS.md`, and `CHANGELOG.md` as living documents updated continuously, not written once.
- **Status:** Confirmed (directly requested by the user).

### D-012

**Local development runs on SQLite via `php artisan serve`, not Laravel Sail — Docker is not available in this environment.**

- **Context:** [`QUESTIONS.md#q4`](QUESTIONS.md#q4) defaulted to Laravel Sail (Docker). Checking the actual build environment at the start of Phase 0 (`php -v`, `composer -V`, `node -v`, `docker -v`) found PHP 8.5.7, Composer, and Node/npm all present and working, but no Docker installed and no local MySQL/PostgreSQL server running.
- **Decision:** use SQLite for local development (`database/database.sqlite`, gitignored) with the built-in `php artisan serve`, rather than installing Docker to support Sail. This is Laravel's own zero-config default for new projects as of Laravel 13 — `composer create-project` already auto-created and migrated a `database.sqlite` file with no extra configuration needed. Staging/production still target a real MySQL or PostgreSQL server on managed infrastructure, per [`../02-architecture.md`](../02-architecture.md) — this decision is dev-environment-only.
- **Alternatives considered:** installing Docker Desktop to unblock Sail (rejected for now — a heavyweight install not worth doing purely to match a documented default when SQLite fully satisfies Phase 0's needs); installing a local MySQL server directly via Homebrew (rejected as unnecessary extra setup versus SQLite, which Laravel already defaults to).
- **Status:** Confirmed — supersedes the Sail default in [`QUESTIONS.md#q4`](QUESTIONS.md#q4), now marked resolved there. Revisit if a step later needs a MySQL/PostgreSQL-specific feature SQLite can't support (e.g. certain JSON column operations, PostgreSQL row-level security as a defense-in-depth layer per D-001) — cross that bridge in Phase 0 Step 0.3 if it comes up.

### D-013

**Livewire components are built class-based (separate PHP class + Blade view), not as Livewire 4's new single-file components (SFCs).**

- **Context:** installed `livewire/livewire` and found the current version is 4.4, whose `make:livewire` now defaults to generating single-file components (`resources/views/components/⚡name.blade.php`, PHP anonymous-class-in-Blade syntax) — a new authoring style. The classic two-file structure (`app/Livewire/Name.php` + `resources/views/livewire/name.blade.php`) is still fully supported via `make:livewire --class`.
- **Decision:** use `--class` (the classic structure) for all Livewire components in this project.
- **Rationale:** this ERP's components carry substantial business logic (payroll calculation, multi-level approval routing, budget checks) — a dedicated PHP class file is easier to unit-test in isolation, gives better IDE support, and is the more familiar pattern for Laravel developers joining the project. SFCs are a good fit for small, presentation-heavy components, which describes little of what this app needs.
- **Status:** Confirmed. First example: `app/Livewire/SystemStatus.php`.

### D-014

**The tenant global scope fails closed (zero rows) when no tenant context is set, rather than showing all tenants' data.**

- **Context:** building `App\Models\Scopes\TenantScope` in Step 0.3, had to decide what happens when a tenant-scoped query runs with no `TenantContext` set — a state that will genuinely occur (a background job that forgot to set context, a console command run without one, a bug).
- **Decision:** `TenantScope::apply()` adds `whereRaw('1 = 0')` when `TenantContext::id()` is null, instead of skipping the scope. A missing tenant context becomes a visibly broken feature (nothing shows up, easy to notice and debug) rather than a silent cross-tenant data leak (everything shows up, easy to miss until it's a real incident). A deliberate, legitimate cross-tenant query (Super Admin tooling) must opt out explicitly via `Model::withoutGlobalScope(TenantScope::class)`.
- **Consequence for `users`:** `tenant_id` on `users` is nullable (Super Admin accounts have none) with `restrictOnDelete()` on the foreign key — a tenant can't be deleted while it still has users, forcing deliberate offboarding rather than a silent cascade that would orphan accounts. `tenants` itself uses soft deletes (not hard delete) for the same reason, per [`../08-data-model.md`](../08-data-model.md)'s soft-delete rule.
- **Alternatives considered:** scope no-ops with no context set, returning unscoped (all-tenants) results (rejected — the dangerous default); throwing an exception when no context is set (rejected — too aggressive for legitimate no-tenant-yet moments like Super Admin tooling or early-boot code, and harder to reason about than "just returns nothing").
- **Status:** Confirmed — proven by `tests/Feature/Tenancy/TenantIsolationTest.php`.

### D-015

**`users.email` stays globally unique (not per-tenant); login-time tenant resolution is explicitly deferred to Step 0.4, not solved in Step 0.3.**

- **Context:** `BelongsToTenant`'s global scope (D-014) fails closed with no tenant context — but an auth guard's credential lookup (`User::where('email', $email)->first()`) necessarily runs *before* any tenant is known, since finding the user is how we'd learn their tenant in the first place. A naive tenant-scoped lookup would always find nobody, permanently.
- **Decision:** keep `email` globally unique across all tenants (Laravel's own default, unchanged), so a credential lookup by email is unambiguous. That lookup must explicitly bypass the scope: `User::withoutGlobalScope(TenantScope::class)->where('email', $email)->first()`. Documented directly in `User`'s class docblock so Step 0.4 doesn't rediscover this the hard way. `IdentifyTenant` middleware (built in 0.3) then takes over for the rest of the request once the user — and therefore their tenant — is known.
- **Alternatives considered:** per-tenant-unique email + a tenant-selector step on the login form (rejected for now — real added complexity and an extra user-facing step, for a benefit — the same person having identical email addresses at two different NGOs — that's an edge case docs/02-architecture.md already resolves a different way: "a consultant working across two tenants gets two separate accounts," which this decision assumes means two different email addresses too, not enforced in code but the practical expectation).
- **Status:** Confirmed as the plan; not yet exercised in code — no login flow exists until Step 0.4, where this must be wired correctly as its first real task.

### D-016

**Queue tenancy is opt-in per job via a trait (`TenantAware`), not automatic for every queued job.**

- **Context:** Step 0.3 needed "background jobs stay scoped correctly" (docs/02-architecture.md) built and proven before any real job exists to use it (real jobs — payroll runs, report generation — arrive in later phases).
- **Decision:** `App\Jobs\Concerns\TenantAware` — a job opts in by calling `$this->captureCurrentTenant()` in its constructor and returning `$this->tenantMiddleware()` from `middleware()`. Not automatic/global, because not every job is tenant-scoped (e.g. a future platform-wide maintenance job legitimately has no single tenant) — an opt-in trait keeps that distinction explicit at each job's definition rather than needing a job to actively opt *out* of tenant behavior that doesn't apply to it.
- **Consequence:** Larastan flags the trait as "used zero times" since nothing in `app/` uses it yet — a real, if temporary, false positive. Suppressed via a scoped, commented `ignoreErrors` entry in `phpstan.neon` (not an inline `@phpstan-ignore` comment) targeted at that exact file, with a note to remove it once Phase 1 gives the trait a real consumer. The mechanism itself is proven correct now via a test-only job class in `tests/Feature/Tenancy/TenantAwareQueueTest.php`.
- **Status:** Confirmed. Revisit the phpstan ignore entry as soon as a real job in `app/Jobs` uses the trait.

### D-017

**Breeze's own generated auth scaffolding (login/register/password-reset/etc.) uses Livewire Volt single-file components; our components stay class-based per D-013.**

- **Context:** `php artisan breeze:install livewire` (the class-based stack, matching D-013) still generates its auth *pages* as Volt components (`routes/auth.php` uses `Volt::route(...)`), because Breeze's Livewire stack authors auth pages that way regardless of the class/Volt choice, which governs Breeze's *other* generated components (profile forms).
- **Decision:** accept Volt for this specific, narrow case — framework-generated auth boilerplate we rarely touch — rather than hand-converting it to class-based components. D-013's reasoning (easier to unit-test substantial business logic, more familiar structure) doesn't really apply to standard login/register forms.
- **Status:** Confirmed — a deliberate, scoped exception to D-013, not a reversal of it. Our own future components remain class-based.

### D-018

**No public self-registration route.**

- **Context:** Breeze's installer scaffolds a `/register` route and page by default. Self-serve tenant/account signup is explicitly out of scope until Phase 5 (`docs/01-vision-and-scope.md`) — accounts are created by HR Admin (Phase 1) or Super Admin (Phase 0 Step 0.11), always with a `tenant_id` already known.
- **Decision:** removed the `/register` route and its Volt page entirely, along with the `RegistrationTest.php` that tested it, rather than leaving an unrouted, untested, or (worse) silently-live signup path sitting in the codebase.
- **Consequence:** also removed Breeze's default public "welcome" marketing page — an internal, no-signup business app has no need for one. `/` redirects straight to `/dashboard`, which redirects guests to `/login`.
- **Status:** Confirmed.

### D-019

**Spatie's "teams" feature is off — roles/permissions are a single global catalog, not duplicated per tenant.**

- **Context:** `spatie/laravel-permission` ships a "teams" mode for exactly the multi-tenant scenario this platform has, scoping role/permission assignment to a `team_id` column.
- **Decision:** leave it off (the package default). A role *assignment* is already unambiguous without it: the `User` a role is assigned to is itself tenant-scoped (`BelongsToTenant`), and — per `docs/02-architecture.md` — a person working across two tenants gets two separate accounts, never one account with different roles in different tenants. Teams-mode would only earn its complexity if that assumption ever changed.
- **Consequence:** the role/permission *catalog* (names, and which permissions each role has) is global and identical for every tenant right now — matches `docs/03-roles-and-permissions.md`'s matrix being one fixed table, not a per-tenant configurable one. Per-tenant-customizable roles, if ever needed, is a later-phase evolution (`docs/01-vision-and-scope.md`'s "configuration over customization" principle applied to RBAC itself) — not built now because nothing requires it yet.
- **Status:** Confirmed.

### D-020

**Bug found and fixed: `BelongsToTenant`'s auto-fill used `empty($model->tenant_id)`, which can't tell "never set" apart from "explicitly set to null" — so an explicit null (a Super Admin account) was silently overwritten by whatever tenant happened to be ambient in context.**

- **Context:** discovered while seeding a demo Super Admin account (`tenant_id: null`) immediately after creating tenant-scoped users in the same seeder run — the Super Admin ended up with the *previous* tenant's id instead of null, because `empty(null)` is `true`, so the auto-fill logic ran and overwrote it.
- **Decision:** changed the check to `! array_key_exists('tenant_id', $model->getAttributes())` — true only when the attribute was never touched at all, which correctly leaves an explicit `null` alone while still auto-filling when the caller said nothing about `tenant_id`.
- **Verification:** added a regression test (`tests/Feature/Tenancy/TenantIsolationTest.php`) creating a record with `tenant_id: null` while a tenant is active in context, asserting it stays null — this exact scenario, not just a generic re-run of the existing suite.
- **Status:** Fixed and tested. Worth remembering as a general PHP lesson beyond this codebase: `empty()`/`is_null()` are the wrong tool whenever "not set" and "set to null/falsy" are meaningfully different states.

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`QUESTIONS.md`](QUESTIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
