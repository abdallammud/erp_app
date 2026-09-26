<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\DutyStationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A physical office/operating location within one tenant — enables
 * multi-location operation. See docs/04-module-hrm.md and
 * docs/08-data-model.md's core entities.
 */
#[Fillable(['name', 'code', 'country_code', 'city', 'address', 'is_active'])]
class DutyStation extends Model
{
    /** @use HasFactory<DutyStationFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
