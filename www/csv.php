<?php
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/FacetDetails.php');
require_once('../includes/SourceDetails.php');

require_once('../config.php');

/*
 This is a simple script that will render a remotely 
 hosted CSV file as expanded metadata for facet value provenance.

 It is intentionally not branded to look like the site.

*/

// passed in the DataSource ID

// passed in the WFO ID

// fetch the associated CSV file (caching for an hour or more?)

// search to the rows containing the data

// render those rows as a simple HTML page

// display last fetch time for caching

$source = new SourceDetails($_GET['source_id']);
$record = new TaxonRecord($_GET['wfo_id']);

?>

<h1>Raw CSV Source Data</h1>
<p>This is raw data extracted from the source for this attribute.</p>

<p><?php echo $source->getHarvestLink() ?></p>

<p><?php echo $record->getFullNameStringHtml(); ?></p>
