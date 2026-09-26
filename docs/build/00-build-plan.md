# Build Plan — Step by Step

The actual engineering checklist, derived from [`../09-roadmap.md`](../09-roadmap.md) but broken down to task level. We work through this top to bottom. Check items off as they're done; if a step's approach changes, update it here rather than letting this drift from reality.

**How to use this doc:** each step is small enough to be one focused work session. Steps carry a short **Definition of Done (DoD)**. Anything a step depends on that isn't decided yet is marked with a link into [`QUESTIONS.md`](QUESTIONS.md) — the step proceeds under the documented default until answered.

**Status legend:** `[ ]` not started · `[~]` in progress · `[x]` done

---

## Phase 0 — Platform Foundation `[~ in progress — 0.1-0.6 done]`

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

### 0.7 Notification engine
- [ ] `notifications` table (Laravel's built-in database notifications) + mail channel
- [ ] Notification types seeded: approval needed, approval decision, contract/document expiring, budget threshold — even if only a couple have real triggers yet
- [ ] SMS channel left as a documented extension point, not built in Phase 0 (see [Q — SMS gateway](QUESTIONS.md))
- **DoD:** an in-app + email notification fires when the demo approval workflow above changes status.

### 0.8 Audit log
- [ ] Install `spatie/laravel-activitylog` (see [`DECISIONS.md`](DECISIONS.md#d-005)) or equivalent; wire into tenant-scoped models
- [ ] Every create/update/delete on a tenant-scoped model logs who/when/what changed (old → new value)
- **DoD:** editing any seeded record produces a visible, correct audit log entry.

### 0.9 Document store
- [ ] S3-compatible storage disk configured (local `public`/`local` disk for dev, S3 for staging/prod)
- [ ] Generic `Document` model: polymorphic attachment to any record, category, expiry date, "verified" flag, tenant- and path-scoped
- [ ] Encrypted-at-rest confirmed for the chosen storage backend
- **DoD:** a file can be uploaded against a demo record, downloaded only by an authorized role, and is inaccessible cross-tenant.

### 0.10 Reporting & export framework
- [ ] Shared table/list component with Excel, CSV, PDF export baked in (used by every module's "Reports" screens later)
- **DoD:** one demo dataset exports correctly in all three formats.

### 0.11 Super Admin portal
- [ ] Tenant CRUD (create/suspend/configure a tenant)
- [ ] Cross-tenant system health view (basic — job queue status, error rate, storage usage)
- [ ] Logged impersonation flow for support access into a tenant
- **DoD:** a Super Admin can create a brand-new tenant end to end and it's immediately usable (empty but functional).

**Phase 0 exit criteria:** two tenants exist in the same database, are provably isolated (0.3's test), each has its own users/roles/org structure, and the approval + notification + audit + document + export plumbing all work on at least one trivial record type. This is the foundation everything else is built on — don't start Phase 1 until this is solid.

---

## Phase 1 — HRM & Payroll

*Full functional spec: [`../04-module-hrm.md`](../04-module-hrm.md).*

### 1.1 Employee data model
- [ ] `employees` table (links to `users` where the employee has portal access; not every historical employee needs a login)
- [ ] `contracts` table: type, start/end date, salary grade link, renewal history
- [ ] `salary_grades`, `allowance_types`, `deduction_types`, `tax_brackets` — all tenant-configurable
- **DoD:** an employee can be created with a contract and a salary grade, entirely through UI, no seeders needed.

### 1.2 Employee Portal shell + profile
- [ ] Employee Portal navigation shell (per [Roles & Permissions](../03-roles-and-permissions.md))
- [ ] Profile view/edit with change-history logging (old/new value + approver)
- [ ] Dependents & emergency contacts, with insurance-beneficiary percentage-split validation (must total 100%)
- [ ] Document repository per employee (using the Phase 0 Document store), with category + expiry + verified status
- **DoD:** matches the reference behavior in the Nova HRM UI screens for My Profile → Dependents/Documents/History.

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
