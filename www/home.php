<?php

$page_title = "WFO: Home Page";

require_once('../fragments/header.php');
?>

<div class="container" style="margin-top: 4%;">

    <div class="row">
        <div id="logo" class="text-center">
            <h2 style="font-size: 300%;  padding-bottom: 0.5em;">An Online Flora of All Known Plants
            </h2>

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
                'field' => 'facet_values_ss', // all the facets are here now
                'prefix' => '1-', // iso countries facet id
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

    $accepted_taxa = 0;
    $accepted_genera = 0;
    $accepted_families = 0;
    $synonyms = 0;
    $unplaced = 0;
    $deprecated = 0;
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

    echo "<p><strong>$names scientific names representing {$accepted_taxa} taxa - $accepted_species species in $accepted_genera genera and $accepted_families families from $country_count countries.</strong></p>";


?>

            <form role="form" method="GET" action="search">
                <?php require_once('search_box.php') ?>
            </form>

            <div class="card shadow-sm  bg-secondary-subtle" style="margin-top: 3em; margin-bottom: 2em;" >


           <!--  <div class="container" style="margin-top: 3em; margin-bottom: 2em;> -->

           <div class="card-header">

                <div id="mapCarousel" class="carousel carousel-fade" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php

                $facet_details = new FacetDetails($map_choropleth_facet);
                
                // work through the countries and add them to a carousel
                $active = 'active';

                // we want it to be in a random order
                shuffle($solr_response->facets->{$map_choropleth_facet}->buckets);

                $first_country_code = null;
                foreach($solr_response->facets->{$map_choropleth_facet}->buckets as $country){

                    $country_code = $facet_details->getFacetValueCode($country->val);

                    // only do the ones we can draw a map for.
                    if(!file_exists("data/1/{$country_code}.json")) continue;

                    // keep hold of the first one in the list so we can load it with the page
                    if(!$first_country_code) $first_country_code = $country_code;

                    echo "<div class=\"carousel-item $active text-start\"
                        data-facet-value-code=\"{$country_code}\" 
                        data-facet-value=\"{$country->val}\"
                        >";

                    $search_url = "/search?q=&search_type=name&timestamp=".time()."&1-facet_values_ss%5B%5D=" . urlencode($country->val);

                    echo "<a href=\"$search_url\">";
                    echo '<h3>';
                    echo $facet_details->getFacetValueName($country->val);
                    echo '</h3>';
                    echo '</a>';

                    echo '<p style="margin-bottom: 0px;">';
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
</div>

            <div id="backgroundMap" style="width: 100%; height: 350px; "></div>

            <!-- map behind everything -->
            <script>
            // the map itself
            const map = L.map('backgroundMap', {
                zoomControl: false,
                attributionControl: false
            }).setView([33, 120], 3);

            // base layer is top
            const openTopoMap = L.tileLayer(
                'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
                }).addTo(map);

            // make it responsive
            map.invalidateSize();
            const resizeObserver = new ResizeObserver(() => {
                map.invalidateSize();
            });
            resizeObserver.observe(document.getElementById('backgroundMap'));

            // change the map with the carousel
            let activeLayer = null;

            // add the first displayed country
            fetch('data/1/<?php echo $first_country_code ?>.json').then(response => response.json())
                .then(json => {
                    activeLayer = L.geoJson(json, {
                        style: {
                            fillColor: 'blue',
                            fillOpacity: 0.5,
                            weight: 0
                        }
                    });
                    activeLayer.addTo(map);
                    map.fitBounds(activeLayer.getBounds());
                })

            // listen for the changes.
            document.getElementById('mapCarousel').addEventListener('slide.bs.carousel', function(event) {
                const countryCode = event.relatedTarget.dataset.facetValueCode;
                const jsonFilePath = `data/1/${countryCode}.json`;
                console.log(jsonFilePath);

                // get rid of the last layer if there is one
                if (activeLayer) map.removeLayer(activeLayer);

                // fetch a new one and display that.
                fetch(jsonFilePath).then(response => response.json())
                    .then(json => {
                        activeLayer = L.geoJson(json, {
                            style: {
                                fillColor: 'blue',
                                fillOpacity: 0.5,
                                weight: 0
                            }
                        });
                        activeLayer.addTo(map);
                        map.fitBounds(activeLayer.getBounds());
                    })

                console.log(countryCode);
            });
            </script>

        </div>
    </div><!-- centering -->
</div>
</div>

<?php
require_once('../fragments/footer.php');
?>