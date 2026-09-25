# 01 · Vision & Scope

Why this exists, who it's for, and what it deliberately does not try to do yet.

## Problem statement

Small and mid-size NGOs typically run operations across spreadsheets, generic accounting software, WhatsApp for approvals, and paper personnel files. Four recurring failures, independently identified across the source documents:

- **Donor compliance risk** — no single place showing how staff time, spend, and assets map to a grant or budget line; every audit becomes a manual reconstruction.
- **No real-time visibility** — leadership can't see burn rate, headcount cost by project, or leave liability without asking three departments to compile numbers by hand.
- **Weak internal control** — approvals happen informally (email, verbal), so segregation of duties and audit trails don't really exist.
- **Duplicated effort across systems** — the same employee, project, or donor is re-entered in HR, finance, and program tools that don't talk to each other.

## Vision statement

> One web-based platform where an NGO's HR, Finance, Procurement & Logistics, and Programs teams work from the same organizational data — staff, donors, projects, budgets, assets — with role-based access, full audit trails, and donor-ready reporting, from day one.

> **The one decision that changes the whole plan:** this is being built as a **product used by many NGOs**, not a custom system for one. WARDI's ToR is the sharpest requirements document available, so it anchors the HRM module and phasing logic — but every WARDI-specific detail (six regions, single-vendor contract, org name) is one tenant's configuration, not a hard-coded assumption. See [`02-architecture.md`](02-architecture.md).

## Design principles

Synthesized from what all four source documents agree on, generalized where they only addressed one organization:

1. **Multi-tenant by design** — each NGO is an isolated tenant with its own users, org structure, chart of accounts, donors, leave policies, and branding, on one shared codebase.
2. **Donor-compliance first** — cost centers, budget lines, and multi-donor allocation are core concepts in every module, not a Finance-only afterthought.
3. **Configuration over customization** — leave types, salary grades, approval chains, tax grids, and donor report formats are tenant-level settings, not code forks.
4. **Modular but integrated** — each module works standalone and can be adopted in phases (mirroring WARDI's own phased rollout) but shares one database and one set of core entities.
5. **Field-realistic** — built for multi-location operations with variable connectivity, high staff turnover, and cash-heavy field offices.
6. **Auditable by default** — every approval, payroll run, and financial transaction is logged with who/when/what changed, not an opt-in.

## Who this is for

| Persona | Primary needs |
|---|---|
| Employee | Self-service: payslip, leave request, timesheet, profile/dependents, self-assessment, assigned assets. |
| Supervisor / Line Manager | Approve leave/timesheets for their team, conduct performance reviews, see team roster and project allocation. |
| HR Admin / HR Manager | Full employee lifecycle: recruitment, records, leave policy, performance cycles, exit management, safeguarding oversight, HR reporting. |
| Payroll / Finance Officer | Run payroll, manage tax & insurance config, allocate staff cost across donors/projects, reconcile the GL, process AP/AR. |
| Procurement / Logistics Officer | Requisitions, RFQs, purchase orders, suppliers, warehouse stock, organizational assets, vehicle fleet. |
| Program / M&E Officer | Project activities, beneficiary registration, distributions, indicators, donor narrative reporting. |
| Country Director / Executive | Cross-module dashboards: burn rate, headcount cost, contract expiries, safeguarding summary, program KPIs. |
| Auditor / Donor *(optional, read-only)* | Scoped, read-only access to financial/compliance reports for a specific grant or period. |
| Platform / Super Admin | Onboards new tenant NGOs, manages platform config, monitors system health across tenants. |

## In scope for this plan

- The four modules — HRM & Payroll, Finance & Donor Compliance, Procurement & Logistics, Programs/Grants/M&E — fully specified, phased for delivery.
- A multi-tenant platform core: tenant management, authentication, RBAC, notifications, audit logging, document storage, shared reporting/BI layer.
- Web-based, mobile-responsive UI, usable on low-bandwidth connections, cloud-hosted.
- Standard donor compliance reporting shapes (USAID, UN, EU-style formats) as configurable export templates.

## Explicitly out of scope (for now)

Named so the roadmap isn't quietly derailed — real needs, deferred rather than ignored:

- **Clinical/case-management depth** for protection or health beneficiary data — Programs tracks beneficiaries, distributions, and indicators, not clinical workflows.
- **Payment/disbursement integrations** (mobile money, bank API payouts) — Phase 1 exports bank transfer files; live payment rails are a later integration.
- **Native mobile apps** — the web app is mobile-responsive from day one; dedicated iOS/Android apps are a Phase 5 consideration.
- **Biometric attendance/registration hardware integration** — API-ready for it, not built in Phase 1.
- **Self-serve tenant signup/billing** — early tenants onboarded manually by the platform operator.

## Success criteria

| Outcome | How we'll know |
|---|---|
| Donor compliance is provable, not reconstructed | Any staff-cost or expenditure line traces to a donor/project/budget line with one click, full audit trail. |
| Leadership has real-time visibility | Burn rate, headcount cost by project, contract-expiry dashboards always current — no manual compilation. |
| Internal controls are real | Every leave, payroll, and procurement action passes through a configurable, logged, multi-level approval chain. |
| The platform generalizes past WARDI | A second NGO onboards as a new tenant using only configuration — zero code changes. |

---
**Prev:** [`index.md`](index.md) · **Next:** [`02-architecture.md`](02-architecture.md)
