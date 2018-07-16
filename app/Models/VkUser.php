<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VkUser extends Model
{
    protected $table = "vk_users";
    //public $fillable = [];
    protected $guarded = [
        'id'
    ];

    use \App\Models\Traits\DBAdvancedTrait;

    public function getUrl()
    {
        return 'https://vk.com/' . $this->domain;
    }
}
