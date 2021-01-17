<?php

namespace App\Models\Parsers;

use App\Models\Services\TranslitService as Translit;
use App\Models\Parsers\ParserException;
use App\Models\Parsers\VkApiAbstractClient;

/**
 * Base class for simple VK, 1-curl-request parsers.
 *******basic usage:*****
 * $parser = new MyParser       // where MyParser extends VkAbstractParser
 * try {
 *   $report_message = $parser->parse();
 * } catch (\Exception $e) {
 *  $e_msg = $e->getMessage();
 * }
 *
 */
abstract class VkAbstractParser extends VkApiAbstractClient
{
    protected $api_url;
    protected $request_url;
    protected $offset = 0;
    protected $count = 1000;
    protected $curl_getfields = [];
    protected $curl_postfields = [];
    protected $curl_timeout = 3;
    protected $curl_response;
    protected $curl_status;
    protected $curl_requests_count = 0;
    protected $report_message;
    protected $error;

    protected $musthave_params = [
        'api_url',
    ];

    protected $response_collection;     // raw response collection
    protected $prepared_data_arr;       // data ready to store in db

    public function __construct($params = [])
    {
        foreach ($params as $key => $val){
            $this->$key = $val;
        }
        $this->curl_getfields['offset'] = $this->offset;
        $this->curl_getfields['count'] = $this->count;
        $this->lang = isset($params['lang']) ? $params['lang'] : 'en';
        $this->curl_getfields['lang'] = $this->lang == 'en' ? 3 : 0;
    }

    public function parse()
    {
        if (!$this->checkParamsValidity()) {
            throw new ParserException($this->error);
        }
        $this->constructRequestUrl();
        $this->performRequest();
        $this->preParseResponse();
        $this->parseResponse();
        $this->storeData();
        $this->makeReport();
        return $this->getReportMessage();
    }

    /**
     * no default implementation I guess...
     */
    abstract protected function storeData();

    /**
     * make $this->prepared_data_arr
     * from $this->response_collection
     */
    abstract protected function parseResponse();

    protected function checkParamsValidity()
    {
        foreach($this->musthave_params as $param) {
            if (!isset($this->$param)) {
                $this->setError(self::class . ' param "' . $param . '" not set up');
                return false;
            }
        }
        return true;
    }

    protected function preParseResponse()
    {
        $this->response_decoded = json_decode($this->curl_response, true);
        if (!is_array($this->response_decoded['response'])) {
            $msg = 'curl status: '. $this->curl_status . PHP_EOL;
            $msg .= 'curl response: ' . $this->curl_response;
            throw new ParserException($msg);
        }
        $this->response_collection = $this->response_decoded['response'];
    }

    protected function getReportMessage()
    {
        return $this->report_message;
    }

    protected function makeReport()
    {
        $error = $this->getError();
        if (!$error) {
            $cnt = count($this->response_collection);
            $this->report_message = 'Success, it parsed ' . $cnt. ' items.';
        } else {
            $this->report_message = 'Failure, ' . $error;
        }
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

    protected function constructRequestUrl()
    {
        $query_arr = $this->curl_getfields;
        $query_arr['v'] = $this->v;
        $query_arr['offset'] = $this->offset;
        $query_arr['count'] = $this->count;
        if (isset(self::$access_token)) {
            $query_arr['access_token'] = self::$access_token;
        }
        $query_str = http_build_query($query_arr);
        $this->request_url = $this->api_url . '?' . $query_str;       
        return $this->request_url;
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
