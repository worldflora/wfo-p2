<?php
/*
    A simple utility that allows an ajax call to generate a
    decorated link to a name/taxon page
    - we can pass around IDs not the full HTML thing
*/

require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');

$id = @$_GET['id'];

if(!$id){
    echo 'NOT FOUND'; 
    exit;
}
$record = new TaxonRecord($id);

echo "<a href=\"{$record->getId()}\">{$record->getFullNameStringHtml()}</a>";
