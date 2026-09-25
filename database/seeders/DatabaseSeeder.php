<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * RolesAndPermissionsSeeder is the global, always-needed catalog
     * (docs/03-roles-and-permissions.md). DemoTenantSeeder is local
     * exploration data — safe to run repeatedly (idempotent), never
     * intended for a real production database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DemoTenantSeeder::class,
        ]);
    }
}
