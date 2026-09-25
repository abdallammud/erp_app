# NGO ERP Platform — Planning Docs

A multi-tenant ERP for NGOs — HRM, Finance, Procurement & Logistics, and Programs — built once and reusable across many organizations, not custom-built for a single one. Implemented in Laravel; this documentation set is what that build follows. See [`docs/README.md`](README.md) for how this folder is organized, and [`build/00-build-plan.md`](build/00-build-plan.md) for the actual step-by-step engineering plan.

## What this is built from

Four source documents were read in full and synthesized into this plan, treated as incomplete drafts, not a finished spec:

| Document | What it is | How it was used |
|---|---|---|
| **Terms of Reference — WARDI RDI** | WARDI's official ToR for a phased ERP (HRM → Finance → Procurement & Logistics → Programs), single-sourced to one vendor, 90-day HRM phase. | Primary source for scope, phasing logic, deliverables, compliance requirements. WARDI-specific assumptions generalized into tenant configuration. |
| **WARDI ERP Implementation Proposal** (Prime Tech Solutions) | A vendor's response to the ToR — granular HRM feature list, security features, team structure, methodology, 11-month timeline, $180k budget. | Main source for the HRM feature checklist and the security/governance feature list. Vendor pricing/staffing dropped. |
| **Enterprise Resource Planning** (generic proposal, for "MARDO") | A template-style ERP proposal already organized as four modules: HRM & Payroll, Finance & Donor Compliance, Logistics & Supply Chain, Beneficiary & Project Management. | Confirms the four-module shape independently of WARDI; main source for the Programs module and the Logistics/Fleet feature list. |
| **Nova Humanitarian HRM** (70-page UI reference) | Screen designs: Employee Portal, Supervisor Portal, HR Admin Portal, Payroll Portal, for a fictional NGO. | UX/functional source of truth for the HRM module — shows working detail the text docs only describe as bullets. |

> **The key gap this plan fills:** every source document assumes the system is built for one organization. None address multi-tenancy — separate NGOs each running their own instance on one shared platform. That's the central design problem this plan solves; see [`02-architecture.md`](02-architecture.md).

## The module map

| Module | Covers |
|---|---|
| **1. HRM & Payroll** | Recruitment, employee records, leave & attendance, payroll, performance, training, safeguarding, exit management. Most detailed module. |
| **2. Finance & Donor Compliance** | General ledger, multi-donor budgeting, AP/AR, bank reconciliation, fixed assets, procure-to-pay, donor-format reporting. |
| **3. Procurement & Logistics** | Procurement planning, RFQ/tender, supplier management, warehouse & inventory, asset & fleet management. |
| **4. Programs, Grants & M&E** | Project/grant lifecycle, beneficiary registration, distributions, sector tracking, indicators, donor reporting dashboards. |
| **Cross-cutting: Platform Core** | Tenant & org management, auth & RBAC, notifications, audit log, document store, reporting engine — shared by all four modules. |

## Reading order

1. [`01-vision-and-scope.md`](01-vision-and-scope.md) — what we're building, for whom, out of scope for now.
2. [`02-architecture.md`](02-architecture.md) — the multi-tenancy decision that changes everything else.
3. [`03-roles-and-permissions.md`](03-roles-and-permissions.md) — who uses the system, what each portal shows.
4. Module docs in build order: [HRM](04-module-hrm.md) → [Finance](05-module-finance.md) → [Procurement & Logistics](06-module-procurement-logistics.md) → [Programs](07-module-programs.md).
5. [`08-data-model.md`](08-data-model.md) — the shared entities tying all four modules together.
6. [`09-roadmap.md`](09-roadmap.md) — build sequence (see [`build/00-build-plan.md`](build/00-build-plan.md) for the granular execution version).
7. [`10-non-functional.md`](10-non-functional.md) — security, performance, localization, compliance.
8. [`11-glossary.md`](11-glossary.md) — NGO/humanitarian terms and full source traceability.

## Confirmed vs. still open

**Confirmed:** Backend framework is Laravel. Four core modules. Multi-tenant, multi-org from day one. Cloud-hosted, web-based, mobile-responsive. Donor-compliance and audit-trail are first-class.

**Still open (see [`build/QUESTIONS.md`](build/QUESTIONS.md) for the full list with defaults):** frontend stack, shared-DB vs. schema-per-tenant for large customers, native mobile app timing, which donor report templates to ship first, local dev environment tooling, hosting target.
