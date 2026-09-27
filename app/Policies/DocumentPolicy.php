<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Support\Authorization\Permission;

/**
 * Who can view/download a Document — see
 * docs/build/00-build-plan.md Step 0.9's DoD ("downloaded only by an
 * authorized role"). The pattern anticipated in
 * App\Providers\AuthorizationServiceProvider's docblock for Restricted-
 * tier data: "is this the record's own owner" OR "does the user hold
 * the relevant org-level permission" — the first real model to use it.
 *
 * Tenant isolation isn't handled here — a cross-tenant Document never
 * even resolves via route-model binding (BelongsToTenant's fail-closed
 * TenantScope), so this policy is never reached for one.
 */
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        if ($user->id === $document->uploaded_by_id) {
            return true;
        }

        if ($document->documentable_type === User::class && (int) $document->documentable_id === $user->id) {
            return true;
        }

        return $user->can(Permission::HrmOrgView->value);
    }
}
