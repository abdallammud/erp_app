<?php

namespace App\Support\Impersonation;

use App\Models\AuditLogEntry;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Logged support impersonation — see docs/build/00-build-plan.md
 * Step 0.11 and docs/02-architecture.md ("can impersonate (with
 * logging) for support — but has no default access to tenant business
 * data").
 *
 * Every start/stop writes an App\Models\AuditLogEntry directly (not via
 * the Auditable trait's automatic model-event hooks — there's no
 * "model" being created/updated here, and more importantly, the acting
 * user during start() is a Super Admin with no ambient TenantContext at
 * all, so the auto-fill tenant_id logic that works for ordinary
 * tenant-scoped models has nothing to fill from). The tenant_id
 * recorded is always the TARGET's tenant — this log entry is about that
 * tenant's data being accessed, not about the Super Admin's (nonexistent)
 * tenant, so it shows up in that tenant's own Audit Log screen too, not
 * just to platform staff.
 */
class ImpersonationManager
{
    private const SESSION_KEY = 'impersonator_id';

    public function start(User $admin, User $target): void
    {
        if (! $admin->hasRole(Role::SuperAdmin->value)) {
            throw new RuntimeException('Only a Super Admin can start an impersonation.');
        }

        if ($target->hasRole(Role::SuperAdmin->value)) {
            throw new RuntimeException('Super Admin accounts cannot be impersonated.');
        }

        $this->log($admin, $target, 'impersonation_started', "{$admin->name} started impersonating {$target->name}");

        Session::put(self::SESSION_KEY, $admin->id);
        Auth::login($target);
    }

    /**
     * Ends the current impersonation and restores the original Super
     * Admin's session. Returns the restored admin, or null if there was
     * nothing to stop (defensively re-validates the stored id really is
     * a Super Admin — see this class's docblock).
     */
    public function stop(): ?User
    {
        $adminId = Session::get(self::SESSION_KEY);

        if (! $adminId) {
            return null;
        }

        $admin = User::withoutGlobalScopes()->find($adminId);
        $target = Auth::user();

        Session::forget(self::SESSION_KEY);

        if (! $admin instanceof User || ! $admin->hasRole(Role::SuperAdmin->value) || ! $target instanceof User) {
            return null;
        }

        $this->log($admin, $target, 'impersonation_ended', "{$admin->name} stopped impersonating {$target->name}");

        Auth::login($admin);

        return $admin;
    }

    public function isImpersonating(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    private function log(User $admin, User $target, string $event, string $description): void
    {
        AuditLogEntry::create([
            'tenant_id' => $target->tenant_id,
            'log_name' => 'impersonation',
            'event' => $event,
            'description' => $description,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
            'subject_type' => User::class,
            'subject_id' => $target->id,
            'properties' => [],
        ]);
    }
}
