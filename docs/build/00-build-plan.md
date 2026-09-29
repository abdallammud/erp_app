# Build Plan — Step by Step

The actual engineering checklist, derived from [`../09-roadmap.md`](../09-roadmap.md) but broken down to task level. We work through this top to bottom. Check items off as they're done; if a step's approach changes, update it here rather than letting this drift from reality.

**How to use this doc:** each step is small enough to be one focused work session. Steps carry a short **Definition of Done (DoD)**. Anything a step depends on that isn't decided yet is marked with a link into [`QUESTIONS.md`](QUESTIONS.md) — the step proceeds under the documented default until answered.

**Status legend:** `[ ]` not started · `[~]` in progress · `[x]` done

**Visual design:** every screen built from 2026-09-27 onward must follow [`DESIGN.md`](DESIGN.md) (extracted from the Nova Humanitarian HRM reference PDF) — see [`DECISIONS.md#d-040`](DECISIONS.md#d-040). This is a standing requirement, not a one-time step.

---

## Phase 0 — Platform Foundation `[✅ complete — 0.1-0.11 done]`

*Nothing in later phases works correctly without this. See [`../02-architecture.md`](../02-architecture.md) for the design reasoning.*

### 0.1 Project scaffolding — ✅ done 2026-09-25
- [x] `composer create-project laravel/laravel` at repo root (alongside the existing `docs/` folder) — installed **Laravel 13.33.0**, PHP `^8.3` required (repo has 8.5.7)
- [x] Set PHP version, install Laravel Pint (code style, shipped by default) and Larastan/PHPStan (static analysis, added — `phpstan.neon` at repo root, level 5)
- [x] Install Pest as the testing framework (see [Q6](QUESTIONS.md#q6)) — installed, example tests converted from PHPUnit-class style to Pest functional style
- [x] `.env.example` with all config placeholders documented — Laravel's default, `APP_NAME` set to "NGO ERP Platform"
- [x] Basic GitHub Actions CI: run Pint + tests on every push/PR — `.github/workflows/ci.yml`, runs Pint, Larastan, and Pest
- **DoD:** ✅ met — `php artisan serve` serves the real dashboard (not the stock welcome page, replaced — see 0.2); `composer lint:test`, `composer analyse`, and `composer test` (aliased together as `composer ci`) all pass locally and in CI.
- **Deviation from plan:** no Docker available in this environment, so Laravel Sail was not used — see [`DECISIONS.md#d-012`](DECISIONS.md#d-012). Local dev runs on SQLite + `php artisan serve` directly.

### 0.2 Frontend stack decision & setup — ✅ done 2026-09-25
- [x] Confirm/finalize choice — **Livewire + Blade + Tailwind CSS**, confirmed by the user (see [Q1](QUESTIONS.md#q1), now resolved)
- [x] Install and configure Tailwind (shipped by default in the Laravel 13 skeleton, Tailwind v4, CSS-first config), Livewire 4.4 (installed via Composer); Alpine.js not added separately — Livewire 4 bundles its own JS runtime and no interactivity beyond Livewire has been needed yet
- [x] Build the base layout shell: portal sidebar + content area pattern (matches the [portal concept](../03-roles-and-permissions.md)) — `resources/views/components/layouts/app.blade.php`, an anonymous Blade component (`<x-layouts.app>`), mobile-responsive (sidebar stacks above content below the `md` breakpoint)
- **DoD:** ✅ met — `app/Livewire/SystemStatus.php` (class-based component, not Livewire 4's new single-file-component style — see [`DECISIONS.md#d-013`](DECISIONS.md#d-013)) renders inside the shell at `/`, proven interactive (a `wire:click` action that mutates state and re-renders) by `tests/Feature/SystemStatusTest.php`, not just a static render.

### 0.3 Multi-tenancy foundation — ✅ done 2026-09-25
- [x] `tenants` table + `Tenant` model (org profile: name, slug, logo, countries of operation, default currency, timezone, fiscal year start month, `is_active` + soft deletes for Super Admin suspend/offboard) — `app/Models/Tenant.php`
- [x] `BelongsToTenant` trait + global Eloquent scope — `app/Models/Concerns/BelongsToTenant.php` + `app/Models/Scopes/TenantScope.php`, applied to `User` as the first (real, permanent — not a throwaway demo) tenant-scoped model, via `tenant_id` added to `users` in a follow-up migration
- [x] Tenant resolution: `App\Support\Tenancy\TenantContext` (singleton) + `App\Http\Middleware\IdentifyTenant` (reads `$request->user()->tenant_id`, appended to the `web` middleware group). Currently a no-op in practice — no login flow exists until Step 0.4; wired and ready for it.
- [x] Tenant-aware queue connection — `App\Support\Tenancy\SetsTenantContext` (job middleware) + `App\Jobs\Concerns\TenantAware` (trait for jobs to adopt), proven with a test-only job in `tests/Feature/Tenancy/TenantAwareQueueTest.php` (no real queued job exists yet — arrives with Phase 1)
- [x] Automated test: two tenants' data is created, and a query from tenant A's context provably cannot see tenant B's rows — `tests/Feature/Tenancy/TenantIsolationTest.php`, 5 tests covering isolation, auto-fill-on-create, explicit-tenant-id-not-overridden, fail-closed-with-no-context, and the deliberate `withoutGlobalScope` bypass path
- **DoD:** ✅ met — the cross-tenant-leakage test passes and runs in CI on every push (`composer ci`, part of `.github/workflows/ci.yml`). 10 tests total, Pint and Larastan (level 5) both clean.
- **Design decisions made along the way** (see `DECISIONS.md`): the global scope fails closed with no tenant context (D-014) rather than silently showing everything; `tenant_id` on `users` is nullable + `restrictOnDelete()` for Super Admin accounts and safe tenant offboarding (D-014); `email` stays globally unique, and the auth guard's login-time lookup will need to explicitly bypass the tenant scope since tenant isn't known until the user is found — flagged as Step 0.4's responsibility, not solved here (D-015).

### 0.4 Auth & RBAC — ✅ done 2026-09-25
- [x] Laravel Breeze for auth scaffolding (login, password reset, 2FA-ready groundwork) — installed the class-based Livewire stack (`--stack=livewire`, matching D-013); Breeze's own auth *pages* turned out to use Volt regardless, accepted as a scoped exception (D-017). No self-registration — removed `/register` entirely, not part of this product's design (D-018).
- [x] **Wired up the login-time tenant resolution left open by Step 0.3** ([`DECISIONS.md#d-015`](DECISIONS.md#d-015)): a custom `tenant-aware-eloquent` auth provider (`AppServiceProvider::boot()`) bypasses `TenantScope` for the credential lookup *and* the per-request session-reload lookup (`retrieveById` — easy to miss, but without this fix users would appear logged out on every request after the first). Proven correct by the full auth test suite, not just a login-once test.
- [x] `users` table with `tenant_id` — done in 0.3, scoped to one tenant per login
- [x] Roles & permissions — installed `spatie/laravel-permission` (see [`DECISIONS.md#d-004`](DECISIONS.md#d-004)), **without** its "teams" feature — not needed, and why, in [`DECISIONS.md#d-019`](DECISIONS.md#d-019). Added `HasRoles` to `User`. Typed catalogs: `App\Support\Authorization\Role` and `Permission` (PHP enums).
- [x] Seeded the default role set from [`../03-roles-and-permissions.md`](../03-roles-and-permissions.md) — all 10 roles, `database/seeders/RolesAndPermissionsSeeder.php`, with permissions assigned per the doc's access matrix (representative, module-level permissions — fine-grained record-level scoping is each future module's own Policy work, not this seeder's job). Idempotent (`firstOrCreate`/`syncPermissions` throughout).
- [x] Policy/Gate scaffolding for the confidentiality tiers — `App\Providers\AuthorizationServiceProvider` defines Gates for the one domain that concretely has all three tiers today (Safeguarding — Standard/submit, Restricted/scoped, Highly Restricted/manage). Deliberately not a generic abstraction with no real consumer; the same compose-a-Gate-from-permissions pattern extends to other Restricted-tier data (salary, beneficiary PII, etc.) once those models exist in Phase 1+.
- [x] `database/seeders/DemoTenantSeeder.php` — one demo tenant, one user per non-Super-Admin role (9) plus one Super Admin (tenant_id null), all `password`/`password` for local exploration only.
- **Found and fixed a real bug along the way**, not just built the happy path: `BelongsToTenant`'s auto-fill couldn't distinguish "tenant_id never mentioned" from "explicitly set to null," so an ambient tenant context was silently overwriting an intentional `null` (a Super Admin account). Fixed, regression-tested — see [`DECISIONS.md#d-020`](DECISIONS.md#d-020).
- **DoD:** ✅ met, with an honest caveat — a seeded demo tenant has one user per role (`DemoTenantSeeder`), and role-based permission differentiation is proven by tests (`tests/Feature/Authorization/`). "Logging in as each user shows only the portal(s) they should see" is proven at the *mechanism* level (correct permissions per role) rather than the *UI* level, since no role-specific portal UI exists yet beyond the one generic dashboard built in 0.2 — that's Phase 1+'s job, building on this foundation.
- 41 tests total, Pint and Larastan (level 5, memory limit raised to 512M — the codebase outgrew the 128M default mid-step) both clean.

### 0.5 Core org-structure entities — ✅ done 2026-09-26
- [x] `departments` (with optional light parent hierarchy), `duty_stations` (with country/city/address), `positions` (with free-text grade + optional department link) — all tenant-scoped, soft-deleted, `tenant_id` uses `restrictOnDelete()` per [`DECISIONS.md#d-021`](DECISIONS.md#d-021), now the standing convention for every tenant-scoped table
- [x] Simple CRUD — no dedicated HR Admin portal exists yet (that's Phase 1), so this lives at `/organization` behind the `hrm.org.view`/`hrm.org.edit` permissions built in Step 0.4 (the first real feature to actually use them, not just prove them in tests). Three focused Livewire components (`App\Livewire\Organization\{Departments,DutyStations,Positions}`), one page, consistent list+create+edit+soft-delete pattern.
- **Two real bugs found and fixed while building this, not just the happy path** — both from the same underlying lesson (a `null`/unset comparison or an unmapped key silently does the wrong thing instead of erroring loudly): a "can't be its own parent" guard that incorrectly fired on every plain create (`null === null`), and a validated-array key (`parentDepartmentId`) that never matched its database column (`parent_department_id`), so a selected parent silently never saved. See [`DECISIONS.md#d-022`](DECISIONS.md#d-022) and [`#d-023`](DECISIONS.md#d-023) — both caught by tests before this was called done, not after.
- **DoD:** ✅ met — verified against the actual running dev server (not just the test suite): HR Admin sees and can manage all three; Employee gets a real 403 hitting `/organization` directly.
- 53 tests total (up from 41), Pint and Larastan clean.

### 0.6 Approval workflow engine — ✅ done 2026-09-26
- [x] Generic `ApprovalChain`/`ApprovalChainStep` (tenant + action-type + ordered steps, role-based approvers) and `ApprovalInstance`/`ApprovalInstanceStep` (a specific request's progress through its chain) models — all tenant-scoped
- [x] Segregation-of-duties rule: requester cannot act on their own request even holding the step's role — enforced in `App\Support\Approvals\ApprovalWorkflow::canAct()`, not left to callers to remember
- [x] Status API/component reused by every future approval UI — `App\Support\Approvals\ApprovalWorkflow` (submit/approve/reject/canAct/awaitingActionBy) is the single entry point every module will call; `<x-approval-status>` Blade component renders any instance's timeline
- [x] Domain events (`ApprovalStepActedOn`, `ApprovalInstanceFinished`) dispatched on every decision, ready for Step 0.7's notification listeners without touching this code again
- [x] A real, working demo screen (`/approvals-demo`) — not just tests — where you can submit a test request and watch it move through a real 2-step chain (Supervisor → HR Admin), visible in the sidebar under "Engine demos"
- **Two real bugs found — one only by manually driving the live app, not by the test suite:**
  1. `tenant_id` wasn't in the Fillable list of any tenant-scoped model built since Step 0.4 except `User` — surfaced as a `NOT NULL constraint failed` when a `WithoutModelEvents` seeder needed to set it explicitly. Fixed across all 8 affected models, now a standing rule. See [`DECISIONS.md#d-025`](DECISIONS.md#d-025).
  2. The Livewire `Demo::submit()` method never set `requester_id` at all — invisible to `ApprovalWorkflowTest.php` because every test there used the factory (which sets it), never the actual Livewire entry point. Caught only by driving the real page via `tinker` before calling this done. Fixed, and added `tests/Feature/Approvals/DemoComponentTest.php` testing the real component, not just the service underneath it. See [`DECISIONS.md#d-026`](DECISIONS.md#d-026) — an explicit lesson about testing at the right layer.
- **DoD:** ✅ met — verified against the live running server via `tinker` (submit → supervisor approves → HR Admin approves → status reads "approved", correct approver name and comment on each step, segregation of duties denies the requester), not just the test suite.
- 69 tests total (up from 53), Pint and Larastan clean.

### 0.7 Notification engine ✅ done (2026-09-26)
- [x] `notifications` table (Laravel's built-in database notifications, via `php artisan notifications:table`) + mail channel — `MAIL_MAILER=log` in dev, so email content is verifiable in `storage/logs/laravel.log` without real SMTP
- [x] Notification types seeded: `App\Support\Notifications\NotificationType` enum — `ApprovalNeeded` and `ApprovalDecision` have real triggers; `DocumentExpiring`, `ContractExpiring`, `BudgetThreshold` are seeded as documented extension points for modules that don't exist yet (Phase 1+)
- [x] SMS channel left as a documented extension point, not built in Phase 0 (see [Q — SMS gateway](QUESTIONS.md))
- [x] Wired onto the approval engine's existing events without touching `ApprovalWorkflow`'s public API: added one new event (`ApprovalInstanceSubmitted`, fired at the end of `submit()` — needed so step-1's approver gets notified even though nobody has acted on anything yet) alongside the two events Step 0.6 already dispatched. Two listeners in `app/Listeners/Approvals/`: `NotifyEligibleApprovers` (submitted + non-final step approved → notifies whoever holds the new current step's role) and `NotifyRequesterOfDecision` (every step decision → notifies the requester, whether it's a mid-chain approval, the final approval, or a rejection).
- [x] Listeners are synchronous, not `ShouldQueue` — `QUEUE_CONNECTION=database` has no worker running in this environment, so a queued notification would silently never appear. Revisit once a real queue worker is part of the deploy story.
- [x] `App\Support\Approvals\ApprovalWorkflow::eligibleApprovers()` — new small helper (tenant + current-step-role + not-the-requester) shared by the listener and available to future "who can act on this" UI.
- [x] First "coming soon" nav placeholder promoted to a real screen: `/notifications` (`App\Livewire\Notifications\Inbox`) — paginated list, mark-one-read, mark-all-read, unread badge on the sidebar nav item.
- [x] Auto-discovery enabled: `bootstrap/app.php` didn't call `->withEvents()` at all before this step (Laravel's skeleton doesn't by default), so the two listeners above would have silently never fired. Added it — see [`DECISIONS.md#d-027`](DECISIONS.md#d-027).
- **One real bug found by the CI loop itself (Larastan), not live testing:** the listener imported `Illuminate\Notifications\Notification` (the notification base class) instead of `Illuminate\Support\Facades\Notification` (the facade) for `Notification::send(...)` — a plain fatal error at runtime, caught immediately because Larastan correctly flagged `staticMethod.notFound` and a Pest test exercising the real listener failed with the actual `Error`. Fixed the import. See [`DECISIONS.md#d-028`](DECISIONS.md#d-028).
- **DoD:** ✅ met — verified against the live running server, not just the test suite: logged in as the seeded `employee@demo.test` via a temporary local-only route, submitted a request through `tinker` calling the exact same `ApprovalWorkflow` methods the UI calls, and confirmed (a) a real `notifications` table row exists with the correct message and `read_at: null`, (b) `storage/logs/laravel.log` contains a fully-rendered HTML+text email with the correct subject/body, (c) `GET /notifications` renders both real notification messages and the correct unread count, and (d) the sidebar's unread badge shows the correct number on `/dashboard`.
- 78 tests total (up from 69), Pint and Larastan clean.

### 0.8 Audit log ✅ done (2026-09-26)
- [x] Installed `spatie/laravel-activitylog` (see [`DECISIONS.md`](DECISIONS.md#d-005)); wired into every tenant-scoped model
- [x] Every create/update/delete on a tenant-scoped model logs who/when/what changed (old → new value)
- **Tenant isolation for the audit trail itself:** the package's stock `activity_log` table has no tenant column at all. Added one directly to the published migration, and introduced `App\Models\AuditLogEntry` (extends the package's `Activity`, adds `BelongsToTenant`, registered as `activitylog.activity_model`) so every logged row is tenant-scoped like everything else. A new `App\Models\Concerns\Auditable` trait (not folded into `BelongsToTenant` itself — see why below) is applied alongside `BelongsToTenant` on every business model: `User`, `Department`, `DutyStation`, `Position`, `ApprovalChain`, `ApprovalChainStep`, `ApprovalInstance`, `ApprovalInstanceStep`, `TestRequest`.
- **Design pitfall caught before it was ever run:** the obvious shortcut — put `LogsActivity` directly inside `BelongsToTenant`, since every tenant-scoped model should be audited anyway — would make `AuditLogEntry` (which itself uses `BelongsToTenant` for its own tenant scoping) log its own creation, recursively, forever. Caught by tracing the composition before writing it, not by hitting the recursion at runtime. See [`DECISIONS.md#d-029`](DECISIONS.md#d-029).
- [x] `User` overrides its logging options to exclude `password` from the diff, even hashed — no legitimate reason for a hash to sit in an audit trail.
- [x] A real screen, not just a database table: `/audit-log` (`App\Livewire\AuditLog\Index`) — paginated, filterable by event (created/updated/deleted), each entry showing who did what to which record with an expandable old→new field diff. Linked in the sidebar under "Administration", gated by `HrmOrgView` (no dedicated audit-log permission exists yet — see [`QUESTIONS.md#q8`](QUESTIONS.md#q8)).
- **DoD:** ✅ met — verified against the live running server, not just tests: logged in as `hr-admin@demo.test`, created and updated a real `Department` via `tinker`, loaded `/audit-log` and confirmed both the created and updated entries render with the correct old→new values; confirmed an Employee gets a 403 on the same route.
- 87 tests total (up from 78), Pint and Larastan clean.

### 0.9 Document store ✅ done (2026-09-27)
- [x] Storage disk configured via a `filesystems.documents_disk` indirection (`local` in dev, `env('DOCUMENTS_DISK')` swaps to `s3` in staging/prod without any code change) — never the `public` disk, since documents are access-controlled, not publicly served. `local` resolves to Laravel 13's private `storage/app/private`, not web-accessible directly.
- [x] Generic `App\Models\Document`: polymorphic (`documentable`), free-form `category` (not an enum — categories differ per module), `expiry_date`, `is_verified`/`verified_by_id`/`verified_at`, tenant-scoped (`BelongsToTenant`) and path-scoped (`documents/{tenant_id}/{uuid}` on disk, independent of the DB scoping). Single entry point `App\Support\Documents\DocumentStore` (`store`, `contents`, `verify`) — nothing else calls `Storage::disk(...)` against a document's path directly.
- [x] Encrypted at rest, and actually verified in this environment (not just configured and assumed): file bytes are encrypted with Laravel's `Crypt` facade (app-key-based) *before* ever reaching any disk, local or S3 — so it's true in dev too, not just a staging/prod S3 setting nobody can check without real cloud credentials. A test reads the raw bytes directly off the fake disk and asserts they don't contain the plaintext. S3's own server-side encryption is additionally configured (`ServerSideEncryption: AES256` on the `s3` disk) as defense-in-depth for when real S3 credentials exist, but that specific piece is configured, not verified — no S3 access in this environment. See [`DECISIONS.md#d-030`](DECISIONS.md#d-030).
- [x] A real screen, not a throwaway demo entity this time: "Documents" on **My Profile** (`App\Livewire\Documents\MyDocuments`) — upload, list, and download your own personnel documents. The `Document` model attaches to the real `User` record, matching docs/04-module-hrm.md §B's actual future feature directly. `DocumentStore::verify()` exists and is tested at the service layer for Phase 1's employee-directory screen to call — no UI button for it yet, since no such directory screen exists to hang it on until Phase 1.
- [x] Authorization: `App\Policies\DocumentPolicy` — the document's owner, the employee it's attached to, or anyone with `HrmOrgView` (HR Admin, Country Director, Super Admin). First real use of the "owner OR org-level permission" pattern `AuthorizationServiceProvider`'s docblock anticipated back in Step 0.4.
- **A significant, previously-latent bug found only by live testing, not by the 100-test suite:** `App\Http\Middleware\IdentifyTenant` ran *after* Laravel's `SubstituteBindings` middleware — so the first route ever to use implicit route-model binding on a `BelongsToTenant` model (`Document $document` in `/documents/{document}/download`) resolved that binding with no tenant context yet, and `TenantScope`'s fail-closed behavior turned it into a 404 — even for the document's own owner. Every existing Feature test sets `TenantContext` directly before making a request (including `tests/Pest.php`'s own global default), which completely masks this class of bug regardless of the real middleware order. Fixed via `$middleware->prependToPriorityList()` in `bootstrap/app.php`; added a dedicated regression test that deliberately clears `TenantContext` and relies entirely on the real middleware chain, and confirmed it actually fails without the fix before trusting it. See [`DECISIONS.md#d-031`](DECISIONS.md#d-031) — arguably the most important bug caught in Phase 0 so far, since it would have silently broken every future route of this shape.
- **DoD:** ✅ met — verified against the live running server: uploaded a real file as `employee@demo.test`, confirmed the raw on-disk bytes are ciphertext (not the plaintext marker written into the source file), downloaded it successfully as the owner, got a 403 as a different employee, got 200 as `hr-admin@demo.test`, and got a 404 for an HR Admin belonging to a completely different tenant.
- 100 tests total (up from 87), Pint and Larastan clean.

### 0.10 Reporting & export framework ✅ done (2026-09-27)
- [x] `maatwebsite/excel` (Excel + CSV) and `barryvdh/laravel-dompdf` (PDF) installed — see [`DECISIONS.md#d-032`](DECISIONS.md#d-032) for why both, not one.
- [x] `App\Support\Reporting\ReportDataset` — the one format-agnostic shape every export format consumes (title, ordered columns, flattened rows); `App\Exports\GenericExport` (one class, shared across every module, for XLSX+CSV) and `resources/views/exports/generic-table.blade.php` (shared PDF layout). `App\Support\Reporting\ReportExporter::toExcel()/toCsv()/toPdf()` is the actual entry point — a future module's Reports screen builds a `ReportDataset` from whatever query it already has and gets all three formats for free.
- **A real compatibility bug caught before it shipped, not after:** DomPDF's own `->download()` returns a plain `Illuminate\Http\Response` with the file content already embedded — not a `StreamedResponse`/`BinaryFileResponse`. Livewire's file-download support (`SupportFileDownloads`) only auto-triggers a browser download for those two types, so calling DomPDF's `download()` directly from a Livewire action would have silently done nothing in the browser (no error — the response just wouldn't be recognized as downloadable). Caught by reading Livewire's own file-download source before wiring the PDF path in, not by clicking a dead button. Fixed by building the response manually via `response()->streamDownload()` around DomPDF's raw `->output()` bytes, matching Excel's already-correct `BinaryFileResponse`.
- [x] Wired into a real, existing screen — the Audit Log (`/audit-log`) — rather than a new throwaway demo page: "Export: Excel / CSV / PDF" buttons, respecting whatever event filter is currently active. Added a "Changes" column (a flattened old→new diff, matching what the on-screen expandable table already shows) after an early version of this step exported only who/what/when — a real export missing the diff would be materially less useful than the screen it's exported from.
- **DoD:** ✅ met — verified two ways: (1) automated tests parse the generated files back (PhpSpreadsheet for Excel, plain parsing for CSV, `view()->render()` + a `%PDF-` magic-byte check for PDF) and confirm real content round-trips correctly; (2) live against the real environment (not `Storage::fake()`) via `tinker` — a real 6+KB XLSX opened correctly with PhpSpreadsheet, a real CSV with correct content, and a real PDF confirmed with `pdfinfo`/`pdftotext` (1 page, correct title/headings/row text extracted) — because a library integration working in a faked test environment doesn't guarantee it works against the real filesystem/PDF renderer.
- 108 tests total (up from 100), Pint and Larastan clean.

### 0.11 Super Admin portal ✅ done (2026-09-27)
- [x] Tenant CRUD: create (name + first admin together, not just the tenant — see DoD note below) and suspend/reactivate. "Configure" beyond that (logo, currencies, fiscal year — `Tenant`'s full org-profile columns already exist from Step 0.3) has no UI yet; deliberately not built now, since nothing in Phase 0 needs to *edit* those fields yet and a form for columns nothing reads back would be premature.
- [x] Cross-tenant system health view: active/suspended tenant counts, total users, pending/failed (24h) queue jobs (`jobs`/`failed_jobs` tables — real, not a placeholder), total document storage across all tenants (sums `documents.size`, from Step 0.9).
- [x] Logged impersonation flow: `App\Support\Impersonation\ImpersonationManager` — session-based, defensively re-validated on stop (a tampered session value is rejected, not trusted), every start/stop writes a real `AuditLogEntry` attributed to the **target's** tenant (visible on that tenant's own Audit Log screen, not hidden from it). A persistent "You're impersonating X — Stop" banner lives in the shared layout. See [`DECISIONS.md#d-035`](DECISIONS.md#d-035).
- [x] One new permission, `Permission::PlatformAdmin` — deliberately singular, not several: Super Admin is a single, all-or-nothing platform role (no tiering), unlike every tenant-business module.
- [x] Suspension actually enforced, not just a flag: `App\Livewire\Forms\LoginForm::authenticate()` blocks a login outright for a user whose tenant is suspended, even with correct credentials. Known, documented limitation: this is login-time only — an already-active session isn't forcibly terminated when a tenant is suspended mid-session. See [`QUESTIONS.md#q9`](QUESTIONS.md#q9).
- **Two real bugs found and fixed, both before or via the test suite rather than staying hidden:**
  1. A Super Admin has no ambient `TenantContext` at all (not "the wrong one" — genuinely none), so `Auditable`'s automatic logging had nothing to fill `tenant_id` from when a Super Admin action (creating a brand-new tenant's first user) triggered it — a `NOT NULL constraint failed` on every such write. Masked by the test suite's own global tenant-context setup, exactly like D-031; caught by verifying against the real database via `tinker`, not just `RefreshDatabase`. Fixed with a subject-derived fallback on `AuditLogEntry` itself. See [`DECISIONS.md#d-033`](DECISIONS.md#d-033).
  2. The impersonation banner's Blade view had no root HTML element when there was nothing to show — since it was added to the *global* layout, this would have 500'd every single page in the app, not just one screen. Caught immediately by the very first `composer ci` run (every page-rendering test failed with the same `RootTagMissingFromViewException`), not by a live click. See [`DECISIONS.md#d-034`](DECISIONS.md#d-034).
- **DoD:** ✅ met — verified against the live running server, not just the 125-test suite: created a brand-new tenant + its first admin via `tinker` (same code path as the real dashboard), confirmed the real on-disk audit trail correctly attributed the new tenant's id (proving D-033's fix against real data, not a test double), logged in as that new admin over a real HTTP session and confirmed they could reach `/organization` (200) but not `/super-admin` (403), suspended the tenant and confirmed a login attempt with correct credentials is rejected with the right message (reproducing `LoginForm`'s exact logic against real data), reactivated it, then ran a full impersonation start→stop cycle via `tinker` against real data and confirmed via a real HTTP session that the resulting audit entries are visible on that tenant's own `/audit-log` screen with the correct description text.
- 125 tests total (up from 108), Pint and Larastan clean.

**Phase 0 exit criteria — met:** at least two tenants exist in the same database (the original demo tenant plus every tenant created during this step's live verification) and are provably isolated (0.3's test, still passing, plus every subsequent step's own cross-tenant regression tests — D-031's, Step 0.9's document isolation test, this step's tenant-scoping fixes); each tenant has its own users/roles/org structure (Steps 0.4–0.5, and this step's own brand-new-tenant proof); the approval (0.6) + notification (0.7) + audit (0.8) + document (0.9) + export (0.10) plumbing all work on at least one real record type, exercised end to end against the live server at each step. Phase 0 is complete — Phase 1 is next.

---

## Phase 1 — HRM & Payroll `[~ in progress — 1.1-1.2 done]`

*Full functional spec: [`../04-module-hrm.md`](../04-module-hrm.md).*

**2026-09-27 — visual design pivot (not a numbered step):** the app's UI had only ever followed the Nova HRM reference *functionally* (nav/fields/workflow), never visually — a generic Tailwind theme was substituted without sign-off. First retrofit pass done (layout/top bar, color system, new `<x-button>`/`<x-badge>`/`<x-stat-card>`/`<x-modal>` components, My Profile tabs, Dashboard). 2026-09-29: Organization's four CRUD sections converted to `<x-modal>` create/edit (found and fixed a real bug in the modal component's open/close event contract along the way), then Employees create/edit too. Audit Log/Super Admin/Approvals/Notifications/PersonalDetails' dependent form retrofits still pending. See [`DECISIONS.md#d-040`](DECISIONS.md#d-040) and [`DESIGN.md`](DESIGN.md).

### 1.1 Employee data model ✅ done (2026-09-27)
- [x] `employees` table: `user_id` nullable/`nullOnDelete()` ("not every historical employee needs a login" — see this table's migration), own name/contact fields independent of any linked `User`, org-structure links (department/position/duty station, reusing Phase 0 Step 0.5), self-referential `reports_to_id`, `staff_category` and `status` as typed enums (`App\Support\Hrm\{StaffCategory,EmployeeStatus}`).
- [x] `contracts` table: `type` (enum), `salary_grade_id` (required FK), start/end dates, `status`. Renewal history is just multiple `Contract` rows per employee ordered by `start_date` (`Employee::contracts()`) — no separate renewal table needed.
- [x] `salary_grades` (full CRUD UI — see below), `allowance_types`, `deduction_types`, `tax_brackets` — all tenant-configurable, all migrated and modeled now. The latter three deliberately have **no UI yet**: they're not consumed by anything until Step 1.5 (Payroll & Compensation) actually calculates pay, and a config screen for numbers nothing reads yet would be UI with no purpose. Building their data model now (per this step's own checklist) without a premature UI mirrors the same "data model first, UI when there's a real consumer" pattern already used for `Document::verify()` in Step 0.9.
- [x] `national_id` is encrypted at rest (Laravel's native `encrypted` cast) — the one field docs/02-architecture.md's security NFR explicitly names ("encrypted DB fields for sensitive data: national ID, bank details, ..."), not applied blanket to every PII column. Verified against the real database, not just a test double: the raw column value contains no trace of the plaintext.
- [x] A real screen: `/employees` (`App\Livewire\Hrm\Employees`) — create/edit, with **the first Contract created atomically alongside the Employee** (a required Salary Grade selection is part of that same form) — an employee with no contract would technically satisfy "created" but not this step's actual DoD. Editing an existing employee touches only their own fields, not their contract; contract renewal/amendment is real Step 1.5 scope, not built here. `App\Livewire\Organization\SalaryGrades` (same CRUD shape as Departments/Positions/DutyStations) added as a 4th section on `/organization`, since a Contract needs real, UI-created grades to pick from — not seeded ones.
- **DoD:** ✅ met — verified against the live running server, not just the 139-test suite: created real Department/Position/DutyStation/SalaryGrade records and then a real Employee + Contract via `tinker` (the exact same calls the Livewire component makes), confirmed via `DB::table('employees')` that `national_id`'s raw stored value contains no trace of the plaintext, and confirmed the `/employees` and `/organization` pages correctly render the new records (name, department, position, duty station, contract type, salary grade) — plus confirmed the new Employee/Contract rows already show up correctly on `/audit-log` for free, since both models use `Auditable`.
- 139 tests total (up from 125), Pint and Larastan clean.

### 1.2 Employee Portal shell + profile ✅ done (2026-09-27)
- [x] Employee Portal navigation shell — already built in Phase 0's visual pass (D-024); this step fills in the specific tiles it was waiting on rather than restructuring nav. The four *distinct role* portals (Employee/Supervisor/HR Admin/Payroll) from Roles & Permissions are their own later steps (1.12–1.14), not this one.
- [x] Profile view/edit with change-history logging: the logging was already automatic (User and Employee both use `Auditable`, Step 0.8) — what was missing was a way to *see* it and a way to *edit* Employee's own contact fields (phone/personal email/address) self-service, which had no UI at all before this step (only HR could touch them, via `/employees`). Both now real: `App\Livewire\Employees\History` (self-scoped view of `AuditLogEntry`, reusing the existing engine rather than building a second one) and the contact-info form on `App\Livewire\Employees\PersonalDetails`.
- [x] Dependents & emergency contacts (`App\Models\Dependent`, new): relationship, DOB, passport number (encrypted — Restricted-tier, same treatment as `national_id`), emergency-contact flag, insurance-beneficiary flag + percentage. "Must total 100%" is enforced as *never exceed* 100% across all of an employee's beneficiaries, checked on every save — see [`DECISIONS.md#d-039`](DECISIONS.md#d-039) for why a hard "always exactly 100%" rule isn't realistic for incremental data entry.
- [x] **Document repository moved from User to Employee** (a real design correction, not originally planned for this step): Step 0.9 built it against `User` before `Employee` existed; "not every employee has a login" means a User-keyed repository could never work for an employee without portal access. `App\Livewire\Employees\Documents` (renamed from `App\Livewire\Documents\MyDocuments`) and `DocumentPolicy` now key off `Employee`. See [`DECISIONS.md#d-038`](DECISIONS.md#d-038).
- [x] `App\Livewire\Employees\Employment` (new, read-only): department/position/duty station/staff category/hire date, full contract history including the employee's own salary grade — showing an employee their own salary is explicitly allowed even though it's Restricted-tier data (docs/03-roles-and-permissions.md: "Owner + ... only," and this is always exactly the record's own owner).
- **A real Larastan/Livewire interaction, fixed at the pattern level, not suppressed:** Livewire's `#[Computed]` methods throw `CannotCallComputedDirectlyException` if called directly — `$this->employee` (magic property) is the *only* legal access path, including from the component's own action methods, not just Blade. Larastan doesn't understand that magic when read from PHP code (as opposed to Blade, which it doesn't scan at all), and flags it as an undefined property. Fixed by splitting every such Computed method into a thin wrapper plus a plain private `resolveX()` method that action code calls instead — removes the false positive at its source rather than suppressing it, and is a pattern every future component with both actions and computed state will need too.
- **DoD:** ✅ met — verified against the live running server, not just the 155-test suite: linked a real demo user to a real Employee+Contract via `tinker`, then drove the real UI (`/my-profile`) and confirmed all four tiles render with real data; used `tinker` to update contact info, add two dependents with a 60/40 beneficiary split (confirmed via `DB::table` that `passport_number`'s raw value contains no plaintext), and upload a real document — then reloaded `/my-profile` and confirmed the page reflects all of it, confirmed the new Dependent audit entries appear on `/audit-log` automatically, and confirmed the uploaded document downloads correctly as its owner.
- 155 tests total (up from 139), Pint and Larastan clean.

### 1.3 Recruitment & Onboarding
- [ ] Vacancy model + approval-chain-gated posting
- [ ] Application/candidate intake, scoring matrix, interview notes
- [ ] Offer letter generation from template
- [ ] Onboarding checklist, probation tracking with alert
- **DoD:** a vacancy can go from request → posted → shortlisted → offered → onboarded employee record, through the UI.

### 1.4 Leave & Attendance
- [ ] Tenant-configurable leave types + accrual rules
- [ ] Leave request + multi-level approval (using the Phase 0 workflow engine)
- [ ] Leave balance display (allocated/used/pending/available)
- [ ] Timesheet: full-time or percentage-split-by-project, with multi-level approval
- **DoD:** an employee can submit a leave request and a timesheet; a supervisor can approve both; balances update correctly.

### 1.5 Payroll & Compensation
- [ ] Allowance/deduction configuration UI
- [ ] Payroll run engine: gross → allowances → deductions → net, per employee, per period
- [ ] Cost-allocation percentage split per employee, feeding a `payroll_cost_allocations` table (this is the HRM↔Finance link — see [Data Model](../08-data-model.md))
- [ ] Multi-level payroll approval workflow
- [ ] Payslip PDF generation + Employee Portal access
- [ ] Bank transfer file export
- **DoD:** a full payroll run for a demo tenant, 5+ employees, multiple cost allocations, goes from generate → approve → disburse, and payslips are correct to the cent.

### 1.6 Performance Management
- [ ] Performance cycle, objectives/KPIs with weights, self-assessment form, supervisor review
- [ ] Performance history archive
- **DoD:** matches the self-assessment → supervisor review flow shown in the Nova HRM reference.

### 1.7 Training & Development
- [ ] Training records, mandatory/compliance flag (e.g. PSEA), skills tracking, certificates with expiry alerts
- **DoD:** an employee's training tab shows completed/in-progress/mandatory-pending state correctly.

### 1.8 Asset Management (staff-linked)
- [ ] Uses the shared `Asset` entity — **depends on Phase 3's fuller asset registry for full CRUD**; for Phase 1, build the employee-facing assignment/verification UI against a minimal `Asset` table that Phase 3 will extend, not replace (see [`DECISIONS.md`](DECISIONS.md#d-006))
- [ ] Annual asset verification cycle UI
- **DoD:** an employee can view assigned assets and complete a verification cycle; HR Admin sees org-wide completion status.

### 1.9 Safeguarding & Grievance
- [ ] Confidential + anonymous complaint submission with reference number
- [ ] Case workflow (reported → review → investigator → evidence → report → decision → closed)
- [ ] Confidentiality-tier enforcement (Highly Restricted — see [Roles & Permissions](../03-roles-and-permissions.md#confidentiality-tiers))
- **DoD:** a complaint submitted anonymously is genuinely untraceable to its submitter in the UI/DB for any role except what the design allows; a non-Focal-Point HR Admin cannot open case detail.

### 1.10 Exit Management
- [ ] Exit initiation, checklist (asset return, exit interview, document handover, final payroll, clearance), blocks completion until all items clear
- **DoD:** an exit cannot be marked complete while an asset shows unreturned.

### 1.11 HR reports & dashboards
- [ ] Headcount, payroll cost by donor/project, leave utilization/liability, contract expiry, training/safeguarding compliance summaries
- **DoD:** each report renders correctly against the demo tenant's data and exports via the Phase 0 export framework.

### 1.12–1.14 Portals
- [ ] Supervisor Portal (My Team, approvals, reviews)
- [ ] HR Admin Portal (dashboard, employees, recruitment, leave config, performance, asset mgmt, exit mgmt, safeguarding, reports, settings)
- [ ] Payroll Portal (generation, approval workflow, cost allocation, tax & insurance config, reports)
- **DoD:** each portal matches its section of [Roles & Permissions](../03-roles-and-permissions.md).

### 1.15 Testing & UAT
- [ ] Unit tests for payroll math, leave accrual, approval routing
- [ ] Integration test: payroll run → cost allocation records created correctly
- [ ] UAT script written and run against a pilot tenant's real-shaped data (see [Roadmap → testing gate](../09-roadmap.md#testing--go-live-gate-every-phase))

### 1.16 Documentation
- [ ] User manual (per-portal), admin/technical manual, process flowcharts for recruitment/payroll/exit
- [ ] Update this build plan and `CHANGELOG.md` marking Phase 1 complete

**Phase 1 exit criteria:** a pilot tenant can run its full HR lifecycle — hire, manage leave/time, run payroll, review performance, train, and exit an employee — with correct multi-level approvals and a clean audit trail, entirely through the UI.

---

## Phase 2 — Finance & Donor Compliance

*Full functional spec: [`../05-module-finance.md`](../05-module-finance.md).*

- [ ] 2.1 Chart of accounts + cost center/budget line model, tenant-configurable
- [ ] 2.2 General ledger + journal entries; wire up the Phase 1 payroll cost-allocation postings
- [ ] 2.3 Budget management: donor budget entry, budget vs. actual, burn rate, versioned revisions
- [ ] 2.4 Accounts Payable: vendor registry (shared with Phase 3), invoice processing, payment vouchers, withholding tax
- [ ] 2.5 Accounts Receivable: grant/donor receivable & installment tracking
- [ ] 2.6 Cash & bank management: multi-bank, reconciliation, petty cash/field cash control
- [ ] 2.7 Fixed assets: capitalization, depreciation, transfer, disposal (shared registry with Phase 3)
- [ ] 2.8 Procure-to-pay integration scaffolding (full link completes in Phase 3)
- [ ] 2.9 Donor compliance report templates — start with one format end-to-end before generalizing (see [Q3](QUESTIONS.md#q3))
- [ ] 2.10 Financial reports: trial balance, income & expenditure, balance sheet, project financial report, donor financial statement
- [ ] 2.11 Finance Portal
- [ ] 2.12 Testing & UAT; documentation
- **DoD (phase):** a payroll run from Phase 1 is visible and correctly posted in the general ledger against the right project/donor cost center, and a project financial report reconciles.

---

## Phase 3 — Procurement & Logistics

*Full functional spec: [`../06-module-procurement-logistics.md`](../06-module-procurement-logistics.md).*

- [ ] 3.1 Procurement plan model
- [ ] 3.2 Purchase requisition + multi-level approval + budget check against Phase 2's budget lines
- [ ] 3.3 RFQ/tender + bid comparison
- [ ] 3.4 Purchase order generation
- [ ] 3.5 Supplier/vendor database — **reconcile with the Phase 2 AP vendor table into one shared entity** (see [`DECISIONS.md`](DECISIONS.md#d-007))
- [ ] 3.6 Goods receipt notes, three-way match with PO + invoice
- [ ] 3.7 Warehouse/inventory: multi-warehouse, batch/expiry, stock alerts, damaged/expired handling
- [ ] 3.8 Asset & equipment registry — **extend the Phase 1 minimal `Asset` table into the full registry** (category, barcode/serial, maintenance schedule); staff-linked assignment already built in 1.8 should keep working unmodified
- [ ] 3.9 Fleet management: vehicles, fuel, maintenance, driver/trip logs
- [ ] 3.10 Logistics reports
- [ ] 3.11 Procurement & Logistics Portal
- [ ] 3.12 Testing & UAT; documentation
- **DoD (phase):** a requisition → PO → GRN → invoice flow completes and posts correctly into Phase 2's Finance module; an asset issued to an employee in Phase 1's UI is the same row visible here.

---

## Phase 4 — Programs, Grants & M&E

*Full functional spec: [`../07-module-programs.md`](../07-module-programs.md).*

- [ ] 4.1 Project/grant lifecycle model, linked to Phase 2's budget envelope
- [ ] 4.2 Activity planning & tracking against a results framework
- [ ] 4.3 Beneficiary/household registration with unique-ID de-duplication
- [ ] 4.4 Sector-specific tracking (tenant-configurable sector list)
- [ ] 4.5 Distribution management, drawing stock from Phase 3's warehouse module
- [ ] 4.6 Indicators & M&E dashboards
- [ ] 4.7 Donor reporting dashboards (reads Finance's budget-utilization data, doesn't duplicate it)
- [ ] 4.8 Programs Portal
- [ ] 4.9 Testing & UAT; documentation
- **DoD (phase):** a project created here shows correct staff allocation (from HRM), correct budget/actuals (from Finance), and correct stock depletion (from Procurement) for a distribution event — the full four-module loop closes.

---

## Phase 5 — Multi-tenant hardening & growth

*Full detail: [`../09-roadmap.md`](../09-roadmap.md#phase-5--multi-tenant-hardening--growth).*

- [ ] 5.1 Self-serve tenant onboarding flow
- [ ] 5.2 Advanced cross-module BI/analytics dashboards
- [ ] 5.3 Native mobile app planning (API already exists from Phase 0)
- [ ] 5.4 Payment/disbursement integrations
- [ ] 5.5 Biometric integration
- [ ] 5.6 Additional donor report templates as needed

---

## Cross-cutting, ongoing (not a phase — do continuously)

- [ ] Keep [`CHANGELOG.md`](CHANGELOG.md) updated every session
- [ ] Log every new assumption in [`DECISIONS.md`](DECISIONS.md) the moment it's made, not retroactively
- [ ] Log every open question in [`QUESTIONS.md`](QUESTIONS.md) rather than silently guessing on anything the user would plausibly want to weigh in on
- [ ] Keep this build plan's checkboxes current — it's the team's shared source of truth for "where are we"

---
**See also:** [`../09-roadmap.md`](../09-roadmap.md) (the narrative version) · [`DECISIONS.md`](DECISIONS.md) · [`QUESTIONS.md`](QUESTIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
