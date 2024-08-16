<?php

$wfo = $path_parts[0];

$record = new TaxonRecord($wfo . '-' . WFO_DEFAULT_VERSION); 

$page_title = $record->getFullNameStringPlain();


require_once('header.php');
?>

<div class="container-lg">
    <form role="form" method="GET" action="search">
        <div class="row">
            <!-- main content -->
            <div class="col">
                <div data-bs-toggle="offcanvas" style="float: right;">
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasResponsive"
                        aria-controls="offcanvasResponsive">Classification</button>
                </div>

                <div style="margin-bottom: 0.5em;">
                    <p style="float: right; margin-bottom: 0px;"><a href=""><?php echo $path_parts[0] ?></a></p>


                    <?php

    // description of object
    switch ($record->getRole()) {
        case 'accepted':
           
            if(strpos($record->getFullNameStringPlain(), '×') !== false) $desc = "Accepted hybrid " . $record->getRank();
            else $desc = "Accepted " . $record->getRank();
            $colour = 'green';
            break;
        case 'synonym':
            $desc = "Synonymous " . $record->getRank() . " name";
            $colour = 'blue';
            break;
        case 'unplaced':
            $desc = "Unplaced " . $record->getRank() . " name";
            $colour = 'black';
            break;
        case 'deprecated':
            $desc = "Deprecated " . $record->getRank() . " name";
            $colour = 'red';
            break;
        default:
            $desc = "";
            $colour = 'black';
            break;
    }

    echo "<p class=\"fw-bold\" style=\"color: $colour; margin-bottom: 0px;\">$desc</p>";

?>
                </div>
                <h1><?php echo  $record->getFullNameStringHtml(); ?></h1>
                <p>&nbsp;</p>

                <?php
// Synonyms
                 $syns = $record->getSynonyms();
                 if($syns){
                    echo '<div class="card">';
                 echo '<div class="card-header">Synonyms <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($syns), 0)  .'</span> </div>';
                       echo '<div class="list-group  list-group-flush" style="max-height: 10em; overflow: auto;">';
                    for($i = 0; $i < count($syns); $i++){
                        $syn = $syns[$i];
                        echo "<a href=\"{$syn->getWfoId()}\" class=\"list-group-item  list-group-item-action\">{$syn->getFullNameStringHtml()}</a>";
                    }
                    echo '</div>'; // end list group
                    echo '</div>'; // end card
                 }

?>


                <?php


?>

            </div>

            <!-- Classificaton -->
            <div class="col-4 offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasResponsive"
                aria-labelledby="offcanvasResponsiveLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasResponsiveLabel">Classification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                        data-bs-target="#offcanvasResponsive" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">

                    <div class="row" style="width: 100%">
                        <div class="col">


                            <div class="card" style="width: 100%">
                                <div class="card-header">Placement</div>
                                <div class="list-group  list-group-flush">
                                    <?php
                                    $ancestors = $record->getPath();
                                    $ancestors = array_reverse($ancestors);
                                    array_shift($ancestors);

                                    for($i = 0; $i < count($ancestors); $i++){
                                        $anc = $ancestors[$i];
                                        $disabled = $i == count($ancestors) - 1 ? 'disabled' : '';

                                        echo "<a href=\"{$anc->getWfoId()}\" class=\"list-group-item  list-group-item-action $disabled\">";
                                        echo '<div class="row gx-1">';
                                        echo '<div class="col-4 text-end" style="font-size:90%">' . $anc->getRank() . ':</div>';
                                        echo '<div class="col text-start fw-bold">' . $anc->getFullNameStringNoAuthorsHtml() . '</div>';
                                        echo '</div>'; // end row
                                        
                                        echo "</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div>&nbsp;</div>

                            <?php

                         // children
                         $kids = $record->getChildren();

                        if($kids){
                            echo '<div class="card" style="width: 100%">';
                            echo '<div class="card-header">Child Taxa  <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($kids), 0)  . '</span>  </div>';
                            echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';

                            for($i = 0; $i < count($kids); $i++){
                                $kid = $kids[$i];
                                echo "<a href=\"{$kid->getWfoId()}\" class=\"list-group-item  list-group-item-action\">{$kid->getFullNameStringHtml()}</a>";
                            }

                            echo '</div>'; // end list
                            echo '</div>'; // end card
                        
                        }
                                      
                        ?>


                        </div> <!-- end col -->
                    </div><!-- end row -->

                </div>
            </div>
        </div>

</div>
</form>
</div>

<?php
require_once('footer.php');
?>