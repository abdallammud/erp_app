# Module 2 of 4 · Finance & Donor Compliance

Where every other module's activity turns into money: payroll cost, purchase orders, and project budgets all land here. Built for the reality of grant-funded accounting — multiple donors, multiple currencies, and reports that must match each donor's own required format.

## Sub-modules

### A. General Ledger & Chart of Accounts

- Configurable chart of accounts per tenant, with support for multiple concurrent charts if a tenant needs donor-specific coding alongside its own internal one
- Cost center structure: by project, by donor, by department — the same transaction can roll up multiple ways for internal vs. donor reporting
- Budget lines per project/grant, mapped to chart-of-accounts categories
- Full double-entry ledger with journal entries, postings from payroll, procurement, and manual entry

### B. Budget Management

- Donor budget upload/entry per project, by budget line and by period
- Budget vs. actual tracking, updated in real time as payroll, procurement, and other expenditure post
- Burn rate monitoring — spend-to-date against time-elapsed, flagging projects running ahead of or behind expected pace, at the whole-project level
- Budget revision tracking — every amendment versioned, with reason and approver, never silently overwritten

### C. Accounts Payable

- Vendor/supplier registration — shared registry with [Procurement](06-module-procurement-logistics.md), not duplicated
- Invoice processing against a purchase order (three-way match: PO ↔ Goods Receipt Note ↔ Invoice)
- Payment voucher generation with multi-level approval
- Withholding tax calculation where applicable per country configuration

### D. Accounts Receivable

- Grant/donor receivable tracking — expected vs. received installments per grant agreement
- Installment schedule and ageing

### E. Cash & Bank Management

- Multi-bank account support, including field-office accounts
- Bank reconciliation workflow
- Petty cash / cash advance management, with field-office cash control suited to multi-location, cash-heavy operations

### F. Fixed Assets

- Asset capitalization and depreciation scheduling
- Asset transfer and disposal tracking, feeding the same underlying asset registry shared with HRM (staff-assigned assets) and Procurement (organizational inventory) — one asset record, viewed from whichever module needs it

### G. Procure-to-Pay Integration

- Direct workflow link with Procurement: Purchase Order → Goods Receipt Note → Invoice → Payment, each stage visible from both modules rather than re-entered

### H. Donor Compliance Reporting

- Donor-specific financial report templates — configurable export formats matching common donor requirements (e.g. USAID SF-425-style, UN agency, EU formats) so a finance officer doesn't hand-rebuild a report for every donor every quarter
- Full transaction-level audit trail behind every reported figure, ready for external audit

### I. Financial Reports

- Trial balance
- Income & expenditure statement
- Balance sheet
- Project financial report (budget vs. actual by project, including the payroll cost allocated to it from HRM)
- Donor financial statement / grant utilization report

## Key integration points

| From | Into Finance as |
|---|---|
| HRM payroll run | Salary cost posted to the general ledger, split by the cost-allocation percentages set on each employee's timesheet/assignment |
| Procurement PO → GRN → invoice | Committed and actual expenditure against the relevant budget line, checked against available budget before approval |
| Programs project budget | The budget envelope Finance tracks actuals against; a project cannot exist in Finance without a corresponding Programs project record |
| Fixed asset depreciation | Periodic depreciation journal entries, and disposal gain/loss |

## Data entities specific to Finance

| Entity | Key attributes |
|---|---|
| Chart of Accounts / Account | code, name, type, tenant-configurable |
| Cost Center / Budget Line | project/donor link, period, allocated amount |
| Journal Entry / Ledger Posting | date, account, debit/credit, source module & reference |
| Vendor | shared with Procurement — see [`08-data-model.md`](08-data-model.md) |
| Invoice / Payment Voucher | vendor, PO reference, amount, tax, approval status |
| Bank Account / Bank Reconciliation | tenant, currency, statement matching |
| Fixed Asset | shared registry — acquisition value, depreciation method, book value |
| Donor Report Template | donor, format definition, mapped fields |

---
**Prev:** [`04-module-hrm.md`](04-module-hrm.md) · **Next:** [`06-module-procurement-logistics.md`](06-module-procurement-logistics.md)
