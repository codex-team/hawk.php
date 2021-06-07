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
    'integrationToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwcm9qZWN0SWQiOiI2MGJhODIyNzM1M2MyNzAwMjMxMWE1MzEiLCJpYXQiOjE2MjI4MzU3NTF9.uoKJEwd62N7SfWCTQfSrFuor8xgKCq2WPCPeMDPBHVU',
    'url'              => 'http://localhost:3000/'
]);

function randStr()
{
    return bin2hex(openssl_random_pseudo_bytes(8));
}


function test($aTest)
{
    return divZero($aTest);
}

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


test(15);
