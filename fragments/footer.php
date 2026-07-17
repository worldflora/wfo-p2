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

<?php
  // modal dialogues are all put at the bottom
  require_once('../fragments/modal_facet_prov.php');
  require_once('../fragments/modal_row_metadata.php');
  require_once('../fragments/modal_data_source.php');
  require_once('../fragments/modal_data_version.php');
  require_once('../fragments/modal_copyright.php');
  require_once('../fragments/modal_cite.php');
?>

<script src="theme/js/bootstrap.bundle.min.js">
</script>
<script src="js/main.js"></script>


</body>

</html>