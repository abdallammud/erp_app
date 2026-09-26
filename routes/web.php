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

Route::view('organization', 'organization')
    ->middleware(['auth', 'can:'.Permission::HrmOrgView->value])
    ->name('organization');

require __DIR__.'/auth.php';
