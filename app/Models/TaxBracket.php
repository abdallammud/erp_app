<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\TaxBracketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One row of a tenant's progressive income tax bracket grid for a given
 * country — see docs/04-module-hrm.md §C. Data model only in Step 1.1;
 * see App\Models\AllowanceType's docblock for why the config UI and
 * actual payroll tax calculation wait for Step 1.5.
 */
#[Fillable(['tenant_id', 'country_code', 'sequence', 'min_income', 'max_income', 'rate'])]
class TaxBracket extends Model
{
    /** @use HasFactory<TaxBracketFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'min_income' => 'decimal:2',
            'max_income' => 'decimal:2',
            'rate' => 'decimal:2',
        ];
    }
}
