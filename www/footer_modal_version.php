<?php

require_once('../config.php');
require_once('../includes/SolrIndex.php');

 $classification_date = DateTime::createFromFormat('Y-m-d', $classification_version . '-01');
?>





<ul class="list-group list-group-flush">
  <li class="list-group-item"><strong>Classification: </strong> <?php echo $classification_date->format('F') . ' ' . $classification_date->format('Y') ?></li>
  <li class="list-group-item">Most recently updated</li>
  <li class="list-group-item">Oldest update</li>
  <li class="list-group-item">A fourth item</li>
  <li class="list-group-item">And a fifth one</li>
</ul>