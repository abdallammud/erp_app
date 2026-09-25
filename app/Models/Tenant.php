<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * One NGO organization. Deliberately does NOT use BelongsToTenant — a
 * tenant doesn't belong to itself. Everything else in the system belongs,
 * directly or indirectly, to a Tenant. See docs/02-architecture.md.
 */
#[Fillable([
    'name', 'slug', 'logo_path', 'countries', 'default_currency',
    'timezone', 'fiscal_year_start_month', 'is_active',
])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'is_active' => 'boolean',
            'fiscal_year_start_month' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            if (empty($tenant->slug) && ! empty($tenant->name)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
