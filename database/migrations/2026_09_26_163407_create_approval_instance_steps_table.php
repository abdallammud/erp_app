<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();

            // cascadeOnDelete — these rows belong entirely to their
            // instance; they have no meaning without it (unlike
            // approval_chains, which are reusable tenant configuration).
            $table->foreignId('approval_instance_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('sequence');
            $table->string('approver_role');
            $table->string('label')->nullable();
            $table->string('status')->default('pending');

            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['approval_instance_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_instance_steps');
    }
};
