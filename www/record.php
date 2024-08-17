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

                <?php
    echo '<p style="margin-bottom: 0.5em;">';

    // description of record type
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
    
    echo '<span style="margin-bottom: 0px;">';
    echo "<span class=\"fw-bold\" style=\"color: $colour; margin-bottom: 0px;\">$desc:</span>&nbsp;";

    // WFO ID Linking
    echo '<span
            class="fw-bold"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            title="Click to copy persistent URL to clipboard." 
            onclick="navigator.clipboard.writeText(\'https://list.worldfloraonline.org/'. $record->getWfoId() .'\')" 
            />';
    echo $record->getWfoId();
    echo '</span>';

    echo '</p>'; // header p
    
    // header
    echo "<h1 style=\" position: relative;\">{$record->getFullNameStringHtml()}";



    echo "</h1>";
    echo "<p>{$record->getCitationMicro()}</p>";
    
    // link to accepted name
    if($record->getRole() == 'synonym'){
        $accepted = new TaxonRecord($record->getAcceptedId());
        echo '<p class="fw-bold fs-4" >Correct name: ';
        echo "<a href=\"{$accepted->getWfoId()}\">{$accepted->getFullNameStringHtml()}</a>";
        echo '</p>';
    }

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

    // references    
    render_references($record->getNomenclaturalReferences(), 'Nomenclatural Resources');
    render_references($record->getTaxonomicReferences(), 'Taxonomic Sources');
    render_references($record->getTreatmentReferences(), 'Other Treatments');

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

                                    // classification - ancestor path 
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
                        
                        }else{

                            // no children so are there any siblings?
                            $parent = $record->getParent();
                            if($parent){
                                $siblings = $parent->getChildren();
                                if(count($siblings) > 1){

                                    echo '<div class="card" style="width: 100%">';
                                    echo '<div class="card-header">Sibling Taxa  <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($siblings), 0)  . '</span>  </div>';
                                    echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';

                                    for($i = 0; $i < count($siblings); $i++){
                                        $bro = $siblings[$i];
                                        $disabled = $bro->getId() == $record->getId() ? 'disabled' : '';
                                        echo "<a href=\"{$bro->getWfoId()}\" class=\"list-group-item  list-group-item-action $disabled\" >{$bro->getFullNameStringHtml()}</a>";
                                    }

                                    echo '</div>'; // end list
                                    echo '</div>'; // end card

                                } // has more than one sibling
                            } // has parent
                            
                        } // end siblings
                                      
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

function render_references($refs_all, $title){

    // filter out undesirables
    $refs = array();
    foreach($refs_all as $ref){
        // we don't render the old plantlist links
        if(strpos($ref->uri, 'theplantlist.org')) continue;
        $refs[] = $ref;
    }

    // render nothing if we have nothing
    if(count($refs) == 0) return;
    
    // render the card
    echo '<div class="card">';
    echo '<div class="card-header">' . $title . ' <span class="badge rounded-pill text-bg-success" style="font-size: 70%; vertical-align: super;">'. number_format(count($refs), 0)  .'</span> </div>';
    echo '<div class="list-group  list-group-flush" style="max-height: 20em; overflow: auto;">';
    
    // people first
    foreach($refs as $ref){
        if ($ref->kind != 'person') continue;
        render_reference($ref);
    }
    // literature
    foreach($refs as $ref){
        if ($ref->kind != 'literature') continue;
        render_reference($ref);
    }
    // specimens
    foreach($refs as $ref){
        if ($ref->kind != 'specimen') continue;
        render_reference($ref);
    }
    // database
    foreach($refs as $ref){
        if ($ref->kind != 'database') continue;
        render_reference($ref);
    }

    echo '</div>'; // end list group
    echo '</div>'; // end card

}

function render_reference($ref){


    echo "<a href=\"{$ref->uri}\" class=\"list-group-item  list-group-item-action\" target=\"{$ref->kind}\">";
    echo '<div class="row">';

    echo '<div class="col-1">'; 
    if($ref->thumbnailUri){
        echo "<img src=\"$ref->thumbnailUri\" width=\"50px\" />";
    }else{
        switch ($ref->kind) {
            case 'database':
                echo '<img src="../images/database.png" width="50px" />';
                break;
            case 'person':
                echo '<img src="../images/person.png" width="50px" style="margin-top: 0.5em"/>';
                break;
            case 'literature':
                echo '<img src="../images/literature.png" width="50px" style="margin-top: 0.5em"/>';
                break;
            case 'specimen':
                echo '<img src="../images/literature.png" width="50px" style="margin-top: 0.5em"/>';
                break;
            default:
                echo '<div width="50px" >&nbsp;</div>';
                break;
        }
    }
    echo '</div>';
    echo '<div class="col">';
    echo "<p><span class=\"fw-bold\">$ref->label</span><br/>$ref->comment</p>";
    echo '</div>';
    echo '</div>'; // end row
    echo "</a>";
}


?>