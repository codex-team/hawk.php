<?php
    require_once 'hawk.php';
    HawkErrorManager::init();

    $a = 1 / 0;
    $b = str_replace('test', 'test');

    throw new Exception('Exception');
?>

