<?php

use App\Livewire\SystemStatus;

test('the dashboard returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSeeLivewire(SystemStatus::class);
});
