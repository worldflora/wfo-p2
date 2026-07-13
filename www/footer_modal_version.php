<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');

// we need to talk to the index
$solr = new SolrIndex();

 $classification_date = DateTime::createFromFormat('Y-m-d', $classification_version . '-01');

 // we might be passed a wfo id if we are looking at a name or taxon
 $wfo = @$_GET['wfo'] ? $_GET['wfo'] : null;


?>

<ul class="list-group list-group-flush">
    <li class="list-group-item list-group-item-primary">Now</li>
    <li class="list-group-item"><strong>Time on server: </strong> <?php echo (new DateTimeImmutable())->format('Y-m-d\TH:i:s\Z') ?></li>
    <li class="list-group-item"><strong>Current classification: </strong> <?php echo $classification_date->format('F') . ' ' . $classification_date->format('Y') ?></li>
<?php
    // put a block of information in about the specific record if we have been passed a wfo id
    if(isset($wfo) && $wfo){

        $taxon = $solr->getSolrDoc($wfo . '-' . $classification_version);
        echo '<li class="list-group-item list-group-item-primary">Current Taxon</li>';
        echo '<li class="list-group-item">';
        echo '<a href="/'. $taxon->wfo_id_s .'">';
        echo $taxon->full_name_string_html_s;
        echo '</a>';
        echo '</li>';
        echo '<li class="list-group-item">';
        echo '<strong> Indexed: </strong> ';
        echo $taxon->fyllo_last_indexed_dt;
        echo '</li>';

    }

    // fetch a summary of the indexing

    // firstly get the total names in each role
    $query = array(
        'query' => "*:*", // everything
        'filter' => array(
            'classification_id_s:' . $classification_version, // only look at the current classification
            'wfo_id_s:[* TO *]'
        ),
        'fields' => array( // just the fields we need
            'wfo_id_s'
        ),
        'limit' => 0,
        'facet' => (object)array(
            'roles' => (object)array(
                "type" => "terms",
                "field" => 'role_s'
            )
        )
    );

    $response = $solr->getSolrResponse($query);

    $roles = array();
    foreach ($response->facets->roles->buckets as $bucket) {
        $roles[$bucket->val] = $bucket->count;
    }

?>
    <li class="list-group-item list-group-item-primary">Name Counts</li>
    <li class="list-group-item"><strong>Accepted names: </strong> <?php echo number_format($roles['accepted'], 0) ?></li>
    <li class="list-group-item"><strong>Synonymous names: </strong> <?php echo number_format($roles['synonym'], 0) ?></li>
    <li class="list-group-item"><strong>Unplaced names: </strong> <?php echo number_format($roles['unplaced'], 0) ?></li>
    <li class="list-group-item"><strong>Deprecated names: </strong> <?php echo number_format($roles['deprecated'], 0) ?></li>
<?php

    $query = array(
        'query' => "fyllo_last_indexed_dt:[* TO *]", // only the ones that have been indexed
        'filter' => array(
            'classification_id_s:' . $classification_version, // only look at the current classification
            'wfo_id_s:[* TO *]', // must be a name record
            'role_s:accepted' // only accepted names i.e. taxa
        ),
        'fields' => array( // just the fields we need
            'wfo_id_s',
            'fyllo_last_indexed_dt',
            'full_name_string_html_s'
        ),
        'sort' => 'fyllo_last_indexed_dt DESC', // we only want the latest one
        'limit' => 1
    );

    $response = $solr->getSolrResponse($query);

    if(isset($response->response->numFound)){
        
        // we have some indexed taxa

         echo '<li class="list-group-item list-group-item-primary">Taxa</li>';

        echo '<li class="list-group-item"><strong>Taxa indexed: </strong> ';
        echo number_format($response->response->numFound, 0);

        $percent = ($response->response->numFound/$roles['accepted']) * 100;
        echo ' (' . number_format($percent, 2). '%)';

        echo '</li>';

        // show them the latest one
        if(isset($response->response->docs[0])){
            $taxon = $response->response->docs[0];

            echo '<li class="list-group-item"><strong>Newest indexed: </strong> ';
            echo '<a href="/'. $taxon->wfo_id_s .'">';
            echo $taxon->full_name_string_html_s;
            echo '</a>';
            echo '<strong> On: </strong> ';
            echo $taxon->fyllo_last_indexed_dt;
            echo '</li>';

            $latest_index_date = new DateTimeImmutable($taxon->fyllo_last_indexed_dt);

        }

    } 

    // alter the query to get the first indexed and repeat that
    $query['sort'] = 'fyllo_last_indexed_dt ASC';

    $response = $solr->getSolrResponse($query);

    if(isset($response->response->numFound)){
        
        // show them the first one
        if(isset($response->response->docs[0])){
            $taxon = $response->response->docs[0];

            echo '<li class="list-group-item"><strong>Oldest indexed: </strong> ';
            echo '<a href="/'. $taxon->wfo_id_s .'">';
            echo $taxon->full_name_string_html_s;
            echo '</a>';
            echo '<strong> On: </strong> ';
            echo $taxon->fyllo_last_indexed_dt;
            echo '</li>';

            $first_index_date = new DateTimeImmutable($taxon->fyllo_last_indexed_dt);
        }

    } 

    $index_interval = $first_index_date->diff($latest_index_date);

    if($index_interval->days && $index_interval->days > 7){
        // more than a week so just report the total days
        $index_interval_string = number_format($index_interval->days, 0) . ' days';
    }else{
        $index_interval_string = $index_interval->format('%d days %h hours %i minutes');
    }

