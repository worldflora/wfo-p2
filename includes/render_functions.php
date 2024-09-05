<?php

function render_record_type_description($record, $link_wfo_id = true){

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

    echo '<p style="margin-bottom: 0.5em;">';

    echo "<span class=\"fw-bold\" style=\"color: $colour; margin-bottom: 0px;\">$desc:</span>&nbsp;";
    
    // WFO ID Linking
    if($link_wfo_id){
        echo '<span
                class="fw-bold"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Click to copy persistent URL to clipboard." 
                onclick="navigator.clipboard.writeText(\'https://list.worldfloraonline.org/'. $record->getWfoId() .'\')" 
                >';
    }else{
         echo '<span
            class="fw-bold"
            >';
    }

    echo $record->getWfoId();

    echo '</span>';


  
    // add an IUCN icon if we have an attribute for threat status
    $facets = $record->getFacets();
    foreach($facets as $facet_id => $facet){
        if($facet_id == IUCN_THREAT_FACET_ID){ // from the config.php
            foreach($facet->facet_values as $fv_id => $fv){
                if($fv->code){
                    echo '<span
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    title="IUCN Threat Status is '. $fv->name . '."
                    >';
                    echo '<img style="vertical-align: bottom; height:28px;" src="../data/'. IUCN_THREAT_FACET_ID .'/' . $fv->code . '.svg" alt="star"/>';
                    echo '</span>';
                }
            }
        }
    }

    


    echo '</p>'; // header p
   
}