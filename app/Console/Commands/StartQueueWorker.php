<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class StartQueueWorker extends Command
{
    protected $signature = 'queue:start';
    protected $description = 'Start the Laravel queue worker in forever mode';

    public function handle(): int
    {
        $this->info('Starting queue worker...');

        $process = Process::path(base_path())
            ->forever()
            ->run(['php', 'artisan', 'queue:work', '--sleep=3', '--tries=3']);

        return $process->exitCode();
    }
}
