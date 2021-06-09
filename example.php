<?php

/**
 * To run this example script
 *
 * 1. Update integrationToken
 * 2. Ignore url to use production collector
 * 3. Install composer deps: composer install
 * 4. Run the script: php -e example.php
 */

require_once './vendor/autoload.php';

\Hawk\Catcher::init([
    'integrationToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwcm9qZWN0SWQiOiI2MGJmNzFjMmZmODQ5MWFmNWIwZWZiYWUiLCJpYXQiOjE2MjMxNTkyMzR9.dwFU0VTdKsnDDMTKmGUXkxCs0sH6jsj55uPpqCbXBHA',
//    'url'              => 'http://localhost:3000/'
]);

function randStr()
{
    return bin2hex(openssl_random_pseudo_bytes(8));
}

class Test
{
    public function __construct()
    {
    }

    public function test($aTest)
    {
        return self::testStatic($aTest);
    }

    public static function testStatic($aTest)
    {
        return divZero($aTest);
    }
}

$t = new Test();

function divZero($aDiv)
{
    $b = 0;

    $randA =  randStr();

    fail($randA);

    return $aDiv / $b;
}

function fail($numFail)
{
    throw new Exception('Error ' . $numFail . ' at ' . date('j F Y h:i:s'));
}


$t->test(5);
