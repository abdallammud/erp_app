<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('relationship');
            $table->date('date_of_birth')->nullable();

            // text, not string — see Employee's national_id column for
            // why (encrypted at the app layer, docs/02-architecture.md's
            // "beneficiary personal data" Restricted-tier NFR covers
            // this too, per docs/03-roles-and-permissions.md's
            // confidentiality tiers).
            $table->text('passport_number')->nullable();

            $table->string('phone')->nullable();
            $table->boolean('is_emergency_contact')->default(false);

            // Insurance beneficiary allocation — see
            // docs/04-module-hrm.md §B ("percentage split that must
            // total 100%") and docs/build/DECISIONS.md for how "must
            // total 100%" is actually enforced (never exceed, not
            // forced-exact — incremental entry has to be possible).
            $table->boolean('is_insurance_beneficiary')->default(false);
            $table->decimal('insurance_beneficiary_percentage', 5, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependents');
    }
};
