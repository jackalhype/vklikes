<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parsers\Geo\VkCitiesParser as Parser;
use App\Models\VkRegion;
use App\Models\VkCountry;


/**
 *
 * Main cities parse twice: first --lang=en, second --lang=ru.
 * Don't set --region_id when using --all
 */
class ParseVkCities extends Command
{
    use \App\Console\ConsoleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse_vk:cities {--c|country_id=} {--r|region_id=} {--a|all} {--l|lang=ru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse VK Cities.';

    protected $country_id;  // int
    protected $region_id;   // int
    protected $need_all;    // 0, 1
    protected $lang;        // 'en', 'ru'

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->description = 'Parse Vk Cities. ' . PHP_EOL
        . 'set --a|all, if you want to parse all Cities, not only major ones.';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->commentNicely('Vk Cities Parsing started...');

        $this->country_id = $this->option('country_id');
        $this->region_id = $this->option('region_id');
        $opt_need_all = $this->option('all');
        $this->need_all = isset($opt_need_all) ? (int) $this->option('all') : 0;
        $this->lang = $this->option('lang');

        $this->comment('country_id: ' . $this->country_id);
        $this->comment('region_id: ' . $this->region_id);
        $this->comment('need_all: ' . $this->need_all);
        $this->comment('lang: ' . $this->lang);
        //~ die;

        if (!$this->country_id) {
            $this->need_all = 0;        // I don't want to hang this out.
            $countries = VkCountry::orderBy('cid')->get();
            foreach ($countries as $country) {
                $this->country_id = $country->cid;
                $this->commentNicely($country->title);
                $this->parsePortion();
            }
        } else if ($this->region_id || !$this->need_all) {
            $this->parsePortion();
        } else {
            $regions = VkRegion::where('country_id', $this->country_id)->orderBy('region_id')->get();
            foreach($regions as $region) {
                $this->region_id = $region->region_id;
                $this->commentNicely($region->title . ':');
                $this->parsePortion();
            }
        }
        $this->commentNicely('Vk Cities Parsing finished.');
    }

    protected function parsePortion()
    {
        $params = [
            'country_id' => $this->country_id,
            'region_id' => $this->region_id,
            'need_all' => $this->need_all,
            'lang' => $this->lang,
        ];
        $parser = new Parser($params);
        while($parser->hasJobs()) {
            $msg = $parser->parsePortion(10);
            $this->commentNicely($msg);
        }
    }
}
