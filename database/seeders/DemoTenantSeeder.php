<?php

namespace Database\Seeders;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role as RoleEnum;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * One demo tenant with one user per role — for local exploration and as
 * the concrete artifact behind Build Plan Step 0.4's Definition of Done
 * ("a seeded test tenant has one user per role"). Not for production —
 * every account uses the same known password.
 *
 * Requires RolesAndPermissionsSeeder to have run first (roles must
 * exist to assign).
 */
class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-ngo'],
            [
                'name' => 'Demo NGO',
                'countries' => ['SO'],
                'default_currency' => 'USD',
                'timezone' => 'Africa/Mogadishu',
                'fiscal_year_start_month' => 1,
                'is_active' => true,
            ]
        );

        app(TenantContext::class)->set($tenant);

        foreach (RoleEnum::all() as $role) {
            // Super Admin sits outside every tenant (null tenant_id) —
            // see docs/02-architecture.md — so it isn't one of this
            // tenant's users, even though the role itself always exists
            // in the global catalog.
            if ($role === RoleEnum::SuperAdmin) {
                continue;
            }

            $email = Str::slug($role->value).'@demo.test';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $role->value.' (Demo)',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role->value]);
        }

        // One Super Admin account, outside all tenants. Explicitly bypasses
        // TenantScope — Super Admin's existence check must not be scoped to
        // the demo tenant still active in context above, or fail closed
        // with none (see docs/build/DECISIONS.md D-014); either would make
        // this non-idempotent (a duplicate/failed insert on the next run).
        $superAdmin = User::withoutGlobalScope(TenantScope::class)->firstOrCreate(
            ['email' => 'super-admin@demo.test'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin (Demo)',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles([RoleEnum::SuperAdmin->value]);
    }
}
