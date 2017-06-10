<?php
    require_once 'hawk.php';
    HawkErrorManager::init();

    echo "Script started";

    $a = 1 / 0;

    echo "Script finished";
?>
