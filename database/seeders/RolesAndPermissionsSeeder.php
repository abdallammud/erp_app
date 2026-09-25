<?php

namespace Database\Seeders;

use App\Support\Authorization\Permission as PermissionEnum;
use App\Support\Authorization\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the platform-wide role and permission catalog — see
 * docs/03-roles-and-permissions.md for the role → module access matrix
 * this mirrors, and App\Support\Authorization\{Role,Permission} for the
 * canonical name lists.
 *
 * Global, not per-tenant (docs/build/DECISIONS.md D-019) — run once, not
 * once per tenant. Idempotent: safe to run against a database that
 * already has some of these rows (firstOrCreate throughout).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionEnum::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'web']);
        }

        foreach (RoleEnum::all() as $role) {
            $roleModel = Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
            $roleModel->syncPermissions($this->permissionsFor($role));
        }
    }

    /**
     * @return list<string>
     */
    protected function permissionsFor(RoleEnum $role): array
    {
        $p = PermissionEnum::class;

        return match ($role) {
            RoleEnum::Employee => [
                $p::HrmOwnView->value, $p::HrmOwnEdit->value,
                $p::PayrollViewOwn->value,
                $p::ProgramsViewOwn->value,
                $p::SafeguardingSubmit->value,
            ],

            RoleEnum::Supervisor => [
                $p::HrmOwnView->value, $p::HrmOwnEdit->value,
                $p::HrmTeamView->value, $p::HrmTeamEdit->value, $p::HrmTeamApprove->value,
                $p::PayrollView->value,
                $p::ProcurementView->value,
                $p::ProgramsView->value,
                $p::SafeguardingSubmit->value,
            ],

            RoleEnum::HrAdmin => [
                $p::HrmOwnView->value, $p::HrmOwnEdit->value,
                $p::HrmOrgView->value, $p::HrmOrgEdit->value, $p::HrmOrgApprove->value,
                $p::PayrollView->value,
                $p::FinanceView->value,
                $p::SafeguardingViewScoped->value, $p::SafeguardingEditScoped->value,
            ],

            RoleEnum::PayrollFinanceOfficer => [
                $p::HrmOrgView->value,
                $p::PayrollView->value, $p::PayrollEdit->value, $p::PayrollApprove->value,
                $p::FinanceView->value, $p::FinanceEdit->value, $p::FinanceApprove->value,
                $p::ProcurementView->value,
                $p::ProgramsView->value,
            ],

            RoleEnum::ProcurementOfficer => [
                $p::FinanceView->value,
                $p::ProcurementView->value, $p::ProcurementEdit->value, $p::ProcurementApprove->value,
                $p::ProgramsView->value,
            ],

            RoleEnum::ProgramOfficer => [
                $p::HrmTeamView->value,
                $p::FinanceView->value,
                $p::ProcurementView->value,
                $p::ProgramsView->value, $p::ProgramsEdit->value, $p::ProgramsApprove->value,
            ],

            RoleEnum::CountryDirector => [
                $p::HrmOwnView->value, $p::HrmOrgView->value,
                $p::PayrollView->value,
                $p::FinanceView->value,
                $p::ProcurementView->value,
                $p::ProgramsView->value,
                $p::SafeguardingViewSummary->value,
            ],

            RoleEnum::SafeguardingFocalPoint => [
                $p::SafeguardingManage->value,
            ],

            RoleEnum::AuditorDonor => [
                $p::PayrollView->value,
                $p::FinanceView->value,
                $p::ProcurementView->value,
                $p::ProgramsView->value,
            ],

            // No default tenant-business permissions — see
            // docs/03-roles-and-permissions.md ("Super Admin deliberately
            // has no default access to any tenant's business data").
            RoleEnum::SuperAdmin => [],
        };
    }
}
