<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_stations', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete(), not cascade — see docs/build/DECISIONS.md D-014.
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code')->nullable();

            // Country of operation, not the tenant's — a tenant like WARDI
            // spans Somalia and Kenya (docs/02-architecture.md), so a duty
            // station's own country matters independently of the tenant's
            // list of countries of operation.
            $table->string('country_code', 2);
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_stations');
    }
};
