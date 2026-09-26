<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Adds audit logging (docs/build/00-build-plan.md Step 0.8) to a
 * tenant-scoped model. Deliberately a separate trait from
 * BelongsToTenant, not folded into it, so App\Models\AuditLogEntry
 * itself — which needs BelongsToTenant for its own tenant isolation —
 * never logs its own creation. See docs/build/DECISIONS.md D-029.
 *
 * Defaults: log every fillable attribute, only when it actually
 * changed, skip empty logs, and don't bother repeating `tenant_id` in
 * the diff (it's already the log row's own tenant_id column and never
 * changes after creation). Override getActivitylogOptions() on a
 * specific model to exclude anything sensitive — see App\Models\User
 * excluding `password`.
 */
trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept(['tenant_id']);
    }
}
