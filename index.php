<?php
    require_once 'hawk.php';
    HawkErrorManager::init();

    echo "Script started";
    $a = 1 / 0;
//    $b = str_replace('test', 'test');

//    throw new Exception('Exception');
    echo "Script finished";
?>
