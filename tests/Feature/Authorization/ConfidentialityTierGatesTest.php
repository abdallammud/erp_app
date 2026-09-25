<?php

use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * The confidentiality-tier Gates (App\Providers\AuthorizationServiceProvider)
 * for Safeguarding — see docs/03-roles-and-permissions.md's confidentiality
 * tiers and docs/build/00-build-plan.md Step 0.4.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('only the Safeguarding Focal Point sees full case detail', function () {
    $focalPoint = User::factory()->create();
    $focalPoint->assignRole(Role::SafeguardingFocalPoint->value);

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    expect($focalPoint->can('safeguarding.view-full-case'))->toBeTrue()
        ->and($hrAdmin->can('safeguarding.view-full-case'))->toBeFalse();
});

test('HR Admin sees the scoped case view but not the Country Director-only summary permission path', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $director = User::factory()->create();
    $director->assignRole(Role::CountryDirector->value);

    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    expect($hrAdmin->can('safeguarding.view-scoped-case'))->toBeTrue()
        ->and($director->can('safeguarding.view-scoped-case'))->toBeFalse()
        ->and($director->can('safeguarding.view-summary'))->toBeTrue()
        ->and($employee->can('safeguarding.view-scoped-case'))->toBeFalse()
        ->and($employee->can('safeguarding.view-summary'))->toBeFalse();
});

test('the Safeguarding Focal Point\'s full access implies the lower tiers too', function () {
    $focalPoint = User::factory()->create();
    $focalPoint->assignRole(Role::SafeguardingFocalPoint->value);

    expect($focalPoint->can('safeguarding.view-full-case'))->toBeTrue()
        ->and($focalPoint->can('safeguarding.view-scoped-case'))->toBeTrue()
        ->and($focalPoint->can('safeguarding.view-summary'))->toBeTrue();
});

test('any employee can submit a safeguarding report', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    expect($employee->can('safeguarding.submit-case'))->toBeTrue();
});
