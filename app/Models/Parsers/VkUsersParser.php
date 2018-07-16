<?php

namespace App\Models\Parsers;

use App\Models\VkUser;
use App\Models\Parsers\VkApiAbstractClient;

/**
 * class for retrieving vk user profiles.
 *
 * basic usage:
 * $parser = new VkUsersParser($params);
 * $parser->getVkUserProfiles($uids);
 */
class VkUsersParser extends VkApiAbstractClient
{
    protected $uids = [];
    protected $profiles_total_num;
    protected $profiles_parsed_num = 0;

    protected $api_url = 'https://api.vk.com/method/users.get';
    protected $v = '4.1';
    protected $count = 1000; // per 1 request
    protected $offset = 0;
    protected $max_requests_num = 10;
    
    protected $request_url;

    protected $error = false;

    protected $curl_requests_count = 0;
    protected $curl_postfields;

    protected $init_record_table;
    protected $init_record_id;

    protected $prepared_data_arr = []; // prepared data, ready for storage

    public function __construct(array $params = [])
    {
        $this->init_record_table = isset($params['init_record_table'])
            ? $params['init_record_table'] : null;     // e.g. vklikes_requests

        $this->init_record_id = isset($params['init_record_id'])
            ? $params['init_record_id'] : null;
    }

    /**
     * Parse vk user profiles.
     * rtfm: https://new.vk.com/dev/users.get
     *
     * @return int profiles parsed number
     */
    public function getVkUserProfiles(array $uids)
    {
        $this->resetClassForNewParsing($uids);

        $iterations_num = min(ceil($this->profiles_total_num / $this->count), $this->max_requests_num);
        for ($i = 1; $i <= $iterations_num; $i++) {
            $this->constructRequestUrl($i);
            $this->performRequest();
            if (!$this->parseResponse()) {
                return $this->profiles_parsed_num;
            }
            $this->storeData();
        }

        return $this->profiles_parsed_num;
    }

    protected function resetClassForNewParsing($uids)
    {
        $this->uids = $uids;
        $this->profiles_total_num = count($uids);
        $this->profiles_parsed_num = 0;
        $this->error = false;
        $this->curl_requests_count = 0;
    }

    protected function storeData()
    {
        VkUser::insertOrUpdate($this->prepared_data_arr);
        $this->profiles_parsed_num += count($this->prepared_data_arr);
    }

    protected function parseResponse()
    {
        if ($this->curl_status != 200) {
            $this->setError('VK API request failed. HTTP Status returned: ' . $this->curl_status);
            return false;
        }
        $response = json_decode($this->curl_response, true);
        if (!isset($response['response'])) {
            $this->setError('VK API request failed: Unknown response format.');
            return false;
        }
        $users_data = $response['response'];
        $this->prepared_data_arr = [];
        foreach($users_data as $data) {
            $fields = [];
            $fields['uid'] = $data['uid'];
            $fields['first_name'] = isset($data['first_name']) ? $data['first_name'] : null;
            $fields['last_name'] = isset($data['last_name']) ? $data['last_name'] : null;
            $fields['deactivated'] = isset($data['deactivated']) ? $data['deactivated'] : false;
            $fields['hidden'] = isset($data['hidden']) ? $data['hidden'] : false;
            $fields['sex'] = isset($data['sex']) ? $data['sex'] : 0;
            $fields['bdate'] = null;
            $fields['birthdate'] = null;
            $fields['birth_month_day'] = null;
            if (isset($data['bdate'])) {
                // bdate examples: 24.2.1985, 5.2, 10.10.1984
                $fields['bdate'] = $bdate = $data['bdate'];
                $bdate_parts = explode('.', $bdate);
                $day = isset($bdate_parts[0]) ?  sprintf("%02d", $bdate_parts[0]) : '';
                $month = isset($bdate_parts[1]) ?  sprintf("%02d", $bdate_parts[1]) : '';
                $year = isset($bdate_parts[2]) ?  sprintf("%04d", $bdate_parts[2]) : '';
                if ($year) {
                    $fields['birthdate'] = $year.'-'.$month.'-'.$day;
                }
                $fields['birth_month_day'] = $month . '.' . $day;
            }
            $fields['city_id'] = isset($data['city']) ? $data['city'] : null;
            $fields['country_id'] = isset($data['country']) ? $data['country'] : null;
            $fields['photo'] = isset($data['photo']) ? $data['photo'] : null;
            $fields['photo_medium'] = isset($data['photo_medium']) ? $data['photo_medium'] : null;
            $fields['photo_big'] = isset($data['photo_big']) ? $data['photo_big'] : null;
            $fields['contacts'] = isset($data['contacts']) ? $data['contacts'] : null;
            $fields['last_seen_time'] = isset($data['last_seen']['time']) ? date('Y-m-d H:i:s', $data['last_seen']['time']) : null;
            $fields['last_seen_platform'] = isset($data['last_seen']['platform']) ? $data['last_seen']['platform'] : null;
            $fields['followers_count'] = isset($data['followers_count']) ? $data['followers_count'] : null;
            $fields['domain'] = isset($data['domain']) ? $data['domain'] : null;
            $fields['site'] = isset($data['site']) ? $data['site'] : null;
            $fields['updating_record_table'] = $this->init_record_table;
            $fields['updating_record_id'] = $this->init_record_id;
            $fields['updated_at'] = date('Y-m-d H:i:s');
            $this->prepared_data_arr[] = $fields;
        }
        return true;
    }

    protected function performRequest()
    {
        $this->curl_response = false;
        $this->curl_status = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->request_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->curl_postfields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->curl_response = curl_exec($ch);
        $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->curl_requests_count++;
    }

    /**
     * @param iteration int
     */
    protected function constructRequestUrl($iteration)
    {
        $url = $this->api_url;
        $count = $this->count;
        $offset = ($iteration - 1) * $count;
        $uids = array_slice($this->uids, $offset, $count);
        $user_ids_str = implode(',', $uids);
        $this->curl_postfields = 'user_ids=' . $user_ids_str;
        $query_arr = [
            'v' => $this->v,
            'fields' =>'sex,bdate,city,country,photo,photo_medium,photo_big,contacts,last_seen,status,followers_count,domain,site',
            //'user_ids' => $user_ids_str,
        ];
        if (isset(self::$access_token)) {
            $query_arr['access_token'] = self::$access_token;
        }
        $query_str = http_build_query($query_arr);
        $this->request_url = $url . '?' . $query_str;
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
