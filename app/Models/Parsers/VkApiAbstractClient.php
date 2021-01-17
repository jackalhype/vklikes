<?php

namespace App\Models\Parsers;

abstract class VkApiAbstractClient
{
    // create vk app and use the token serverside, dont pass it to the browser.
    // https://vk.com/editapp?act=create
    # public static $access_token = 'a1fc2918a1fc2918a1fc291825a19927d0aa1fca1fc2918fac7427e343d33f3dcac9dc9';
    public static $access_token = '4e62ba1a4e62ba1a4e62ba1a514e1743df44e624e62ba1a2e66274e714c09c53a67ae2b';

    // default api version
    # protected $v = '4.100';
    protected $v = '5.21';
    
}
