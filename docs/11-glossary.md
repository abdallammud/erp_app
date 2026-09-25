# Glossary & Sources

Humanitarian/NGO terms used throughout this plan, and a full trace back to where each requirement came from.

## Glossary

| Term | Meaning |
|---|---|
| **ToR** | Terms of Reference — the formal document an NGO issues describing what it needs from a vendor/consultant. |
| **Donor compliance** | Meeting a funder's specific financial and reporting rules for how their money is tracked, spent, and reported. |
| **Cost center** | A bucket (project, department, or donor) that costs are charged against, for internal and donor reporting. |
| **Burn rate** | How fast a project is spending its budget relative to how much time has elapsed in the grant period. |
| **Grant / Project** | A funded body of work with its own budget, timeline, and donor reporting obligations. |
| **Beneficiary** | A person or household receiving assistance through a project. |
| **NFI** | Non-Food Item — e.g. blankets, hygiene kits — distributed as humanitarian assistance. |
| **FSL** | Food Security & Livelihoods — a common humanitarian program sector. |
| **WASH** | Water, Sanitation & Hygiene — a common humanitarian program sector. |
| **M&E** | Monitoring & Evaluation — tracking whether project activities are happening and achieving their intended results. |
| **PSEA** | Protection from Sexual Exploitation and Abuse — a mandatory safeguarding compliance area for humanitarian staff. |
| **Duty station** | An organization's physical office/operating location. |
| **RFQ** | Request for Quotation — a formal request to suppliers to bid on a purchase. |
| **PO / GRN** | Purchase Order / Goods Receipt Note — the documents that authorize a purchase and confirm delivery. |
| **Tenant** | In this plan: one NGO organization using the platform, isolated from every other tenant's data. See [`02-architecture.md`](02-architecture.md). |
| **RBAC** | Role-Based Access Control — permissions assigned by role rather than per individual user. |
| **Segregation of duties** | An internal-control principle: the person who requests/creates something should not be the same person who approves it. |

## Source documents

All four were read in full before this plan was written. Nothing here is guessed from titles alone.

### 1. Terms of Reference for Establishment of an ERP System for WARDI RDI

**Issuer:** WARDI Relief and Development Initiatives — a Somali NGO founded 1993, operating across six regions of South Central Somalia (Mogadishu, Jowhar, Guriel, Wanlaweyn, Beletweyne, Buloburte) plus a Nairobi liaison office, working in health & nutrition, FSL, education, protection, and WASH.

**What it specifies:** A single-sourced, phased ERP — HRM (Phase I, 90 days) → Finance (Phase II) → Procurement & Logistics (Phase III) → Programs (Phase IV) — with a detailed HRM feature list, technical specifications (web-based, HTTPS, mobile-responsive, RBAC, audit logs, 98% uptime, multi-currency, Excel/CSV/PDF export), deliverables list, 25/25/25/25% milestone payment structure, and confidentiality/IP clauses giving WARDI ownership of all data and custom configuration.

**How it was used:** Primary source for the module sequence ([Roadmap](09-roadmap.md)), the HRM feature checklist ([HRM](04-module-hrm.md)), the non-functional requirements ([Non-Functional Requirements](10-non-functional.md)), and the data-ownership principle ([Architecture](02-architecture.md)). WARDI-specific details (its six regions, its single vendor) were generalized into tenant-level configuration rather than platform assumptions.

### 2. WARDI ERP Implementation — Technical & Financial Proposal

**Author:** Prime Tech Solutions, responding directly to the ToR above.

**What it specifies:** A very granular HRM feature list (11 sub-areas, including safeguarding policy management and incident/compliance monitoring not spelled out in the ToR itself), a full security & governance feature list, a project team structure, an 11-month/$180,000 implementation plan across all four modules, and a risk register covering data migration, user resistance, scope creep, technical risk, budget overrun, timeline delay, and vendor dependency.

**How it was used:** Main source for the detailed HRM sub-module breakdown ([HRM](04-module-hrm.md), sections A–I), the security feature list ([Architecture](02-architecture.md)), and the Finance/Procurement/Programs feature lists it lists as future-phase scope. Vendor-specific pricing, staffing, and company-history content was not carried into this plan.

### 3. Enterprise Resource Planning (ERP) System — for "MARDO"

**Author:** A template-style proposal from "Prime Tech Solutions" for a different (apparently fictional/placeholder) NGO, "MARDO."

**What it specifies:** The same four-module shape arrived at independently — Module 1: Humanitarian HRM & Payroll; Module 2: Finance & Donor Compliance; Module 3: Logistics & Supply Chain (including Fleet Management); Module 4: Beneficiary & Project Management System — plus a technical architecture summary and an illustrative $90,000/6–8 month budget and timeline.

**How it was used:** Cross-validates the four-module structure independently of WARDI's specific ToR, and is the main source for the [Fleet Management](06-module-procurement-logistics.md#f-fleet-management) feature list and the [Beneficiary registration & sector-tracking](07-module-programs.md) detail, which the WARDI ToR only sketches as future-phase headings.

### 4. Nova Humanitarian HRM (70-screen UI reference)

**What it is:** A near-complete set of UI screens for a fictional humanitarian NGO's HRM system, covering four distinct portals: **Employee Portal** (profile, dependents, projects, leave, performance, self-assessment, payroll, timesheet, training, assets, asset verification, safeguarding, calendar, history, notifications, reports, settings), **Supervisor Portal** (my team, leave approvals, timesheet approvals, performance reviews), **HR Admin Portal** (dashboard, employees, recruitment, leave config, performance, asset management, exit management, safeguarding, reports, settings), and **Payroll Portal** (payroll generation, approval workflow, cost allocation, tax & insurance configuration, reports).

**How it was used:** The primary UX and functional source of truth for [the HRM module](04-module-hrm.md) and the [portal concept](03-roles-and-permissions.md) applied to every other module — it shows working-level detail (exact fields, statuses, approval-chain visuals, cost-allocation percentages) that the text documents only describe as bullet points. Concrete examples reused in this plan: the multi-level payroll approval chain (Supervisor → HR Admin → Finance → Country Director), the leave-balance display pattern (allocated/used/available), the annual asset-verification cycle, the confidential/anonymous safeguarding complaint flow, and the employment-history/profile-change-history audit pattern.

## What was generalized, and how

| Source assumption | Generalized to |
|---|---|
| One organization (WARDI, or fictional MARDO/Nova) | Many organizations, each an isolated tenant — see [Architecture](02-architecture.md) |
| Fixed leave types, salary grades, tax rules | Tenant-configurable policy, not hard-coded values |
| Somalia/Kenya-specific tax & currency context | Per-tenant, per-country configuration; multi-currency and multi-language from Phase 1 |
| Single named vendor delivering a custom build | A reusable product built once, onboarding new NGOs through configuration |
| WARDI's specific six regions and duty stations | Generic, tenant-defined Duty Station entity |

---
**Prev:** [`10-non-functional.md`](10-non-functional.md) · **Back to:** [`index.md`](index.md)
