<?php

namespace App\Livewire\Impersonation;

use App\Support\Impersonation\ImpersonationManager;
use Livewire\Component;

/**
 * Persistent "you're impersonating X" banner — lives in the shared
 * layout (resources/views/components/layouts/app.blade.php), not just
 * the Super Admin portal, since impersonation means being logged in AS
 * a real tenant user across every ordinary page. See
 * docs/build/00-build-plan.md Step 0.11.
 */
class Banner extends Component
{
    public function stop(): void
    {
        app(ImpersonationManager::class)->stop();

        $this->redirect(route('super-admin'), navigate: false);
    }

    public function render()
    {
        return view('livewire.impersonation.banner', [
            'isImpersonating' => app(ImpersonationManager::class)->isImpersonating(),
        ]);
    }
}
