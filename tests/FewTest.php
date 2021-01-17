<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\Parsers\VkApiAbstractClient;
use App\Models\VkCountry;
use App\Models\VkRegion;
use App\Models\VkCity;
use App\Models\VkLike;
use App\Models\VkUser;
use App\Models\Parsers\Geo\VkCountriesParser;
use App\Models\Parsers\Geo\VkRegionsParser;
use App\Models\Parsers\Geo\VkCitiesParser;
use App\Models\Parsers\VkLikesParser;
use App\Models\Parsers\VkUsersParser;

class FewTest extends TestCase
{

    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        // do not destroy my tables!!!
    }

    public function testRootUrl() {
        $response = $this->call('GET', '/');
        $this->assertEquals(200, $response->status());
        $this->see('Likes GEO');
    }

    public function testAccessToken() {
        $access_token = VkApiAbstractClient::$access_token;
        $api_url = "https://api.vk.com/method/users.get?v=5.21&fields=sex,bdate,city,country,photo,photo_medium,photo_big,contacts,last_seen,status,followers_count,domain,site&user_ids=1,2,3,4,5
&access_token={$access_token}";
        $resp = file_get_contents($api_url);
        $this->assertNotEquals(false, $resp);
        $resp_arr = json_decode($resp, true);
        $this->assertTrue(!array_key_exists('error', $resp_arr));
        $this->assertTrue(array_key_exists('response', $resp_arr));
    }

    public function testVkCountriesParser() {
        $parser = new VkCountriesParser(['lang' => 'en']);
        $msg = $parser->parse();
        $countries_query = VkCountry::limit(300);
        $countries_query = $countries_query->orderBy('title', 'asc');
        $countries = $countries_query->get();
        $countries_cnt = count($countries);
        $this->assertGreaterThan(0, $countries_cnt, "Expected more than 0 countries");
    }

    public function testVkRegionsParser() {
        $parser = new VkRegionsParser(['lang' => 'en', 'country_id' => 1]);
        $msg = $parser->parseVkRegions();
        $query = VkRegion::limit(300);
        $query = $query->orderBy('title', 'asc');
        $regions = $query->get();
        $cnt = count($regions);
        $this->assertGreaterThan(0, $cnt, "Expected more than 0 regions");
    }
    
    public function testVkCitiesParser() {
        // main cities:
        $parser = new VkCitiesParser(['lang' => 'en', 'country_id' => 1, 'need_all' => 0]);
        while($parser->hasJobs()) {
            $msg = $parser->parsePortion(10);
        }
        $query = VkCity::limit(300);
        $query = $query->orderBy('title', 'asc');
        $cities = $query->get();
        $cnt = count($cities);
        $this->assertGreaterThan(0, $cnt, "Expected more than 0 cities");
    }

    public function testVkLikesParser() {
        $vk_url = 'https://vk.com/lovestime?w=wall-36318299_127147';
        $request = new Request;
        $parser = new VkLikesParser($request);
        $likes = $parser->getVkLikes($vk_url);
        $cnt = count($likes);
        $this->assertGreaterThan(0, $cnt, "Expected more than 0 likes");
    }
    
    // and so on...
    
}
