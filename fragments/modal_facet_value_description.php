<!-- Data version -->
<div class="modal fade modal-lg" id="facetValueDescriptionModal" tabindex="-1" aria-labelledby="facetValueDescriptionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="facetValueDescriptionModalLabel">Facet value description</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="facetValueDescriptionModalBody">
        This is the facet description.
      </div>
      <div class="modal-footer">
          <button
            id="facetValueDescriptionModalBackButton"
            type="button"
            aria-label="Back"
            data-bs-toggle="modal"
            data-bs-target="#facetDescriptionModal"
            data-back-button="1"
            class="btn btn-primary"
            style="cursor: pointer;"
            >&#8678; Facet</button>
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
    const facetValueDescriptionModal = document.getElementById('facetValueDescriptionModal');
    facetValueDescriptionModal.addEventListener('show.bs.modal', function (event) {

          // data passed from click event
          const dataset = event.relatedTarget.dataset;

          const modalContent = document.getElementById('facetValueDescriptionModalBody');
          modalContent.innerHTML = 'Loading ...';

          document.getElementById('facetValueDescriptionModalBackButton').setAttribute('data-facet-id', dataset.facetId);
          document.getElementById('facetValueDescriptionModalBackButton').setAttribute('data-facet-value-id', dataset.facetValueId);

          fetch("/modal_content_facet_value_description.php?facet_id=" + dataset.facetId + "&facet_value_id=" + dataset.facetValueId )
              .then(response => response.text())
              .then(text => modalContent.innerHTML = text);


    });
</script>