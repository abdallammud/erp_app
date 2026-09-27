<?php

use App\Livewire\AuditLog\Index;
use App\Models\Department;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

/**
 * Proves the shared export framework (App\Support\Reporting\
 * ReportExporter) is actually wired into a real screen, not just
 * unit-tested in isolation — see docs/build/00-build-plan.md Step 0.10.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);
    $this->actingAs($hrAdmin);

    Department::create(['name' => 'Water & Sanitation']);
});

test('exporting the audit log as Excel triggers a real file download', function () {
    Livewire::test(Index::class)
        ->call('exportExcel')
        ->assertFileDownloaded('audit-log.xlsx');
});

test('exporting the audit log as CSV triggers a real file download with real content', function () {
    $component = Livewire::test(Index::class)->call('exportCsv');

    $component->assertFileDownloaded('audit-log.csv');

    $content = base64_decode(data_get($component->effects, 'download.content'));

    expect($content)->toContain('Water & Sanitation')
        ->toContain('created');
});

test('exporting the audit log as PDF triggers a real file download', function () {
    Livewire::test(Index::class)
        ->call('exportPdf')
        ->assertFileDownloaded('audit-log.pdf');
});

test('the export respects the active event filter', function () {
    $department = Department::sole();
    $department->update(['name' => 'Water & Sanitation & Hygiene']);

    $component = Livewire::test(Index::class)->set('event', 'created')->call('exportCsv');

    $content = base64_decode(data_get($component->effects, 'download.content'));

    expect($content)->toContain('Water & Sanitation')
        ->and(substr_count($content, 'Department'))->toBe(1);
});
