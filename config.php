<?php

// this file includes configuration values and is included everywhere
// it goes into github and so in turn it includes a file outside the
// github root that has secret information in (passwords and stuff)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
//error_reporting(E_ALL);
session_start();

require_once('../../wfo_p2_secrets.php'); // things we don't put in github
require_once('includes/language_codes.php');

// $system_message = null;
if(!isset($system_message)) $system_message = null;

// Location of the solr server
define('SOLR_QUERY_URI', $solr_query_uri); // from wfo_p2_secrets.php
define('SOLR_USER', $solr_user); // from wfo_p2_secrets.php
define('SOLR_PASSWORD', $solr_password); // from wfo_p2_secrets.php

// This will normally be the most recent.
define('WFO_DEFAULT_VERSION','9999-04');

/*
    Facet configuration
    Which facets are displayed on taxon pages and in the faceted searching
*/

// attributes
// these are the facets displayed in the attributes box on the
// taxon pages, and the order they are displayed.
$attribute_facets = array(
    'wfo-f-5', // life form
    'wfo-f-10', // threat status
    'wfo-f-2', // Country ISO
    'wfo-f-8' // TDWG Level 3 
);

$map_facets = array(
    'wfo-f-2', // Country ISO
    'wfo-f-8' // TDWG Level 3 
);

// used to do the maps on family and genus pages
$map_choropleth_facet = 'wfo-f-2'; // Country ISO

// these are the facets used 
// in the search pages for filtering
// in the order provided
$search_facets = array(
  "wfo-f-5", // Life form
  "wfo-f-2", // Countries ISO
  "wfo-f-8", // TDWG Countries
  "wfo-f-10", // IUCN Statuses
  "role_s", // note this is the SOLR index field name - not a facet
  "rank_s", // note this is the SOLR index field name - not a facet
  "placed_in_phylum_s", // note this is the SOLR index field name - not a facet
  "placed_in_family_s", // note this is the SOLR index field name - not a facet
  "placed_in_genus_s", // note this is the SOLR index field name - not a facet
  "nomenclatural_status_s", // note this is the SOLR index field name - not a facet
  "snippet_text_categories_ss",
  "snippet_text_languages_ss"
);

$search_facets = array();
$search_facets[] = (object)array('kind' => 'facet_service', 'field_name' => 'wfo-f-5_ss', 'facet_name' =>  "wfo-f-5");
$search_facets[] = (object)array('kind' => 'facet_service', 'field_name' => 'wfo-f-2_ss', 'facet_name' =>  "wfo-f-2");
$search_facets[] = (object)array('kind' => 'facet_service', 'field_name' => 'wfo-f-8_ss', 'facet_name' =>  "wfo-f-8");
$search_facets[] = (object)array('kind' => 'facet_service', 'field_name' => 'wfo-f-10_ss', 'facet_name' =>  "wfo-f-10");

$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "snippet_text_categories_ss", 'label' => 'Text category');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "snippet_text_languages_ss", 'label' => 'Text language');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "snippet_text_sources_ss", 'label' => 'Text source');

$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "role_s", 'label' => 'Name role');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "nomenclatural_status_s", 'label' => 'Nomenclatural status');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "rank_s", 'label' => 'Taxonomic rank');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "placed_in_phylum_s", 'label' => 'Phylum');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "placed_in_family_s", 'label' => 'Family');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "placed_in_genus_s", 'label' => 'Genus');
$search_facets[] = (object)array('kind' => 'solr_field', 'field_name' =>  "wfo-facet-sources_ss", 'label' => 'Facet data source');

// used to render icons on the record page
define('IUCN_THREAT_FACET_ID', 'wfo-f-10');

// the facets cache 
$facets_cache = @$_SESSION['facets_cache'];

if(!$facets_cache || @$_GET['facets_cache_refresh'] == 'true' || time() - $_SESSION['facets_cache_modified'] > 60*60*10){ // refreshes every 10 minutes

    $facets_cache = array();

    $query = array(
        'query' => "kind_s:wfo-facet",
        'limit' => 10000
    );
  
    $docs  = SolrIndex::getSolrDocs($query);
    foreach($docs as $doc){
        $facets_cache[$doc->id] = json_decode($doc->json_t);
    }

    $_SESSION['facets_cache'] = $facets_cache;
    $_SESSION['facets_cache_modified'] = time();


}


// we do the same for sources of info
$sources_cache = @$_SESSION['sources_cache'];

if(!$sources_cache || @$_GET['sources_cache_refresh'] == 'true' || time() - $_SESSION['sources_cache_modified'] > 60*60*10){
    
    $sources_cache = array();

    $query = array(
        'query' => "kind_s:wfo-facet-source",
        'limit' => 10000
    );
  
    $docs  = SolrIndex::getSolrDocs($query);
    foreach($docs as $doc){
        $sources_cache[$doc->id] = json_decode($doc->json_t);
    }

    $_SESSION['sources_cache'] = $sources_cache;
    $_SESSION['sources_cache_modified'] = time();


}