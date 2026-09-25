<?php

namespace App\Livewire;

use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Phase 0 smoke-test component: proves Livewire + Tailwind render correctly
 * inside the shared portal shell, and that a component re-renders on a
 * user-triggered action (not just on page load). Superseded once real
 * portal dashboards exist (see docs/build/00-build-plan.md, Phase 1+).
 */
class SystemStatus extends Component
{
    public int $refreshes = 0;

    public function refresh(): void
    {
        $this->refreshes++;
    }

    public function render()
    {
        return view('livewire.system-status', [
            'now' => Carbon::now(),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'environment' => app()->environment(),
        ]);
    }
}
