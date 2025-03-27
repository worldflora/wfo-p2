<?php
require_once('../config.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/ListExporter.php');

$out = (object)array();

// set things up
if(@$_SESSION['exporter']){
    $exporter = unserialize($_SESSION['exporter']);
}else{
    // we are being called for the first time
    $exporter = new ListExporter($_GET['format']);
}

// do a page
$exporter->page();

$out->message = $exporter->getMessage();
$out->finished = $exporter->isFinished();

// remove the exporter if it has finished
if($out->finished){
    unset($_SESSION['exporter']);
    $out->downloadUrl = $exporter->getDownloadUrl();
}else{
    // save the new version to the session
    $_SESSION['exporter'] = serialize($exporter);
}

//sleep(1);

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);