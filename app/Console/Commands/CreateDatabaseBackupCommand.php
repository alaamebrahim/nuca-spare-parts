<?php

namespace App\Console\Commands;

use App\Actions\Backups\CreateDatabaseBackupAction;
use App\Enums\BackupTypeEnum;
use Illuminate\Console\Command;
use Throwable;
use ValueError;

class CreateDatabaseBackupCommand extends Command
{
    protected $signature = 'backup:database {--type=manual : Backup type (manual, daily, weekly, monthly)}';

    protected $description = 'Create a database backup and upload it to Cloudflare R2';

    public function handle(CreateDatabaseBackupAction $action): int
    {
        try {
            $type = BackupTypeEnum::from((string) $this->option('type'));
        } catch (ValueError) {
            $this->error('Invalid backup type. Use: manual, daily, weekly, monthly.');

            return self::FAILURE;
        }

        $this->info("Creating {$type->value} database backup...");

        try {
            $backup = $action->run(type: $type);

            $this->info("Backup created: {$backup->name}");
            $this->line("Type: {$backup->type->getLabel()}");
            $this->line("Path: {$backup->path}");
            $this->line("Size: {$backup->humanSize()}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
