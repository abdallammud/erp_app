<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A deliberately trivial "subject" for the approval engine to prove
     * itself against — see docs/build/00-build-plan.md Step 0.6's
     * Definition of Done. Not a real business object; will not be used
     * once Phase 1 gives the engine a real subject (LeaveRequest, etc.).
     */
    public function up(): void
    {
        Schema::create('test_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_requests');
    }
};
