<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('approval_chain_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();

            // The actual thing being approved — a LeaveRequest, PayrollRun,
            // PurchaseRequisition in later phases; TestRequest for this
            // step's demo. See docs/build/00-build-plan.md Step 0.6.
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');

            $table->string('status')->default('pending');

            // Null once the instance reaches a terminal state (approved
            // or rejected) — there's no "current" step to act on anymore.
            $table->unsignedTinyInteger('current_sequence')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_instances');
    }
};
