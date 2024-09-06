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

// fetch the associated CSV file (caching for an hour or more?)

// search to the rows containing the data

// render those rows as a simple HTML page

// display last fetch time for caching

$source = new SourceDetails($_GET['source_id']);
$record = new TaxonRecord($_GET['wfo_id']);

// do we have a cached version of the csv file.
$cached_file_dir = '../data/facet_source_cache/';
$cached_file_path = $cached_file_dir . $source->getId() . '.csv';

// do we have one cached? If not then go get it
if(!file_exists($cached_file_path) || time() - filemtime($cached_file_path) > 60*60*24){ // less than a day old - could be configured
    file_put_contents($cached_file_path, file_get_contents($source->getHarvestLink()));
}

$title = "Raw CSV data from \"{$source->getName()}\" for {$record->getWfoID()}";

?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title; ?></title>
    <style>
        th{
            text-align: right;
        }
    </style>
</head>

<body>
    <h1><?php echo $title; ?></h1>
    <p>This is the complete, raw metadata for <strong><?php echo $record->getFullNameStringHtml() ?></strong>
     extracted from the source CSV datafile as provided. You can download the whole datafile at source <a href="<?php echo $source->getHarvestLink() ?>">here.</a></p>
     <hr/>


<?php


$in = fopen($cached_file_path, 'r');
$header = fgetcsv($in);

if(!$in){
    echo "<p>Sorry couldn't access the source CSV file at {$source->getHarvestLink()}.</p>";
}

// now we seek to the lines that start with the wfo id
$hits = array();
while($line = fgetcsv($in)){
    if($line[0] == $record->getWfoId()) $hits[] = $line;
}

if(!$hits){
    echo "<p>Sorry couldn't find {$record->getWfoId()} in {$source->getHarvestLink()}.</p>";
}

// work through the hits and render them pretty
foreach($hits as $hit){

    echo "<table>";
    for($i = 0; $i < count($header); $i++){

        // blanks are common
        if(!$header[$i] && !$hit[$i]) continue;

        echo "<tr>";
        echo "<th>{$header[$i]}:</th>";
        echo "<td>{$hit[$i]}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<hr/>";

}

echo "<p>Data file last cached: " . date ("F d Y H:i:s.", filemtime($cached_file_path)) . "</p>";
echo "<hr/>";
?>

