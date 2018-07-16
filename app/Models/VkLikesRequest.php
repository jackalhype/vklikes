<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VkLikesRequest extends Model
{
    protected $table = 'vklikes_requests';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
