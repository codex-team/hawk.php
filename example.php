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
    'integrationToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwcm9qZWN0SWQiOiI2MDY2ZWI5N2U3NTU2ZDAwMjM2M2UyNjYiLCJpYXQiOjE2MTczNTc3MTl9.OpelHPPvS_TB8wUqCHRzcO3-Cp1VNL0UzlFuMfR35tk',
    'release'          => '12345',
    'url'              => 'http://localhost:3000/'
]);

$a = 1 / 0;
