<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\Documents\DocumentStore;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Regression test for docs/build/DECISIONS.md D-031: a real bug, found
 * only by manually driving the live server, where IdentifyTenant ran
 * AFTER Laravel's SubstituteBindings middleware — so implicit route-
 * model binding on any BelongsToTenant model (e.g. `Document $document`
 * in a route signature) queried with no tenant context yet, and
 * TenantScope's fail-closed behavior turned every such route into a
 * 404, even for the record's rightful owner.
 *
 * Every other Feature test in this suite sets TenantContext directly
 * (tests/Pest.php's global beforeEach, or explicitly per-test) BEFORE
 * making a request — which completely masks this exact bug, since the
 * context is already populated by the time the (fake) request fires,
 * regardless of what order the real middleware pipeline would run in.
 * This test is the one place that deliberately does NOT do that: it
 * clears the context and relies entirely on the real middleware chain
 * (auth -> IdentifyTenant -> SubstituteBindings) to derive it from the
 * authenticated user, the way an actual request does.
 */
test('IdentifyTenant resolves tenant early enough for implicit route-model binding on a tenant-scoped model to succeed', function () {
    Storage::fake('local');

    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $user = User::factory()->create();
    $document = app(DocumentStore::class)->store(
        UploadedFile::fake()->create('id.pdf'),
        $user,
        $user,
        'ID',
    );

    // The point of this test: everything above is arrange-phase setup;
    // this clear() is what makes it actually exercise the real bug.
    app(TenantContext::class)->clear();

    $this->actingAs($user)
        ->get(route('documents.download', $document))
        ->assertOk();
});
