<?php
/*
    A simple utility that allows an ajax call to generate a
    decorated link to a name/taxon page
    - we can pass around IDs not the full HTML thing
*/

require_once('../config.php');
require_once('../includes/SolrIndex.php');

$id = @$_GET['id'];

if(!$id) exit; // empty return should force display of cached string on other end

$index = new SolrIndex();
$doc = $index->getSolrDoc('ds-' . $id);

if(!$doc) exit; // no doc then no play - it isn't in the index

$meta = json_decode($doc->json_t);

// tag them with random id so the javascript can find the data.
$div_id = 'id-' . rand(0, 1000000);

// we have metadata so we output trigger link and data for modal to display
echo "<a href=\"#\" type=\"link\" data-bs-toggle=\"modal\" data-bs-target=\"#dataSourceModal\" data-source-metadata-id=\"{$div_id}\" >{$meta->name}</a>";
echo "<script type=\"application/json\" id=\"{$div_id}\">";
echo json_encode($meta, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
echo '</script>';
