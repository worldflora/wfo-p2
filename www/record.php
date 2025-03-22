<?php

$wfo = $path_parts[0];

$record = new TaxonRecord($wfo . '-' . WFO_DEFAULT_VERSION);
$page_title = $record->getFullNameStringPlain();


require_once('header.php');

?>

<div class="container-lg">
    <form role="form" method="GET" action="search">
        <div class="row">
            <!-- main content -->
            <div class="col">
                <div data-bs-toggle="offcanvas" style="float: right;">
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasResponsive"
                        aria-controls="offcanvasResponsive">Classification</button>
                </div>

                <?php
     
    render_record_type_description($record);
    
    // header
    echo "<h1 style=\" position: relative;\">{$record->getFullNameStringHtml()}";
    
    echo "</h1>";
    echo "<p>{$record->getCitationMicro()}<br/>";

    // we load summary stats async because it needs another index call
    echo '<span id="taxonStatsSpan">&nbsp;</span>';
    echo '<script>';
    echo "\tconst placeholder = document.getElementById('taxonStatsSpan');\n";
    echo "\tfetch('widget_taxon_stats.php?wfo_id={$record->getWfoId()}&path={$record->getNameDescendentPath()}&name_string={$record->getNameString()}&rank_string={$record->getRank()}').then(response => response.text()).then(text => placeholder.innerHTML = text)";        
    echo '</script>';

    echo "</p>"; // end of micro citation
    
    // link to accepted name
    if($record->getRole() == 'synonym'){
        $accepted = new TaxonRecord($record->getAcceptedId());
        echo '<p class="fw-bold fs-4" >Correct name: ';
        echo "<a href=\"{$accepted->getWfoId()}\">{$accepted->getFullNameStringHtml()}</a>";
        echo '</p>';
    }

    // Synonyms
    render_name_list($record->getSynonyms(), $record, "Synonyms", "Other names that are placed in this taxon but that are not the formally accepted name of this taxon.");

    // attributes (facets)
    $all_facets = $record->getFacets();
    $facets = array(); // these are the facets to display - and in the correct order as defined in attribute_facets
    foreach($attribute_facets as $facet_id){ // attribute_facets is defined in the config.php
        if(isset($all_facets[$facet_id])) $facets[] = $all_facets[$facet_id];
    }

    
    if($facets){
        
        // we have facets to render as attribute box

        echo '<div class="card shadow-sm bg-secondary-subtle">';
        echo '<div class="card-header">';
         echo '<span
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Features of this taxon. Click for provenance details." >Taxon Attributes</span>&nbsp;';
        echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($facets), 0)  .'</span> </div>';
        echo '<ul class="list-group  list-group-flush" style="max-height: 10em; overflow: auto;">';

        // work through the facets
        foreach($facets as $f){

            echo '<li class="list-group-item  list-group-item-action">';

            echo "<strong>{$f->name}: </strong>";
            $spacer = '';

            // work through the facet values
            foreach($f->facet_values as $fv){
                echo $spacer;
                $spacer = '- ';

                // package the provenance data up into a data attribute
                $prov_data = (object)array(
                    'kind' => 'facet',
                    'facet_name' => $f->name,
                    'facet_id' => $f->id,
                    'facet_value' => $fv,
                    'taxon_wfo' => $record->getWfoId(), 
                    'taxon_name' => $record->getFullNameStringHtml()

                );

                $prov_json = urlencode(json_encode($prov_data));

                // render the actual facet value.
                echo '<span data-bs-toggle="modal" data-bs-target="#facetProvModal" data-wfoprov="'. $prov_json .'" style="cursor: pointer;">';
                echo $fv->name;
                // badge with the data source count 
                echo '<span class="badge rounded-pill text-bg-light" style="font-size: 60%; vertical-align: super;">'. number_format(count($fv->provenance), 0)  .'</span>';
                echo '</span>';
                            
            } // end facet value

            echo '</li>';
    
        } // end facet

       echo '</ul>'; // end list group
       echo '</div>'; // end card

    }
