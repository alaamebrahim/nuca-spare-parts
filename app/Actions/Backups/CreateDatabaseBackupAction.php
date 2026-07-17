<?php

namespace App\Actions\Backups;

use App\Enums\BackupStatusEnum;
use App\Enums\BackupTypeEnum;
use App\Models\Backup;
use App\Services\Backups\DatabaseDumpService;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CreateDatabaseBackupAction
{
    public function __construct(
        protected DatabaseDumpService $databaseDumpService,
    ) {}

    public function run(?int $userId = null, BackupTypeEnum $type = BackupTypeEnum::Manual): Backup
    {
        $now = now();
        $disk = config('backup.disk', 'r2');
        $filename = sprintf(
            '%s_%s_%s.sql.gz',
            Str::slug((string) config('app.name', 'database')),
            $type->value,
            $now->format('Y-m-d_His')
        );
        $path = sprintf(
            '%s/%s/%s/%s',
            trim((string) config('backup.path_prefix', 'backups/database'), '/'),
            $now->format('Y'),
            $now->format('m'),
            $filename
        );

        $backup = Backup::create([
            'name' => $this->readableName($type, $now),
            'filename' => $filename,
            'path' => $path,
            'disk' => $disk,
            'status' => BackupStatusEnum::Processing,
            'type' => $type,
            'user_id' => $userId,
        ]);

        $localPath = null;

        try {
            $localPath = $this->databaseDumpService->createGzippedDump();
            $size = filesize($localPath);

            if ($size === false) {
                throw new RuntimeException('Unable to determine backup file size.');
            }

            $stream = fopen($localPath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Unable to open backup file for upload.');
            }

            try {
                $uploaded = Storage::disk($disk)->put($path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (! $uploaded) {
                throw new RuntimeException('Failed to upload backup to cloud storage.');
            }

            $backup->update([
                'status' => BackupStatusEnum::Completed,
                'size' => $size,
                'completed_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Database backup failed.', [
                'backup_id' => $backup->id,
                'type' => $type->value,
                'message' => $exception->getMessage(),
            ]);

            $backup->update([
                'status' => BackupStatusEnum::Failed,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            if (is_string($localPath) && is_file($localPath)) {
                @unlink($localPath);
            }
        }

        return $backup->fresh();
    }

    protected function readableName(BackupTypeEnum $type, DateTimeInterface $moment): string
    {
        $timestamp = match ($type) {
            BackupTypeEnum::Manual => $moment->format('Y-m-d H:i'),
            BackupTypeEnum::Daily => $moment->format('Y-m-d'),
            BackupTypeEnum::Weekly => $moment->format('Y-m-d'),
            BackupTypeEnum::Monthly => $moment->format('Y-m'),
        };

        return $type->readableNamePrefix().' — '.$timestamp;
    }
}
