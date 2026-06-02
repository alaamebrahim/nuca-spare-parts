<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateDb extends Command
{
    protected $signature = 'app:update-db';
    protected $description = 'Run database migrations with force';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        $this->info('Migration done');
        return self::SUCCESS;
    }
}

