<?php

use Illuminate\Http\Request;

Route::group(['domain' => ''.env('APP_DOMAIN')], function() {
    Route::get('/', ['uses' => 'VkLikesController@main', 'as' => 'likes']);
    // Route::get('/', ['uses' => 'VkSmmController@filters', 'as' => 'smm.filters']);
    Route::post('/ajax/filterIds', ['uses' => 'VkSmmController@filterIds', 'as' => 'smm.filterIds']);    
    Route::get('/ajax/showLikes', ['uses' => 'VkLikesController@showLikes', 'as' => 'ajax/showLikes'])->middleware('throttle:60');
    
    Route::get('/ajax/vk/countries.get', ['uses' => 'VkGeoController@getCountries', 'as' => 'vk.countries.get']);
    Route::get('/ajax/vk/regions.get', ['uses' => 'VkGeoController@getRegions', 'as' => 'vk.regions.get']);
    Route::get('/ajax/vk/cities.get', ['uses' => 'VkGeoController@getCities', 'as' => 'vk.cities.get']);
});

