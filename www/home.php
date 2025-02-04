<?php

$page_title = "WFO: Home Page";

require_once('header.php');
?>

<div class="container" style="margin-top: 4%;">
        <div class="row">
            <div id="logo" class="text-center">
                <h2 style="font-size: 300%; padding-top: 2em; padding-bottom: 0.5em;">An Online Flora of All Known Plants</h2>

<?php

    $index = new SolrIndex();

    $query = array(
        'query' => '*:*',
        'facet' => (object) array(
            'role_s' => (object) array(
                'type' => "terms",
                'limit' => -1,
                'mincount' => 1,
                'missing' => false,
                'sort' => 'index',
                'field' => 'role_s',
                'facet' => (object) array(
                    'rank_s' => (object) array(
                        'type' => "terms",
                        'limit' => -1,
                        'mincount' => 1,
                        'missing' => false,
                        'sort' => 'index',
                        'field' => 'rank_s'
                    ),
                )
            ),
            'snippet_text_bodies_txt' => (object) array(
                'type' => "query",
                'q' => "snippet_text_bodies_txt:[* TO *]"
            ),

            // get a list by country so we can iterate through them
            $map_choropleth_facet => (object) array(
                'type' => "terms",
                'limit' => -1,
                'mincount' => 1,
                'missing' => false,
                'sort' => 'index',
                'field' =>  $map_choropleth_facet . '_ss',
                'facet' => (object) array(
                    'rank_s' => (object) array(
                        'type' => "terms",
                        'limit' => -1,
                        'mincount' => 1,
                        'missing' => false,
                        'sort' => 'index',
                        'field' => 'rank_s'
                    )
                )
            )
        ),
        'filter' => ['classification_id_s:' . WFO_DEFAULT_VERSION ], // we only want to look at the one classification
        'limit' => 0 // we don't want any results we are only counting
    );


    $solr_response  = SolrIndex::getSolrResponse($query);


    $names = number_format($solr_response->facets->count, 0);
    
    $taxa_with_text =  number_format($solr_response->facets->snippet_text_bodies_txt->count, 0);

    $accepted_taxa = null;
    $synonyms = null;
    $unplaced = null;
    $deprecated = null;
    foreach($solr_response->facets->role_s->buckets as $role){

        if($role->val  == 'accepted'){
            $accepted_taxa = number_format($role->count);
            foreach($role->rank_s->buckets as $rank){
                if($rank->val == 'family') $accepted_families = number_format($rank->count);
                if($rank->val == 'genus') $accepted_genera = number_format($rank->count);
                if($rank->val == 'species') $accepted_species = number_format($rank->count);
            }
        }

        if($role->val  == 'synonym'){
            $synonyms = number_format($role->count);
        }

        if($role->val  == 'unplaced'){
            $unplaced = number_format($role->count);
        }

    }

    $country_count = number_format(count($solr_response->facets->{$map_choropleth_facet}->buckets, 0 ));

    echo "<p><strong>$names scientific names representing $accepted_taxa taxa - $accepted_species species in $accepted_genera genera and $accepted_families families from $country_count countries.</strong></p>";

   // print_r($solr_response->facets->snippet_text_bodies_txt);

    /*
    $solr_response->facets->count // total names
    $solr_response->facets->role_s->buckets // roles

    $solr_response->facets->role_s->buckets // roles - accepted is broken down into ranks

    $solr_response->facets->{$map_choropleth_facet}->buckets

    */


?>

                <form role="form" method="GET" action="search">
                    <?php require_once('search_box.php') ?>
                </form>
  
<p>&nbsp</p>
                <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
<?php

                $facet_details = new FacetDetails($map_choropleth_facet);
                
                // work through the countries and add them to a carousel
                $active = 'active';

                // we want it to be in a random order
                shuffle($solr_response->facets->{$map_choropleth_facet}->buckets);

                foreach($solr_response->facets->{$map_choropleth_facet}->buckets as $country){

                    echo '<div class="carousel-item '. $active .'">';

                    echo '<p>';

                    echo '<strong>';
                    echo $facet_details->getFacetValueName($country->val);
                    echo ': </strong>';

                    echo number_format($country->count, 0);

                    echo ' taxa';

                    foreach($country->rank_s->buckets as $rank){
                        if($rank->val == 'species') echo ' - '. number_format($rank->count) . ' species';
                        if($rank->val == 'subspecies') echo ' - '. number_format($rank->count) . ' subspecies';
                        if($rank->val == 'variety') echo ' - '. number_format($rank->count) . ' varieties';
                    }

                    echo '</p>';

                    echo '</div>';

                    $active = '';

                }

?>
                </div>

            </div> <!-- end carousel -->


            <p style="padding: 0.5em; margin-top: 2em; margin-left: 20%; margin-right: 20%; font-size: 120%; border: solid 1px black;">
                    This is a mock-up of a new WFO data portal for evaluation purposes only.
                    <br/>
                    <strong>The data here may not be correct at this stage.</strong>
                    <br/>
                    The current official portal is available <a href="https://www.worldfloraonline.org">here</a>.
                </p>

            </div><!-- centering -->



        </div>
</div>

<?php
require_once('footer.php');
?>