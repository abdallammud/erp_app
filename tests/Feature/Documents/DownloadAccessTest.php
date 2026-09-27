<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Build Plan Step 0.9's Definition of Done, exercised over real HTTP
 * requests against the actual download route+controller+policy — not
 * just the service layer (see the DemoComponentTest / D-026 lesson:
 * a correct service doesn't prove the thing users actually hit is wired
 * right).
 */
beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($this->tenant);

    $this->owner = User::factory()->create();
    $this->owner->assignRole(Role::Employee->value);

    $this->document = app(DocumentStore::class)->store(
        UploadedFile::fake()->createWithContent('id-card.txt', "secret contents\n"),
        $this->owner,
        $this->owner,
        'ID',
    );
});

test('the document owner can download their own document', function () {
    $response = $this->actingAs($this->owner)->get(route('documents.download', $this->document));

    $response->assertOk()
        ->assertDownload('id-card.txt');

    expect($response->streamedContent())->toBe("secret contents\n");
});

test('another employee cannot download someone else\'s document', function () {
    $otherEmployee = User::factory()->create();
    $otherEmployee->assignRole(Role::Employee->value);

    $this->actingAs($otherEmployee)
        ->get(route('documents.download', $this->document))
        ->assertForbidden();
});

test('an HR Admin can download any employee\'s document', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdmin)
        ->get(route('documents.download', $this->document))
        ->assertOk();
});

test('a document is inaccessible cross-tenant, even to an HR Admin of a different tenant', function () {
    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($otherTenant);

    $hrAdminElsewhere = User::factory()->create();
    $hrAdminElsewhere->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdminElsewhere)
        ->get(route('documents.download', $this->document))
        ->assertNotFound();
});
