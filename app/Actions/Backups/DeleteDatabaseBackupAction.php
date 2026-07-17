<?php

namespace App\Actions\Backups;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;

class DeleteDatabaseBackupAction
{
    public function run(Backup $backup): void
    {
        DB::transaction(function () use ($backup): void {
            $backup->delete();
        });
    }

    /**
     * @param  iterable<int, Backup>  $backups
     */
    public function runMany(iterable $backups): int
    {
        $deleted = 0;

        foreach ($backups as $backup) {
            $this->run($backup);
            $deleted++;
        }

        return $deleted;
    }
}
