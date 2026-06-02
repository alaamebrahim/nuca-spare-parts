<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeClear extends Command
{
    protected $signature = 'app:optimize-clear';
    protected $description = 'Clear optimization caches';

    public function handle(): int
    {
        $this->call('optimize:clear');
        $this->info('Optimization caches cleared');
        return self::SUCCESS;
    }
}

