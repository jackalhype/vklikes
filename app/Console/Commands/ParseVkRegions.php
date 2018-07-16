<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parsers\Geo\VkRegionsParser as Parser;

class ParseVkRegions extends Command
{
    use \App\Console\ConsoleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse_vk:regions {--c|country_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse Vk Regions';

    protected $country_id;  // int

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
        $this->commentNicely('Vk Regions Parsing started..');
        $country_id = $this->option('country_id');
        $this->commentNicely('country_id: ' . $country_id);
        $params = [
            'country_id' => $country_id,
        ];
        $parser = new Parser($params);
        $msg = $parser->parseVkRegions();
        $this->commentNicely($msg);
        $this->commentNicely('Vk Regions Parsing finished.');
    }
}
