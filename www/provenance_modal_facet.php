<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/SourceDetails.php');

$provs = json_decode($_GET['prov']);

echo '<ul class="list-group  list-group-flush" >';

// Rendering the metadata for a facet score
// this can come from multiple sources and each of those could have
// scored it as different names.

echo '<li class="list-group-item wfo-meta-row" >';
echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Taxon:</div><div class="col">' . $provs->taxon_name . '</div><div>';
echo '</li>';

echo '<li class="list-group-item gx-1 wfo-meta-row" >';

// the facet value link provides a description of the facet value
if($provs->facet_value->link){
    $facet_value_name = "<a target=\"attributes\" href=\"{$provs->facet_value->link}\">{$provs->facet_value->name}</a>";
}else{
    $facet_value_name = $provs->facet_value->name;
}

echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Attribute:</div><div class="col">'."{$provs->facet_name} - {$facet_value_name}".'</div><div>';
echo '</li>';

// echo "<pre>";
// print_r($provs);
//    echo "</pre>";
    

// now we render the data sources
$counter = 1;
foreach($provs->facet_value->provenance as $prov){

    
    // prov string looks something like this wfo-0000408573-s-1560-direct

    echo '<li class="list-group-item wfo-meta-row" >';

    $matches = array();
    preg_match('/(wfo-[0-9]{10})-s-([0-9]+)-(.+)/', $prov, $matches);

    $source = new SourceDetails($matches[2]);
    $record = new TaxonRecord($matches[1]);
    

    // linking to the score metadata - which will be in the solr index somewhere..
    // The id is wfo-fvs-wfo-0123456789-1-2  where 1 is the source_id and 2 is the value ids in the database. 

    $wfo_id = $matches[1];
    $source_id = $matches[2];
    $facet_value_id = str_replace('wfo-fv-', '', $provs->facet_value->id); // just the integer
    $score_metadata_id = "wfo-fvs-{$wfo_id}-{$source_id}-{$facet_value_id}";

    $score_provs = (object)array(
        'kind' => 'facet_value_score',
        'source_id' => $score_metadata_id,
        'facet_provs' => $provs // we include the facet provs so we can link back to this modal display.
    );

    $score_provs_json = urlencode(json_encode($score_provs));

    $source_link = $source->getLink($record->getId());

    echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Data Source ';
    echo $counter;
    echo ':</div><div class="col">';
    if($source_link){
        echo "<em><a target=\"wfo-source\" href=\"{$source_link}\">{$source->getName()}</a></em>";
    }else{
        echo "<em>{$source->getName()}</em>";
    }

    switch ($matches[3]) {
        case 'direct':
            echo '&nbsp;- <a href="#" data-dismiss="modal" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">directly scored the taxon</a>';
            break;
        case 'synonym':
            echo '&nbsp;- <a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">scored the synonym</a>';
            echo "&nbsp;<a target=\"wfo-plantlist\" href=\"https://list.worldfloraonline.org/{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
            break;
        case 'ancestor':
            echo '&nbsp;- <a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $score_provs_json . '" style="cursor: pointer;">scored the ancestor</a>';
            echo "&nbsp;<a target=\"wfo-plantlist\" href=\"https://list.worldfloraonline.org/{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
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