<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The only way a document's file content ever leaves the app — see
 * docs/build/00-build-plan.md Step 0.9's DoD. Route-model binding on
 * Document already fails closed cross-tenant (BelongsToTenant's
 * TenantScope — a cross-tenant id 404s before this class ever runs);
 * App\Policies\DocumentPolicy::view() covers same-tenant authorization.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        return response()->streamDownload(
            fn () => print (app(DocumentStore::class)->contents($document)),
            $document->original_filename,
            ['Content-Type' => $document->mime_type],
        );
    }
}
