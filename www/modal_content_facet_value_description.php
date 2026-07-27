<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/Parsedown.php');

// we need to talk to the index
$solr = new SolrIndex();

 // we might be passed a wfo id if we are looking at a name or taxon
$facet_id = @$_GET['facet_id'] ? $_GET['facet_id'] : null;
$facet_value_id = @$_GET['facet_value_id'] ? $_GET['facet_value_id'] : null;

if(!$facet_id){
    echo "No facet id specified";
    exit;
}

if(!$facet_value_id){
    echo "No facet value id specified";
    exit;
}

$facet_doc = $solr->getSolrDoc($facet_id);

if(!$facet_doc){
    echo "No facet doc for '{$facet_id}'";
    exit;
}

$facet = json_decode($facet_doc->json_t);

$facet_value = null;
foreach ($facet->facet_values as $fv) {
    if($fv->id == $facet_value_id){
        $facet_value = $fv;
        break;
    }
}

if(!$facet_value){
    echo "No facet value {$facet_value_id}'";
    exit;
}

echo "<h3>{$facet_value->name}</h3>";

$parser = new Parsedown();
$description = $parser->text($facet_value->description);

echo "<div>{$description}</div>";
if($facet_value->link_uri) echo "<p class=\"text-end\"><a href=\"{$facet_value->link_uri}\" target=\"facet-description\">More</a>&nbsp;↗</p>";


//echo '<pre>';
//print_r($facet);
//echo '</pre>';

?>
