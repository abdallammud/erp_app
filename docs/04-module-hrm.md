# Module 1 of 4 · HRM & Payroll

The foundation module — every NGO has staff before it has anything else, and every other module (project cost allocation, procurement approvals, safeguarding) hangs off the employee record. Also the most thoroughly specified module: it draws directly on a 70-screen UI reference in addition to the ToR and both proposals.

> **Why it's Phase 1:** WARDI's ToR sequences the whole ERP as HRM → Finance → Procurement & Logistics → Programs, and both proposals agree HRM is the right starting point since org structure, staff, and cost centers are referenced by every later module. This plan keeps that sequence — see [`09-roadmap.md`](09-roadmap.md).

## Sub-modules

### A. Recruitment & Onboarding

- Vacancy planning with multi-level approval before a role can be posted
- Internal and external (public) job posting
- Applicant Tracking System — CV repository, application intake, shortlisting
- Candidate scoring matrix and ranking; interview panel documentation
- Automated offer letter generation from templates
- Digital onboarding checklist (equipment, accounts, orientation, policy acknowledgements)
- Probation tracking with automated alerts ahead of the review date
- Recruitment analytics: time-to-hire, applications/shortlist/interview/offer funnel per vacancy, offer acceptance rate

### B. Employee Records Management

- Centralized digital personnel file per employee, with a secure document repository (ID, contract, certificates, police clearance, etc.) — each document tagged by category, with an expiry date where relevant and a "verified" status set by HR
- Contract lifecycle management: type (fixed-term, project-based, consultant, intern), start/end dates, renewal history, automated expiry alerts
- Organizational structure mapping: department, position/grade, duty station, reporting line — multi-location by design
- Staff categorization (permanent, fixed-term, consultant, intern, casual)
- Full employment history log per employee: position changes, salary adjustments, contract renewals, duty station transfers, project reassignments — each entry shows what changed, why, who approved it, and when
- Profile change history/audit: any change to personal data (phone, email, address) is logged with old/new value and approver
- Dependents & emergency contacts: family members, relationship, DOB, passport number; flag as emergency contact and/or health-insurance-covered; insurance beneficiary allocation with percentage split that must total 100%
- HR analytics dashboard: headcount by department/duty station/employment type, gender & diversity breakdown

### C. Payroll & Compensation

- Salary grading and pay scale structure (grade → base salary band)
- Allowance configuration: housing, transport, hardship, risk, communication, per diem — tenant-defined, not hard-coded
- Deduction configuration: income tax (progressive bracket grid, configurable per country), health/life insurance, pension contribution, loan/advance repayment, absence deductions
- Staff loan and salary advance management with repayment scheduling
- Automatic payroll processing engine: pulls base salary + allowances − deductions per employee for the pay period
- Multi-level payroll approval workflow (mirrors the reference: Supervisor → HR Admin → Finance → Country Director, each level shows amount, employee count, and can approve/reject with a comment) before disbursement
- Payslip generation (PDF) with full earnings/deductions breakdown, available in the Employee Portal immediately after approval
- Bank transfer file export for disbursement
- Cost allocation: each employee's salary is split by percentage across one or more projects/donors/budget lines; payroll cost rolls up automatically into project and donor cost reports — **this is the single most important link between HRM and Finance**
- Payroll reconciliation and audit trail — every payroll run, every approval, every amount, immutable
- Multi-currency support

### D. Leave & Attendance

- Configurable leave types per tenant — annual, sick, maternity/paternity, emergency, unpaid, compassionate, plus custom types
- Automated leave accrual calculation and running balance (allocated / used / pending / available) shown per type
- Multi-level leave approval workflow, with status visible to the employee at every stage
- Team/org leave calendar; "who's on leave today" widget for supervisors and HR
- Attendance tracking (manual or, later, biometric-integration-ready)
- Monthly timesheets with two modes: full-time (one project) or percentage split across multiple projects/donors for the month — directly feeds payroll cost allocation and Finance's project financial reports
- Multi-level timesheet approval (supervisor, then cost-allocation review)
- Leave & timesheet analytics/reporting

### E. Performance Management

- Performance cycle management (e.g. "2026 Annual Review", "Mid-Year Review") with a defined deadline, visible to all in-scope employees
- Objective/KPI setting per employee, each with a weight (%), a progress percentage, and status (on track / ahead / at risk)
- Structured self-assessment form: rate each objective against a defined scale (e.g. Improvement Needed / Meets / Exceeds / Outstanding), provide evidence/comments, set development goals, sign digitally, submit to supervisor
- Supervisor review: rate the same objectives, add overall rating, key strengths, development areas, additional comments
- Performance history archive per employee, with past cycle ratings visible over time
- Promotion recommendation tracking and contract-renewal alerts tied to performance outcomes
- Reporting dashboard: average rating by department, completion rate of the current cycle, ratings distribution

### F. Training & Development

- Individual capacity-building plan per employee, with an annual training-hours target and tracked progress
- Training catalog with priority, category (compliance, technical, soft skills, professional development), and target date
- Mandatory/compliance training tracking with due dates — e.g. PSEA (Protection from Sexual Exploitation and Abuse) and Code of Conduct refreshers — flagged distinctly from optional development training
- Skills assessment and tracking by category (technical, sector/protection-specific, soft skills), with a percentage proficiency score and last-assessed date
- Completed training history with certificates (downloadable), provider, completion date, score, and expiry where applicable, with "expiring soon" alerts
- Training evaluation and completion reporting

