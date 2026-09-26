<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;

// No public marketing page — this is an internal, multi-tenant business
// app with no self-serve signup (see docs/01-vision-and-scope.md and
// docs/build/DECISIONS.md D-018). `auth` middleware on /dashboard sends
// guests straight to /login.
Route::redirect('/', '/dashboard');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('my-profile', 'my-profile')
    ->middleware(['auth'])
    ->name('my-profile');

Route::view('organization', 'organization')
    ->middleware(['auth', 'can:'.Permission::HrmOrgView->value])
    ->name('organization');

// A real, working proof of the approval engine (docs/build/00-build-plan.md
// Step 0.6) — open to any authenticated user, since anyone can submit a
// test request; per-instance approval eligibility is enforced by
// App\Support\Approvals\ApprovalWorkflow itself, not route middleware.
Route::view('approvals-demo', 'approvals-demo')
    ->middleware(['auth'])
    ->name('approvals-demo');

// Employee Portal nav items from the Nova Humanitarian HRM UI reference
// (see docs/build/DECISIONS.md D-024) that don't have a real screen yet —
// each gets its own route and an honest "coming in Phase 1" placeholder
// (docs/04-module-hrm.md has the real spec) rather than a raw 404 or,
// worse, being hidden from the sidebar. Real screens replace these one
// at a time as Phase 1 modules are built.
foreach ([
    'dependents' => ['Dependents', 'users'],
    'projects' => ['Projects', 'briefcase'],
    'leave' => ['Leave', 'calendar'],
    'performance' => ['Performance', 'chart-bar'],
    'self-assessment' => ['Self-Assessment', 'clipboard-check'],
    'payroll' => ['Payroll', 'currency-dollar'],
    'timesheet' => ['Timesheet', 'clock'],
    'training' => ['Training', 'book-open'],
    'assets' => ['Assets', 'cube'],
    'asset-verification' => ['Asset Verification', 'clipboard-check'],
    'safeguarding' => ['Safeguarding', 'shield-check'],
    'calendar' => ['Calendar', 'calendar'],
    'history' => ['History', 'history'],
    'notifications' => ['Notifications', 'bell'],
] as $slug => [$label, $icon]) {
    Route::view($slug, 'coming-soon', ['feature' => $label, 'icon' => $icon])
        ->middleware(['auth'])
        ->name($slug);
}

require __DIR__.'/auth.php';
