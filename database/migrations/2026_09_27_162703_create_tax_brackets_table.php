<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('country_code', 2);

            // Progressive bracket grid, per docs/04-module-hrm.md §C.
            // max_income null = the top, open-ended bracket.
            $table->unsignedInteger('sequence');
            $table->decimal('min_income', 12, 2);
            $table->decimal('max_income', 12, 2)->nullable();
            $table->decimal('rate', 5, 2);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'country_code', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
