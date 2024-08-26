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

// Location of the solr server
define('SOLR_QUERY_URI', $solr_query_uri); // from wfo_p2_secrets.php
define('SOLR_USER', $solr_user); // from wfo_p2_secrets.php
define('SOLR_PASSWORD', $solr_password); // from wfo_p2_secrets.php

// This will normally be the most recent.
define('WFO_DEFAULT_VERSION','2024-06');

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
  "nomenclatural_status_s" // note this is the SOLR index field name - not a facet
);

// the facets cache
$facets_cache = @$_SESSION['facets_cache'];

if(!$facets_cache || @$_GET['facet_cache_refresh'] == 'true'){

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


}


// we do the same for sources of info
$sources_cache = @$_SESSION['sources_cache'];

if(!$sources_cache || @$_GET['sources_cache_refresh'] == 'true'){
    
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


}