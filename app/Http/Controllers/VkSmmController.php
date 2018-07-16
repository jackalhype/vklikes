<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parsers\VkUsersParser;
use View;
use DB;

class VkSmmController extends Controller
{
    const TILES_PER_PAGE = 30;

    /**
     * Фильтры на группы ретаргетинга.
     */
    public function filters(Request $request)
    {
        $data = [];
        return view('vklikes.smm', $data);
    }

    /**
     * filter vk ids
     */
    public function filterIds(Request $request)
    {
        $country_id = $request->input('country_id');
        $country_id = $country_id ? $country_id : false;
        $region_id = $request->input('region_id');
        $city_id = $request->input('city_id');
        $vklikes_request_id = $request->input('vklikes_request_id');

        if (!$vklikes_request_id) {
            // 1st request, need fill db from vk.
            $vkUsersParser = new VkUsersParser([
                'init_record_table' => $init_model->getTable(),
                'init_record_id'    => $init_model->id,
            ]);
            $vkUsersParser->getVkUserProfiles($vk_likes_ids);
        }

        $response = [
            'success' => true,
            'vklikes_request_id' => $vklikes_request_id,
            'items_num' => $items_num,
            'ids' => $ids_filtered,
        ];

        return response()->json($response);
    }

}
