<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_chain_steps', function (Blueprint $table) {
            $table->id();
            // Directly tenant-scoped too, not just via the parent chain —
            // defense in depth so a direct query on this table (bypassing
            // the relation) still can't leak across tenants. See
            // docs/02-architecture.md.
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('approval_chain_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');

            // A role name from App\Support\Authorization\Role — "whoever
            // holds this role approves this step," not one specific
            // person. Resolving to "this requester's specific supervisor"
            // needs a reporting-line field on Employee, which doesn't
            // exist until Phase 1 — see docs/build/DECISIONS.md.
            $table->string('approver_role');

            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['approval_chain_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_chain_steps');
    }
};
