<?php
require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/ranks_table.php');

// A widget to lazy load the taxon stats on the record page

$wfo_id = @$_GET['wfo_id'];
$name_string = @$_GET['name_string'];
$rank_string =  @$_GET['rank_string'];
$path = @$_GET['path'];
if(!$wfo_id || !$path) exit;

// build a facet query
$query = array(
    'query' => "name_descendent_path:\"{$path}\" !wfo_id_s:{$wfo_id}", // all descendents not ourselves
    "limit" => 0, // we don't want records we only want facets
    'filter' => array(
        "classification_id_s:" . WFO_DEFAULT_VERSION, // this classificatoin
        "role_s:accepted" // only the accepted ones
    ),
    'facet' => array(
        'rank_s' => array(
            'type' => "terms",
            'limit' => 100,
            'mincount' => 1,
            'missing' => false,
            'field' => 'rank_s'
        )
    )
    );

    $response = SolrIndex::getSolrResponse($query);

    if(isset($response->facets->rank_s)){

        // work through the ranks in hierarchical order
        $sep = ""; // comma between items after the firs on
        foreach ($ranks_table as $rank_name => $rank) {

            if(!$rank['faceted'] && $rank_name != 'species') continue;

            foreach($response->facets->rank_s->buckets as $bucket){
                if($bucket->val == $rank_name){


                    // build a query to get this
                    $query_url = array(
                        'q' => '',
                        'search_type' => 'name',
                        'timestamp' => time(),
                        "placed_in_{$rank_string}_s[]" => $name_string,
                        "role_s[]" => 'accepted',
                        "rank_s[]" => $rank_name
                    );
                    $query_url = 'search?' . http_build_query($query_url);

                    echo $sep;
                    echo "<a href=\"{$query_url}\">";
                    echo number_format($bucket->count, 0);
                    echo " {$rank['plural']}";
                    echo "</a>";
                    
                    $sep = " - ";
                }
            }
        }
     
    }



?>

<!--
<div class="card shadow-sm bg-secondary-subtle">

<div class="card-header">
<span
    data-bs-toggle="tooltip"
    data-bs-placement="top"
    title="A summary of the subtaxa within this taxon" >
Subtaxa
</span>
</div>

<div class="list-group  list-group-flush">
    <a href="#" class="list-group-item  list-group-item-action">Some text diagram thing</a>
</div>
</div>

-->