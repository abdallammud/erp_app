<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply this trait to any Eloquent model that belongs to exactly one tenant.
 *
 * What it does:
 *  - Every query is automatically scoped to the current tenant (fail-closed
 *    if no tenant is known — see TenantScope).
 *  - `tenant_id` is auto-filled from the current tenant on create, if the
 *    model didn't already set it explicitly (e.g. Super Admin tooling
 *    creating a record for a specific tenant it isn't "in").
 *
 * Using models must have a `tenant_id` column.
 *
 * @property int|null $tenant_id
 *
 * See docs/02-architecture.md for the design and docs/build/00-build-plan.md
 * Step 0.3 for how this was introduced.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        // `self` (not `Model`) so Larastan resolves `$model->tenant_id`
        // against the concrete using-class, which the @property above
        // documents as having that column.
        static::creating(function (self $model): void {
            if (empty($model->tenant_id)) {
                $tenantId = app(TenantContext::class)->id();

                if ($tenantId !== null) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
