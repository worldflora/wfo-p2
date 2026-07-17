<!-- Data version -->
<div class="modal fade modal-xl" id="classification_modal" tabindex="-1" aria-labelledby="classification_modal_label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="classification_modal_label">Index State</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="classification_modal_body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    // load the version content on demand
    const myModalEl = document.getElementById('classification_modal');
    myModalEl.addEventListener('show.bs.modal', function (event) {
           const modalContent = document.getElementById('classification_modal_body');
            modalContent.innerHTML = 'Loading ...';
            // we pass the wfo id if there is one
            fetch("footer_modal_version.php?wfo=<?php if(isset($wfo)){echo $wfo;} else { echo ''; } ?>")
                .then(response => response.text())
                .then(text => modalContent.innerHTML = text);
    });
</script>