# 03 · Roles, Portals & Permissions

The system isn't one dashboard — it's a set of distinct portals, each showing only what that role needs. This pattern comes from the Nova Humanitarian HRM reference and is extended to the other three modules.

## The portal concept

Rather than one navigation menu with everything in it (permission checks hiding items), each role gets a purpose-built portal with its own sidebar. Keeps the UI simple for a field employee while still giving an HR Admin or Finance Controller a dense, powerful workspace. A user with more than one role (e.g. a Country Director who also approves their own team's leave) can switch between the portals they have access to.

| Module | Portal | Contains |
|---|---|---|
| HRM | **Employee Portal** | Self-service: profile, dependents, projects, leave, performance, self-assessment, payroll (view), timesheet, training, assets, safeguarding, calendar, notifications. |
| HRM | **Supervisor Portal** | My Team, leave approvals, timesheet approvals, performance reviews for direct reports. |
| HRM | **HR Admin Portal** | Dashboard, employees, recruitment, leave configuration, performance cycles, asset management, exit management, safeguarding oversight, reports, settings. |
| HRM | **Payroll Portal** | Payroll generation, multi-level approval workflow, cost allocation by donor/project, tax & insurance configuration, payroll reports. |
| Finance | **Finance Portal** | General ledger, budgets, AP/AR, bank reconciliation, fixed assets, donor compliance reports. |
| Procurement | **Procurement & Logistics Portal** | Requisitions, RFQs/tenders, suppliers, purchase orders, warehouse/inventory, assets, fleet. |
| Programs | **Programs Portal** | Projects/grants, activities, beneficiaries, distributions, indicators, donor reporting dashboards. |
| Platform | **Super Admin Portal** | Tenant onboarding & configuration, cross-tenant system health, platform-level user management — outside any single tenant's data. |

## Role → module access matrix

`V` = view, `E` = edit/create, `A` = approve, `—` = no access. Exact permissions are configurable per tenant; this is the sensible default.

| Role | HRM (own record) | HRM (team/org) | Payroll | Finance | Procurement | Programs | Safeguarding |
|---|---|---|---|---|---|---|---|
| Employee | V/E (profile fields) | — | V (own payslip) | — | — | V (assigned project only) | E (submit only) |
| Supervisor | V/E | V + A (own team) | V (team cost) | — | V (own requisitions) | V (assigned projects) | E (submit only) |
| HR Admin | V/E | V/E/A (org-wide) | V | V (staff cost reports) | — | — | V/E (case mgmt, scoped) |
| Payroll / Finance Officer | — | V (for allocation) | V/E/A | V/E/A | V (P2P integration) | V (budget utilization) | — |
| Procurement Officer | — | — | — | V (budget checks) | V/E/A | V (distribution needs) | — |
| Program / M&E Officer | — | V (assigned team) | — | V (project financials) | V (stock for distributions) | V/E/A | — |
| Country Director / Executive | V | V (org-wide, read) | V | V | V | V | V (summary only, unless also Focal Point) |
| Safeguarding Focal Point | — | — | — | — | — | — | V/E/A (full case detail) |
| Auditor / Donor | — | — | V (aggregate only) | V (scoped to grant/period) | V (scoped) | V (scoped) | — |
| Super Admin | — | — | — | — | — | — | — |

> Super Admin deliberately has no default access to any tenant's business data — platform administration and tenant business operations are separate concerns. Support access requires explicit, logged impersonation.

## Approval workflow engine

Every module needs the same underlying capability — a configurable, multi-level approval chain — so it's built once in the platform core and reused everywhere:

- **Configurable chain per action type**, per tenant: e.g. annual leave 1–3 days needs only the direct supervisor; 4+ days also needs the department head; payroll needs Supervisor → HR Admin → Finance → Country Director.
- **Segregation of duties** enforced structurally: the requester can never also be an approver in their own chain.
- **Status tracking** visible to the requester at every stage (Pending → Level 1 approved → Level 2 pending → …), consistent across leave, timesheet, payroll, and procurement approvals.
- **Escalation & reminders** — configurable SLA per step (e.g. 48-hour reminder, auto-escalate after 5 days) feeding the shared notification engine.

## Confidentiality tiers

Not every record is equally sensitive. Three tiers, applied consistently across modules:

| Tier | Examples | Default visibility |
|---|---|---|
| Standard | Employee name, position, department; project activity records; supplier catalog | Visible to relevant portal roles org-wide |
| Restricted | Salary, bank details, national ID, performance ratings, beneficiary personal data | Owner + direct approval chain + HR Admin/Finance/Program lead only |
| Highly restricted | Safeguarding case content, disciplinary records, grievance details | Named Safeguarding Focal Point(s) and Country Director only — even HR Admin excluded by default |

---
**Prev:** [`02-architecture.md`](02-architecture.md) · **Next:** [`04-module-hrm.md`](04-module-hrm.md)
