<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The generic document/attachment primitive — see
 * docs/build/00-build-plan.md Step 0.9. Any record can have documents
 * attached (`documentable`); file bytes are never stored directly here,
 * only enough to locate and describe them — see
 * App\Support\Documents\DocumentStore, the single entry point for
 * actually reading/writing file content (handles the encrypt-at-rest
 * step — see docs/build/DECISIONS.md D-030). Don't call
 * Storage::disk(...) directly against a Document's path/disk from
 * anywhere else.
 *
 * Access is NOT open to every authenticated user just because they can
 * see the row — see App\Policies\DocumentPolicy, checked on every
 * download.
 */
#[Fillable([
    'tenant_id', 'documentable_type', 'documentable_id', 'category',
    'original_filename', 'disk', 'path', 'mime_type', 'size',
    'expiry_date', 'is_verified', 'verified_by_id', 'verified_at', 'uploaded_by_id',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }
}