?>
                <!-- Modal 1 for facet provenance -->
                <div class="modal fade" id="facetProvModal" tabindex="-1" aria-labelledby="provModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="provModalLabel">Facet Provenance</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div id="facetProvModalContent">
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-bs-dismiss="modal" aria-label="Close"
                                    class="btn btn-primary">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                // modal dialogue - load content on show
                document.getElementById('facetProvModal').addEventListener('show.bs.modal', event => {

                    const modalContent = document.getElementById('facetProvModalContent');
                    modalContent.innerHTML = 'Loading ...';
                    fetch("provenance_modal_facet.php?prov=" + event.relatedTarget.dataset.wfoprov)
                        .then(response => response.text())
                        .then(text => modalContent.innerHTML = text);

                })
                </script>

                <!-- Modal 2 for data provenance -->
                <div class="modal fade" id="dataProvModal" tabindex="-1" aria-labelledby="provModalLabel2"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="provModalLabel2">Data Provenance</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div id="dataProvModalContent">
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-bs-dismiss="modal" aria-label="Close"
                                    class="btn btn-primary">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                // modal dialogue - load content on show
                document.getElementById('dataProvModal').addEventListener('show.bs.modal', event => {

                    const modalContent = document.getElementById('dataProvModalContent');
                    modalContent.innerHTML = 'Loading ...';
                    fetch("provenance_modal_data.php?prov=" + event.relatedTarget.dataset.wfoprov)
                        .then(response => response.text())
                        .then(text => modalContent.innerHTML = text);

                })
                </script>

                <?php

    // mapping card
    $facets = array(); // these are the facets to display - and in the correct order as defined in map_facets
    foreach($map_facets as $facet_id){ // map_facets is defined in the config.php
        if(isset($all_facets[$facet_id])) $facets[$facet_id] = $all_facets[$facet_id];
    } 

    if($facets){
        // we have facets to render as a map interface
        // we make a card
        // add the map
        // add each facet as a layer to the card

?>
                <!-- Taxon Map based on facets this ta -->
                <div class="card  shadow-sm  bg-secondary-subtle">
                    <div class="card-header">
                        <span data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Areas the taxon is found in. Click an area for provenance info.">Taxon Maps</span>
                        &nbsp;
                        <span class="badge rounded-pill text-bg-success"
                            style="font-size: 70%; vertical-align: super;"><?php echo number_format(count($facets), 0) ?></span>
                    </div>

                    <div id="map" class="container-fluid" style="min-height: 300px"></div>
                    <script>
                    // the map itself
                    const map = L.map('map').setView([33, 120], 1);

                    // the default base layer - streets 
                    const osm = L.tileLayer(
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(map);

                    // alternate base layer - topo
                    const openTopoMap = L.tileLayer(
                        'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
                        });

                    // labels for the layer control - base layers
                    const baseMaps = {
                        "Open Street Map": osm,
                        "Open Topology": openTopoMap
                    };


                    // labels for the layer control overlays
                    let overlayMaps = {};
                    <?php

        // we need a unique var for each layer
        $layer_count = 0;
        foreach($facets as $f_id => $f){
            $layer_count++;
            $layer_var = 'lg' . $layer_count;
            echo "let $layer_var = L.featureGroup();\n";                            
            foreach($f->facet_values as $fv){
                $path = "data/{$f_id}/{$fv->code}.json";
                if(file_exists($path)){

                    // the actual polygon
                    $json = file_get_contents($path);

                    // package the provenance data up into a data attribute
                    $prov_data = (object)array(
                        'kind' => 'facet',
                        'facet_name' => $f->name,
                        'facet_value' => $fv,
                        'taxon_wfo' => $record->getWfoId(), 
                        'taxon_name' => $record->getFullNameStringHtml() 
                    );
                    $prov_json = urlencode(json_encode($prov_data));

                    ?>

                    var p = L.geoJSON(<?php echo $json ?>, {
                        style: {
                            fillColor: 'blue',
                            fillOpacity: 0.5,
                            weight: 0
                        }
                    }).addTo(<?php echo $layer_var ?>);
                    p.openPopup();
                    p.on('click', function() {
                        const myModal = new bootstrap.Modal(document.getElementById('facetProvModal'));
                        const span = document.createElement('span');
                        span.setAttribute('data-wfoprov', '<?php echo $prov_json ?>');
                        myModal.show(span);
                    });

                    <?php
                }
            }
            echo "$layer_var.addTo(map);\n";
            echo "overlayMaps['{$f->name}'] = $layer_var;\n";
            
        } // end facet
?>

                    // we add a layer control to the map
                    const layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);
                    </script>

                </div>
                <?php

    }else{// end if we have a taxon map to render

        // we don't have a taxon map to render so do we want to render a choropleth map from a 
        // facet search and link it to the search reasults?
       // if($record->getRank() == 'genus' || $record->getRank() == 'family'){
        if( 
            in_array(
                $record->getRank(),
                array('family','genus')
            )){

            // do a search to get a facet with the country distributions in it
            $filters = array();
            $filters[] = 'classification_id_s:' . WFO_DEFAULT_VERSION;
            $filters[] = 'role_s:accepted';
            $filters[] = "placed_in_{$record->getRank()}_s:{$record->getNameString()}";

            // facet on a field we have in the configuration file.
            $facets = array();
            $facets[$map_choropleth_facet] = (object)array(
                    "type" => "terms",
                    'limit' => 200,
                    'mincount' => 1,
                    "field" => $map_choropleth_facet . '_ss'
            );
            
            $map_query = array(
                'query' => '*:*', // get everything
                'filter' => $filters,
                'limit' => 0, // return no docs - just facets
                'facet' => (object)$facets
            );

            $map_response = SolrIndex::getSolrResponse($map_query);
            $areas = array();


            if(
                isset($map_response->facets) // facets in the response
                && isset($map_response->facets->{$map_choropleth_facet}->buckets)  // buckets in this facet
                && $map_response->facets->{$map_choropleth_facet}->buckets) // some buckets
                
            {
                
                $buckets = $map_response->facets->{$map_choropleth_facet}->buckets;
                
                // highest one comes first
                $max = $buckets[0]->count;
                $area_count = 0;
                $facet_details = new FacetDetails($map_choropleth_facet);
                foreach($buckets as $bucket){
                    $areas[$bucket->val] = (object)array(
                        'name' => $facet_details->getFacetValueName($bucket->val),
                        'count' => $bucket->count,
                        'code' => $facet_details->getFacetValueCode($bucket->val),
                        'percent' => round($bucket->count/$max * 100)
                    );
                    if($bucket->count > 0 )$area_count++;
                }// build the areas


?>
                <div class="card">
                    <div class="card-header">
                        <span data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Areas this <?php echo $record->getRank()  ?> is found in. Click the map to search for subtaxa by area.">Summary
                            Map</span>
                        &nbsp;
                        <span class="badge rounded-pill text-bg-success"
                            style="font-size: 70%; vertical-align: super;"><?php echo number_format($area_count, 0) ?></span>
                    </div>

                    <div id="map" class="container-fluid" style="min-height: 300px">

                        <script>
                        // function to get a map color
                        function getChoroplethColors(d) {
                            return d > 90 ? '#800026' :
                                d > 80 ? '#BD0026' :
                                d > 70 ? '#E31A1C' :
                                d > 60 ? '#FC4E2A' :
                                d > 50 ? '#FD8D3C' :
                                d > 40 ? '#FEB24C' :
                                d > 30 ? '#FED976' :
                                d > 0 ? '#FFEDA0' :
                                '#DDDDDD';
                        }

                        // the map itself
                        const map = L.map('map').setView([33, 120], 1);

                        // the default base layer - streets 
                        const osm = L.tileLayer(
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                            }).addTo(map);

                        <?php

                    echo "let lg = L.featureGroup();\n";                            
                    foreach($areas as $fv_id => $fv){
                        $path = "data/{$map_choropleth_facet}/{$fv->code}.json";
                        if(file_exists($path)){
                            // the actual polygon
                            $json = file_get_contents($path);

                            // we add properties to the polygon that we use
                            // for interaction in the map
                            $data = json_decode($json);

                            // density is used for the colour
                            $data->properties->density = $fv->percent;
                            
                            // build a query string used for the onclick action
                            $query_url = array(
                                'q' => '',
                                'search_type' => 'name',
                                'timestamp' => time(),
                                "placed_in_{$record->getRank()}_s[]" => $record->getNameString(),
                                "{$map_choropleth_facet}_ss[]" => $fv_id
                            );
                            $data->properties->query_url = 'search?' . http_build_query($query_url);

                            $data->properties->taxon_count = $fv->count;

                            // turn it back into json for use in the javascript
                            $json = json_encode($data);
                    ?>
                        var p = L.geoJSON(<?php echo $json ?>, {
                            style: feature => {
                                return {
                                    fillColor: getChoroplethColors(feature.properties.density),
                                    weight: 2,
                                    opacity: 1,
                                    color: 'white',
                                    dashArray: '3',
                                    fillOpacity: 0.7
                                };
                            },
                            onEachFeature: (feature, layer) => {
                                layer.on({
                                    'click': e => {
                                        window.location = e.target.feature.properties.query_url;
                                    }
                                });
                                console.log(feature.properties);
                                const tt = feature.properties.tags["name:en"] + ": " + feature.properties
                                    .taxon_count + " taxa";
                                layer.bindTooltip(tt)
                                    .openTooltip();
                            }
                        }).addTo(lg);

                        <?php
                        } // file exists
                    } // for each area
 
                   echo "lg.addTo(map);\n";

            ?>
                        </script>
                    </div>
                </div>
                <!--end of choropleth -->

                <?php
            }
            }// end is genus or family for chloropleth map

        }// end chloropleth not taxon map


    // Load the tools associated but do it assync because it will
    // require another faceting search or two.
