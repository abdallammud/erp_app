# Module 3 of 4 · Procurement & Logistics

From "we need this" to "it's in the warehouse, tagged, and someone's accountable for it" — procurement planning, sourcing, inventory, organizational assets, and the vehicle fleet.

## Sub-modules

### A. Procurement Planning

- Annual/period procurement plan, ideally built from project budgets (Programs/Finance) so purchasing is anticipated, not purely reactive

### B. Purchase Requisition & Sourcing

- Purchase requisition workflow with multi-level approval, configurable per tenant and per spend threshold
- Request for Quotation (RFQ) and tender management for larger purchases
- Bid comparison / analysis tools — side-by-side supplier quotes against defined criteria
- Purchase Order generation from an approved requisition/winning bid

### C. Supplier / Vendor Management

- Supplier database — shared with Finance's Accounts Payable, not duplicated
- Supplier performance rating, tracked over time from delivery/quality history
- Compliance document tracking per supplier (registration, tax certificate, due-diligence documents)

### D. Goods Receipt & Inventory / Warehouse

- Goods Receipt Note on delivery, matched against the PO (feeds the three-way match in Finance)
- Multi-warehouse support across duty stations
- Batch and expiry tracking for perishable/medical stock
- Stock in/out management with minimum-stock alerts
- Damaged/expired stock tracking and write-off workflow
- Inventory valuation reporting

### E. Asset & Equipment Tracking

- Organization-wide asset registry — barcode/serial tagging, category, value, condition, location
- Field asset allocation (assigning an asset to a project, a location, or an employee — the same registry HRM's staff-linked asset assignment draws from, see [HRM → Asset Management](04-module-hrm.md#g-asset-management-staff-linked))
- Maintenance scheduling and history per asset
- Feeds Finance's fixed-asset depreciation records

### F. Fleet Management

- Vehicle registry (make/model, registration, assigned duty station)
- Fuel tracking and consumption reporting
- Maintenance logs and scheduled service alerts
- Driver assignment and trip logging

### G. Logistics Reports

- Stock status and valuation reports
- Procurement status (open requisitions, POs in progress, delivery pending)
- Supplier performance summary
- Asset register export (by category, location, condition, age)

## Key integration points

| With | How |
|---|---|
| Finance | Procure-to-pay chain (PO → GRN → Invoice → Payment); every requisition checks available budget on the linked cost center before it can be approved. |
| HRM | Shared asset registry — an asset issued to an employee is the same record Procurement tracks as organizational inventory; on employee exit, HRM's exit checklist blocks clearance until the asset shows returned here. |
| Programs | Distribution items (food, NFI, cash-adjacent goods) drawn from warehouse stock for a specific project/activity, so distribution reporting and inventory depletion stay consistent. |

## Data entities specific to Procurement & Logistics

| Entity | Key attributes |
|---|---|
| Procurement Plan | tenant, period, planned items linked to project budgets |
| Purchase Requisition | requester, items, justification, approval status |
| RFQ / Bid | requisition link, invited suppliers, quotes, comparison scoring |
| Purchase Order | supplier, items, amount, budget-line link, status |
| Goods Receipt Note | PO link, items received, condition, discrepancies |
| Vendor | shared with Finance AP — see [`08-data-model.md`](08-data-model.md) |
| Warehouse / Stock Item / Stock Movement | location, batch, expiry, quantity in/out |
| Asset | shared registry — category, value, condition, current holder (employee, project, or warehouse) |
| Vehicle / Trip Log / Fuel Log | registration, duty station, driver, maintenance history |

---
**Prev:** [`05-module-finance.md`](05-module-finance.md) · **Next:** [`07-module-programs.md`](07-module-programs.md)
