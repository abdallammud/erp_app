<?php

use App\Livewire\SystemStatus;
use Livewire\Livewire;

test('it renders initial platform status', function () {
    Livewire::test(SystemStatus::class)
        ->assertSee('Platform status')
        ->assertSee(ucfirst(app()->environment()))
        ->assertSee(app()->version())
        ->assertSee('0'); // initial refresh count
});

test('clicking refresh increments the refresh count', function () {
    Livewire::test(SystemStatus::class)
        ->assertSet('refreshes', 0)
        ->call('refresh')
        ->assertSet('refreshes', 1)
        ->assertSee('1');
});