?>
    <div class="card shadow-sm bg-secondary-subtle banana" id="toolsCard">
    <div class="card-header">
        <span
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="External tools with data on this taxon" >
        Tools
        </span>
        <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;" id="toolsCardBadge"></span>
        </div>
        <div class="list-group  list-group-flush" id="toolsCardContent">
    Loading ...
    </div>
    </div>
    <script>;
        const tc = document.getElementById('toolsCard');
        const tcc = document.getElementById('toolsCardContent');
        const tcb = document.getElementById('toolsCardBadge');
        fetch('widget_taxon_link_outs.php?wfo_id=<?php echo $record->getWfoId() ?>')
            .then(response => response.json())
            .then(json => {
                    if(json.count > 0){
                        tc.classList.remove('d-none');
                        tc.classList.add('d-block');
                        tcc.innerHTML = json.body;
                        tcb.innerHTML = json.count;
                    }else{
                        tc.classList.add('d-none');
                        tcc.innerHTML = '';                         
                    }
                }
            );        
    </script>
<?php



    render_snippets($record->getTextSnippets(), $record->getWfoId());

    // references    
    render_references($record->getNomenclaturalReferences(), 'Nomenclatural Resources', "Links to information useful for understanding the nomenclature of this name.");
    render_references($record->getTaxonomicReferences(), 'Taxonomic Sources', "Links information supporting the taxonomy accepted here.");
    render_references($record->getTreatmentReferences(), 'Other Treatments', "Other occurrences of this name that may be useful, including alternative taxonomic views.");

    // unplaced names
    render_name_list($record->getUnplacedNames(), $record, "Unplaced Names", "Names that experts have not yet placed in the classification.");


