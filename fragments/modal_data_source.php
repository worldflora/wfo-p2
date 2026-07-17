<?php
/*
    A modal to display the datasource details anywhere in the page.

*/

?>
<div class="modal fade" id="dataSourceModal" tabindex="-1" aria-labelledby="dataSourceModal"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="provModalLabel">Data source details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="dataSourceModalContent">
                <h4 id="dataSourceModalContentName">name</h4>
                <div id="dataSourceModalContentDescription" >description</div>
                <hr/>
                <p><strong>Source Link: </strong><a href="#" target="data-source" id="dataSourceModalContentLink">link</a>&nbsp;↗</p>
                <p><strong>Source file: </strong><a href="#" target="github" id="dataSourceModalContentFile" >View on GitHub</a></p>
                <p><strong>Last import: </strong><span id="dataSourceModalContentMod">date</span></p>
            <div class="modal-footer">
                <button
                    id="dataSourceModalBackButton"
                    type="button"
                    aria-label="Close"
                    data-bs-toggle="modal"
                    data-bs-target="#facetProvModal"
                    class="btn btn-primary"
                    style="cursor: pointer;"
                    >&#8678; Back</button>
                <button type="button" data-bs-dismiss="modal" aria-label="Close"
                    class="btn btn-primary">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// modal dialogue - load content on show
document.getElementById('dataSourceModal').addEventListener('show.bs.modal', event => {

    // data passed from click event
    const dataset = event.relatedTarget.dataset;

    // load data from json in page
    const sourceMetadata = JSON.parse(document.getElementById(dataset.sourceMetadataId).innerHTML);

    console.log(sourceMetadata);
    
    // write in the data
    document.querySelector("#dataSourceModalContentName").innerHTML = sourceMetadata.name;
    document.querySelector("#dataSourceModalContentLink").innerHTML = sourceMetadata.name;
    document.querySelector("#dataSourceModalContentLink").setAttribute('href', sourceMetadata.link_uri);

    document.querySelector("#dataSourceModalContentDescription").innerHTML = sourceMetadata.description;
    document.querySelector("#dataSourceModalContentMod").innerHTML = sourceMetadata.last_import;
    document.querySelector("#dataSourceModalContentFile").setAttribute('href', 'https://github.com/worldflora/wfo-text-content/blob/main/' + sourceMetadata.git_file_path);

    // disable the back back button if we are not viewing a facet source
    if(sourceMetadata.facet_value_id){
        document.querySelector("#dataSourceModalBackButton").hidden = false;
    }else{
        document.querySelector("#dataSourceModalBackButton").hidden = true;
    }


})
</script>