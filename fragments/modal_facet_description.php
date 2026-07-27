<!-- Data version -->
<div class="modal fade modal-lg" id="facetDescriptionModal" tabindex="-1" aria-labelledby="facetDescriptionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="facetDescriptionModalLabel">Facet description</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="facetDescriptionModalBody" >
        This is the facet description.
      </div>
      <div class="modal-footer">
          <button
            id="facetDescriptionModalBackButton"
            type="button"
            aria-label="Back"
            data-bs-toggle="modal"
            data-bs-target="#facetProvModal"
            class="btn btn-primary"
            style="cursor: pointer;"
            >&#8678; Back</button>
        <button 
          type="button"
          class="btn btn-primary"
          data-bs-dismiss="modal"
          >Close</button>
      </div>
    </div>
  </div>
</div>

<script>

    // load the facet data on demand
    const facetDescriptionModal = document.getElementById('facetDescriptionModal');
    facetDescriptionModal.addEventListener('show.bs.modal', function (event) {

          // data passed from click event
          const dataset = event.relatedTarget.dataset;

          const modalContent = document.getElementById('facetDescriptionModalBody');
          modalContent.innerHTML = 'Loading ...';

          fetch("/modal_content_facet_description.php?facet_id=" + dataset.facetId)
              .then(response => response.text())
              .then(text => modalContent.innerHTML = text);


          if(dataset.backButton){
            // make sure the backbutton is visible
            document.querySelector("#facetDescriptionModalBackButton").hidden = false;
          }else{
            document.querySelector("#facetDescriptionModalBackButton").hidden = true;
          }



    });
</script>