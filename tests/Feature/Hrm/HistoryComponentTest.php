<?php

use App\Livewire\Employees\History;
use App\Models\Employee;
use App\Models\User;
use Livewire\Livewire;

/**
 * Build Plan Step 1.2: "Profile view/edit with change-history logging
 * (old/new value + approver)". The logging itself is already automatic
 * (User and Employee both use Auditable, Step 0.8) — this proves the
 * self-scoped VIEW of it actually shows the right entries with the
 * right old->new values and causer.
 */
test('a profile name change shows up in the user\'s own history with old and new values and who made it', function () {
    $user = User::factory()->create(['name' => 'Original Name']);
    $hrAdmin = User::factory()->create(['name' => 'HR Person']);

    $user->update(['name' => 'Renamed']);

    // Simulate the change being made by someone else (HR), to prove the
    // causer shown is genuinely whoever acted, not always "self".
    auth()->login($hrAdmin);
    $user->update(['name' => 'Renamed By HR']);
    auth()->login($user);

    Livewire::actingAs($user)
        ->test(History::class)
        ->assertSee('Renamed By HR')
        ->assertSee('Original Name')
        ->assertSee('HR Person');
});

test('an employee\'s contact info change shows up in their own history', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id, 'phone' => 'old-number']);

    $employee->update(['phone' => 'new-number']);

    Livewire::actingAs($user)
        ->test(History::class)
        ->assertSee('old-number')
        ->assertSee('new-number');
});

test('a user with no linked employee record still sees their own User history', function () {
    $user = User::factory()->create(['name' => 'Solo User']);
    $user->update(['name' => 'Solo User Renamed']);

    Livewire::actingAs($user)
        ->test(History::class)
        ->assertSee('Solo User Renamed');
});

test('one user cannot see another user\'s history', function () {
    // $user's own account creation is itself a real, expected history
    // entry (User uses Auditable too) — this test isn't about $user
    // having none at all, only that $other's own changes never leak in.
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => 'Someone Else']);
    $other->update(['name' => 'Someone Else Renamed']);

    Livewire::actingAs($user)
        ->test(History::class)
        ->assertDontSee('Someone Else Renamed');
});
