<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A job title/grade slot within one tenant — see docs/04-module-hrm.md's
 * org structure mapping. `grade` is free-text for now; becomes a proper
 * link to a tenant-configurable salary grade in Phase 1 (see this
 * model's migration for why that's not built yet).
 */
#[Fillable(['title', 'grade', 'department_id', 'is_active'])]
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
