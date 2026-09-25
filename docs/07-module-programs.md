# Module 4 of 4 · Programs, Grants & M&E

The reason the organization exists — projects, the people they serve, and proof to donors that the work happened and mattered. The WARDI ToR calls this "Programs / Project Management"; the generic proposal calls it "Beneficiary & Project Management" — this plan merges both views into one module.

> **Scope note:** this module tracks beneficiaries, distributions, activities, and indicators at the level needed for program management and donor reporting. It is not a clinical case-management system — see [Vision & Scope → out of scope](01-vision-and-scope.md#explicitly-out-of-scope-for-now).

## Sub-modules

### A. Project / Grant Lifecycle Management

- Project record per grant/award: donor, budget envelope (linked to Finance's cost centers), start/end dates, sectors covered, geographic scope
- Project status lifecycle (planning → active → closing → closed) with budget and reporting obligations tracked per stage

### B. Activity Planning & Tracking

- Activity plan per project, aligned to a results framework (outputs/outcomes)
- Activity status and completion tracking against plan

### C. Beneficiary Registration

- Individual and household registration, with a unique beneficiary ID to prevent duplicate registration across projects
- Vulnerability scoring/criteria per project's targeting methodology
- Geo-location tagging for coverage mapping
- Designed to be biometric-integration-ready without requiring it in Phase 1 (see [Vision & Scope](01-vision-and-scope.md))

### D. Sector-Specific Tracking

Configurable by tenant to match the sectors it actually works in (WARDI, for example, operates in health & nutrition, food security & livelihoods, education, protection, and WASH):

- Nutrition tracking
- Health tracking
- Food Security & Livelihoods (FSL) support tracking
- Protection case tracking (program-level, not the HR safeguarding case module — see [HRM → Safeguarding](04-module-hrm.md#h-safeguarding--grievance-management) for staff/organizational conduct cases)
- Education support tracking
- WASH tracking

### E. Distribution Management

- Cash transfer tracking
- Food distribution tracking
- Non-food item (NFI) distribution tracking
- Attendance sheet generation/export for distribution events
- Draws distributed items from warehouse stock in [Procurement & Logistics](06-module-procurement-logistics.md), so inventory depletion and distribution records stay in sync automatically

### F. Indicators & Monitoring & Evaluation

- Indicator tracking system aligned to each project's results framework
- Target vs. achievement monitoring
- Outcome measurement
- Indicator dashboards, filterable by project, donor, sector, and period

### G. Donor Reporting

- Budget utilization tracking (pulled from Finance's project financial report, not re-entered)
- Donor reporting dashboards with narrative-report support data — the numbers and disaggregations a program officer needs when writing the narrative, not the narrative text itself
- Gender and geographic disaggregation on beneficiary and indicator reports
- Data export (Excel, CSV, PDF) for all program reports

## Key integration points

| With | How |
|---|---|
| Finance | Every project here corresponds to a budget envelope and cost center there; budget utilization is read from Finance, not duplicated. |
| HRM | Staff are assigned to projects (via HRM's project allocation/cost-split), so "who's working on this grant" is answered from the employee record, not a separate program roster. |
| Procurement & Logistics | Distributions consume warehouse stock; procurement plans should be informed by project activity plans. |

## Data entities specific to Programs

| Entity | Key attributes |
|---|---|
| Project / Grant | donor, budget link, dates, sectors, status |
| Activity | project link, plan vs. actual, output/outcome mapping |
| Beneficiary / Household | unique ID, demographic data, vulnerability score, geo-location |
| Distribution Event | project, type (cash/food/NFI), beneficiary list, stock items drawn, attendance |
| Indicator / Indicator Result | project, definition, target, achieved-to-date, disaggregation |
| Sector Record | configurable per-sector tracking (nutrition, health, FSL, protection, education, WASH) |

---
**Prev:** [`06-module-procurement-logistics.md`](06-module-procurement-logistics.md) · **Next:** [`08-data-model.md`](08-data-model.md)
