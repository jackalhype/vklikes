<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Demands collectied with landing contact form.
 */
class Demand extends Model
{
    protected $table = 'demands';
    public $fillable = [
        'name',
        'email',
        'description',
        'ip', 
        'browser_info', 
        'status',
        'comment',
        'page_url',
    ];
}
