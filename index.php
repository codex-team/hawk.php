<?php

require_once './vendor/autoload.php';

$stub = new \Hawk\Tests\Stub();

\Hawk\Catcher::init('token')
    ->enableHandlers();

$stub->test();
