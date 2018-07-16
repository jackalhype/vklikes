<?php

use Illuminate\Support\Facades\Artisan;
use Iluminate\Support\Facades\Mail;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://vklikes.loc';
    private static $appConf = null;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {        
        return self::init();
    }

    public static function init() {        
        if (is_null(self::$appConf)) {
            $app = require __DIR__.'/../bootstrap/app.php';
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            Artisan::call('migrate:reset');
            Artisan::call('migrate');
            // Artisan::call('db:seed');
            self::$appConf = $app;
            
        }

        return self::$appConf;
    }

}
