<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests get the full Laravel TestCase (HTTP, DB, auth helpers).
| Unit tests stay on Pest's default plain PHPUnit TestCase — no framework
| bootstrapping needed for pure logic tests.
|
*/

pest()->extend(TestCase::class)->in('Feature');
