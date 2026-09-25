<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

/**
 * The "current tenant" for this request or queue job.
 *
 * Bound as a singleton (see AppServiceProvider) so it's the same instance
 * throughout a request/job lifecycle. Every tenant-scoped model consults
 * this — see App\Models\Scopes\TenantScope and App\Models\Concerns\BelongsToTenant.
 *
 * Resolution happens in App\Http\Middleware\IdentifyTenant for web requests
 * (from the authenticated user's tenant_id) and in
 * App\Support\Tenancy\SetsTenantContext for queued jobs. See
 * docs/02-architecture.md for the design reasoning.
 */
class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
