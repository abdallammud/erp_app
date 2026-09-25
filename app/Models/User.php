<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * A login. Belongs to exactly one tenant (tenant_id), EXCEPT Super Admin
 * accounts, which have a null tenant_id and sit outside all tenants by
 * design — see docs/02-architecture.md and docs/03-roles-and-permissions.md.
 *
 * Auth lookups (session reload by id on every request, credential lookup
 * on login) bypass BelongsToTenant's fail-closed scope by design — see
 * the custom 'tenant-aware-eloquent' provider registered in
 * AppServiceProvider::boot() and docs/build/DECISIONS.md D-015. `email`
 * is globally unique (not per-tenant) specifically so that lookup is
 * unambiguous.
 *
 * Roles/permissions (Spatie) are NOT tenant-scoped separately — the
 * "teams" feature is deliberately off (docs/build/DECISIONS.md D-019).
 * A role assignment is already unambiguous because the User it's
 * assigned to is tenant-scoped; see docs/03-roles-and-permissions.md for
 * the role catalog these map to.
 *
 * Implements MustVerifyEmail: Breeze's scaffolding (verify-email routes,
 * VerifyEmailController, the `verified` middleware on /dashboard)
 * assumes it — Laravel's default stub ships it commented out, which
 * would leave that scaffolding half-wired. Doesn't require it be used
 * for every account (e.g. Super Admin/HR-Admin-created accounts could
 * be pre-verified), just makes the feature coherent if/when it's used.
 */
#[Fillable(['tenant_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
