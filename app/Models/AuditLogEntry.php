<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * The platform's audit trail — see docs/build/00-build-plan.md Step 0.8
 * and docs/02-architecture.md's "Audit Log" requirement. Extends
 * spatie/laravel-activitylog's own Activity model purely to add tenant
 * isolation (see the create_activity_log_table migration's docblock and
 * docs/build/DECISIONS.md D-029) — every other behavior (subject/causer
 * morphs, attribute_changes, properties) is inherited unchanged.
 *
 * Named AuditLogEntry rather than Activity in this app's own code to
 * match the domain language used throughout docs/ ("audit log"), even
 * though the underlying table is still `activity_log` — that's the
 * package's table, not ours to rename.
 *
 * Registered as `activitylog.activity_model` in config/activitylog.php
 * so every `LogsActivity` call anywhere in the app creates one of these,
 * not the package's own un-scoped Activity model.
 */
class AuditLogEntry extends Activity
{
    use BelongsToTenant;

    /**
     * Fallback for when BelongsToTenant's own ambient-TenantContext
     * auto-fill (which runs first — see that trait) can't fill
     * tenant_id, because there IS no ambient tenant: a Super Admin
     * (tenant_id null) acting on a tenant-scoped subject they don't
     * "belong" to, e.g. creating a brand-new tenant's first user (Step
     * 0.11). Found live: `User::create(...)` for a fresh tenant's admin
     * threw a NOT NULL constraint violation on activity_log.tenant_id,
     * because Auditable's auto-logging had nothing to fill it from.
     *
     * Derives it from the subject being logged instead — every
     * Auditable-driven subject is itself BelongsToTenant, so its own
     * tenant_id is authoritative and always available, no ambient
     * context required. Bypasses the subject's own TenantScope
     * deliberately: if ambient context is unset, a scoped lookup would
     * fail closed too, right back to the same problem.
     */
    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            if ($entry->tenant_id !== null) {
                return;
            }

            if (! is_string($entry->subject_type) || $entry->subject_id === null) {
                return;
            }

            if (! is_a($entry->subject_type, Model::class, true)) {
                return;
            }

            $subject = $entry->subject_type::withoutGlobalScopes()->find($entry->subject_id);

            if ($subject instanceof Model && array_key_exists('tenant_id', $subject->getAttributes())) {
                $entry->tenant_id = $subject->getAttribute('tenant_id');
            }
        });
    }
}
