<?php

    $classification_date = DateTime::createFromFormat('Y-m-d', $classification_version . '-01');
    
?>

</main>
       
<footer class="footer container-lg border-top" >

<div class="row align-items-start">

    <div class="col-md">
      <p>
        <h4>World Flora Online</h4>
        c/o Geneva Botanic Garden</br>
        1 Chemin de l'Impérarice</br>
        1292 Chambésy - Geneva</br>
        Switzerland</br>
        <a href="mailto:contact@worldfloraonline.org">contact@worldfloraonline.org</a></br>
      </p>
      <p>
      <a href="/pages/terms.md" >Terms of Use</a>
      |
      <a href="/pages/copyright.md" >Copyright</a>
      |
      <a href="/pages/cookies.md" >Cookies</a>
      </p>
    </div>

    <div class="col-md">
      <p>
        <h4>Follow us</h4>
        <p>
          <a href="https://www.linkedin.com/company/world-flora-online/posts/">LinkedIn</a><br/>
          <a href="https://www.facebook.com/WorldFloraOnline">Facebook</a><br/>
          <a href="https://twitter.com/TheWorldFlora">Twitter</a>
        </p>
    </div>

    <div class="col-md text-end">
      <img src="/images/wfo_logo_mono_gray.svg" alt="World Flora Online Logo in gray" style="width: 100%;margin-right: 1em; margin-top: 1em;"/>
    </div>

  </div>
<!--
  <div class="container">
        <ul class="nav navbar justify-content-center pb-1 mb-3 " >
            <li class="nav-item"><a href="/pages/copyright.md" class="nav-link" style="color: gray;">Copyright</a></li>
            <li class="nav-item"><a href="/pages/cite.md" class="nav-link" style="color: gray;">How to cite</a></li>
            <li class="nav-item"><a href="mailto:contact@worldfloraonline.org?subject=Enquiry from portal" class="nav-link" style="color: gray;">Contact</a></li>
            <li class="nav-item"><a href="/pages/terms.md" class="nav-link" style="color: gray;">Terms of Use</a></li>
            <li class="nav-item"><a href="/pages/privacy.md" class="nav-link" style="color: gray;">Privacy</a></li>
            <li class="nav-item"><a href="/pages/socials.md" class="nav-link" style="color: gray;">Social Media</a></li>
            <li class="nav-item"><button class="nav-link" style="color: gray;" data-bs-toggle="modal" data-bs-target="#classification_modal">Index State</button></li>
        </ul>
  </div>
-->
  <div class="wfo-botguard">
    <?php echo BotGuard::summaryString(); ?> 
    <a data-bs-toggle="modal" data-bs-target="#classification_modal">| Index State</a>
  </div>
</footer>

<?php
  // modal dialogues are all put at the bottom
  require_once('../fragments/modal_facet_description.php');
  require_once('../fragments/modal_facet_value_description.php');
  require_once('../fragments/modal_facet_prov.php');
  require_once('../fragments/modal_row_metadata.php');
  require_once('../fragments/modal_data_source.php');
  require_once('../fragments/modal_data_version.php');
  require_once('../fragments/modal_download_progress.php');
?>

<script src="/theme/js/bootstrap.bundle.min.js">
</script>
<script src="/js/main.js"></script>


</body>

</html>