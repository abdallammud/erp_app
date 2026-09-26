<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete(), not cascade — see docs/build/DECISIONS.md D-014.
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('title');

            // Free-text for now (e.g. "P3", "G5", matching the grade codes
            // seen in the Nova HRM reference) — becomes a proper foreign
            // key to a tenant-configurable SalaryGrade once Phase 1 builds
            // Payroll & Compensation (docs/04-module-hrm.md §C). Not
            // building that table now would be getting ahead of Step 0.5's
            // actual scope.
            $table->string('grade')->nullable();

            $table->foreignId('department_id')->nullable()
                ->constrained()->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
