<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VkCity extends Model
{
    protected $table = 'vk_cities';
    public $fillable = [
        'country_id',
        'region_id',
        'cid',
        'title',
        'title_en',
        'area',
        'region',
    ];

    use \App\Models\Traits\DBAdvancedTrait;
}
