# Roadmap & Phasing

The build sequence, reconciled from three different timeline estimates in the source documents, adjusted for the fact that this is a reusable platform rather than a one-off build for one NGO.

> For the granular, task-by-task execution version of this roadmap — the thing we actually work off day to day — see [`build/00-build-plan.md`](build/00-build-plan.md).

> **Why this timeline differs from the source proposals:** the WARDI ToR asks for HRM in 90 days; the matching proposal commits to 11 months for all four modules at $180,000; the generic MARDO proposal estimates 6–8 months at $90,000. Those are vendor estimates for *a custom build serving one organization*. Building a genuinely multi-tenant, configurable platform takes real additional upfront work in Phase 0 (tenant architecture, configuration-driven policy engine, RBAC) that a single-org custom build can skip — but that investment is what lets the second, third, and tenth NGO onboard through configuration alone. Treat the phase-by-phase scope below as directional, not committed dates.

## Phase 0 — Platform Foundation

*Not present in any source document — this is the addition that makes the rest of the plan multi-tenant. Nothing in Phase 1 can be built correctly without it.*

- Tenant model, tenant provisioning, and the global tenant-scoping mechanism
- Authentication, roles & permissions engine, configurable approval-workflow engine
- Core org-structure entities: Department, Duty Station, Position
- Notification engine, audit log, document store, shared report/export framework
- Super Admin portal (tenant onboarding & platform health)

## Phase 1 — HRM & Payroll

*Matches the ToR's own Phase I.*

- Recruitment & onboarding, employee records, leave & attendance, payroll & compensation, performance management, training & development, staff asset assignment, safeguarding, exit management
- Employee Portal, Supervisor Portal, HR Admin Portal, Payroll Portal

**Required deliverables** (per ToR §12, generalized to the platform): System Requirements Specification & System Design Documentation; configured HRM module, tested (unit, integration, UAT); user manuals, technical manuals, process flowcharts, training materials; final implementation report.

## Phase 2 — Finance & Donor Compliance

- General ledger, chart of accounts, budget management, AP/AR, bank & cash management, fixed assets
- Payroll → General Ledger integration (the HRM cost-allocation link goes live here)
- Donor compliance report templates

## Phase 3 — Procurement & Logistics

- Procurement planning, requisitions, RFQ/tender, supplier management
- Warehouse & inventory, asset & equipment tracking, fleet management
- Procure-to-pay integration with Finance goes live; asset registry shared with HRM's staff-asset assignment goes live

## Phase 4 — Programs, Grants & M&E

- Project/grant lifecycle, activity tracking, beneficiary registration, distributions, sector tracking, indicators
- Full four-module integration complete: an employee's time, a purchase, and a distribution can all be traced to the same project budget line

## Phase 5 — Multi-tenant hardening & growth

*Where the "reusable product" investment pays off, and where the deferred items from [Vision & Scope](01-vision-and-scope.md) get picked back up.*

- Self-serve tenant onboarding flow (currently manual by the platform operator)
- Advanced BI/analytics dashboards across modules
- Native mobile app (API is already in place from Phase 0)
- Payment/disbursement integrations (mobile money, bank API payouts)
- Biometric attendance/registration hardware integration
- Additional donor report templates as new tenants bring new donor requirements

## Payment/milestone structure to reuse for individual NGO engagements

Even though the platform itself isn't billed per-organization, if a specific NGO (WARDI or otherwise) is engaged as a funded implementation project, the ToR's milestone structure is a clean, donor-defensible pattern to reuse:

| Instalment | Milestone |
|---|---|
| 25% | HRM module live for that tenant |
| 25% | Finance module live for that tenant |
| 25% | Procurement & Logistics module live for that tenant |
| 25% | Programs module live for that tenant |

## Testing & go-live gate (every phase)

Carried forward unchanged from the ToR, because it's good practice, not because it's WARDI-specific:

1. **Unit testing** — automated tests on business logic (payroll calculations, budget checks, approval routing) per module as it's built.
2. **Integration testing** — cross-module flows: payroll → ledger, requisition → budget check, distribution → stock depletion.
3. **User Acceptance Testing** — real users from a pilot tenant exercise real workflows before go-live sign-off.
4. **Go-live** — only after written/explicit approval from the tenant's designated approver, never a silent cutover.

---
**Prev:** [`08-data-model.md`](08-data-model.md) · **Next:** [`10-non-functional.md`](10-non-functional.md)
