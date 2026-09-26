<?php

use App\Livewire\Notifications\Inbox;
use App\Models\ApprovalChain;
use App\Models\TestRequest;
use App\Models\User;
use App\Support\Approvals\ApprovalWorkflow;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $chain = ApprovalChain::factory()->create(['action_type' => 'test_request']);
    $chain->steps()->create(['sequence' => 1, 'approver_role' => Role::Supervisor->value, 'label' => 'Supervisor review']);

    $this->supervisor = User::factory()->create();
    $this->supervisor->assignRole(Role::Supervisor->value);

    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);

    app(ApprovalWorkflow::class)->submit($chain, $request, $requester);
});

test('a user sees their own notifications and can mark one as read', function () {
    $notification = $this->supervisor->notifications()->sole();

    $component = Livewire::actingAs($this->supervisor)
        ->test(Inbox::class)
        ->assertSee('Supervisor review');

    expect($component->instance()->unreadCount)->toBe(1);

    $component->call('markAsRead', $notification->id);

    expect($this->supervisor->notifications()->sole()->read_at)->not->toBeNull();
});

test('mark all as read clears the unread count', function () {
    $component = Livewire::actingAs($this->supervisor)
        ->test(Inbox::class)
        ->call('markAllAsRead');

    expect($component->instance()->unreadCount)->toBe(0);
});

test('a user cannot see another user\'s notifications', function () {
    $stranger = User::factory()->create();

    $component = Livewire::actingAs($stranger)
        ->test(Inbox::class)
        ->assertDontSee('Supervisor review');

    expect($component->instance()->unreadCount)->toBe(0);
});
