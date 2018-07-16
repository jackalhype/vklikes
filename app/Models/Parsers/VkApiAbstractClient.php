<?php

namespace App\Models\Parsers;

abstract class VkApiAbstractClient
{
    // create vk app and use the token serverside, dont pass it to the browser.
    // https://vk.com/editapp?act=create
    public static $access_token = 'a1fc2918a1fc2918a1fc291825a19927d0aa1fca1fc2918fac7427e343d33f3dcac9dc9';
    
    // default api version
    protected $v = '4.100';
    
}
