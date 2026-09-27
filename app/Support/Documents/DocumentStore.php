<?php

namespace App\Support\Documents;

use App\Models\Document;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The single entry point for storing and reading document file content
 * — see docs/build/00-build-plan.md Step 0.9. Every module that attaches
 * files to a record (personnel files, safeguarding evidence, receipts)
 * goes through this, not `Storage::disk(...)` directly, so the
 * encrypt-at-rest step (docs/build/DECISIONS.md D-030) and tenant-scoped
 * path layout can't be forgotten by a future caller.
 */
class DocumentStore
{
    public function store(UploadedFile $file, Model $documentable, User $uploader, string $category, ?string $expiryDate = null): Document
    {
        $disk = config('filesystems.documents_disk');
        $tenantId = app(TenantContext::class)->id();
        $path = "documents/{$tenantId}/".Str::uuid()->toString();

        Storage::disk($disk)->put($path, Crypt::encryptString(file_get_contents($file->getRealPath())));

        return Document::create([
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->getKey(),
            'category' => $category,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'expiry_date' => $expiryDate,
            'uploaded_by_id' => $uploader->id,
            // Explicit, not left to the DB column default: without this,
            // $document->is_verified is `null` in memory immediately
            // after create() (Eloquent doesn't refresh to pick up
            // server-side defaults), not `false` — a real footgun for
            // any caller checking it right after storing.
            'is_verified' => false,
        ]);
    }

    /**
     * Decrypted file bytes — the only correct way to read a Document's
     * content back. Never read `$document->path` directly off
     * `$document->disk`; the bytes on disk are ciphertext.
     */
    public function contents(Document $document): string
    {
        return Crypt::decryptString(Storage::disk($document->disk)->get($document->path));
    }

    /**
     * HR (or anyone else with org-wide HR visibility) marking a
     * document as verified — see docs/04-module-hrm.md §B ("verified"
     * status set by HR). No UI calls this yet in Phase 0 (there's no
     * employee-directory screen to hang a "verify" button on until
     * Phase 1's Employee Records module); it exists now because the
     * `is_verified`/`verified_by_id`/`verified_at` columns are part of
     * this step's required data model either way, and Phase 1 needs
     * this exact entry point already built and tested, not
     * re-discovered — same reasoning as
     * App\Support\Approvals\ApprovalWorkflow::awaitingActionBy() existing
     * ahead of every consuming UI.
     */
    public function verify(Document $document, User $verifier): Document
    {
        if (! $verifier->can(Permission::HrmOrgView->value)) {
            throw new RuntimeException('This user cannot verify documents.');
        }

        $document->update([
            'is_verified' => true,
            'verified_by_id' => $verifier->id,
            'verified_at' => now(),
        ]);

        return $document->fresh();
    }
}
