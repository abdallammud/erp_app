<?php

namespace App\Support\Authorization;

/**
 * The platform-wide permission catalog, mirroring the role → module
 * access matrix in docs/03-roles-and-permissions.md.
 *
 * Naming: `{module}.{scope-or-action}`. `own` = the user's own record
 * only; `team` = a supervisor's direct reports; `org` = tenant-wide.
 * These are coarse, module-level permissions for Phase 0 — individual
 * features in later phases may need finer-grained permissions, added
 * alongside these rather than replacing them.
 *
 * Fine-grained data scoping (e.g. "this employee's own leave requests
 * only") is enforced by query scoping / Policies in the modules that
 * own that data (Phase 1+), not by permission names alone — a
 * permission says a role CAN see a category of thing; a Policy decides
 * WHICH records within that category.
 */
enum Permission: string
{
    // HRM
    case HrmOwnView = 'hrm.own.view';
    case HrmOwnEdit = 'hrm.own.edit';
    case HrmTeamView = 'hrm.team.view';
    case HrmTeamEdit = 'hrm.team.edit';
    case HrmTeamApprove = 'hrm.team.approve';
    case HrmOrgView = 'hrm.org.view';
    case HrmOrgEdit = 'hrm.org.edit';
    case HrmOrgApprove = 'hrm.org.approve';

    // Payroll
    case PayrollViewOwn = 'payroll.view.own';
    case PayrollView = 'payroll.view';
    case PayrollEdit = 'payroll.edit';
    case PayrollApprove = 'payroll.approve';

    // Finance
    case FinanceView = 'finance.view';
    case FinanceEdit = 'finance.edit';
    case FinanceApprove = 'finance.approve';

    // Procurement & Logistics
    case ProcurementView = 'procurement.view';
    case ProcurementEdit = 'procurement.edit';
    case ProcurementApprove = 'procurement.approve';

    // Programs, Grants & M&E
    case ProgramsViewOwn = 'programs.view.own';
    case ProgramsView = 'programs.view';
    case ProgramsEdit = 'programs.edit';
    case ProgramsApprove = 'programs.approve';

    // Safeguarding — see the confidentiality tiers in
    // docs/03-roles-and-permissions.md. `Manage` is Highly Restricted
    // (full case detail); `ViewScoped`/`EditScoped` is Restricted (HR
    // Admin's case-management view, not full detail); `ViewSummary` is
    // the Country Director's counts-only visibility.
    case SafeguardingSubmit = 'safeguarding.submit';
    case SafeguardingViewScoped = 'safeguarding.view.scoped';
    case SafeguardingEditScoped = 'safeguarding.edit.scoped';
    case SafeguardingManage = 'safeguarding.manage';
    case SafeguardingViewSummary = 'safeguarding.view.summary';

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
