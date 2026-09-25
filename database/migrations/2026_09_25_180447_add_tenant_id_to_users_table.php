<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable: every login belongs to exactly one tenant EXCEPT Super Admin,
            // who sits outside all tenants by design — see docs/02-architecture.md
            // and docs/03-roles-and-permissions.md.
            //
            // restrictOnDelete(): a tenant can't be deleted while it still has users —
            // offboarding a tenant is a deliberate process (docs/08-data-model.md's
            // soft-delete rule), never a silent cascade that orphans accounts.
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
