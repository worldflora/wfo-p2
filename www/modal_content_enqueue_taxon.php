<?php

require_once('../config.php');

$wfo = trim($_GET['wfo']);
if(preg_match('/^wfo-[0-9]{10}$/', $wfo)){
    file_put_contents(INDEX_QUEUE_FILE_PATH, "$wfo\n", FILE_APPEND | LOCK_EX);
    echo "Queued" ;
}else{
    echo "Not a WFO ID";
}
