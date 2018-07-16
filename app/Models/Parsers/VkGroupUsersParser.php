<?php

namespace App\Models\Parsers;

use App\Models\Parsers\VkAbstractParser;
use App\Models\VkCountry;
use App\Models\Parsers\ParserException;

/**
 * No VK API method, so heavy html parsing
 */
class VkGroupUsersParser
{
    protected $request_url = 'https://vk.com/al_search.php';
    protected $curl_timeout = 3;
    protected $curl_requests_count = 0;
    protected $curl_response;
    protected $offset = 0;
    // protected $count = 20;
    protected $has_more = true;
    protected $params;
    protected $group_id;
    protected $age_from;
    protected $age_to;
    
    protected $age;
    protected $ids_buffer;
    
    protected $error = false;
    
    public function __construct($params = [])
    {
        $this->params = $params;
        $this->age_from = isset($params['age_from']) ? $params['age_from'] : 18;
        $this->age_to = isset($params['age_to']) ? $params['age_to'] : 62;
        $this->group_id = $params['group_id'];

    }

    public function parse()
    {
        for ($age = $this->age_from; $age <= $this->age_to; $age++) {
            $this->age = $age;
            $this->ids_buffer = [];
            // ~20k ids enough
            while($this->has_more && $this->curl_requests_count < 1000) {
                $this->prepareRequest();
                $this->performRequest();
                $this->parseResponse();                
            }
            $this->storeData();
        }
        $this->makeReport();
        return $this->getReportMessage();
    }

    protected function storeData() {
        $this->prepared_data_arr = [];
        // uid, age, vk_group_id
        VkGroupUsers::insertOrUpdate($this->prepared_data_arr);
    }

    protected function parseResponse()
    {
        $response = $this->curl_response;
        $this->prepared_data_arr = [];
        
        if (!is_string($response)) {
            $this->setError('Invalid response, vk request number: ' . $this->curl_requests_count);
            return;
        }
        
        preg_match('/"has_more":(true|false),"offset":([0-9]+)/', $response, $matches);
        if (!isset($matches[2])) {
            $this->setError('Unknown response format, vk request number: ' . $this->curl_requests_count);
            return;
        }
        $this->has_more = $matches[1];
        $this->offset = $matches[2];
        
        preg_match_all('/search_sub([0-9]+)/', $response, $matches_ids);
        $ids = $matches_ids[1];
        $this->ids_buffer += $ids;        
    }

    protected function prepareRequest()
    {
        $this->offset = 0;
        $this->has_more = true;
        $fields = [
            'al' => '1',
            'c' => [
                'group' => $this->group_id,
                'name' => 1,
                'photo' => 1,
                'section' => 'people',
                'age_from' => $this->age,
                'age_to' => $this->age,
            ],
        ];
        $fields['offset'] = $this->offset;
        isset($params['country_id']) ? $fields['country'] = $params['country_id'];

        $this->curl_postfields = $fields;
    }
    
    protected function performRequest()
    {
        $this->curl_response = false;
        $this->curl_status = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->request_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($this->curl_postfields)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->curl_postfields);
        }
        $this->curl_response = curl_exec($ch);
        $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->curl_requests_count++;
    }
    
    protected function setError($error)
    {
        $this->error = $error;
    }
    
    public function getError()
    {
        return $this->error;
    }
}
