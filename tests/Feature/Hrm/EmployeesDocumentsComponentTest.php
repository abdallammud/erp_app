<?php

use App\Livewire\Employees\Documents;
use App\Models\Document;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Supersedes the old tests/Feature/Documents/MyDocumentsComponentTest.php
 * — Step 1.2 moved the "Documents" tile from attaching to User to
 * attaching to Employee (see docs/build/DECISIONS.md D-038: not every
 * employee has a login, so User can't be the universal attachment point).
 */
beforeEach(function () {
    Storage::fake('local');
});

test('uploading through the real component creates a document attached to the signed-in user\'s employee record', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Documents::class)
        ->set('category', 'ID')
        ->set('expiryDate', '2028-06-01')
        ->set('file', UploadedFile::fake()->create('national-id.pdf', 100, 'application/pdf'))
        ->call('upload')
        ->assertHasNoErrors();

    $document = Document::sole();

    expect($document->documentable_type)->toBe(Employee::class)
        ->and($document->documentable_id)->toBe($employee->id)
        ->and($document->uploaded_by_id)->toBe($user->id)
        ->and($document->category)->toBe('ID');
});

test('the documents list shows what the employee actually uploaded, and only their own', function () {
    $user = User::factory()->create();
    Employee::factory()->create(['user_id' => $user->id]);

    $otherUser = User::factory()->create();
    Employee::factory()->create(['user_id' => $otherUser->id]);

    Livewire::actingAs($otherUser)
        ->test(Documents::class)
        ->set('category', 'Contract')
        ->set('file', UploadedFile::fake()->create('other-persons-contract.pdf'))
        ->call('upload');

    Livewire::actingAs($user)
        ->test(Documents::class)
        ->assertDontSee('other-persons-contract.pdf')
        ->set('category', 'Certificate')
        ->set('file', UploadedFile::fake()->create('my-certificate.pdf'))
        ->call('upload')
        ->assertSee('my-certificate.pdf')
        ->assertDontSee('other-persons-contract.pdf');
});

test('uploading requires a category and a file', function () {
    $user = User::factory()->create();
    Employee::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Documents::class)
        ->call('upload')
        ->assertHasErrors(['category', 'file']);
});

test('a user with no linked employee record sees a friendly message, not an error', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Documents::class)
        ->assertSee('No employee record is linked to your account yet')
        ->call('upload')
        ->assertHasNoErrors();

    expect(Document::count())->toBe(0);
});
