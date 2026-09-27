<?php

use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Build Plan Step 0.9's Definition of Done covers upload/download/
 * cross-tenant; this file also verifies the specific claim in
 * docs/build/DECISIONS.md D-030 — files are encrypted at rest, not just
 * access-controlled — by reading the RAW bytes directly off the fake
 * disk and asserting they do NOT match the plaintext.
 */
beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($this->tenant);
    $this->store = app(DocumentStore::class);
});

test('storing a file encrypts it at rest — the raw bytes on disk are not the plaintext', function () {
    $user = User::factory()->create();
    $content = "This is a real personnel document.\n";
    $file = UploadedFile::fake()->createWithContent('id-card.txt', $content);

    $document = $this->store->store($file, $user, $user, 'ID');

    $rawBytes = Storage::disk('local')->get($document->path);

    expect($rawBytes)->not->toBe($content)
        ->and($rawBytes)->not->toContain('real personnel document');
});

test('contents() correctly decrypts what was stored', function () {
    $user = User::factory()->create();
    $content = "Round-trip check.\n";
    $file = UploadedFile::fake()->createWithContent('note.txt', $content);

    $document = $this->store->store($file, $user, $user, 'ID');

    expect($this->store->contents($document))->toBe($content);
});

test('store() records the original filename, mime type, plaintext size, and category', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('contract.pdf', 42, 'application/pdf');

    $document = $this->store->store($file, $user, $user, 'Contract', '2027-01-01');

    expect($document->original_filename)->toBe('contract.pdf')
        ->and($document->mime_type)->toBe('application/pdf')
        ->and($document->size)->toBe(42 * 1024)
        ->and($document->category)->toBe('Contract')
        ->and($document->expiry_date->toDateString())->toBe('2027-01-01')
        ->and($document->is_verified)->toBeFalse()
        ->and($document->uploaded_by_id)->toBe($user->id);
});

test('a user with HrmOrgView can verify a document; one without cannot', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);
    $file = UploadedFile::fake()->create('id.pdf');
    $document = $this->store->store($file, $employee, $employee, 'ID');

    $anotherEmployee = User::factory()->create();
    $anotherEmployee->assignRole(Role::Employee->value);

    expect(fn () => $this->store->verify($document, $anotherEmployee))->toThrow(RuntimeException::class);

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $verified = $this->store->verify($document, $hrAdmin);

    expect($verified->is_verified)->toBeTrue()
        ->and($verified->verified_by_id)->toBe($hrAdmin->id)
        ->and($verified->verified_at)->not->toBeNull();
});

test('a document from one tenant is invisible from another tenant\'s context', function () {
    $userA = User::factory()->create();
    $file = UploadedFile::fake()->create('a.pdf');
    $documentA = $this->store->store($file, $userA, $userA, 'ID');

    $tenantB = Tenant::factory()->create();
    app(TenantContext::class)->set($tenantB);

    expect(Document::find($documentA->id))->toBeNull();

    app(TenantContext::class)->set($this->tenant);
    expect(Document::find($documentA->id))->not->toBeNull();
});
