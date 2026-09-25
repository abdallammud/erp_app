<?php

namespace App\Jobs\Concerns;

use App\Support\Tenancy\SetsTenantContext;
use App\Support\Tenancy\TenantContext;

/**
 * Add to any queued job that touches tenant-scoped models, so the correct
 * tenant is active when the job actually runs on a worker — not just when
 * it was dispatched. See App\Support\Tenancy\SetsTenantContext and
 * docs/build/00-build-plan.md Step 0.3.
 *
 * Usage in the job class:
 *
 *   use TenantAware;
 *
 *   public function __construct(...)
 *   {
 *       $this->captureCurrentTenant();
 *   }
 *
 *   public function middleware(): array
 *   {
 *       return $this->tenantMiddleware();
 *   }
 */
trait TenantAware
{
    public ?int $tenantId = null;

    /**
     * Call from the job's constructor to record which tenant was active
     * when the job was dispatched.
     */
    protected function captureCurrentTenant(): void
    {
        $this->tenantId = app(TenantContext::class)->id();
    }

    /**
     * Include in the job's middleware() method.
     *
     * @return array<int, SetsTenantContext>
     */
    protected function tenantMiddleware(): array
    {
        return [new SetsTenantContext($this->tenantId)];
    }
}
