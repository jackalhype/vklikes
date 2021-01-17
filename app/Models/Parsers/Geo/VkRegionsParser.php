<?php

namespace App\Models\Parsers\Geo;

use App\Models\VkRegion;
use App\Models\Services\TranslitService as Translit;
use App\Models\Parsers\ParserException;
use App\Models\Parsers\VkApiAbstractClient;

class VkRegionsParser extends VkApiAbstractClient
{
    protected $country_id;
    protected $api_url = 'https://api.vk.com/method/database.getRegions';
    protected $request_url;
    protected $offset = 0;
    protected $count = 1000;
    protected $curl_response;
    protected $curl_status;
    protected $report_message;

    protected $regions_arr = [];

    public function __construct(array $params)
    {
        $this->country_id = isset($params['country_id']) ? $params['country_id'] : 1;
    }

    /**
     * parse Vk Regions and Store them into DB
     *
     * @return string
     */
    public function parseVkRegions()
    {
        $this->constructRequestUrl();
        $this->performRequest();
        $this->parseResponse();
        $this->storeData();
        $this->makeReport();
        return $this->getReportMessage();
    }

    protected function getReportMessage()
    {
        return $this->report_message;
    }

    protected function makeReport()
    {
        $cnt = count($this->regions_arr);
        $this->report_message = 'Success, it parsed ' . $cnt. ' regions.';
    }

    protected function storeData()
    {
        foreach($this->regions_arr as $region_data) {
            $region = VkRegion::where(['region_id' => $region_data['region_id']])->get()->first();
            if (!$region) {
                $region = new VkRegion;
            }
            $region->fill($region_data);
            $region->save();
        }
    }

    protected function parseResponse()
    {
        $response_decoded = json_decode($this->curl_response, true);
        //throw new ParserException(print_r($response_decoded, true));
        //throw new ParserException(print_r($this->request_url, true));
        $regions = $response_decoded['response'];
        foreach($regions['items'] as $region) {
            $title_en = Translit::rutoen($region['title']);
            $this->regions_arr[] = [
                'country_id' => $this->country_id,
                'region_id' => $region['id'],
                'title' => $region['title'],
                'title_en' => $title_en,
            ];
        }
    }

    protected function performRequest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->request_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->curl_response = curl_exec($ch);
        $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    protected function constructRequestUrl()
    {
        $query_arr = [
            'v' => $this->v,
            'country_id' => $this->country_id,
            'offset' => $this->offset,
            'count' => $this->count,
        ];
        if (isset(self::$access_token)) {
            $query_arr['access_token'] = self::$access_token;
        }
        $this->request_url = $this->api_url . '?' . http_build_query($query_arr);
    }
}
