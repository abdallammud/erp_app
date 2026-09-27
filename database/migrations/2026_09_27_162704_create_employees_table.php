<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();

            // Nullable and set-null-on-delete, not restrict: a portal
            // account can be removed (or never existed — "not every
            // historical employee needs a login", see
            // docs/build/00-build-plan.md Step 1.1) without touching the
            // employee record itself.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('employee_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            // text, not string: encrypted at the app layer (see
            // Employee's 'encrypted' cast, per docs/02-architecture.md's
            // "encrypted DB fields for sensitive data" NFR) — ciphertext
            // is comfortably longer than the plaintext and can exceed a
            // varchar(255).
            $table->text('national_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('personal_email')->nullable();
            $table->text('address')->nullable();

            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('duty_station_id')->nullable()->constrained()->nullOnDelete();

            // Reporting line — self-referential, per docs/04-module-hrm.md
            // §B ("reporting line... multi-location by design").
            $table->foreignId('reports_to_id')->nullable()
                ->constrained('employees')->nullOnDelete();

            $table->string('staff_category');
            $table->string('status')->default('active');

            $table->date('hire_date');
            $table->date('exit_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'employee_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
