<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VkLike;

class gg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'gg-command. Simple as it is ^^';

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
        $vklike = new VkLike;
        $vklike->vklikes_request_id = 100;
        $vklike->vk_uid = 1;    // Pasha, privet!
        $vklike->save();
    }
}
