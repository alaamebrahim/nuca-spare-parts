<?php

use App\Actions\Backups\CreateDatabaseBackupAction;
use App\Actions\Backups\DeleteDatabaseBackupAction;
use App\DataProcessors\BackupMetricsDataProcessor;
use App\Enums\BackupStatusEnum;
use App\Enums\BackupTypeEnum;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'backup.disk' => 'local',
        'backup.path_prefix' => 'backups/database',
        'backup.temp_directory' => storage_path('app/private/backups-temp-testing'),
        'backup.schedules.daily.enabled' => true,
        'backup.schedules.weekly.enabled' => true,
        'backup.schedules.monthly.enabled' => true,
    ]);

    Storage::fake('local');
});

it('creates a gzipped database backup and stores metadata', function () {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $backup = app(CreateDatabaseBackupAction::class)->run($user->id);

    expect($backup)->toBeInstanceOf(Backup::class)
        ->and($backup->status)->toBe(BackupStatusEnum::Completed)
        ->and($backup->type)->toBe(BackupTypeEnum::Manual)
        ->and($backup->user_id)->toBe($user->id)
        ->and($backup->name)->toStartWith('نسخة احتياطية — ')
        ->and($backup->filename)->toEndWith('.sql.gz')
        ->and($backup->path)->toContain('backups/database/')
        ->and($backup->size)->toBeGreaterThan(0)
        ->and($backup->completed_at)->not->toBeNull();

    Storage::disk('local')->assertExists($backup->path);
});

it('creates scheduled daily weekly and monthly backups with readable names', function (BackupTypeEnum $type, string $namePrefix) {
    $backup = app(CreateDatabaseBackupAction::class)->run(type: $type);

    expect($backup->type)->toBe($type)
        ->and($backup->name)->toStartWith($namePrefix)
        ->and($backup->filename)->toContain('_'.$type->value.'_')
        ->and($backup->status)->toBe(BackupStatusEnum::Completed);

    Storage::disk('local')->assertExists($backup->path);
})->with([
    [BackupTypeEnum::Daily, 'نسخة يومية — '],
    [BackupTypeEnum::Weekly, 'نسخة أسبوعية — '],
    [BackupTypeEnum::Monthly, 'نسخة شهرية — '],
]);

it('runs the backup artisan command with a schedule type', function () {
    $exitCode = Artisan::call('backup:database', ['--type' => 'daily']);

    expect($exitCode)->toBe(0);

    $backup = Backup::query()->latest('id')->first();

    expect($backup)->not->toBeNull()
        ->and($backup->type)->toBe(BackupTypeEnum::Daily)
        ->and($backup->status)->toBe(BackupStatusEnum::Completed);
});

it('deletes backup metadata and cloud file', function () {
    $path = 'backups/database/2026/07/test-backup.sql.gz';
    Storage::disk('local')->put($path, 'gzipped-content');

    $backup = Backup::create([
        'name' => 'نسخة احتياطية — 2026-07-17 21:00',
        'filename' => 'test-backup.sql.gz',
        'path' => $path,
        'disk' => 'local',
        'status' => BackupStatusEnum::Completed,
        'type' => BackupTypeEnum::Manual,
        'size' => 16,
        'completed_at' => now(),
    ]);

    app(DeleteDatabaseBackupAction::class)->run($backup);

    expect(Backup::query()->find($backup->id))->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

it('builds backup overview metrics and schedule cards', function () {
    Backup::create([
        'name' => 'نسخة يومية — A',
        'filename' => 'a.sql.gz',
        'path' => 'backups/database/a.sql.gz',
        'disk' => 'local',
        'status' => BackupStatusEnum::Completed,
        'type' => BackupTypeEnum::Daily,
        'size' => 1000,
        'completed_at' => now(),
    ]);

    Backup::create([
        'name' => 'نسخة احتياطية — B',
        'filename' => 'b.sql.gz',
        'path' => 'backups/database/b.sql.gz',
        'disk' => 'local',
        'status' => BackupStatusEnum::Failed,
        'type' => BackupTypeEnum::Manual,
        'size' => null,
    ]);

    $metrics = BackupMetricsDataProcessor::overview();
    $schedules = BackupMetricsDataProcessor::schedules();

    expect($metrics['total'])->toBe(2)
        ->and($metrics['completed'])->toBe(1)
        ->and($metrics['failed'])->toBe(1)
        ->and($metrics['total_size'])->toBe(1000)
        ->and($metrics['last_backup_at'])->not->toBeNull()
        ->and($schedules)->toHaveCount(3)
        ->and($schedules[0]['type'])->toBe(BackupTypeEnum::Daily)
        ->and($schedules[0]['enabled'])->toBeTrue()
        ->and($schedules[0]['next_run_at'])->not->toBeNull()
        ->and($schedules[0]['last_run_at'])->not->toBeNull();
});
