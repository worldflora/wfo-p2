<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/SourceDetails.php');

$provs = json_decode($_GET['prov']);

$out = (object)array();

// we need to get the snippet object incase it has a "citation" field we need so show with the image
$snippet = SolrIndex::getSolrDoc($provs->snippet_id);
if(isset($snippet->citation) && $snippet->citation){
    // use the citation in the metadata
    $citation = $snippet->citation;
}else{
    $citation = 'Meta data';
}

$source = SolrIndex::getSolrDoc($provs->source_id);
$source = json_decode($source->json_t);

$alt_txt = " This image is from .\nClick for more information.";
$alt_escaped = htmlspecialchars($alt_txt, ENT_QUOTES, 'UTF-8');

// render an image!
$image_uri_large = IMAGE_CACHE_URI . 'server/wfo/'. $provs->image_id . '/full/,'. IMAGE_CACHE_SIZES[2] . '/0/default.jpg';
$out->body = "<img src=\"$image_uri_large\" alt=\"{$alt_escaped}\" class=\"img-fluid\" style=\"
    max-height: 100vh;
    margin: -113px 0;
    padding: 113px 0;\" />";


// link to the row in the file
$snippet_prov_data = (object)array(
    'kind' => 'snippet',
    'source_id' => $snippet->id
);

$snippet_prov_json = urlencode(json_encode($snippet_prov_data));


//$out->body .=  "<br/><a href=\"#\">{$citation}</a>";
$out->body .= "<br/><a href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#dataProvModal\" data-wfoprov=\"{$snippet_prov_json}\">{$citation}</a>";

$out->body .=  " from ";

// Link to the source of the original CSV file - the data source
$source_prov_data = (object)array(
    'kind' => 'snippet_source',
    'source_id' => $provs->source_id
);
$source_prov_json = urlencode(json_encode($source_prov_data));

$out->body .= "<a href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#dataProvModal\" data-wfoprov=\"{$source_prov_json}\">{$source->name}</a>";


//$out->body .= print_r($provs, true);

$out->title = $provs->taxon_name;

$out->downloads = "<strong>Sizes: </strong> ";

// we can get the 
$image_uri_info = IMAGE_CACHE_URI . 'server/wfo/'. $provs->image_id . '/info.json';
$data = json_decode(file_get_contents($image_uri_info));

// make a nice name for the downloaded file
$filename = preg_replace('/[^0-9a-zA-Z]/', '_', strip_tags($provs->taxon_name)) . '.jpg';

$sep = "";
foreach ($data->sizes as $size){
     $image_uri_size = IMAGE_CACHE_URI . 'server/wfo/'. $provs->image_id . '/full/,'. $size->height . '/0/default.jpg?download=' . $filename;
     $out->downloads .= "{$sep}<a href=\"{$image_uri_size}\">{$size->width}x{$size->height}px</a>";
     $sep = ', ';
}

//$out->downloads .= print_r($data, true);

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);

