<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();

            // Not part of spatie/laravel-activitylog's stock schema —
            // added so the audit trail itself is tenant-isolated the
            // same way every other tenant-scoped table is (see
            // docs/build/DECISIONS.md D-014/D-029), not just implicitly
            // scoped via whatever subject it points to. restrictOnDelete
            // matches every other tenant-scoped table's convention.
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();

            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }
};
