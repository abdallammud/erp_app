<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Note: this builds a plausible row, not a real encrypted file on
     * disk — go through App\Support\Documents\DocumentStore::store()
     * instead of this factory whenever a test needs to actually read
     * the file content back (download/decrypt assertions).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documentable_type' => User::class,
            'documentable_id' => User::factory(),
            'category' => fake()->randomElement(['ID', 'Contract', 'Certificate']),
            'original_filename' => fake()->word().'.pdf',
            'disk' => 'local',
            'path' => 'documents/'.fake()->uuid(),
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
            'expiry_date' => null,
            'is_verified' => false,
            'verified_by_id' => null,
            'verified_at' => null,
            'uploaded_by_id' => User::factory(),
        ];
    }
}
