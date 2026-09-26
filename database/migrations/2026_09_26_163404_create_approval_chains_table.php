<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('name');

            // e.g. 'test_request', later 'leave.annual', 'payroll.run',
            // 'procurement.requisition' — see docs/03-roles-and-permissions.md's
            // approval workflow engine. One active chain per (tenant,
            // action_type) for now; per-condition variants (the doc's
            // example of annual leave needing an extra step past 3 days)
            // are a Phase 1 refinement once a real module needs them —
            // see this migration's sibling steps table and
            // docs/build/DECISIONS.md.
            $table->string('action_type');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'action_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_chains');
    }
};
