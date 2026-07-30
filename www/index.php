<?php

// common header file included in all pages
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/FacetDetails.php');
require_once('../includes/render_functions.php');

// config variables
require_once('../config.php');

// this is the landing page that parses all the other calls
$path = parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH);
$path_parts = explode('/', $path);
array_shift($path_parts); // lose the first always blank one

// some things we never handle
if($path_parts[0] == 'js') return false;
if($path_parts[0] == 'theme') return false;
if($path_parts[0] == 'downloads') return false;
if($path_parts[0] == 'style') return false;
if($path_parts[0] == 'data') return false;

// images
if(preg_match('/\.jpg$/i', $path)) return false;
if(preg_match('/\.jpeg$/i', $path)) return false;
if(preg_match('/\.png$/i', $path)) return false;
if(preg_match('/\.gif$/i', $path)) return false;


// other things are handled by specific scripts
if(preg_match('/^wfo-[0-9]{10}/', $path_parts[0])){
    // we are viewing a name or taxon
    require_once('record.php');
}elseif($path_parts[0] == 'search'){
    require_once('search.php');
}elseif($path_parts[0] == 'pages'){
    require_once('page.php');
}elseif($path_parts[0] == 'csv'){
    require_once('csv.php');
}elseif(!$path_parts[0]){
    // all else fails render the home page
    require_once('home.php');
}else{
    // finally return false for anything else
    return false;
}

