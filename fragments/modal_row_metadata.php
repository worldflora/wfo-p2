<?php
/*
    Modal to display row level metadata 
    Is launched from the facet prov modal
    Or directly from a snippet diplay
*/
?>
<div class="modal fade" id="dataProvModal" tabindex="-1" aria-labelledby="provModalLabel2"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="provModalLabel2">Row level metadata</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div id="dataProvModalContent">
                <ul class="list-group  list-group-flush" ></ul>
            </div>
            <div class="modal-footer">
                    <button
                    id="dataProvModalBackButton"
                    type="button"
                    aria-label="Close"
                    data-bs-toggle="modal"
                    data-bs-target="#facetProvModal"
                    class="btn btn-primary"
                    style="cursor: pointer;"
                    >&#8678; Back</button>
                <button 
                    type="button"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                    class="btn btn-primary"
                    >Close</button>
            </div>
        </div>
    </div>
</div>
<template id="datasourceMetadataRow">
    <li class="list-group-item wfo-meta-row" >
        <div class="row gx-1">
            <div class="col-3 text-end fw-bold"></div>
            <div class="col"></div>
        </div>
    </li>
</template>

<script>
// modal dialogue - load content on show
document.getElementById('dataProvModal').addEventListener('show.bs.modal', event => {
    
    // data passed from click event
    const dataset = event.relatedTarget.dataset;

    // we can be called from a facet or from a snippet

    let row_metadata = null;
    if(dataset.facetId){
        // were are rendering a facet metadata row which we pull from
        // the big json object in the page
        const facetMetadata = JSON.parse(document.getElementById('facetsMetadata').innerHTML);
        const facet = facetMetadata[dataset.facetId];
        const facetValue = facet.facet_values[dataset.facetValueId];
        let source = facetValue.sources[dataset.sourceId];
        row_metadata = source.score_metadata;

        // make sure the backbutton is visible
        document.querySelector("#dataProvModalBackButton").hidden = false;
    }else{
        // we are rendering a snippet and the json is in the 
        // refering element
        const json = event.relatedTarget.parentElement.querySelector("script.wfo-row-metadata").innerHTML;
        const meta = JSON.parse(json);
        console.log(meta);
        row_metadata = meta.row_metadata;

        // Hide the back button because we will have got here directly
        document.querySelector("#dataProvModalBackButton").hidden = true;
    }


    // where we will put it
    const listGroup = document.querySelector("#dataProvModal ul");
    const template = document.querySelector("#datasourceMetadataRow");

    // remove any old ones first
    listGroup.querySelectorAll(".wfo-meta-row").forEach(li => {li.remove()});

    for(const key in row_metadata){
        let val = row_metadata[key];

        const clone = document.importNode(template.content, true);

        clone.querySelector("li div div:nth-child(1)").innerHTML = key + ": ";

        if(URL.canParse(val)){
            clone.querySelector("li div div:nth-child(2)").innerHTML = `<a href="${val}" target="meta-link">${val}<a>&nbsp;↗`;
        }else{
            clone.querySelector("li div div:nth-child(2)").innerHTML = val;
        }

        listGroup.appendChild(clone);
    }

})
</script>
