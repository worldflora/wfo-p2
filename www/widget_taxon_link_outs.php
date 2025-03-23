<?php
require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');
require_once('../includes/ranks_table.php');

// A widget to lazy load the taxon tools as they involve 
// more faceting query 

$wfo_id = @$_GET['wfo_id'];
if(!$wfo_id || !preg_match('/^wfo-[0-9]{10}$/', $wfo_id)) exit;

$record = new TaxonRecord($wfo_id);

// we don't output till we know we have content to display
$content_count = 0;
ob_start();

$link_outs = $record->getLinkOuts();

foreach ($link_outs as $link_out) {

    $content_count++;
   
    echo '<div class="list-group-item  list-group-item-action">';
   
    // the data source as a link to the modal
    $prov_data = (object)array(
        'kind' => 'snippet_source',
        'source_id' => $link_out->source_id
    );
    $prov_json = urlencode(json_encode($prov_data));

    if(isset($link_out->source_name)) $sname = $link_out->source_name;
    else $sname = 'Source';

    echo "<a href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#dataProvModal\" data-wfoprov=\"{$prov_json}\"><strong>{$sname}:</strong></a>&nbsp;";

    if($link_out->described_wfo_id == $record->getWfoId()){
        echo "Features the taxon <a href=\"{$link_out->uri}\" target=\"tool\">{$record->getFullNameStringHtml()}</a>";
    }else{
        $syn = new TaxonRecord($link_out->described_wfo_id . '-' . WFO_DEFAULT_VERSION);
        echo "Features <a href=\"{$link_out->uri}\" target=\"tool\">{$syn->getFullNameStringHtml()}</a> which is a synonym of {$record->getFullNameStringHtml()}.";
    }

    echo '</div>';
}


// So we have actual link outs from this taxon

// We also have link outs from any descendents - which will take a facet query.
// but only for key ranks

if($ranks_table[$record->getRank()]['faceted']){

    $path = $record->getNameDescendentPath();
    $wfo_id = $record->getWfoId();
    
    
    // build a facet query
    $query = array(
        'query' => "name_descendent_path:\"{$path}\" !wfo_id_s:{$wfo_id}", // all descendents not ourselves
        "limit" => 0, // we don't want records we only want facets
        'filter' => array(
            "classification_id_s:" . WFO_DEFAULT_VERSION, // this classificatoin
            "role_s:accepted" // only the accepted ones
        ),
        'facet' => array()
        );
    
        // we add facet for each tool in the list in the config
        foreach(LINK_OUT_DATA_SOURCE_IDS as $ds_id){
            $query['facet'][$ds_id] = array(
                    'type' => "query",
                    'q' => "snippet_text_sources_ss:{$ds_id}"
            );
        }
    
        $response = SolrIndex::getSolrResponse($query);
    
        if(isset($response->facets) && $response->facets){
    
            $index = new SolrIndex();
    
            foreach(LINK_OUT_DATA_SOURCE_IDS as $ds_id){

                // build a link to the datasource provenance
                $prov_data = (object)array(
                    'kind' => 'snippet_source',
                    'source_id' => $ds_id
                );
                $prov_json = urlencode(json_encode($prov_data)); 
    
                // if we got a response and it is greater than zero write it out
                if(isset($response->facets->$ds_id) && $response->facets->$ds_id->count){
    
                    $content_count++;
    
                    $count = $response->facets->$ds_id->count;
                    $percent = $count/$response->response->numFound  * 100;
    
                    $count = number_format($count, 0);
                    $percent = number_format($percent, 0);
                    $total = number_format($response->response->numFound, 0);
    
                    $data_source = $index->getSolrDoc($ds_id);
                    $data_source = json_decode($data_source->json_t);
    
                    // build a query to get this
                    $query_url = array(
                        'q' => '',
                        'search_type' => 'name',
                        'timestamp' => time(),
                        "placed_in_{$record->getRank()}_s[]" => $record->getNameString(),
                        "role_s[]" => 'accepted', 
                        "snippet_text_sources_ss[]" =>  $ds_id
                    );
                    $query_url = 'search?' . http_build_query($query_url);

                    echo '<div class="list-group-item list-group-item-action">';

                    echo "<strong><a href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#dataProvModal\" data-wfoprov=\"{$prov_json}\">{$data_source->name}</a>:</strong>&nbsp;";

                    echo "covers {$percent}%, <a href=\"{$query_url}\">$count of the $total subtaxa</a>, of {$record->getFullNameStringHtml()}";
    
                    echo '</div>';   
    
    
                }
    
            }// end going through tools
    
        }
    


} // is faceted rank

// return a json object of the content
// so we can render it nicely
if($content_count > 0){
    $json = json_encode((object)array(
        'count' => $content_count,
        'body' => ob_get_contents()
    ));
}else{
    $json = json_encode((object)array(
        'count' => 0,
        'body' => ''
    ));
}
ob_clean();


header('Content-Type: application/json');
echo $json;
exit;


?>