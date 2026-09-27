<?php

use App\Livewire\Documents\MyDocuments;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('uploading through the real component creates a document attached to the signed-in user', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyDocuments::class)
        ->set('category', 'ID')
        ->set('expiryDate', '2028-06-01')
        ->set('file', UploadedFile::fake()->create('national-id.pdf', 100, 'application/pdf'))
        ->call('upload')
        ->assertHasNoErrors();

    $document = Document::sole();

    expect($document->documentable_type)->toBe(User::class)
        ->and($document->documentable_id)->toBe($user->id)
        ->and($document->uploaded_by_id)->toBe($user->id)
        ->and($document->category)->toBe('ID');
});

test('the documents list shows what the user actually uploaded, and only their own', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Livewire::actingAs($otherUser)
        ->test(MyDocuments::class)
        ->set('category', 'Contract')
        ->set('file', UploadedFile::fake()->create('other-persons-contract.pdf'))
        ->call('upload');

    Livewire::actingAs($user)
        ->test(MyDocuments::class)
        ->assertDontSee('other-persons-contract.pdf')
        ->set('category', 'Certificate')
        ->set('file', UploadedFile::fake()->create('my-certificate.pdf'))
        ->call('upload')
        ->assertSee('my-certificate.pdf')
        ->assertDontSee('other-persons-contract.pdf');
});

test('uploading requires a category and a file', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(MyDocuments::class)
        ->call('upload')
        ->assertHasErrors(['category', 'file']);
});
