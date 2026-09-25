<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant for a web request from the authenticated
 * user's tenant_id and sets it on TenantContext for the rest of the
 * request lifecycle.
 *
 * Deliberately session/auth-based, not subdomain-based — see
 * docs/02-architecture.md ("not subdomain alone, to keep local/offline
 * field use simple"). Runs after auth middleware in the pipeline, so
 * $request->user() is already resolved when this executes.
 *
 * A user with no tenant_id (Super Admin — see docs/03-roles-and-permissions.md)
 * leaves no tenant set, which means tenant-scoped models fail closed (see
 * App\Models\Scopes\TenantScope) unless Super Admin tooling explicitly
 * opts out of the scope.
 */
class IdentifyTenant
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $this->tenantContext->set($user->tenant);
        }

        return $next($request);
    }
}
