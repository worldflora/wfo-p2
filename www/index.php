<?php

// common header file included in all pages
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/FacetDetails.php');
require_once('../includes/render_functions.php');

// config variables
require_once('../config.php');

// this is the landing page that parses all the other calls
$path_parts = explode('/', parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH));
array_shift($path_parts); // lose the first always blank one

if(preg_match('/^wfo-[0-9]{10}/', $path_parts[0])){
    // we are viewing a name or taxon
    require_once('record.php');
}elseif($path_parts[0] == 'search'){
    require_once('search.php');
}elseif($path_parts[0] == 'about'){
    require_once('about.php');
}elseif($path_parts[0] == 'news'){
    require_once('news.php');
}elseif($path_parts[0] == 'contribute'){
    require_once('contribute.php');
}elseif($path_parts[0] == 'csv'){
    require_once('csv.php');
}else{
    // all else fails render the home page
    require_once('home.php');
}