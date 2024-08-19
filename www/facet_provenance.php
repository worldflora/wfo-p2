<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/SourceDetails.php');

$provs = json_decode($_GET['prov']);

//echo '<pre>';
//print_r($provs);
//echo '</pre>';

echo '<ul class="list-group  list-group-flush" >';

echo '<li class="list-group-item" >';
echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Taxon:</div><div class="col">' . $provs->taxon_name . '</div><div>';
echo '</li>';

echo '<li class="list-group-item gx-1" >';

if($provs->facet_value->link){
    $facet_value_name = "<a target=\"attributes\" href=\"{$provs->facet_value->link}\">{$provs->facet_value->name}</a>";
}else{
    $facet_value_name = $provs->facet_value->name;
}

echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">Attribute:</div><div class="col">'."{$provs->facet_name} - {$facet_value_name}".'</div><div>';
echo '</li>';


$counter = 1;
foreach($provs->facet_value->provenance as $prov){

    echo '<li class="list-group-item" >';

    $matches = array();
    preg_match('/(wfo-[0-9]{10})-s-([0-9]+)-(.+)/', $prov, $matches);

    $source = new SourceDetails($matches[2]);
    
    if($source->getLink()){
        echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">DS ';
        echo $counter;
        echo ':</div><div class="col">';
        echo "<em><a target=\"wfo-source\" href=\"{$source->getLink()}\">{$source->getName()}</a></em>";
    }else{
        echo '<div class="row gx-1"><div class="col-2 text-end fw-bold">DS ';
        echo $counter;
        echo ':</div><div class="col">';
        echo "<em>{$source->getName()}</em>";
    }

    $record = new TaxonRecord($matches[1]);

    switch ($matches[3]) {
        case 'direct':
            echo "&nbsp;- directly scored the taxon.";
            break;
        case 'synonym':
            echo "&nbsp;- scored the synonym <a target=\"wfo-plantlist\" href=\"https://list.worldfloraonline.org/{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
            break;
        case 'ancestor':
             echo "&nbsp;- scored the ancestor <a target=\"wfo-plantlist\" href=\"https://list.worldfloraonline.org/{$record->getId()}\">{$record->getFullNameStringHtml()}</a>.";
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