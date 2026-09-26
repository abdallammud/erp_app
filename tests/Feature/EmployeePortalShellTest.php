<?php

use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * The sidebar/nav pass from docs/build/DECISIONS.md D-024 — visual
 * fidelity to the Nova Humanitarian HRM reference. Most nav items are
 * honest placeholders; these tests lock in that every one of them is
 * actually reachable and correctly labeled, not just present in a
 * hard-coded list that could silently drift from the real routes.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('the dashboard sidebar lists every Employee Portal nav item from the reference', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::Employee->value);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();

    foreach ([
        'Home', 'My Profile', 'Dependents', 'Projects', 'Leave', 'Performance',
        'Self-Assessment', 'Payroll', 'Timesheet', 'Training', 'Assets',
        'Asset Verification', 'Safeguarding', 'Calendar', 'History', 'Notifications',
    ] as $label) {
        $response->assertSee($label);
    }
});

test('an Employee does not see the Organization link; an HR Admin does', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    $this->actingAs($employee)->get('/dashboard')->assertDontSee('Organization');

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdmin)->get('/dashboard')->assertSee('Organization');
});

test('my-profile shows the real signed-in user\'s data, not placeholder text', function () {
    $user = User::factory()->create(['name' => 'Amina Yusuf']);
    $user->assignRole(Role::Employee->value);

    $response = $this->actingAs($user)->get('/my-profile');

    $response->assertOk()
        ->assertSee('Amina Yusuf')
        ->assertSee($user->email)
        ->assertSee($user->tenant->name)
        ->assertSee('Employee'); // role badge
});

test('every placeholder nav route is reachable and honestly labeled', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::Employee->value);

    foreach ([
        'dependents' => 'Dependents',
        'projects' => 'Projects',
        'leave' => 'Leave',
        'performance' => 'Performance',
        'self-assessment' => 'Self-Assessment',
        'payroll' => 'Payroll',
        'timesheet' => 'Timesheet',
        'training' => 'Training',
        'assets' => 'Assets',
        'asset-verification' => 'Asset Verification',
        'safeguarding' => 'Safeguarding',
        'calendar' => 'Calendar',
        'history' => 'History',
        'notifications' => 'Notifications',
    ] as $path => $label) {
        $this->actingAs($user)->get("/{$path}")
            ->assertOk()
            ->assertSee($label)
            ->assertSee('Coming in Phase 1');
    }
});
