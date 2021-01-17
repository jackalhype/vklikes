<?php

namespace App\Models\Parsers;

use Illuminate\Http\Request;
use App\Models\VkLikesRequest;
use App\Models\VkLike;
use App\Models\Parsers\ParserException;
# use Illuminate\Database\Eloquent\Model;
use App\Models\Parsers\VkApiAbstractClient;

/**
 * class for parsing VK Likes.
 *
 * basic usage:
 * $parser = new VkLikesParser($request);
 * $likes = $parser->getVkLikes($vk_url);
 * $init_model = $parser->getInitModel();
 */
class VkLikesParser extends VkApiAbstractClient
{
    protected $request; // initiating user request.

    protected $vk_url; // vk post URL which we parse;

    protected $api_url = 'https://api.vk.com/method/likes.getList';

    // required
    protected $types = [
        'post',  // — post on user or community wall
        'comment', // — comment on a wall post
        'photo', // — photo
        'audio', // — audio
        'video', // — video
        'note',  // — note
        'photo_comment', // — comment on the photo
        'video_comment', // — comment on the video
        'topic_comment', // — comment in the discussion
        'sitepage',
    ];
    protected $owner_id;    // let's say, wall id.
    protected $item_id;     // let's say, post id.

    protected $count = 1000;
    protected $offset = 0;
    protected $filters = ['likes', 'copies']; // defaults to likes
    protected $filter = 'likes';

    /**
     * if so many likes, I dont need them all :P
     * 10 * 1000 = 10000 likes, which is pretty rare.
     */
    protected $max_requests_num = 10;

    protected $error = false;

    protected $request_url;
    protected $curl_response;
    protected $curl_status;
    protected $curl_error;
    protected $curl_requests_count = 0;

    protected $vk_user_count;
    protected $vk_user_ids = []; // total overall collection

    protected $init_model;  // e.g. VkLikesRequest

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function resetClassForNewParsing($vk_url)
    {
        $this->vk_url = $vk_url;
        $this->vk_user_ids = [];
        $this->vk_user_count = 0;
        $this->curl_requests_count = 0;
        $this->error = false;
    }

    /**
     * Get post likes ids.
     * Save to DB.
     * https://new.vk.com/dev/likes.getList
     *
     * @returns array of vk user ids or false
     */
    public function getVkLikes($vk_url)
    {
        $this->resetClassForNewParsing($vk_url);
        if (!$this->parseVkUrl($vk_url)) {
            return false;
        }
        $this->constructRequestUrl();        
        $this->performRequest();
        if (!$this->parseResponse()) {
            return false;
        }
        if ($this->vk_user_count > $this->offset + $this->count) {
            //need some more iterations
            $iterations_num = min(ceil($this->vk_user_count / $this->count) - 1, $this->max_requests_num);
            for ($i = 1; $i <= $iterations_num; $i++) {
                $this->offset += $this->count;
                $this->constructRequestUrl();
                $this->performRequest();
                if (!$this->parseResponse()) {
                    // we already got something to show
                    $this->storeResults();
                    return $this->vk_user_ids;
                }
            }
        }
        $this->storeResults();
        return $this->vk_user_ids;
    }

    public function getInitModel()
    {
        return $this->init_model;
    }

    /**
     * Store results to DB
     */
    protected function storeResults()
    {
        $ip = $this->request->ip();
        $port = isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '';
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $vkLikesRequest = new VkLikesRequest;
        $fields = [
            'vk_url' => $this->vk_url,
            'filter' => $this->filter,
            'total_users' => $this->vk_user_count,
            'requests_count' => $this->curl_requests_count,
            'is_error' => (boolean) $this->error,
            'error_str' => (string) $this->error,
            'user_session_id' => 'FIX_ME',
            'user_http_user_agent' => $browser,
            'user_remote_addr' => $ip,
            'user_remote_port' =>  $port,
        ];
        $vkLikesRequest->fill($fields);
        $vkLikesRequest->save();
        $this->init_model = $vkLikesRequest;

        //----
        $vklikes_request_id = $vkLikesRequest->id;
        $vklike_insert_fields = [];
        foreach($this->vk_user_ids as $vk_uid) {
            $vklike_insert_fields[] = [
                'vklikes_request_id' => $vklikes_request_id,
                'vk_uid' => $vk_uid,
            ];
        }
        VkLike::insert($vklike_insert_fields);
    }

    protected function parseResponse()
    {
        $response = json_decode($this->curl_response, true);
        if ((!isset($response['response']['count']) || !isset($response['response']['items'])) && $this->offset < $this->count ){
            if (isset($response['error']['error_msg'])) {
                $this->setError($response['error']['error_msg']);
            } else {
                //$this->setError($this->request_url);
                $this->setError('VK API request failed.');
            }
            //~ throw new ParserException(print_r($this->request_url, true));
            return false;
        }
        $this->vk_user_count = (int) $response['response']['count'];
        $this->vk_user_ids = array_merge($this->vk_user_ids, $response['response']['items']);
        return true;
    }

    protected function performRequest()
    {
        $this->curl_response = false;
        $this->curl_status = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->request_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->curl_response = curl_exec($ch);
        $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->curl_error = curl_error($ch);
        curl_close($ch);
        $this->curl_requests_count++;
    }

    /**
     * Example:
     * https://vk.com/lovestime?w=wall-36318299_127147
     * https://vk.com/wall-36318299_127147
     */
    protected function parseVkUrl($vk_url)
    {
        preg_match('/(wall-)([0-9]+)_([0-9]+)$/', $vk_url, $matches);
        if (!isset($matches[3])) {
            $this->setError('unknown URL format');
            return false;
        }
        $this->owner_id = '-' . $matches[2];
        $this->item_id = $matches[3];
        return true;
    }

    /**
     * https://api.vk.com/method/likes.getList?type=post&owner_id=-57846937&item_id=2927464&count=1000&offset=0
     *
     * TODO: recognize the others besides 'type' => 'post'
     */
    protected function constructRequestUrl()
    {
        $url = $this->api_url;
        $query_arr = [
            'type' => 'post',
            'owner_id' => $this->owner_id,
            'item_id' => $this->item_id,
            'filter' => $this->filter,
            'count' => $this->count,
            'offset' => $this->offset,
            'v' => $this->v,
        ];
        if (self::$access_token) {
            $query_arr['access_token'] = self::$access_token;
        }
        $query_str = http_build_query($query_arr);        
        return $this->request_url = $url . '?' . $query_str;
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