### G. Asset Management (staff-linked)

- Registry of assets issued to each employee (laptop, phone, vehicle access, office keys, etc.) with serial number, value, assigned date, and condition
- Annual/periodic asset verification cycle: employee confirms possession and condition of each assigned asset within a defined window; HR/Asset Manager tracks completion (verified / pending) org-wide
- Issue reporting (damage/loss) from the employee portal
- Full asset history per employee, including returned/reassigned items and reason
- Automatic flag for unreturned assets at contract end, blocking final clearance until resolved
- Shares its underlying asset registry with the [Procurement & Logistics](06-module-procurement-logistics.md) module — an asset exists once, whether it's tracked as organizational inventory or currently assigned to a staff member

### H. Safeguarding & Grievance Management

- Digital safeguarding policy acknowledgement, required at onboarding and on policy updates
- Confidential incident/complaint submission — from any employee, with an explicit anonymous-submission option that generates a secure reference number for follow-up without revealing identity
- Case categorization (PSEA violation, harassment, fraud & corruption, discrimination, code-of-conduct breach) and severity/priority
- Restricted, role-based case visibility — see the [confidentiality tiers](03-roles-and-permissions.md#confidentiality-tiers); only designated Safeguarding Focal Points and the Country Director see full case detail
- Structured investigation workflow with defined stages: reported → preliminary review → investigator assigned → evidence collection → investigation report → decision & action → case closure
- Evidence/document upload, witness tracking, investigator assignment, and full timeline history per case
- Compliance dashboard: training completion, policy acknowledgement status, and case summary counts — without exposing case content to unauthorized roles

### I. Exit Management

- Exit initiation from either a resigning employee or HR, capturing exit type (resignation, contract expiry, termination), notice date, and last working day
- Standard exit checklist with progress tracking: asset return, exit interview, document handover, final payroll, clearance certificate
- Contract-expiry early warning: employees with contracts ending soon surface automatically so HR can act before the last working day, not after
- Exit completes only once every checklist item clears — blocks final payroll disbursement until asset return and clearance are confirmed
- Completed-exits archive for institutional record and rehire-eligibility reference

## Cross-cutting HR reports

- Headcount by department, duty station, employment type, gender
- Payroll cost by donor / project / department (the direct link into Finance's project financial reports)
- Leave utilization and liability (accrued-but-unused leave = a financial liability Finance needs visibility into)
- Contract expiry / turnover report
- Training compliance and safeguarding compliance summaries
- Full audit trail export for any HR or payroll transaction

## Key workflows

**Recruitment to hire**
1. **Vacancy request** — hiring manager submits a vacancy request; routed through the tenant's configured approval chain before posting.
2. **Posting & applications** — vacancy published internally/externally; applications collected into the ATS.
3. **Shortlisting & interview** — panel scores candidates against a defined matrix; interview notes attached per candidate.
4. **Offer** — offer letter auto-generated from the selected candidate's data and the position's salary grade; sent for acceptance.
5. **Onboarding** — accepted candidate becomes a new employee record; onboarding checklist assigned; probation period starts with an automated review-date alert.

**Payroll run to disbursement**
1. **Generate** — payroll officer selects the pay period and employee scope; the engine calculates gross, allowances, deductions, and net pay per employee from current salary/allowance/deduction configuration and cost-allocation percentages.
2. **Review** — preview totals (gross, deductions, net) and per-employee/per-project breakdown before submitting for approval.
3. **Approve** — payroll moves through the configured multi-level chain (e.g. Supervisor → HR Admin → Finance → Country Director), each level able to approve or reject with a comment; full status visible throughout.
4. **Disburse** — on final approval: payslips generate and publish to the Employee Portal, a bank transfer file exports, and cost posts into Finance's general ledger against the correct donor/project cost centers.

## Data entities specific to HRM

Shared/global entities (Employee, Department, Duty Station, Project, Donor) live in the core data model — see [`08-data-model.md`](08-data-model.md).

| Entity | Key attributes |
|---|---|
| Vacancy / Application / Candidate | position, grade, status, score, panel notes |
| Contract | employee, type, start/end date, salary grade, renewal history |
| Salary Grade / Allowance Type / Deduction Type / Tax Bracket | tenant-configurable pay structure components |
| Payroll Run / Payslip / Cost Allocation Line | period, per-employee breakdown, per-project percentage split |
| Leave Type / Leave Request / Leave Balance | employee, type, dates, days, approval status |
| Timesheet / Timesheet Allocation Line | employee, period, hours, project split |
| Performance Cycle / Objective / Assessment | employee, cycle, objectives, ratings, comments |
| Training Record / Certificate | employee, course, provider, completion, expiry |
| Employee Asset Assignment | employee, asset (shared registry), assigned/returned dates, condition |
| Safeguarding Case | type, severity, status, investigator, restricted-access flag, timeline |
| Exit Record | employee, type, notice/last-day dates, checklist status |

---
**Prev:** [`03-roles-and-permissions.md`](03-roles-and-permissions.md) · **Next:** [`05-module-finance.md`](05-module-finance.md)
