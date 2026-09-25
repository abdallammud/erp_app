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

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`QUESTIONS.md`](QUESTIONS.md) · [`CHANGELOG.md`](CHANGELOG.md)
