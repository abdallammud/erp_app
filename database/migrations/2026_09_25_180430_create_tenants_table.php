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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();

            // Org profile — see docs/02-architecture.md "What tenant actually contains".
            $table->json('countries')->nullable();
            $table->char('default_currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);

            // Super Admin controls (docs/03-roles-and-permissions.md): suspend without
            // destroying data. Soft delete (below) covers "mark for offboarding" while
            // retaining the audit trail — see docs/08-data-model.md's soft-delete rule.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