?>
            </div>
            <!-- Classificaton -->
            <div class="col-4 offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasResponsive"
                aria-labelledby="offcanvasResponsiveLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasResponsiveLabel">Classification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                        data-bs-target="#offcanvasResponsive" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">

                    <div class="row" style="width: 100%">
                        <div class="col">
                            <div class="card shadow-sm bg-secondary-subtle" style="width: 100%">
                                <?php
                            
                            // taxonomic placement

                            // header
                            echo '<div class="card-header">';
                            if($record->getRole() == 'unplaced'){
                                    echo '<span
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    title="The genus part of the names suggests it could occur here in the classification, if it isn\'t synonymised." >Potential Placement</span>&nbsp;';
                            }else{
                                echo '<span
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    title="The position of the taxon within the classification." >Placement</span>&nbsp;';
                            }
                            
                            
                            // list depends on role
                            switch ($record->getRole()) {

                                // placement of a synonym
                                case 'synonym':
                                $accepted = new TaxonRecord($record->getAcceptedId());
                                $ancestors = $accepted->getPath(); // get the path to the root
                                $ancestors = array_reverse($ancestors); // reverse order
                                array_shift($ancestors); // remove 'code'
                                
                                // add badge
                                echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($ancestors), 0)  . '</span>';
                                echo '</div>'; // end header
                                echo '<div class="list-group  list-group-flush">'; // start list

                                // write in the path to the accepted name
                                render_ancestors($ancestors, false);

                                // add in self as a synonym
                                echo "<a href=\"{$record->getWfoId()}\" class=\"list-group-item list-group-item-action
                                    disabled\">";
                                    echo '<div class="row gx-1">';
                                        echo '<div class="col-4 text-end" style="font-size:90%">synonym:</div>';
                                        echo '<div class="col text-start fw-bold">' .
                                            $record->getFullNameStringNoAuthorsHtml() . '</div>';
                                        echo '</div>'; // end row
                                    echo "</a>";

                                break;

                                // Unplaced - we try and find something!
                                case 'unplaced':
                                    $candidates = $record->getAssociatedGenusNames();
                                    if($candidates){

                                    if(count($candidates) == 1 && $candidates[0]->getRole() == 'accepted'){
                                        $candidate = new TaxonRecord($candidates[0]->getId() . '-'. WFO_DEFAULT_VERSION); // convert to taxon object
                                        $ancestors = $candidate->getPath(); // get the path to the root
                                        $ancestors = array_reverse($ancestors); // reverse order
                                        array_shift($ancestors); // remove 'code'
                                        
                                        // add badge
                                        echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($ancestors), 0)  . '</span>';
                                        echo '</div>'; // end header
                                        echo '<div class="list-group  list-group-flush">'; // start list
                                        render_ancestors($ancestors, false); // write it out

                                    }else{
                                        
                                        // add badge
                                        echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($candidates), 0)  . '</span>';
                                        echo '</div>'; // end header
                                        echo '<div class="list-group  list-group-flush">'; // start list
                                        render_ancestors($candidates, false);
                                    }

                                    }else{
                                        // no badge
                                        echo '</div>'; // end header
                                        echo '<div class="list-group  list-group-flush">'; // start list   
                                        echo "<a class=\"list-group-item list-group-item-action disabled\">Unknown</a>";
                                    }
                                    break;

                                case 'deprecated':
                                    echo '</div>'; // end header
                                    echo '<div class="list-group  list-group-flush">'; // start list  
                                    echo "<a class=\"list-group-item list-group-item-action disabled\">Deprecated names will not be placed in the classification.</a>";
                                    break;

                                // by default we treat it like it is accepted
                                default:
                                    $ancestors = $record->getPath(); // get the path to the root
                                    $ancestors = array_reverse($ancestors); // reverse order
                                    array_shift($ancestors); // remove 'code'

                                    // badge
                                    echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($ancestors), 0)  . '</span>';
                                    echo '</div>'; // end header
                                    echo '<div class="list-group  list-group-flush">'; // start list
                                    render_ancestors( $ancestors, true);
                                    break;

                            } // end switch
                                ?>
                            </div>
                        </div>
                        <?php

                        // children
                        $kids = $record->getChildren();
                        if($kids){
                            echo '<div class="card shadow-sm bg-secondary-subtle" style="width: 100%">';
                            echo '<div class="card-header">';
                             echo '<span
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    title="Direct descendants of this taxon." >Child Taxa</span>&nbsp;';
                            echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($kids), 0)  . '</span>  </div>';
                            echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';

                            for($i = 0; $i < count($kids); $i++){
                                $kid = $kids[$i];
                                echo "<a href=\"{$kid->getWfoId()}\" class=\"list-group-item  list-group-item-action\">{$kid->getFullNameStringHtml()}</a>";
                            }

                            echo '</div>'; // end list
                            echo '</div>'; // end card
                        
                        }

                        // Do we have siblings?
                        $parent = $record->getParent();
                        if($parent){
                            $siblings = $parent->getChildren();
                            if(count($siblings) > 1){

                                echo '<div class="card shadow-sm bg-secondary-subtle" style="width: 100%">';
                                echo '<div class="card-header">';
                                echo '<span
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="left"
                                        title="Taxa at the same level in the classification." >Sibling Taxa</span>&nbsp;';
                                echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($siblings), 0)  . '</span>  </div>';
                                echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';

                                for($i = 0; $i < count($siblings); $i++){
                                    $bro = $siblings[$i];
                                    $disabled = $bro->getId() == $record->getId() ? 'disabled' : '';
                                    echo "<a href=\"{$bro->getWfoId()}\" class=\"list-group-item  list-group-item-action $disabled\" >{$bro->getFullNameStringHtml()}</a>";
                                }

                                echo '</div>'; // end list
                                echo '</div>'; // end card

                            } // has more than one sibling
                        } // has parent

                        // taxonomic experts (included TENs and then Editors)
                        $experts = $record->getExperts();
                        if($experts){
                            echo '<div class="card shadow-sm bg-secondary-subtle" style="width: 100%">';
                            echo '<div class="card-header">';
                            echo '<span
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    title="Individuals and networks of experts who curate the taxonomy of this taxon in WFO Plant List" >Taxonomic Experts</span>&nbsp;';
                            echo '<span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($experts), 0)  . '</span>  </div>';
                            echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';

                            foreach($experts as $expert){
                                echo '<div class="list-group-item">';
                                echo $expert->name;
                                echo " ";
                                echo $expert->description;
                                echo '</div>';
                            }

                            echo '</div>'; // end list
                            echo '</div>'; // end card

                        }

                                      
                        ?>
                    </div> <!-- end col -->
                </div><!-- end row -->

            </div>
        </div>
