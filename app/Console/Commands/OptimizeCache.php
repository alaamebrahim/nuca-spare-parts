<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeCache extends Command
{
    protected $signature = 'app:optimize';
    protected $description = 'Run optimization';

    public function handle(): int
    {
        $this->call('optimize');
        $this->info('Optimization completed');
        return self::SUCCESS;
    }
}

