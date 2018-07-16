<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parsers\VkLikesParser;
use App\Models\Parsers\VkUsersParser;
use App\Models\VkLikesRequest;
use App\Models\VkLike;
use App\Models\VkUser;
use App\Models\VkCity;
use View;
use DB;

class VkLikesController extends Controller
{
    const TILES_PER_PAGE = 30;

    /**
     * Display form for VK URLs to analize with submit button
     */
    public function main(Request $request)
    {
        $data = [];
        return view('vklikes.main', $data);
    }

    /**
     * @ajax
     * @return json with response as html and some params
     */
    public function showLikes(Request $request)
    {
        $page = $request->input('page');
        $page = $page ? $page : 1;
        $vk_url = $request->input('vk_url');
        $country_id = $request->input('country_id');
        $country_id = $country_id ? $country_id : false;
        $region_id = $request->input('region_id');
        $city_id = $request->input('city_id');
        $vklikes_request_id = $request->input('vklikes_request_id');
        if (!$vk_url) {
            return response()->json(['success' => false, 'error' => 'No VK URL provided']);
        }
        if (!$vklikes_request_id) {
            // usually, it's the 1st request for a given vk-post.
            // all the heavy job goes here.
            // lots of cURLs, at least 2.
            //
            // 1. gaining likes
            $vkLikesParser = new VkLikesParser($request);            
            $vk_likes_ids = $vkLikesParser->getVkLikes($vk_url);
            if ($vk_likes_ids === false) {                
                return response()->json(['success' => false, 'error' => $vkLikesParser->getError()]);
            }
            $init_model = $vkLikesParser->getInitModel();
            $vklikes_request_id = $init_model->id;
            // 2. gaining user profiles
            $vkUsersParser = new VkUsersParser([
                'init_record_table' => $init_model->getTable(),
                'init_record_id'    => $init_model->id,
            ]);
            $vkUsersParser->getVkUserProfiles($vk_likes_ids);
        } else { // no need to parse, just restore from db.
            $init_model = VkLikesRequest::where('id', $vklikes_request_id)->first();
        }
        $offset = ($page - 1) * self::TILES_PER_PAGE;
        $limit = self::TILES_PER_PAGE;
        $vk_users_query = DB::table(with(new VkLike)->getTable() . ' as l')
            ->join(with(new VkUser)->getTable() . ' as u', function($join) {
                $join->on('l.vk_uid', '=', 'u.uid');
            })->where('l.vklikes_request_id', $vklikes_request_id)
            ->orderBy('u.last_seen_time', 'desc')
            ->orderBy('u.uid', 'desc');
        if ($country_id) {
            $vk_users_query = $vk_users_query->where('u.country_id', $country_id);
        }
        if ($city_id) {
            $vk_users_query = $vk_users_query->where('city_id', $city_id);
        } elseif ($region_id) {
            $city_ids_query = VkCity::select('cid')
                ->where('country_id', $country_id)
                ->where('region_id', $region_id)
                ->orderBy('cid', 'ASC');
            $city_ids = $city_ids_query
                ->pluck('cid')
                ->toArray();
            $vk_users_query = $vk_users_query->whereIn('city_id', $city_ids);
        }
        $vk_users_query = $vk_users_query->offset($offset)->limit($limit);
        $sql = $vk_users_query->toSql();
        $vk_users = $vk_users_query->get();
        //print_r($vk_users);die;
        $items_num = count($vk_users);
        $html = View::make('vklikes.users-tile', ['vk_users' => $vk_users])->render();
        $response = [
            'success' => true,
            'vklikes_request_id' => $vklikes_request_id,
            'items_num' => $items_num,
            //'sql'      => $sql,
            //'city_ids' => $city_ids,
            'response' => $html,
        ];

        return response()->json($response);
    }
}
