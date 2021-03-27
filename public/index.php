<?php

require_once './vendor/autoload.php';

$stub = new \Hawk\Tests\Stub();

\Hawk\Catcher::init([
    'accessToken' => 'token',
    'release'     => '123321'
]);

$stub->test();
