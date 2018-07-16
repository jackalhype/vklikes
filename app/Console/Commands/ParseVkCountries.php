<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parsers\Geo\VkCountriesParser;

class ParseVkCountries extends Command
{
    use \App\Console\ConsoleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse_vk:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse VK Countries';

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
        $this->comment($this->time() . ' Vk Countries Parsing started...');
        $this->comment($this->time() . ' english:');
        $parser = new VkCountriesParser(['lang' => 'en']);
        $msg_en = $parser->parse();
        $this->comment($this->time() . $msg_en);

        $this->comment($this->time() . ' russian:');
        $parser = new VkCountriesParser(['lang' => 'ru']);
        $msg_ru = $parser->parse();
        $this->comment($this->time() . $msg_ru);
        $this->comment($this->time() . ' Vk Countries Parsing finished.');
    }
}
