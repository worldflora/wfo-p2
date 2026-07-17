<?php

    $classification_date = DateTime::createFromFormat('Y-m-d', $classification_version . '-01');
    
?>

</main>
       
<footer class="footer border-top">
  <div class="container">
        <ul class="nav navbar justify-content-center pb-1 mb-3 " >
            <li class="nav-item"><button class="nav-link" style="color: gray;" data-bs-toggle="modal" data-bs-target="#copyright_modal">Copyright</button></li>
            <li class="nav-item"><button class="nav-link" style="color: gray;" data-bs-toggle="modal" data-bs-target="#cite_modal">How to cite</button></li>
            <li class="nav-item"><a href="mailto:contact@worldfloraonline.org?subject=Enquiry from portal"
                class="nav-link" style="color: gray;">Contact</a></li>
            <li class="nav-item"><a href="#" class="nav-link" style="color: gray;">Terms of Use</a></li>
            <li class="nav-item"><a href="#" class="nav-link" style="color: gray;">Privacy</a></li>
            <li class="nav-item"><button href="#" class="nav-link" style="color: gray;">Social Media</button></li>
            <li class="nav-item"><button class="nav-link" style="color: gray;" data-bs-toggle="modal" data-bs-target="#classification_modal">Index State</button></li>
        </ul>
  </div>
</footer>

<!-- FOOTER MODALS -->

<?php
 require_once('../fragments/modal_data_source.php');
?>


<!-- Copyright -->
<div class="modal fade" id="copyright_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">How to cite</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Unless otherwise noted, text and images are licenced: CC BY 4.0</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Cite -->
<div class="modal fade" id="cite_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">How to cite</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Cite as:
        "World Flora Online. Published on the Internet; http://www.worldfloraonline.org. Accessed on: <?php echo date('d M Y') ?>"</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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



<script src="theme/js/bootstrap.bundle.min.js">
</script>
<script src="js/main.js"></script>


</body>

</html>