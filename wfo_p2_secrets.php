
<?php

// this is a template file.
// the real version of this file is placed outside the
// github root and included in the config.php file

//$solr_query_uri = "***"; // the location of the SOLR index containing the data - ideally local to this server
$solr_query_uri = "http://localhost:8983/solr/wfo-portal";
$solr_user = '****';
$solr_password = '*****';

// uncomment to display a system message
//$system_message = "Index rebuilding - this could be a mess for a while!";

// authorisation for updates from an API
// this is used for communications between Fyllo / Airflow tasks and the portal API methods.
$api_bearer_token = "***";

// the name of the classification to be used
$classification_version = '2026-06';