<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VkCountry extends Model
{
    protected $table = 'vk_countries';
    protected $guarded = [
        'id',
    ];

    use \App\Models\Traits\DBAdvancedTrait;
}