</div>

</div>
</form>
</div>

<?php
require_once('footer.php');


/**
 * Called to render all the snippet texts
 * but each category of snippet is then
 * rendered as its own card in the interface
 */
function render_snippets($snippets, $current_wfo_id){

    if(count($snippets) == 0 ) return;

    echo '<div class="card">';
    echo '<div class="card-header bg-secondary-subtle">';

    // tabs in the header
    echo '<ul class="nav nav-tabs card-header-tabs">';
    //echo '<span class="nav-link disabled" >Text</span>';

    $first = true;
    foreach($snippets as $category => $snips){

        echo '<li class="nav-item">';

        // tool tip
        echo '<span
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="'. ucfirst($category) .' text from published sources" >';

        // tab button
        if($first){
            echo '<button class="nav-link active" id="'. $category .'-tab" data-bs-toggle="tab" data-bs-target="#' . $category . '" type="button" role="tab" aria-controls="'. $category .'" aria-selected="true">';
            $first = false;
        }else{
            echo '<button class="nav-link" id="'. $category .'-tab" data-bs-toggle="tab" data-bs-target="#' . $category . '" type="button" role="tab" aria-controls="'. $category .'" aria-selected="false">';
        }
        echo ucfirst($category);
        echo ' <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($snips), 0)  .'</span> ';
        echo '</button>';
        
        echo '</span>'; // end tooltip

        echo '</li>';
    }
    echo '</ul>'; // tab list
    
    echo '</div>'; // end header

    // body
    echo '<div class="card-body tab-content" style="max-height: 40em; overflow: auto;">';

    $first = true;
    foreach($snippets as $category => $snips){
        if($first){
            echo '<div class="tab-pane fade show active" id="'. $category .'" role="tabpanel" aria-labelledby="home-tab">';
            $first = false;
        }else{
            echo '<div class="tab-pane fade" id="'. $category .'" role="tabpanel" aria-labelledby="home-tab">';
        }
        render_snippet_category_body($category, $snips, $current_wfo_id);
        echo '</div>';
    }

    echo "</div>"; // card body
    echo '</div>'; // end card
   
}


