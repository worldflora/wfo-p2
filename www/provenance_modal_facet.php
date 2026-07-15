<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/SourceDetails.php');
require_once('../includes/FacetDetails.php');

$provs = json_decode($_GET['prov']);

$facet_details = new FacetDetails($provs->facet_id);

echo '<ul class="list-group  list-group-flush" >';

// Rendering the metadata for a facet score
// this can come from multiple sources and each of those could have
// scored it as different names.


echo '<li class="list-group-item wfo-meta-row" >';
echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Taxon:</div><div class="col">' . $provs->taxon_name . '</div><div>';
echo '</li>';

echo '<li class="list-group-item gx-1 wfo-meta-row" >';

// the facet value link provides a description of the facet value
if($facet_details->getFacetValueLink($provs->facet_value->facet_value_id)){
    $facet_value_name = "<a target=\"attributes\" href=\"{$facet_details->getFacetValueLink($provs->facet_value->id)}\">{$provs->facet_value->name}</a>";
}else{
    $facet_value_name = $provs->facet_value->facet_value_name;
}

echo '<div class="row gx-1">';
echo '<div class="col-2 text-end fw-bold">Attribute:</div>';
echo '<div class="col">';
echo "<div>{$provs->facet_name} - {$facet_value_name}</div>";
//echo "<div>{$facet_details->getFacetDescription()}</div>";
echo "<div>{$facet_details->getFacetValueDescription($provs->facet_value->facet_value_id)}</div>";
echo '</div><div>';
echo '</li>';

// echo "<pre>";
 //print_r($provs);
 // echo "</pre>";
    

// now we render the data sources
$counter = 1;
foreach($provs->facet_value->sources as $prov){

    
    // prov string looks something like this wfo-0000408573-s-1560-direct

// echo "<pre>";
// print_r($prov);
// echo "</pre>";


    echo '<li class="list-group-item wfo-meta-row" >';

    $record = new TaxonRecord($prov->scored_wfo_id);

    //$facet_value_id = str_replace('wfo-fv-', '', $provs->facet_value->id); // just the integer
    //$score_metadata_id = "wfo-fvs-{$wfo_id}-{$source_id}-{$facet_value_id}";

    $score_provs_json = urlencode(json_encode($prov));

//    $source_link = $source->getLink($record->getId());

    echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Data Source ';
    echo $counter;
    echo ':</div><div class="col">';
    echo "<em><a target=\"wfo-source\" href=\"{$prov->source_name}\">{$prov->source_name}</a></em>";
    
    switch ($prov->scored_via) {
        case 'direct':
            echo '&nbsp;- <a href="#" data-dismiss="modal" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">directly scored the taxon</a>';
            break;
        case 'synonym':
            echo '&nbsp;- <a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">scored the synonym</a>';
            echo "&nbsp;<a target=\"wfo-plantlist\" href=\"{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
            break;
        case 'ancestor':
            echo '&nbsp;- <a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">scored the ancestor</a>';
            echo "&nbsp;<a target=\"wfo-plantlist\" href=\"{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
            break;
        default:
            $phrase = 'unrecognised';
            break;
    }
    echo '</div><div>';
    echo '</li>';

    $counter++;

}


echo '</ul>'; // end list group

?>