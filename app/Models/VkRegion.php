<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VkRegion extends Model
{
    protected $table = 'vk_regions';
    public $fillable = [
        'country_id',
        'region_id',
        'title',
        'title_en',
    ];

    use \App\Models\Traits\DBAdvancedTrait;
}
