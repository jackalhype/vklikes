<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearDB extends Command
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleardb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear DB, fast growing tables. Maintain only recent few-hour-living records.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('clearing db...');
        $job = new \App\Jobs\ClearDB();
        $job->onQueue('cleardb');
        $this->dispatch($job);
        $this->info('clearing db finished');
    }
}