?>
    <li class="list-group-item"><strong>Index interval: </strong> <?php echo $index_interval_string ?></li>  

<?php
    // now facet indexing details

    // get some totals
    $query = array(
        'query' => 'kind_s:[* TO *]', // everything with a kind field
        'limit' => 0, // but none of them returned
        'facet' => (object)array(
            'kinds' => (object)array(
                "type" => "terms",
                "field" => 'kind_s',
                'sort' => 'count'
            )
        )
    );

    $response = $solr->getSolrResponse($query);

    /*
        echo '<pre>';
        print_r($response->facets->kinds->buckets);
        echo '</pre>';
    */
    $kinds = array();
    foreach (array_reverse($response->facets->kinds->buckets) as $bucket) {

        //if($bucket->val != 'wfo-facet') continue;

        switch ($bucket->val) {
            case 'wfo-facet':
                $display_name = 'Facets';
                break;
            case 'wfo-snippet-source':
                $display_name = 'Snippet Sources';
                break;            
            case 'wfo-facet-source':
                $display_name = 'Facet Sources';
                break;
            default:
                $display_name = $bucket->val;
                break;
        }


        $query = array(
            'query' => "fyllo_last_indexed_dt:[* TO *]", // only the ones that have been indexed
            'filter' => array(
                'kind_s:' . $bucket->val // only of this kind
            ),
            'fields' => array( // just the fields we need
                'json_t',
                'fyllo_last_indexed_dt',
                'id'
            ),
            'sort' => 'fyllo_last_indexed_dt DESC, id DESC', // we only want the latest one
            'limit' => 1
        );

        $response = $solr->getSolrResponse($query);

        $count_display = number_format($bucket->count);

        echo "<li class=\"list-group-item list-group-item-primary\">{$display_name}</li>";
        
        $indexed = $response->response->numFound;
        $indexed_display = number_format($indexed);

        echo "<li class=\"list-group-item\"><strong>Indexed: </strong>{$indexed_display}</li>";

        if(isset($response->response->docs[0])){
            $thing = $response->response->docs[0];

            $json = json_decode($thing->json_t);
            echo '<li class="list-group-item"><strong>Newest indexed: </strong> ';
            //print_r($json);
            echo isset($json->name) ? $json->name : $thing->id;
            echo '<strong> On: </strong> ';
            echo $thing->fyllo_last_indexed_dt;
            echo '</li>';

            $latest_index_date = new DateTimeImmutable($thing->fyllo_last_indexed_dt);
        }

        // alter the query to get the first indexed and repeat that
        $query['sort'] = 'fyllo_last_indexed_dt ASC, id ASC';

        $response = $solr->getSolrResponse($query);

        if(isset($response->response->docs[0])){
            $thing = $response->response->docs[0];

            $json = json_decode($thing->json_t);
            echo '<li class="list-group-item"><strong>Oldest indexed: </strong> ';
            echo isset($json->name) ? $json->name : $thing->id;
            //echo '<pre>';
            //print_r($thing);
            //echo '</pre>';
            echo '<strong> On: </strong> ';
            echo $thing->fyllo_last_indexed_dt;
            echo '</li>';

            $first_index_date = new DateTimeImmutable($thing->fyllo_last_indexed_dt);
        }

        $index_interval = $first_index_date->diff($latest_index_date);

        if($index_interval->days && $index_interval->days > 7){
            // more than a week so just report the total days
            $index_interval_string = number_format($index_interval->days, 0) . ' days';
        }else{
            $index_interval_string = $index_interval->format('%d days %h hours %i minutes');
        }

        echo '<li class="list-group-item"><strong>Index interval: </strong>' . $index_interval_string . '</li>';
        

        
    }








?>
    
</ul>