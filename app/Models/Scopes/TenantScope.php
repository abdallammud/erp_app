<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filters every query on a tenant-scoped model to the current tenant.
 *
 * Fails CLOSED: if no tenant is currently known (see TenantContext), the
 * query returns zero rows rather than every tenant's rows. This is a
 * deliberate choice — see docs/build/DECISIONS.md D-014 — so that a
 * missing/forgotten tenant context is a visibly broken feature (nothing
 * shows up) instead of a silent cross-tenant data leak.
 *
 * A legitimate cross-tenant query (Super Admin tooling) must opt out
 * explicitly: Model::withoutGlobalScope(TenantScope::class)->...
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