// render one of the categories of snippet
function render_snippet_category_body($category, $snippets, $current_wfo_id){

    //print_r($snippets);

    $done_one = false;
    foreach($snippets as $snippet){

        // separator line
        if($done_one) echo "<hr/>";
        else $done_one = true;

        // the body
        echo '<p>';
        echo $snippet->body;
        echo '</p>';

        // the metadata

        $prov_data = (object)array(
            'kind' => 'snippet_source',
            'source_id' => $snippet->source_id
        );

        $prov_json = urlencode(json_encode($prov_data));

        echo '<p>';

        echo 'From a treatment in <a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $prov_json . '"><em>'. $snippet->source_name .'</em></a>';

        if($snippet->described_wfo_id == $current_wfo_id){
            echo ' describing a taxon with this name ';
        }else{
            echo ' describing ';
            $syn = new TaxonRecord($snippet->described_wfo_id . '-' . WFO_DEFAULT_VERSION);
            echo "<a href=\"{$syn->getWfoId()}\">{$syn->getFullNameStringHtml()}</a>";
            echo ' (which is a synonym of this taxon under the current classification)';
        }

        echo " in  $snippet->language_label. ";
        $prov_data = (object)array(
            'kind' => 'snippet',
            'source_id' => $snippet->id
        );

        $prov_json = urlencode(json_encode($prov_data));

        echo ' <strong>Imported: </strong> ' . $snippet->imported;
        echo '&nbsp;[<a href="#" data-bs-toggle="modal" data-bs-target="#dataProvModal" data-wfoprov="' . $prov_json . '" style="cursor: pointer;">';
        echo  'Data provenance';
        echo '</a>]';

        echo '</p>';


    }


}


