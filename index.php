<?php

require_once 'vendor/autoload.php';

\Hawk\Catcher::init('sdfsdf')
    ->enableHandlers();

$t = new \Hawk\Testing();
$t->test();