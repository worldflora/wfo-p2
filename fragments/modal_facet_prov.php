<?php
/*
    Modal dialogue displayed on record page that pops up when
    you click on the badge next to a facet value
    Will display the data sources for that assertion
*/
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
                <ul class="list-group  list-group-flush" >
                    <li class="list-group-item wfo-meta-row" >
                        <div class="row gx-1">
                            <div class="col-2 text-end fw-bold">Taxon:</div>
                            <div class="col" id="facetModalTaxonName">Taxon Name</div>
                        <div>
                    </li>
                    <li class="list-group-item gx-1 wfo-meta-row" >
                        <div class="row gx-1">
                        <div class="col-2 text-end fw-bold">Attribute:</div>
                        <div class="col">
                            <div><span id="facetModalFacetName">facet_name</span> - <span  id="facetModalFacetValueName" >facet_value_name</span></div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" data-bs-dismiss="modal" aria-label="Close"
                    class="btn btn-primary">Close</button>
            </div>
        </div>
    </div>
</div>
<template id="datasourceRow">
    <li class="list-group-item gx-1 wfo-ds-row" >
        <div class="row gx-1">
            <div class="col-2 text-end fw-bold wfo-ds-row">Source&nbsp;<span>n</span>:</div>
            <div class="col"><div>source_name</div>
                scored <strong>method</strong><span>Loading ... </span>
                &nbsp;[<a 
                    href="#"
                    data-dismiss="modal"
                    data-bs-toggle="modal"
                    data-bs-target="#dataProvModal"
                    data-facet-id=""
                    data-facet-value-id=""
                    data-source-id=""
                    data-taxon-name=""
                    style="cursor: pointer;">row level metadata</a>]
            </div>
            
        </div>
    </li>
</template>

<script>
// modal dialogue - load content on show
document.getElementById('facetProvModal').addEventListener('show.bs.modal', event => {

    // data passed from click event
    const dataset = event.relatedTarget.dataset;

    // if we haven't got a facet value we are being called by the back button
    // so don't change existing values
    if (!dataset.facetValueId) return;

    // load data from json in page
    const facetMetadata = JSON.parse(document.getElementById('facetsMetadata').innerHTML);
    const facet = facetMetadata[dataset.facetId];
    const facetValue = facet.facet_values[dataset.facetValueId];
    
    // write in the data
    document.getElementById('facetModalTaxonName').innerHTML = atob(dataset.taxonName);
    document.getElementById('facetModalFacetName').innerHTML = facet.facet_name;
    document.getElementById('facetModalFacetValueName').innerHTML = facetValue.facet_value_name;

    const listGroup = document.querySelector("#facetProvModalContent ul");
    const template = document.querySelector("#datasourceRow");

    // remove any old ones first
    listGroup.querySelectorAll(".wfo-ds-row").forEach(li => {li.remove()});

    // insert a li for each source
    let count = 1;
    for(const sourceId in facetValue.sources){
        
        let source = facetValue.sources[sourceId];

        const clone = document.importNode(template.content, true);
    
        // the count of the source
        clone.querySelector("li div div span").innerHTML = count;
        count++;

        // source name
        // we have the name but if there is source object in the index we want to 
        // be able to display a link to it (it may not be there)

        // put the cached name is as a place holder
        clone.querySelector("li div div div").innerHTML = source.source_name;

        // tag the element with a unique id so the ajax call can find it later
        const sourceCelId = 'wfo-' + Math.random().toString(36).substring(2, 20);
        clone.querySelector("li div div div").setAttribute('id', sourceCelId);
        
        // set up an ajax call to populate the 
        fetch("link_to_data_source.php?id=" + source.source_id)
            .then(response => response.text())
            .then((text) => {
                // only replace the text if we are returned a useful value
                if(text)document.querySelector("#" + sourceCelId).innerHTML = text;
            });

        // method of scoring
        if (source.scored_via == 'direct'){
            clone.querySelector("li div div strong").innerHTML = 'directly to: ';
        }
        if (source.scored_via == 'synonym'){                            
            clone.querySelector("li div div strong").innerHTML = 'the synonym: ';
        }
            if (source.scored_via == 'ancestor'){
            clone.querySelector("li div div strong").innerHTML = 'the ancestor: ';
        }

        // add the values to the model launch button
        clone.querySelector("li div:nth-child(2) a").setAttribute('data-facet-id', facet.facet_id);
        clone.querySelector("li div:nth-child(2) a").setAttribute('data-facet-value-id', facetValue.facet_value_id);
        clone.querySelector("li div:nth-child(2) a").setAttribute('data-source-id', source.source_id);
        clone.querySelector("li div:nth-child(2) a").setAttribute('data-taxon-name', dataset.taxonName);
        
        // add an id to the name span so we can ajax update it
        const rando = 'wfo-' + Math.random().toString(36).substring(2, 20);
        clone.querySelector("li div:nth-child(2) span").setAttribute('id', rando);

        // set up an ajax call to populate that cell
        fetch("link_to_name.php?id=" + source.scored_wfo_id)
            .then(response => response.text())
            .then((text) => {
                document.querySelector("#" + rando).innerHTML = text
            });

        listGroup.appendChild(clone);

    
    }

})
</script>