<?php

namespace App\Models\Parsers\Geo;

use App\Models\VkCity;
use App\Models\Services\TranslitService as Translit;
use App\Models\Parsers\ParserException;
use App\Models\Parsers\VkApiAbstractClient;

/**
 * Basic usage:
 * $parser = new VkCitiesParser($params)
 * while($parser->hasJobs()) {
 *     $message = $parser->parsePortion();
 * }
 */
class VkCitiesParser extends VkApiAbstractClient
{
    protected $params;
    protected $country_id;
    protected $region_id;
    protected $api_url = 'https://api.vk.com/method/database.getCities';
    protected $v = '4.100';
    protected $request_url;
    protected $offset = 0;
    protected $count = 1000;
    protected $need_all = 1;
    protected $lang;    // int
    protected $real_response_lang;  // int
    protected $curl_response;
    protected $curl_status;
    protected $last_curl_cnt;
    protected $total_cnt = 0;
    protected $report_message;
    protected $has_jobs = true;     // can make another curl and get new results
    protected $portion_cnt;
    protected $prepared_data_arr;   // ready to store in db

    public function __construct(array $params)
    {
        $this->country_id = isset($params['country_id']) ? $params['country_id'] : 1; // Россия
        //$this->region_id = isset($params['region_id']) ? $params['region_id'] : 1053480; // Московская
        $this->region_id = isset($params['region_id']) ? $params['region_id'] : false;
        $this->setLang($params);
        $this->need_all = isset($params['need_all']) ? $params['need_all'] : 1;
    }

    /**
     * parse Vk Cities and Store them into DB
     *
     * @return str
     * @deprecated
     */
    private function parse()
    {
        while($this->hasJobs()) {
            $parser->parsePortion(10);
        }
        return (string) $this->total_cnt . ' cities parsed.';
    }

    public function hasJobs()
    {
        return $this->has_jobs;
    }

    /**
     * makes $max_requests_num curl requests per time.
     *
     * @return string
     */
    public function parsePortion($max_requests_num = 5)
    {
        $this->portion_cnt = 0;
        for ($i = 1; $i <= $max_requests_num; $i++) {
            $this->constructRequestUrl();
            $this->performRequest();
            $this->parseResponse();
            $this->storeData();
            $this->offset += $this->count;
            if (!$this->hasJobs()) {
                break;
            }
        }
        $this->makeReport();
        return $this->getReportMessage();
    }

    protected function getReportMessage()
    {
        return $this->report_message;
    }

    protected function makeReport()
    {
        $cnt = $this->portion_cnt;
        $this->report_message = (string) $cnt . ' cities parsed.';
            //~ . PHP_EOL
            //~ . $this->storage_start_time_msg
            //~ . $this->storage_finish_time_msg;
    }

    protected function storeData()
    {
        //$this->storage_start_time_msg = date('i:s') . ' storing started' . PHP_EOL;
        VkCity::insertOrUpdate($this->prepared_data_arr);
        //$this->storage_finish_time_msg = date('i:s') . ' storing finished' . PHP_EOL;
    }

    protected function parseResponse()
    {
        $response_decoded = json_decode($this->curl_response, true);
        if (!is_array($response_decoded['response'])) {
            $msg = 'curl status: '. $this->curl_status . PHP_EOL;
            $msg .= 'curl response: ' . $this->curl_response;
            throw new ParserException($msg);
        }
        $collection = $response_decoded['response'];
        $this->last_curl_cnt = count($collection);
        $this->portion_cnt += $this->last_curl_cnt;
        $this->total_cnt += $this->last_curl_cnt;
        if ($this->last_curl_cnt < $this->count) {
            $this->has_jobs = false;
        }
        $this->prepared_data_arr = [];
        foreach($collection as $item) {
            if (!isset($item['title'])) {
                throw new ParserException(print_r($this->curl_response, true));
            }
            $title = $this->getTitle($item['title']);
            $title_en = $this->getTitleEn($item['title']);
            $fields = [
                'country_id' => $this->country_id,
                'region_id' => $this->region_id,
                'cid' => $item['cid'],
                'title' => $title,
                'title_en' => $title_en,
                'area' => isset($item['area']) ? $item['area'] : null,
                'region' => isset($item['region']) ? $item['region'] : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->need_all === 0 && $this->lang === 0 && $this->real_response_lang === 0) {
                unset($fields['title_en']);
            }
            $this->prepared_data_arr[] = $fields;
        }
    }

    protected function getTitle($str)
    {
        return $str;
    }

    protected function getTitleEn($str)
    {
        if ($this->real_response_lang === null) {
            $this->setRealResponseLang($str);
        }
        if ($this->real_response_lang === 0) {
            $title_en = Translit::rutoen($str);
            return $title_en;
        }
        return $str;
    }

    /**
     * Intellegent guess, what kind of language we recieved in response.
     */
    protected function setRealResponseLang($str)
    {
        $this->real_response_lang = preg_match('/[А-Яа-яЁё]/u', $str) ? 0 : 3;
    }

    protected function performRequest()
    {
        $this->curl_status = false;
        for ($i = 3; $i > 0; $i--) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $this->request_url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $this->curl_response = curl_exec($ch);
            $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($this->curl_status == 200) {
                break;
            }
            sleep(1);
        }
    }

    protected function constructRequestUrl()
    {
        $query_arr = [
            'country_id' => $this->country_id,
            'offset' => $this->offset,
            'count' => $this->count,
            'need_all' => $this->need_all,
            'lang' => $this->lang,
            'v' => $this->v,
        ];
        if ($this->region_id) {
            $query_arr['region_id'] = $this->region_id;
        }
        if (isset(self::$access_token)) {
            $query_arr['access_token'] = self::$access_token;
        }
        $this->request_url = $this->api_url . '?' . http_build_query($query_arr);
        $this->real_response_lang = null;
        //throw new ParserException($this->request_url);
    }

    protected function setLang($params)
    {
        if (isset($params['lang']) && $params['lang'] == 'en') {
            $this->lang = 3;
        } else {
            $this->lang = 0; // ru
        }
    }
}
