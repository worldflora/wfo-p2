<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/SourceDetails.php');

$provs = json_decode($_GET['prov']);

echo '<ul class="list-group  list-group-flush" >';


// rendering a solr metadata document from the index
// this is a generic thing 

$index = new SolrIndex();
$meta = $index->getSolrDoc($provs->source_id);

if($meta){
    $meta = json_decode($meta->json_t);
    foreach($meta as $key => $value){

        // turn values into links
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = "<a href=\"$value\" target=\"provenance\" >$value</a>";
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $value = "<a href=\"mailto:$value\" >$value</a>";
        }

        echo '<li class="list-group-item wfo-meta-row" >';
        echo '<div class="row gx-1">';
        
        echo '<div class="col-3 text-end fw-bold">';

        $key = str_replace('_', ' ', $key);
        echo $key;
        echo ':</div>';

        echo '<div class="col">';
        echo $value;
        echo '<div>';
        
        echo '</div>'; // end row
        echo '</li>';
    }
}else{
    echo '<li class="list-group-item" >';
    echo '<div class="row gx-1"><pre>';
    echo 'Source metadata not found for ' . $provs->source_id;
    echo '</pre><div>';
    echo '</li>';
}

// if we were passed a facet_prov object then render a link back to that modal
if(isset($provs->facet_provs)){
    echo '<li class="list-group-item wfo-meta-row" >';
    echo '&#8678; <a href="#" data-bs-toggle="modal" data-bs-target="#facetProvModal" data-wfoprov="'. urlencode(json_encode($provs->facet_provs)) .'" style="cursor: pointer;">';
    echo 'Back to facet provenance. ';
    echo '</a>';
    echo '</li>';
}

echo '</ul>'; // end list group

?>