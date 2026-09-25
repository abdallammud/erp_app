<?php

namespace App\Support\Authorization;

/**
 * The platform-wide role catalog — see docs/03-roles-and-permissions.md.
 *
 * Roles are global (shared across all tenants), not per-tenant records —
 * see docs/build/DECISIONS.md D-019. A role assignment is already
 * tenant-unambiguous because the User it's assigned to belongs to exactly
 * one tenant. Seeded by database/seeders/RolesAndPermissionsSeeder.
 */
enum Role: string
{
    case Employee = 'Employee';
    case Supervisor = 'Supervisor';
    case HrAdmin = 'HR Admin';
    case PayrollFinanceOfficer = 'Payroll/Finance Officer';
    case ProcurementOfficer = 'Procurement Officer';
    case ProgramOfficer = 'Program/M&E Officer';
    case CountryDirector = 'Country Director';
    case SafeguardingFocalPoint = 'Safeguarding Focal Point';
    case AuditorDonor = 'Auditor/Donor';
    case SuperAdmin = 'Super Admin';

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
