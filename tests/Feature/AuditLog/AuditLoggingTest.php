<?php

use App\Models\AuditLogEntry;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * Build Plan Step 0.8's Definition of Done: editing any seeded record
 * produces a visible, correct audit log entry. Uses Department (a real,
 * permanent model) rather than a throwaway entity, and — per the
 * standing lesson from D-014/D-025/D-029 — asserts the audit trail is
 * itself tenant-isolated, not just that a row gets written somewhere.
 */
beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($this->tenant);
    $this->actor = User::factory()->create();
    $this->actingAs($this->actor);
});

test('creating a tenant-scoped model writes an audit log entry with the correct tenant, causer, and attributes', function () {
    $department = Department::create(['name' => 'Health & Nutrition', 'code' => 'HN']);

    // Scoped to Department specifically: $this->actor's own creation in
    // beforeEach is itself an audited event now that User uses
    // Auditable too — sole() across all entries would see both.
    $entry = AuditLogEntry::where('subject_type', Department::class)->sole();

    expect($entry->tenant_id)->toBe($this->tenant->id)
        ->and($entry->event)->toBe('created')
        ->and($entry->causer_id)->toBe($this->actor->id)
        ->and($entry->subject_id)->toBe($department->id)
        ->and($entry->subject_type)->toBe(Department::class)
        ->and($entry->attribute_changes->get('attributes'))->toMatchArray(['name' => 'Health & Nutrition', 'code' => 'HN']);
});

test('updating a tenant-scoped model logs only what actually changed, old and new', function () {
    $department = Department::create(['name' => 'Original Name', 'code' => 'OG']);

    $department->update(['name' => 'Renamed']);

    $entry = AuditLogEntry::where('event', 'updated')->sole();

    expect($entry->attribute_changes->get('attributes'))->toMatchArray(['name' => 'Renamed'])
        ->and($entry->attribute_changes->get('old'))->toMatchArray(['name' => 'Original Name'])
        ->and($entry->attribute_changes->get('attributes'))->not->toHaveKey('code');
});

test('deleting a tenant-scoped model logs the deleted state', function () {
    $department = Department::create(['name' => 'To Be Deleted']);

    $department->delete();

    $entry = AuditLogEntry::where('event', 'deleted')->sole();

    expect($entry->attribute_changes->get('old'))->toMatchArray(['name' => 'To Be Deleted']);
});

test('tenant_id is never duplicated inside the logged attribute diff', function () {
    Department::create(['name' => 'Whatever']);

    $entry = AuditLogEntry::where('subject_type', Department::class)->sole();

    expect($entry->attribute_changes->get('attributes'))->not->toHaveKey('tenant_id');
});

test('a user\'s password is never logged, even hashed', function () {
    $this->actor->update(['name' => 'Renamed Actor', 'password' => 'a-brand-new-password']);

    $entry = AuditLogEntry::where('subject_type', User::class)->where('event', 'updated')->sole();

    expect($entry->attribute_changes->get('attributes'))->not->toHaveKey('password')
        ->and($entry->attribute_changes->get('old') ?? [])->not->toHaveKey('password');
});

test('an audit log entry from one tenant is invisible from another tenant\'s context', function () {
    $context = app(TenantContext::class);
    $departmentEntries = fn () => AuditLogEntry::where('subject_type', Department::class);

    $tenantA = $this->tenant;
    Department::create(['name' => 'Tenant A Department']);
    $entryA = $departmentEntries()->sole();

    $tenantB = Tenant::factory()->create();
    $context->set($tenantB);
    $this->actingAs(User::factory()->create());
    Department::create(['name' => 'Tenant B Department']);

    expect($departmentEntries()->pluck('id'))->not->toContain($entryA->id)
        ->and($departmentEntries()->count())->toBe(1);

    $context->set($tenantA);
    expect($departmentEntries()->count())->toBe(1)
        ->and($departmentEntries()->sole()->id)->toBe($entryA->id);
});
