<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Closure;

/**
 * Job middleware: restores the tenant that was active when a job was
 * dispatched, for the duration of that job's handle(), then restores
 * whatever was set before (relevant when jobs run other jobs inline).
 *
 * A queue worker is a long-running process shared across every tenant —
 * without this, a job for tenant A could run with tenant B's (or nobody's)
 * TenantContext left over from whatever ran before it on that worker.
 *
 * Don't construct this directly in application code — use the
 * App\Jobs\Concerns\TenantAware trait, which captures the dispatching
 * tenant automatically. See docs/build/00-build-plan.md Step 0.3.
 */
class SetsTenantContext
{
    public function __construct(private readonly ?int $tenantId) {}

    public function handle(mixed $job, Closure $next): mixed
    {
        $context = app(TenantContext::class);
        $previous = $context->get();

        $context->set($this->tenantId !== null ? Tenant::find($this->tenantId) : null);

        try {
            return $next($job);
        } finally {
            $context->set($previous);
        }
    }
}
