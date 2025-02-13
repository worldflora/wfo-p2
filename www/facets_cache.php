<?php

require_once('../config.php');

if($facets_cache){
 echo "<pre>";
 print_r($facets_cache);
 echo "</pre>";
}else{
    echo "<p>Not set.</p>";
}