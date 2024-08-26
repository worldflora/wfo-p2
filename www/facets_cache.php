<?php

$facets_cache = @$_SESSION['facets_cache'];

if($facets_cache){
 echo "<pre>";
 print_r($facets_cache);
 echo "</pre>";
}else{
    echo "<p>Not set.</p>";
}