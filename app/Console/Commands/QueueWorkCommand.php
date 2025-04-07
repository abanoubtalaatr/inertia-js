<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class QueueWorkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-work-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log message before starting the worker
        $this->info('Starting the queue worker...');

        Artisan::call('queue:work', [
            '--sleep' => 3,
            '--tries' => 3,
            '--stop-when-empty' => true, // Add this line
        ]);

        // Optionally, log or output the result
        $this->info('Queue worker finished.'); // This will be displayed once the worker stops
    }
}
