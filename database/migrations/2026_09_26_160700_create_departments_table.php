<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete(), not cascade — matches users.tenant_id
            // (docs/build/DECISIONS.md D-014): a tenant is never silently
            // taken down along with its data.
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code')->nullable();

            // Light optional hierarchy (e.g. "Programs" -> "Health & Nutrition")
            // — see docs/04-module-hrm.md's org structure mapping. Nullable:
            // not every tenant needs sub-departments.
            $table->foreignId('parent_department_id')->nullable()
                ->constrained('departments')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
