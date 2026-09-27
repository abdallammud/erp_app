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
- **Status:** Confirmed — installed and wired up in Build Plan Step 0.8. Extended with tenant scoping exactly as anticipated here; see [`#d-029`](#d-029) for how.

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
- **Status:** Confirmed — implemented in Build Plan Step 0.9 via a `filesystems.documents_disk` config indirection (`local` in dev, swaps to `s3` via `DOCUMENTS_DISK` env var). See [`#d-030`](#d-030) for how "encrypted at rest" is actually satisfied given no real S3 credentials exist in this environment to verify against.

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

### D-021

**`tenant_id` foreign keys on tenant-scoped child tables (`departments`, `duty_stations`, `positions`) use `restrictOnDelete()`, matching `users.tenant_id` — established as the standing convention, not a one-off.**

- **Context:** D-014 set `restrictOnDelete()` for `users.tenant_id` specifically. Building the next three tenant-scoped tables in Step 0.5 raised the question of whether that was a `users`-specific choice or a general rule.
- **Decision:** `restrictOnDelete()` is now the default for every tenant-scoped table's `tenant_id` foreign key, not just `users`. A tenant is never silently taken down along with its data, for any table — offboarding stays a deliberate process. Apply this by default to new tenant-scoped migrations going forward without re-deciding it each time.
- **Status:** Confirmed.

### D-022

**Bug found and fixed: a "can't be its own parent" guard fired on every plain create, not just self-referencing edits — because `null === null` is `true` in PHP.**

- **Context:** `Departments`' `save()` checked `if ($this->parentDepartmentId === $this->editingId)` to stop a department being set as its own parent. On a plain **create** (not editing anything yet), both `editingId` and an unset `parentDepartmentId` default to `null` — so the check fired incorrectly on every single create with no parent selected, rejecting it with a validation error.
- **Decision:** guard the check with `$this->editingId !== null &&`, so it only ever runs while actually editing an existing record.
- **Verification:** caught immediately by `tests/Feature/Organization/DepartmentsTest.php`'s create/edit/delete test — the create step failed validation before the fix, passed after.
- **Status:** Fixed and tested. The same general lesson as D-020, from a different angle: comparing two values that can both independently be "unset" (`null`) needs a guard for that shared-empty state, or the comparison accidentally means "neither is set" instead of the intended "these are the same real thing."

### D-023

**Bug found and fixed: the validated array's key didn't match the database column name, so a selected parent department silently never saved.**

- **Context:** the Livewire property is `$parentDepartmentId` (camelCase, matching PHP/Livewire convention), but the column is `parent_department_id` (snake_case). `$this->validate()` returns an array keyed by property name; passing that straight to `Department::create()`/`update()` meant the `parentDepartmentId` key was silently dropped by mass assignment (not in `Department`'s `#[Fillable(...)]` list) — no error, the department just saved with no parent, every time, regardless of what was selected in the form.
- **Decision:** explicitly remap `parentDepartmentId` → `parent_department_id` (and `department_id`, `country_code` in the other two components) before calling `create()`/`update()`. Same pattern already existed correctly in `DutyStations` and `Positions`; `Departments` was the one missed.
- **Verification:** added a dedicated regression test (`tests/Feature/Organization/DepartmentsTest.php`) that creates a department with a parent selected and asserts the link is actually persisted and traversable (`$child->parent->name`) — not just that the form submits without error, which the earlier bug would have passed.
- **Status:** Fixed and tested. General lesson: a silently-ignored mass-assignment key is a "no error, wrong result" class of bug — easy to miss without a test that checks the actual persisted *relationship*, not just that save() didn't throw.

### D-024

**Deliberately paused strict build-plan phase order to deliver visual fidelity to the Nova HRM reference UI, before finishing Phase 0's remaining invisible-infrastructure steps.**

- **Context:** the user asked to see the app running twice, and the second time said plainly the generic placeholder shell didn't look like the reference UI they'd originally provided. Phase 0 (tenancy, auth, RBAC, org-structure CRUD) is real, necessary work, but every bit of it is backend plumbing behind a deliberately generic shell — there was nothing yet that visually resembled the actual product.
- **Decision:** pause before Step 0.6 (approval workflow engine) to reskin the portal shell — full icon sidebar, the Employee Portal's nav item set from the reference — and build one genuinely real screen (My Profile) rather than only placeholders, before returning to the remaining Phase 0 steps.
- **Scope kept honest:** most of the reference's nav items (Dependents, Leave, Payroll, Timesheet, Training, Assets, Safeguarding, Calendar, History, Notifications) have no backing data model yet — those are real Phase 1 work, not something to fake now. Each gets a real route and a clearly-labeled "coming in Phase 1" placeholder rather than a raw 404 or, worse, being hidden — the sidebar looks and navigates like the reference; clicking through immediately shows the true state, not a fabricated one.
- **Also kept honest:** one unified nav for now, not yet the four distinct role-specific portals (Employee/Supervisor/HR Admin/Payroll) the reference and `docs/03-roles-and-permissions.md` describe — that's real Phase 1 scope (Steps 1.12–1.14), not something to rush for a visual pass.
- **Status:** Confirmed — a deliberate reprioritization, not a scope cut. Phase 0 Steps 0.6–0.11 remain to be done; resuming after this pass.

### D-025

**Standing rule: every `BelongsToTenant` model's `#[Fillable(...)]` list must include `tenant_id`.**

- **Context:** `User`'s Fillable list included `tenant_id` from the start (Step 0.4), but every tenant-scoped model built since (Department, DutyStation, Position, and all four approval-engine tables) did not. This went unnoticed until `DemoTenantSeeder` — which runs under `DatabaseSeeder`'s `WithoutModelEvents` trait, so `BelongsToTenant`'s auto-fill-on-create `creating` hook never fires — tried to explicitly pass `tenant_id` to `ApprovalChain::firstOrCreate()` and it was silently dropped by mass assignment, surfacing as a `NOT NULL constraint failed` database error.
- **Decision:** added `tenant_id` to every affected model's Fillable list (Department, DutyStation, Position, ApprovalChain, ApprovalChainStep, ApprovalInstance, ApprovalInstanceStep, TestRequest). Treat this as a standing rule for every future `BelongsToTenant` model, not a one-off fix — the auto-fill mechanism is a convenience for the common case; explicit assignment (seeders, Super Admin tooling, any `WithoutModelEvents` context) must always be possible too.
- **Verification:** added a regression test creating an `ApprovalChain`/`ApprovalChainStep` with an explicit `tenant_id` for a tenant other than the current context, asserting it's respected — not just that the seeder happens to run without error.
- **Status:** Fixed and tested. Third occurrence of the same underlying pattern as D-020 and D-023: a value that looks like it's just quietly not doing anything (an ignored mass-assignment key) rather than throwing — worth specifically watching for whenever a `create()`/`update()` call's array doesn't visibly round-trip into the persisted record.

### D-026

**Bug found and fixed only by manually driving the real page, not by the test suite: the Livewire `Demo::submit()` method never set `requester_id` at all.**

- **Context:** `Demo::submit()` builds `TestRequest::create($validated)`, where `$validated` only ever contains `title` and `reason` (the two fields in `rules()`) — `requester_id` was never included anywhere in that method. Every test in `ApprovalWorkflowTest.php` used `TestRequest::factory()->create(['requester_id' => ...])` directly, which sets it via the factory — completely bypassing the actual bug, since the tests never exercised the real Livewire component's `submit()` method at all, only the underlying `ApprovalWorkflow` service.
- **How it was actually caught:** manually driving the real running app via `tinker` (calling the service directly, the same way `submit()` should) to verify the end-to-end flow before considering the step done — not by the automated test suite, which was green the whole time this bug existed.
- **Decision:** fixed `submit()` to explicitly pass `'requester_id' => Auth::id()`. Added `tenant_id` to `TestRequest`'s Fillable list at the same time (same class of issue as D-025) and `requester_id` too (it wasn't fillable at all).
- **The more important fix:** added `tests/Feature/Approvals/DemoComponentTest.php`, testing the actual `Livewire\Livewire::test(Demo::class)` entry point end to end (submit → approve → approve, and the segregation-of-duties denial), not just the service layer underneath it. `ApprovalWorkflowTest.php` alone was a real gap: a service can be perfectly correct while the one piece of UI code that's supposed to call it is wired wrong, and a test suite that never calls through the actual entry point a user reaches won't catch that.
- **Status:** Fixed and tested, at the correct layer this time. General lesson: for any component with a public method a UI calls, at least one test must call that exact method (or drive the component the way a user would) — testing only the service it delegates to leaves the wiring itself unverified.

### D-027

**`bootstrap/app.php` never called `->withEvents()` — event auto-discovery was off the whole time, which would have made Step 0.7's listeners silently never fire.**

- **Context:** Laravel's skeleton only auto-discovers `handle*()` methods in `app/Listeners` (matching by type-hinted event parameter) if `Application::configure(...)->withEvents()` is explicitly chained in `bootstrap/app.php` — it is **not** on by default the way Laravel 11+'s marketing/docs might imply. This app's `bootstrap/app.php` (written in Step 0.1/0.2) chains `withRouting`, `withMiddleware`, `withExceptions`, but never `withEvents`. Every event dispatched so far (`ApprovalStepActedOn`, `ApprovalInstanceFinished`) had zero listeners, so this gap was invisible until Step 0.7 tried to add the first ones.
- **How it was caught:** before writing any notification code, traced Laravel's own `DiscoverEvents`/`EventServiceProvider` source (`vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php`) to confirm discovery is opt-in via `withEvents()`, rather than assuming it "just works" the way `spatie/laravel-permission` or other packages' auto-registration does. Confirmed empirically with `php artisan event:list` both before (event listed with no listeners under it) and after the fix (listeners appear).
- **Decision:** added `->withEvents()` to `bootstrap/app.php` (default discovery path — `app/Listeners` — is exactly where the two new listener classes live, no custom path needed).
- **Status:** Fixed and verified via `php artisan event:list` and the passing notification test suite (which would fail immediately if listeners weren't wired — they were, once this was added). General lesson: for a "no listener yet, wire one up later" comment left in earlier code (as `ApprovalStepActedOn`'s docblock said), verify the *discovery mechanism itself* is even active before assuming a correctly-named `handle()` method is enough.

### D-028

**Real bug, caught by Larastan + a Pest test in the same CI run: `App\Listeners\Approvals\NotifyEligibleApprovers` imported the wrong `Notification` class.**

- **Context:** wrote `use Illuminate\Notifications\Notification as NotificationFacade;` and called `NotificationFacade::send(...)` — but `Illuminate\Notifications\Notification` is the *base class every notification extends* (`App\Notifications\Approvals\ApprovalNeeded extends Notification`), not the `Illuminate\Support\Facades\Notification` facade that has a static `send()` method. This is a fatal `Error: Call to undefined method`, not a logic bug — it would have broken every approval submission in production.
- **How it was caught:** Larastan (`staticMethod.notFound`) flagged it immediately on the first `composer ci` run after writing the listener. Before treating it as a false positive (per the established pattern of previous method-based-enum-cast false positives — D-016, D-026's phpstan.neon entries), verified via `tinker` that the *correct* facade class does have a real `send()` method on its underlying `ChannelManager` — confirming the even bigger red flag: the class actually being used in the code did not. That distinguished this from the earlier genuine false positives and correctly identified it as a real bug to fix, not ignore.
- **Decision:** fixed the import to `use Illuminate\Support\Facades\Notification as NotificationFacade;`. Also caught independently by `tests/Feature/Notifications/ApprovalNotificationsTest.php`'s segregation-of-duties test, which failed with the real `Error` (not an assertion failure) before the fix.
- **Status:** Fixed and tested. General lesson: when two classes share the same short name (`Notification` the facade vs. `Notification` the base class), a `use ... as` alias makes the mistake harder to spot at the call site — double-check the *fully-qualified* import matches the intended class, especially for common short names, and treat "Larastan flags something in code from this exact session" with more suspicion of a real bug than "Larastan flags long-standing code" (which is more likely an analysis limitation, as in D-016/D-026).

### D-029

**Audit log tenant isolation: a custom `App\Models\AuditLogEntry` extending spatie's `Activity`, plus a separate `Auditable` trait rather than folding logging into `BelongsToTenant` directly.**

- **Context:** `spatie/laravel-activitylog`'s stock `activity_log` table has no tenant column at all — every tenant's audit trail would live in one shared, unscoped table, which fails this codebase's own standing rule ("every tenant-scoped Eloquent model must apply tenant isolation," per `CLAUDE.md`). The obvious-looking shortcut — add `LogsActivity` directly inside the existing `BelongsToTenant` trait, since audit logging should apply to every tenant-scoped model anyway — has a fatal flaw: `AuditLogEntry` itself needs `BelongsToTenant` for its own tenant scoping, and if `BelongsToTenant` also carried `LogsActivity`, every audit log row's creation would try to log itself, recursively, forever.
- **Decision:** (1) added a `tenant_id` column directly to the published `activity_log` migration, `restrictOnDelete()` like every other tenant-scoped table; (2) `App\Models\AuditLogEntry extends Spatie\Activitylog\Models\Activity`, using only `BelongsToTenant` (never `Auditable`) — registered as `activitylog.activity_model` in `config/activitylog.php` so every `LogsActivity`-triggered write anywhere in the app lands on this tenant-scoped subclass, not the package's own un-scoped one; (3) a new, separate `App\Models\Concerns\Auditable` trait (composing `LogsActivity` + sensible default `LogOptions`: log all fillable attributes, only when dirty, skip empty logs, exclude `tenant_id` from the diff since it's redundant with the log row's own column) applied alongside `BelongsToTenant` on every actual business model (`User`, `Department`, `DutyStation`, `Position`, `ApprovalChain`, `ApprovalChainStep`, `ApprovalInstance`, `ApprovalInstanceStep`, `TestRequest`) — never on `AuditLogEntry` itself.
- **`User` overrides `getActivitylogOptions()`** to additionally exclude `password` from the logged diff — even hashed, a password has no legitimate reason to sit in an audit trail.
- **Verification:** exercised create/update/delete on a real model (`Department`) via `tinker` while authenticated as a seeded user and confirmed the resulting `AuditLogEntry` rows have the correct `tenant_id`, `causer` (auto-resolved from the authenticated guard user by the package itself — no extra wiring needed), and old→new attribute diff. A regression test asserts an audit entry from tenant A is invisible when the current `TenantContext` is tenant B, and another asserts `password` never appears in a `User`'s logged changes.
- **Status:** Implemented and tested. General lesson: when composing a "log everything about tenant-scoped models" concern with the models it logs, watch for the specific case where the *logger's own storage model* would otherwise qualify for that same concern — that's a self-reference loop a generic trait can't see coming, only a human tracing the composition can.

### D-030

**Documents are encrypted at rest at the application layer (Laravel's `Crypt` facade, before any disk write), not solely via cloud-provider storage encryption — because the latter can't actually be verified in this environment.**

- **Context:** Step 0.9's build plan requires "encrypted-at-rest confirmed for the chosen storage backend." The obvious approach — enable S3 server-side encryption — can't be *confirmed* here at all: this environment has no real AWS/S3 credentials, so any claim about SSE working would be untested assertion, not verification. Local disk in dev has no encryption of its own either (that's the host machine's disk encryption, an ops concern, not app code).
- **Decision:** `App\Support\Documents\DocumentStore::store()` encrypts file bytes with `Crypt::encryptString()` (Laravel's own `APP_KEY`-based AES-256-CBC, authenticated) *before* the write, on every disk — local or S3, dev or prod. `contents()` decrypts on read. This makes "encrypted at rest" true uniformly, provably, and independent of which disk/cloud provider is configured, rather than resting on infrastructure that can't be exercised here. S3's own `ServerSideEncryption: AES256` option is *also* configured on the `s3` disk as defense-in-depth for whenever real credentials exist — but that piece is configured, not verified, and the build plan doc is explicit about the distinction rather than blurring "configured" into "confirmed."
- **Trade-off, noted honestly:** `Crypt::encryptString()` base64-encodes ciphertext and works on an in-memory string — fine for Phase 0's personnel-document-sized files, but not a good fit for very large files at scale (loads the whole file into memory, inflates storage size). Revisit (e.g. streaming encryption, or relying on S3 SSE alone once it's real) if a future module needs to store large media files.
- **Verification:** a test reads the *raw* bytes directly off the fake storage disk (bypassing `DocumentStore::contents()` entirely) and asserts they don't contain the plaintext content; a second test confirms `contents()` correctly round-trips it back. Also confirmed against the real local disk in this session (not just `Storage::fake()`) via `tinker` — the on-disk file is a base64 JSON envelope (`iv`/`value`/`mac`), no trace of the plaintext marker written into the source file.
- **Status:** Implemented and tested — the stronger, checkable claim, not the unverifiable-here one.

### D-031

**Real bug, found only by manually driving the live server: `IdentifyTenant` ran after `SubstituteBindings` in the middleware pipeline, so implicit route-model binding on any tenant-scoped model resolved with no tenant context — turning the first such route into a 404 for everyone, including the record's rightful owner.**

- **Context:** `bootstrap/app.php` registered `IdentifyTenant` via `$middleware->appendToGroup('web', IdentifyTenant::class)` back in Step 0.3. Laravel's default `web` middleware group already lists `SubstituteBindings` (which resolves route parameters like `Document $document` into actual model instances) *before* anything appended afterward, and `SubstituteBindings` has no middleware-priority entry of its own to be reordered around a later-appended, unprioritized middleware. This was completely invisible for six build-plan steps because no route had ever used implicit route-model binding on a `BelongsToTenant` model directly in its URI — Livewire components resolve their own tenant-scoped queries internally, well after the full middleware stack (including `IdentifyTenant`) has already run. Step 0.9's `/documents/{document}/download` was the first one.
- **Why the 100-test suite never caught it:** `tests/Pest.php`'s global `beforeEach` sets `TenantContext` directly for every Feature test (`app(TenantContext::class)->set(Tenant::factory()->create())`), and several tests set it again explicitly. Because `TenantContext` is a singleton that persists across a test's simulated HTTP request, it was already populated *before* the request fired in every single test — completely masking whether the real middleware pipeline could have derived it correctly on its own, regardless of registration order.
- **How it was actually found:** driving the real server via a temporary local-only login route (the established practice — see D-026) and downloading a document as its own owner, who got an unexplained 404. Diagnosed by first ruling out a stale server process (killed and restarted cleanly, bug persisted), then confirming the route existed (`php artisan route:list`) and the model/tenant context resolved fine outside the HTTP layer (`tinker`) — narrowing it specifically to something about the real request pipeline. Reading Laravel's actual `$middlewarePriority` array (`Illuminate\Foundation\Http\Kernel`) confirmed `SubstituteBindings` is prioritized ahead of anything not in that list, explaining exactly why an appended, unprioritized `IdentifyTenant` stayed stuck after it.
- **Decision:** `$middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: IdentifyTenant::class)` in `bootstrap/app.php`, so Laravel's priority-sorting now places it between `Authenticate` (already prioritized ahead of `SubstituteBindings`) and `SubstituteBindings` itself, regardless of where it's registered in the group array.
- **Verification:** confirmed live (owner: 200 with correct decrypted content; another employee: 403; HR Admin: 200; HR Admin of a *different* tenant: 404) after the fix, versus 404-for-everyone before it. Added `tests/Feature/Tenancy/IdentifyTenantMiddlewareOrderTest.php`, which deliberately clears `TenantContext` and relies entirely on the real middleware chain — confirmed this specific test fails with the pre-fix `bootstrap/app.php` (reverted it temporarily via `git stash` to check) and passes with the fix, before trusting it as a real regression guard.
- **Status:** Fixed and tested. The most significant bug caught in Phase 0 so far — it would have silently broken every future route of this shape (any route binding a tenant-scoped model directly in its URI), and the entire test suite's own conventions were structurally blind to it. General lesson: a test helper that sets up ambient state "so tests don't have to think about X" (here, `tests/Pest.php`'s tenant-context convenience) can accidentally paper over whether the *production* mechanism for establishing that state actually works — worth at least one test per cross-cutting concern that deliberately does the setup for real instead of taking the shortcut.

### D-032

**Export libraries: `maatwebsite/excel` for Excel/CSV, `barryvdh/laravel-dompdf` for PDF — two packages, not one, and PDF goes through a hand-written shared Blade view rather than PhpSpreadsheet's own PDF writer.**

- **Context:** Step 0.10 needs Excel, CSV, and PDF export from one shared framework. `maatwebsite/excel` (built on PhpSpreadsheet) is the de facto Laravel standard for the first two. It technically *can* also produce PDF — PhpSpreadsheet ships its own PDF writer bridge (`Excel::download($export, 'file.pdf', Excel::DOMPDF)`), which would mean one export class covering all three formats.
- **Decision:** used the PhpSpreadsheet-via-dompdf-writer path only implicitly (dompdf is a shared dependency either way), but built the actual PDF output through `barryvdh/laravel-dompdf` directly against a hand-written Blade view (`resources/views/exports/generic-table.blade.php`), not PhpSpreadsheet's PDF writer.
- **Why not the one-class-covers-everything path:** PhpSpreadsheet's PDF writer renders "a spreadsheet grid converted to HTML then to PDF" — reasonable, but opaque and harder to style/verify (no direct control over the intermediate HTML, and PDF-writer-specific configuration quirks — temp directories, font subsetting — are a known source of environment-specific friction). A hand-written Blade view is: (a) consistent with how every other piece of UI in this app is already built, (b) directly testable by rendering the view in isolation and asserting on real HTML content (see `tests/Feature/Reporting/ReportExporterTest.php`), and (c) trivially reusable/restyleable per future module without fighting a spreadsheet-to-PDF bridge's own opinions.
- **A real Livewire/DomPDF incompatibility found and fixed before it shipped:** `Barryvdh\DomPDF\Facade\Pdf`'s own `->download()` method returns a plain `Illuminate\Http\Response` with content already embedded — not a `StreamedResponse` or `BinaryFileResponse`. Livewire's `SupportFileDownloads` feature only recognizes those two types as "a component action returned a file to download" and silently ignores anything else (no error, the button would just do nothing). Caught by reading Livewire's actual file-download source (`vendor/livewire/livewire/src/Features/SupportFileDownloads/SupportFileDownloads.php`) before wiring the PDF export button, not by clicking a dead button and wondering why. Fixed in `App\Support\Reporting\ReportExporter::toPdf()` by taking DomPDF's raw `->output()` bytes and wrapping them in `response()->streamDownload()` manually — the same pattern already used in `App\Http\Controllers\DocumentDownloadController` (Step 0.9), now proven useful a second time.
- **Verification:** confirmed against the real environment, not just `Storage::fake()`-backed tests — via `tinker`, generated a real XLSX (opened and read back with PhpSpreadsheet directly), a real CSV, and a real PDF (confirmed with the system's own `pdfinfo`/`pdftotext` utilities: exactly 1 page, correct extracted title/heading/row text) — because a library integration passing inside a faked test environment doesn't guarantee the real filesystem/PDF-rendering pipeline actually works end to end.
- **Status:** Implemented and tested. Same general lesson as D-030/D-031: read the actual source of a framework feature (Livewire's file-download detection, here) before assuming a "should just work" integration does.

### D-033

**Real bug, found live (not by the 124-test suite): `AuditLogEntry`'s auto-logging had no way to fill `tenant_id` for anything a Super Admin does, because a Super Admin genuinely has no ambient tenant — so creating a brand-new tenant's first user threw a `NOT NULL constraint failed` on every single write.**

- **Context:** `App\Models\Concerns\BelongsToTenant`'s auto-fill (used by `AuditLogEntry` itself, per D-029) fills `tenant_id` from `app(TenantContext::class)->id()`. `App\Http\Middleware\IdentifyTenant` only ever sets that context when `$user->tenant_id` is truthy — by design, a Super Admin's is always `null` (docs/03-roles-and-permissions.md). So when Step 0.11's `createTenant()` calls `User::create(...)` for a brand-new tenant's first admin, `Auditable`'s automatic logging tries to create an `AuditLogEntry` for that `created` event with no ambient tenant to fill `tenant_id` from at all — not "the wrong tenant," genuinely none.
- **Why the test suite didn't catch it:** `tests/Pest.php`'s global `beforeEach` always sets SOME ambient tenant (`app(TenantContext::class)->set(Tenant::factory()->create())`), and `Livewire::test()` never runs real HTTP middleware (same root cause as D-031) — so the test environment always had *a* tenant context available, even while "logged in" as a Super Admin, papering over the exact scenario where a real Super Admin request has none.
- **How it was actually caught:** verifying Step 0.11 against the real dev database via `tinker`, not `Storage::fake()`/`RefreshDatabase` — reproducing the exact `createTenant()` flow against real, persistent data (established practice since D-030/D-032) surfaced the `NOT NULL constraint failed` immediately.
- **Decision:** `AuditLogEntry` gets its own `booted()` hook (registered after `BelongsToTenant`'s, since Eloquent boots traits before the class's own `booted()`) that runs only as a fallback — if `tenant_id` is still unset after `BelongsToTenant`'s ambient-context attempt, derive it from the **subject being logged** instead, via `$subject_type::withoutGlobalScopes()->find($subject_id)`. Every `Auditable`-driven subject is itself a `BelongsToTenant` model, so its own `tenant_id` is always authoritative and available, with no dependency on ambient context at all. Deliberately bypasses the subject's own `TenantScope` — a scoped lookup with no ambient context would fail closed for exactly the same reason, right back to the original problem.
- **Verification:** reproduced the exact failure against the real database first, confirmed the fix resolves it there too, then added a regression test (`tests/Feature/SuperAdmin/TenantManagementTest.php`) that explicitly clears `TenantContext` before exercising the real `createTenant()` flow — confirmed it fails with the pre-fix `AuditLogEntry` (temporarily reverted via `git stash` to check, same discipline as D-031) and passes with the fix.
- **Status:** Fixed and tested. Third time this exact shape of bug has surfaced (D-029's design already anticipated the general risk; D-031 was the routing/middleware version; this is the auto-logging version) — the common thread is "a Super Admin (or any actor with no ambient tenant) doing something to a tenant-scoped resource" is a scenario worth deliberately testing for anywhere ambient `TenantContext` is relied on, not just assumed away.

### D-034

**Real bug caught by the test suite before it ever reached a live page: the impersonation banner Livewire component had no root HTML element when there was nothing to show, which would have 500'd *every single page in the app*.**

- **Context:** `resources/views/livewire/impersonation/banner.blade.php` was written as `@if ($isImpersonating) <div>...</div> @endif` — when not impersonating (the default state, true for essentially every request), the rendered output is empty. Livewire requires every component's rendered output to contain exactly one root HTML element, even when that component currently has nothing to show; an empty render throws `Livewire\Exceptions\RootTagMissingFromViewException`. Because this component was added to the *global* layout (`components/layouts/app.blade.php`, rendered on every authenticated page), this wasn't a broken feature — it was a broken application.
- **How it was caught:** the very first `composer ci` run after wiring the banner into the layout — every single Feature test that renders any page (audit log, notifications, organization, dashboard, etc.) failed with a 500 and the exact `RootTagMissingFromViewException` stack trace, immediately localizing the cause.
- **Decision:** wrapped the whole component in a permanent `<div>` root, with the `@if` living *inside* it rather than around it — the root element is now always present; only its contents are conditional.
- **Status:** Fixed and tested (implicitly, by every other Feature test in the suite passing again). General lesson: any Livewire component whose entire template is a single conditional block is a latent instance of this bug — the `@if` must wrap content inside a permanent root, never the root itself. Worth specifically checking for this shape when adding any component to a *shared/global* layout, where the blast radius of missing it is every page, not just one screen.

### D-035

**Impersonation: session-stored impersonator id (not a stack/chain), defensively re-validated as a real Super Admin on every stop — and its audit trail is attributed to the target's tenant, not hidden from it.**

- **Context:** Step 0.11 needs "logged impersonation flow for support access into a tenant" (docs/02-architecture.md: "can impersonate (with logging) for support — but has no default access to tenant business data"). This is a security-sensitive feature: a naive implementation risks either a privilege-escalation hole (a non-admin tricking the system into restoring "impersonator" access) or silently-lost audit trail.
- **Decision:** `App\Support\Impersonation\ImpersonationManager` stores only the Super Admin's own id in the session (`impersonator_id`) at `start()`, and `Auth::login($target)` replaces the active session's user entirely. `stop()` reads that id back, **re-fetches and re-validates** that it genuinely belongs to a Super Admin (`withoutGlobalScopes()->find($id)` + `hasRole(SuperAdmin)`) before trusting it to restore — a tampered or stale session value is silently rejected (and the key cleared regardless, no dangling state) rather than trusted. No support for nested/chained impersonation (a Super Admin impersonating while already impersonating) — `start()` and `stop()` both assume a single level, matching the actual use case; nesting was never a real requirement here.
- **Every start/stop writes an `AuditLogEntry` directly** (`AuditLogEntry::create([...])`, not through `Auditable`'s automatic hooks — there's no model being created/updated, just a deliberate log entry) with `tenant_id` explicitly set to the **target's** tenant, not the Super Admin's (who has none). This is a deliberate transparency choice: the resulting entry shows up on that tenant's own `/audit-log` screen, visible to its own HR Admin — "someone from the platform accessed this account for support, and it's logged where you can see it" — not a platform-only, tenant-invisible record.
- **Verification:** tested that starting impersonation actually switches the authenticated user and correctly resolves `TenantContext` for the target through the *real* middleware pipeline (not `Livewire::test()`, which bypasses it — same discipline as D-031); that both start and stop write correctly-attributed audit entries; that those entries are visible on the target tenant's own Audit Log screen; that a non-Super-Admin cannot start one; that a Super Admin account can never be impersonated; and that a tampered `impersonator_id` session value is rejected rather than trusted.
- **Status:** Implemented and tested.

### D-036

**`Position.grade` (free-text, from Phase 0 Step 0.5) is deliberately NOT converted into a foreign key to the new `SalaryGrade` table (Step 1.1) — they stay two separate concepts.**

- **Context:** `Position`'s migration (Step 0.5) explicitly flagged its `grade` column as a placeholder: "becomes a proper foreign key to a tenant-configurable SalaryGrade once Phase 1 builds Payroll & Compensation." Now that `SalaryGrade` exists, the obvious-looking move is to make that conversion.
- **Decision:** left `Position.grade` exactly as it was. Step 1.1's own checklist explicitly ties the salary grade link to **Contract**, not Position ("`contracts` table: type, start/end date, salary grade link"). Converting Position too would be real, working functionality, but it's scope beyond what this step actually asked for, would touch Phase 0's already-shipped, already-tested Organization screen for no requirement driving it, and conflates two genuinely different things a real HR system separates: a position's structural grade *label* (org-chart classification, e.g. "P3" as a job-family/seniority marker) versus an individual employee's actual contract's pay band (which can legitimately differ from their position's nominal grade — an acting/temporary assignment, a negotiated exception, etc.).
- **Status:** Confirmed as a deliberate scope boundary, not an oversight — revisit only if a real future requirement (not just tidiness) asks for Position and SalaryGrade to be linked directly.

### D-037

**`national_id` is encrypted via Laravel's native `encrypted` cast, applied to that one column specifically — not a blanket policy of encrypting every personal-data field on Employee.**

- **Context:** docs/02-architecture.md's security NFR is explicit and non-optional: "encrypted DB fields for sensitive data (national ID, bank details, safeguarding case content)." `Employee` (Step 1.1) is the first model with a `national_id` field.
- **Decision:** `'national_id' => 'encrypted'` in `Employee::casts()` — transparent to every read/write in the app, backed by the same `APP_KEY`-based encryption already used for document contents (D-030), just via Laravel's built-in column-level cast instead of a manual `Crypt::encryptString()` call (no file-handling concerns here, so the native cast is the simpler, equally-correct tool). The migration uses `text`, not `string`, for this column — ciphertext (base64-encoded IV + value + MAC) is comfortably longer than the plaintext and can exceed a `varchar(255)`, found before it ever caused a truncated-ciphertext bug by checking Laravel's actual encrypted-string output format rather than assuming the original column size was fine.
- **Not applied to** `phone`, `personal_email`, `date_of_birth`, `address` — real personal data, but not what the NFR names, and encrypting them would prevent any future search/sort/filter on those columns for no requirement currently asking for it. Scoped to exactly what the documented requirement calls out, matching the same "the stronger, checkable claim, not the broader unrequested one" reasoning as D-030.
- **Verification:** a test creates an employee with a real `national_id`, then reads the column directly via `DB::table('employees')` (bypassing the model/cast entirely) and asserts the stored value contains no trace of the plaintext — the same "check the raw bytes, not just that the feature runs" discipline as D-030's document-encryption test. Also confirmed against the real dev database via `tinker`, not just the test double.
- **Status:** Implemented and tested.

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`QUESTIONS.md`](QUESTIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
