<?php

namespace App\Models\Parsers\Geo;

use App\Models\Parsers\VkAbstractParser;
use App\Models\VkCountry;
use App\Models\Parsers\ParserException;
use App\Models\Parsers\VkApiAbstractClient;

class VkCountriesParser extends VkAbstractParser
{
    protected $api_url = 'https://api.vk.com/method/database.getCountries';

    public function __construct($params = [])
    {
        parent::__construct($params);

        $this->curl_getfields['need_all'] = 1;
    }

    protected function storeData() {
        VkCountry::insertOrUpdate($this->prepared_data_arr);
    }

    protected function parseResponse()
    {
        $collection = $this->response_collection;
        $this->prepared_data_arr = [];
        foreach($collection['items'] as $item) {
            $fields = [
                'cid' => $item['id'],
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->lang == 'ru') {
                $fields['title'] = $item['title'];
            } else {
                $fields['title_en'] = $item['title'];
            }
            $this->prepared_data_arr[] = $fields;
        }
    }
}
