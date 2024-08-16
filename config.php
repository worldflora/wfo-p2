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