<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/Parsedown.php');

// we need to talk to the index
$solr = new SolrIndex();

 // we might be passed a wfo id if we are looking at a name or taxon
$facet_id = @$_GET['facet_id'] ? $_GET['facet_id'] : null;

if(!$facet_id){
    echo "No facet id specified";
    exit;
}

$facet_doc = $solr->getSolrDoc($facet_id);

if(!$facet_doc){
    echo "No facet doc for '{$facet_id}'";
    exit;
}

$facet = json_decode($facet_doc->json_t);

echo "<h3>{$facet->name}</h3>";

$parser = new Parsedown();
$description = $parser->text($facet->description);
echo "<div>{$description}</div>";
if($facet->link_uri) echo "<p class=\"text-end\"><a href=\"{$facet->link_uri}\" target=\"facet-description\">More</a>&nbsp;↗</p>";

echo '<div class="card">';
echo '<div class="card-header bg-secondary-subtle">Facet values <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. count(array_keys((array)$facet->facet_values)) .'</span></div>';

echo '<ul class="list-group" style="max-height: 30em; overflow: auto;">';

foreach ($facet->facet_values as $facet_value) {
    echo '<a href="#"';
    echo ' class="list-group-item list-group-item-action"';
    echo ' data-dismiss="modal"';
    echo ' data-bs-toggle="modal"';
    echo ' data-bs-target="#facetValueDescriptionModal"';
    echo " data-facet-id=\"{$facet->id}\"";
    echo " data-facet-value-id=\"{$facet_value->id}\"";
    echo " >{$facet_value->name}</a>";
}

echo '</ul>';

echo '</div>'; // end card



//echo '<pre>';
//print_r($facet);
//echo '</pre>';

?>
