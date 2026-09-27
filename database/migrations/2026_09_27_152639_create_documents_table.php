<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();

            // What this document is attached to — a User's personnel
            // file today, any record in Phase 1+ (see
            // docs/build/00-build-plan.md Step 0.9).
            $table->morphs('documentable');

            // Free-form, not an enum — categories differ by module (ID,
            // contract, certificate for HR; evidence for Safeguarding;
            // receipt for Procurement) and this is a generic Phase 0
            // primitive, not an HR-specific one. See
            // docs/04-module-hrm.md §B.
            $table->string('category');

            $table->string('original_filename');
            $table->string('disk');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size')->comment('Original plaintext bytes, not the encrypted-at-rest size.');

            $table->date('expiry_date')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('uploaded_by_id')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
