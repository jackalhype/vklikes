<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VkCountry;
use App\Models\VkRegion;
use App\Models\VkCity;

/**
 * Geo Controller.
 */
class VkGeoController extends Controller
{
    /**
     * @ajax
     */
    public function getCountries(Request $request)
    {
        $q = $request->input('q');
        $q = isset($q) ? $q : '';
        $lang = $request->input('lang');    
        $lang = isset($lang) ? $lang : 'en';
        $countries_query = VkCountry::limit(12);
        $title_col = ($lang === 'en') ? 'title_en' : 'title';
        if ($q) {
            $countries_query = $countries_query->where($title_col, 'LIKE', $q.'%');
        }
        $countries_query = $countries_query->orderBy($title_col, 'asc');
        $countries = $countries_query->get();
        $data = [];
        foreach($countries as $country) {
            $title = $country->$title_col;
            $data[] = [
                'country_id' => $country->cid,
                'title' => $title,
            ];
        }
        return response()->json(['countries' => $data]);
    }

    /**
     * @ajax
     */
    public function getRegions(Request $request)
    {
        $country_id = $request->input('country_id');
        $country_id = $country_id ? $country_id : -1;
        $q = $request->input('q');
        $q = isset($q) ? $q : '';
        $lang = $request->input('lang');    
        $lang = isset($lang) ? $lang : 'en';
        $data = [];
        $query = VkRegion::where('country_id', $country_id)->limit(12);
        $title_col = ($lang === 'en') ? 'title_en' : 'title';
        $query = $query->where($title_col, 'LIKE', $q.'%')
            ->orderBy($title_col, 'ASC');
        $regions = $query->get();
        foreach($regions as $region) {
            $title = ($lang === 'en') ? $region->title_en : $region->title;
            $data[] = [
                'region_id' => $region->region_id,
                'title' => $title,
            ];
        }

        return response()->json(['regions' => $data]);
    }

    /**
     * @ajax
     */
    public function getCities(Request $request)
    {
        $country_id = $request->input('country_id');
        $country_id = isset($country_id) ? $country_id : false;
        $region_id = $request->input('region_id');
        $region_id = isset($region_id) ? $region_id : false;
        $q = $request->input('q');
        $q = isset($q) ? $q : '';
        $lang = $request->input('lang');    
        $lang = isset($lang) ? $lang : 'en';        
        $data = [];
        $query = VkCity::where('country_id', $country_id)->limit(12);
        if ($country_id) {
            $query = $query->where('country_id', $country_id);
        }
        if ($region_id) {
            $query = $query->where('region_id', $region_id);
        }
        $title_col = ($lang === 'en') ? 'title_en' : 'title';
        $query = $query->where($title_col, 'LIKE', $q.'%')
            ->orderBy($title_col, 'ASC');
        $items = $query->get();
        foreach($items as $item) {
            $title = ($lang === 'en') ? $item->title_en : $item->title;
            $data[] = [
                'city_id' => $item->cid,
                'title' => $title,
            ];
        }

        return response()->json(['cities' => $data]);
    }
}
