<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
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
}
