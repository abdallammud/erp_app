<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();

            $table->string('type');
            $table->foreignId('salary_grade_id')->constrained()->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
