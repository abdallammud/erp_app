<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Gates expressing the three confidentiality tiers from
 * docs/03-roles-and-permissions.md, for the one domain that concretely
 * has them today (Safeguarding — see docs/04-module-hrm.md §H). Phase 1's
 * SafeguardingCase Policy calls these rather than checking raw
 * permissions directly, so the tier logic (who can see how much) lives
 * in one place.
 *
 * Other Restricted-tier data (salary, bank details, performance ratings,
 * beneficiary personal data) gets the same pattern — a Gate/Policy
 * composing "is this the record's own owner" OR "does the user hold the
 * relevant org-level permission" — once those models exist in Phase 1+.
 * Not scaffolded generically here to avoid an abstraction with no real
 * consumer yet; see docs/build/00-build-plan.md Step 0.4.
 */
class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Highly restricted: full case detail. Only the Safeguarding
        // Focal Point (or anyone else explicitly granted `manage`).
        Gate::define('safeguarding.view-full-case', fn (User $user) => $user->can(Permission::SafeguardingManage->value));

        // Restricted: HR Admin's case-management view — sees enough to
        // manage the process, not full detail. Manage implies it too.
        Gate::define('safeguarding.view-scoped-case', fn (User $user) => $user->can(Permission::SafeguardingViewScoped->value)
            || $user->can(Permission::SafeguardingManage->value));

        // Restricted: counts/status only, no case content — the Country
        // Director's visibility.
        Gate::define('safeguarding.view-summary', fn (User $user) => $user->can(Permission::SafeguardingViewSummary->value)
            || $user->can(Permission::SafeguardingViewScoped->value)
            || $user->can(Permission::SafeguardingManage->value));

        // Standard: any employee can submit a report, including
        // anonymously — see docs/04-module-hrm.md §H.
        Gate::define('safeguarding.submit-case', fn (User $user) => $user->can(Permission::SafeguardingSubmit->value));
    }
}
