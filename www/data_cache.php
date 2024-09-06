<?php
/*
    A untility page to display users cache 
    state for debugging.

*/

require_once('../includes/SolrIndex.php');
require_once('../config.php');



?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Cache Info</title>
</head>

<body>
<h1>Data cached in the user session for performance.</h1>

<p><a href="data_cache.php?facets_cache_refresh=true&sources_cache_refresh=true">Refresh the cache now.</a></p>

<h2>Facets Cache - Modified: <?php
    echo date ("F d Y H:i:s.",  @$_SESSION['facets_cache_modified']);
?></h2>
<pre>
<?php
    print_r(@$_SESSION['facets_cache']);
?>
</pre>

<h2>Sources Cache - Modified: <?php
    echo date ("F d Y H:i:s.",  @$_SESSION['sources_cache_modified']);
?></h2>
<pre>
<?php
    print_r(@$_SESSION['sources_cache']);
?>
</pre>
</body>
</html>