<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests get the full Laravel TestCase (HTTP, DB, auth helpers) and
| a freshly migrated in-memory SQLite database per test (RefreshDatabase) —
| needed from Phase 0 Step 0.3 onward since tenancy tests depend on real
| tables. Unit tests stay on Pest's default plain PHPUnit TestCase — no
| framework bootstrapping needed for pure logic tests.
|
*/

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