function render_references($refs_all, $title, $help = ''){

    // filter out undesirables
    $refs = array();

    if(!$refs_all) return $refs;

    foreach($refs_all as $ref){
        // we don't render the old plantlist links
        if(strpos($ref->uri, 'theplantlist.org')) continue;
        $refs[] = $ref;
    }

    // render nothing if we have nothing
    if(count($refs) == 0) return;
    
    // render the card
    echo '<div class="card shadow-sm bg-secondary-subtle">';
    echo '<div class="card-header">';
    echo '<span
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="'. $help .'" >';
    echo $title;
    echo '</span>';
    echo ' <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($refs), 0)  .'</span> </div>';
    echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';
    
    // people first
    foreach($refs as $ref){
        if ($ref->kind != 'person') continue;
        render_reference($ref);
    }
    // literature
    foreach($refs as $ref){
        if ($ref->kind != 'literature') continue;
        render_reference($ref);
    }
    // specimens
    foreach($refs as $ref){
        if ($ref->kind != 'specimen') continue;
        render_reference($ref);
    }
    // database
    foreach($refs as $ref){
        if ($ref->kind != 'database') continue;
        render_reference($ref);
    }

    echo '</div>'; // end list group
    echo '</div>'; // end card

}

function render_reference($ref){


    echo "<a href=\"{$ref->uri}\" class=\"list-group-item  list-group-item-action\" target=\"{$ref->kind}\">";
    echo '<div class="row">';

    echo '<div class="col-1 d-none d-md-block">'; 
    if($ref->thumbnailUri){
        echo "<img src=\"$ref->thumbnailUri\" style=\"max-width: 50px\" />";
    }else{
        switch ($ref->kind) {
            case 'database':
                echo '<img src="../images/database.png" style="max-width: 50px" />';
                break;
            case 'person':
                echo '<img src="../images/person.png" style="max-width: 50px; margin-top: 0.5em"/>';
                break;
            case 'literature':
                echo '<img src="../images/literature.png" style="max-width: 50px; margin-top: 0.5em"/>';
                break;
            case 'specimen':
                echo '<img src="../images/literature.png" style="max-width: 50px; margin-top: 0.5em"/>';
                break;
            default:
                echo '<div style=\"max-width: 50px\"  >&nbsp;</div>';
                break;
        }
    }
    echo '</div>';
    echo '<div class="col">';
    echo "<p><span class=\"fw-bold\">$ref->label</span><br/>$ref->comment</p>";
    echo '</div>';
    echo '</div>'; // end row
    echo "</a>";
}

function render_ancestors($ancestors, $disable_last = true){

            for($i = 0; $i < count($ancestors); $i++){
                $anc = $ancestors[$i];
                $disabled = $i == count($ancestors) - 1 &&  $disable_last ? 'disabled' : '';

                echo "<a href=\"{$anc->getWfoId()}\" class=\"list-group-item  list-group-item-action $disabled\">";
                echo '<div class="row gx-1">';
                echo '<div class="col-4 text-end" style="font-size:90%">' . $anc->getRank() . ':</div>';
                echo '<div class="col text-start fw-bold">' . $anc->getFullNameStringNoAuthorsHtml() .'</div>';
                echo '</div>'; // end row
                echo "</a>";
            }
}


function render_name_list($names, $record, $title, $help){

    if(!$names) return;

    echo '<div class="card shadow-sm bg-secondary-subtle">';
    echo '<div class="card-header">';
    echo '<span
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="'. $help .'" >';
    echo $title;
    echo '</span>';
    echo ' <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($names), 0)  .'</span> </div>';
    echo '<div class="list-group  list-group-flush" style="max-height: 10em; overflow: auto;">';
    for($i = 0; $i < count($names); $i++){
        $n = $names[$i];

        // is this homotypic with the current record?
        if($n->getBasionymWfoId() == $record->getWfoId() || $record->getBasionymWfoId() == $n->getWfoId()){
            $status = "<span class=\"fw-bold\">[{$n->getNomenclaturalStatus()} : homotypic]</span>";
        }else{
            $status = "<span class=\"fw-bold\">[{$n->getNomenclaturalStatus()}]</span>";
        }

        echo "<a href=\"{$n->getWfoId()}\" class=\"list-group-item  list-group-item-action\">{$n->getFullNameStringHtml()} $status</a>";
    }
    echo '</div>'; // end list group
    echo '</div>'; // end card

}

?>