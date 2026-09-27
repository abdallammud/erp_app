<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Impersonation\ImpersonationManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The Super Admin portal — see docs/build/00-build-plan.md Step 0.11
 * and docs/02-architecture.md's Platform/Super Admin role. Outside all
 * tenants by design: every query here deliberately bypasses
 * TenantScope (Tenant itself isn't scoped at all; User/Document queries
 * explicitly opt out) because a Super Admin viewing this screen has no
 * ambient tenant context to begin with (docs/03-roles-and-permissions.md
 * — "no default access to any tenant's business data" means there's
 * nothing for IdentifyTenant to set).
 *
 * Gated by Permission::PlatformAdmin.
 */
class Dashboard extends Component
{
    public string $name = '';

    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'adminName' => 'required|string|max:255',
            'adminEmail' => 'required|email|max:255|unique:users,email',
            'adminPassword' => 'required|string|min:8',
        ];
    }

    #[Computed]
    public function tenants(): Collection
    {
        // withCount('users') builds a real query against User, which
        // would otherwise inherit TenantScope and silently count zero
        // for every tenant — there's no ambient TenantContext on this
        // screen at all (Super Admin isn't "in" any tenant) for the
        // scope to pass.
        return Tenant::withCount(['users' => fn ($query) => $query->withoutGlobalScopes()])
            ->latest()
            ->get();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function systemHealth(): array
    {
        return [
            'tenants_active' => Tenant::where('is_active', true)->count(),
            'tenants_suspended' => Tenant::where('is_active', false)->count(),
            'total_users' => User::withoutGlobalScopes()->count(),
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs_24h' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count(),
            'total_storage_bytes' => (int) Document::withoutGlobalScopes()->sum('size'),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function usersFor(int $tenantId): Collection
    {
        return User::withoutGlobalScopes()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Creates a tenant AND its first user in one step — a tenant with
     * zero users would satisfy "created" but not this step's actual DoD
     * ("immediately usable"): nobody could ever log into it. The new
     * admin gets HR Admin, a sensible default first role for a fresh
     * org to start configuring itself from (docs/03-roles-and-
     * permissions.md).
     */
    public function createTenant(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated): void {
            $tenant = Tenant::create(['name' => $validated['name']]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['adminName'],
                'email' => $validated['adminEmail'],
                'password' => $validated['adminPassword'],
                'email_verified_at' => now(),
            ]);

            $admin->assignRole(Role::HrAdmin->value);
        });

        unset($this->tenants);
        $this->reset(['name', 'adminName', 'adminEmail', 'adminPassword']);
        session()->flash('status', 'Tenant created — its admin can log in immediately.');
    }

    public function toggleSuspend(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['is_active' => ! $tenant->is_active]);

        unset($this->tenants);
    }

    public function impersonate(int $userId): void
    {
        $target = User::withoutGlobalScopes()->findOrFail($userId);

        app(ImpersonationManager::class)->start(Auth::user(), $target);

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard');
    }
}
