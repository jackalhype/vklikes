<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Controller;
use App\Models\VkRegion;
use App\Models\Parsers\VkUsersParser;
use App\Models\VkCity;
use App\Models\VkUser;

class TestController extends Controller
{
    /**
     * /test
     */
    public function test()
    {
        echo '<pre>';
        echo 'Test !' . PHP_EOL;
        echo date('Y-m-d H:i:s') . PHP_EOL;                

        echo '</pre>';
        
        echo phpinfo();

        die();

    }

    public function testXSS() 
    {
        $data = [];
        return view('test.testxss', $data);
    }

    public function testVkUsersParser()
    {
        echo '<pre>';
        echo 'Test VkUsersParser' . PHP_EOL;

        $uids = [1,2,3,4,333,444,53303331,54441112, 512399988, 57488412, 27279911];
        $parser = new VkUsersParser();
        $parsed_num = $parser->getVkUserProfiles($uids);
        var_dump($parsed_num);
        echo PHP_EOL;
        if (!$parsed_num) {
            var_dump($parser->getError());
            echo PHP_EOL;
        }

        echo '</pre>';
        die();
    }
}
