# 02 · Architecture & Multi-Tenancy

How one Laravel codebase serves many independent NGOs, and the technical decisions that follow.

## Multi-tenancy model

None of the source documents address this — each was written as if the system serves one organization. This is the plan's central addition.

### Recommendation: shared database, row-level isolation

Every tenant-scoped table carries a `tenant_id`. A global scope enforces that every query is automatically filtered to the current tenant — application code never has to remember the filter, and it becomes structurally hard to leak one NGO's data into another's view.

| Model | Pros | Cons | Verdict |
|---|---|---|---|
| **Shared DB, `tenant_id` column** *(recommended)* | Cheapest to run/operate; one set of migrations; easy cross-tenant platform analytics. | Requires discipline (global scopes, tests) to prevent leakage; noisy-neighbor risk at very large scale. | Default for all tenants. |
| Database-per-tenant | Strongest isolation; simple to reason about; easy full export/deletion on offboarding. | Migration fan-out (every schema change runs N times); higher hosting cost; harder platform-wide reporting. | Escape hatch for a large enterprise NGO that contractually requires dedicated data residency — not the default. |
| Schema-per-tenant | Middle ground. | Poor fit for MySQL; adds ops complexity for little benefit over row-level scoping. | Not recommended. |

> **Laravel implementation note:** implement as a `BelongsToTenant` trait + global scope on every tenant-scoped Eloquent model, tenant resolved from the authenticated user's session (not subdomain alone, to keep local/offline field use simple), mirrored to a tenant-aware queue connection so background jobs (payroll runs, report generation) stay scoped correctly. A package such as `stancl/tenancy` can accelerate this, but a lean custom implementation is defensible given the moderate tenant count expected (dozens of NGOs, not thousands).

### What "tenant" actually contains

- **Organization profile** — name, logo, registration details, country/countries of operation, default currency, fiscal year.
- **Org structure** — departments, duty stations/offices, positions, reporting lines.
- **Policy configuration** — leave types & accrual rules, salary grades, tax grids per country, approval workflow chains, safeguarding policy text.
- **Reference data** — donors, projects/grants, cost centers, chart of accounts, supplier list.
- **Users & roles** — every login belongs to exactly one tenant (a consultant working across two NGOs gets two separate accounts, not a shared one — keeps audit trails and payroll unambiguous).

A **Platform/Super Admin** role sits outside all tenants: creates new tenant NGOs, sees cross-tenant system health, and can impersonate (with logging) for support — but has no default access to tenant business data.

## Proposed tech stack

| Layer | Choice | Why |
|---|---|---|
| Backend framework | **Laravel** *(confirmed)* | Given. Strong fit: Eloquent ORM, queues, scheduled jobs (payroll runs, contract-expiry alerts), policies/gates for RBAC. |
| Frontend | Blade + Livewire *(recommended)* or Inertia.js + Vue/React | Open decision — see `build/QUESTIONS.md`. Livewire keeps one language and matches the form-heavy, dashboard-heavy UI well. Inertia+Vue/React only wins if a richer SPA feel or near-term native-mobile-via-API path is a priority. |
| Database | MySQL or PostgreSQL | Either works with Laravel; PostgreSQL's row-level security is a nice defense-in-depth layer on top of application-level tenant scoping. |
| Queue & cache | Redis + Laravel Horizon | Payroll generation, report exports, notification fan-out are all queue-shaped; Horizon gives per-tenant job visibility. |
| File storage | S3-compatible object storage | Personnel documents, contracts, receipts, safeguarding case files — encrypted, never local disk, path-scoped per tenant. |
| Auth | Laravel Fortify/Breeze (web) + Sanctum (API/mobile-future) | Session auth for web portals; token auth ready for a future mobile app or integration without re-architecting. |
| Search | Laravel Scout + Meilisearch *(optional, Phase 3+)* | Once supplier/beneficiary/employee datasets grow large enough that SQL search feels slow. |
| Hosting | AWS (as all source docs recommend), or any S3-compatible cloud | Not locked to AWS-only services — keep hosting portable. |

## Platform core (shared by every module)

The piece that doesn't exist in any source document but has to be built first — every module depends on it.

- **Tenant & Org Management** — tenant provisioning, org structure, duty stations, branding.
- **Auth & RBAC** — roles, permissions, segregation-of-duties rules, configurable approval chains reused by every module.
- **Notification Engine** — email/in-app/SMS-ready alerts: leave approvals, contract expiry, document expiry, budget thresholds, safeguarding escalations.
- **Audit Log** — immutable who/what/when record for every create, update, approval, and payroll/financial transaction, across all modules.
- **Document Store** — encrypted file storage with per-record attachments, access-controlled by role.
- **Reporting & Export Engine** — shared dashboard/report framework; Excel, CSV, PDF export; the base every module's "Reports" tab is built on.

## Integration architecture

- **Internal:** modules talk to each other through the shared database and domain events (e.g. `EmployeeExited` triggers asset-return checks in Procurement and access revocation in Auth), not tightly-coupled direct calls — keeps modules independently toggleable per tenant.
- **External, API-first:** a REST API (versioned, tenant-scoped, token-authenticated) is the same surface a future mobile app, donor portal, or third-party accounting tool would use — built once.
- **Outbound exports:** bank transfer files (payroll disbursement), donor report templates (USAID/UN/EU formats), Excel/CSV/PDF everywhere data is listed.
- **Notifications:** email always on; SMS gateway integration is a configurable per-tenant add-on.

## Security & data protection

Required by the ToR, reinforced by both proposals — baseline, not optional:

- **Transport & storage encryption** — HTTPS/TLS everywhere; encrypted DB fields for sensitive data (national ID, bank details, safeguarding case content); encrypted object storage.
- **RBAC with segregation of duties** (e.g. the person who creates a purchase requisition cannot also approve it) and per-tenant configurable approval hierarchies.
- **Full audit trail** on every module, especially payroll and financial transactions.
- **Two-factor authentication**, at minimum available and enforceable per tenant/role (recommended mandatory for HR Admin, Finance, Super Admin).
- **Automated daily backups** with periodic restore testing, plus a documented disaster-recovery plan.
- **Data ownership** — each tenant owns its own data outright; the platform operator has no rights beyond providing the service, with a clean export/return path on offboarding.
- **Confidentiality for sensitive modules** — safeguarding case data is restricted even from most HR Admins by default; only designated safeguarding focal points and the Country Director see full case detail (see [`04-module-hrm.md`](04-module-hrm.md#h-safeguarding--grievance-management)).

## Non-functional targets (summary)

Full detail in [`10-non-functional.md`](10-non-functional.md); headline numbers, consistent across source docs:

| Target | Value |
|---|---|
| Uptime | ≥ 98% (ToR requirement) |
| Backups | Automated, daily, with periodic restore verification |
| Support response | 24–48 hours under the maintenance agreement |
| Access | Web-based, mobile-responsive, HTTPS-only, multi-location |
| Localization | Multi-currency and multi-language (English baseline; Somali, Arabic, French as configurable per-tenant locales) |
| Export | Excel, CSV, PDF wherever data is listed |

---
**Prev:** [`01-vision-and-scope.md`](01-vision-and-scope.md) · **Next:** [`03-roles-and-permissions.md`](03-roles-and-permissions.md)
