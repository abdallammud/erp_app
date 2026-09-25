<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A login. Belongs to exactly one tenant (tenant_id), EXCEPT Super Admin
 * accounts, which have a null tenant_id and sit outside all tenants by
 * design — see docs/02-architecture.md and docs/03-roles-and-permissions.md.
 *
 * NOTE for Step 0.4 (Auth & RBAC): because BelongsToTenant's global scope
 * fails closed with no tenant context (see App\Models\Scopes\TenantScope),
 * the auth guard's credential-lookup query — which runs BEFORE we know
 * which tenant a login belongs to — must explicitly bypass it, e.g.
 * `User::withoutGlobalScope(TenantScope::class)->where('email', $email)->first()`.
 * `email` is globally unique (not per-tenant) specifically so this lookup
 * is unambiguous. See docs/build/DECISIONS.md D-015.
 */
#[Fillable(['tenant_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, Notifiable;

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
