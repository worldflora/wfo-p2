<?php

$page_title = "WFO: Search";

// we preserve the request between calls
if(isset($_REQUEST['timestamp'])){

    $request = $_REQUEST;
    $_SESSION['search_request'] = $request; 

    // they submitted a well formed wfo id then redirect to that name page
    $wfo = trim($_REQUEST['q']);
    if(preg_match('/^wfo-[0-9]{10}$/', $wfo) || preg_match('/^wfo-[0-9]{10}-[0-9]{4}-[0-9]{2}$/', $wfo)){
        header('Location: /' . substr($wfo, 0, 14));
        exit;
    }

}else{
    $request = @$_SESSION['search_request'];
}

// get what they are searching for in the main search box
$terms = @$request['q'];
if(!$terms) $terms = '';

// we always do a search to load the facet fields
 // query by start of word if we have one

if($terms){
    $query_txt = ucfirst($terms); // all names start with an upper case letter
    $query_txt = str_replace(' ', '\ ', $query_txt);
    $query_txt = $query_txt . "*";
    $query_txt = "full_name_string_alpha_s:$query_txt";
}else{
    $query_txt = "*:*";
}

// restrict to the real names in the accepted classification
$filters = array();
$filters[] = 'classification_id_s:' . WFO_DEFAULT_VERSION;
$filters[] = '-role_s:deprecated';

// we need to convert the 
// facet ids given in the config 
// into the field names - adding _ss
for ($i=0; $i <  count($search_facets) ; $i++) { 
    $search_facets[$i] = preg_match('/^wfo-f-[0-9]/',$search_facets[$i]) ? $search_facets[$i] . '_ss' : $search_facets[$i];
}

$facets = array();
foreach($search_facets as $fi){

    // add the field to facet on
    $facets[$fi] = (object)array(
            "type" => "terms",
            "field" => $fi
    );

    // if we have a value for the facet
    // then add a filter for that facet
    if(isset($request[$fi])){
        foreach($request[$fi] as $v){
            $filters[] =  $fi . ':' . $v;
        }
    }
}

// pull whole query together
$query = array(
    'query' => $query_txt,
    'filter' => $filters,
    'sort' => 'full_name_string_alpha_t_sort asc',
    'limit' => 100,
    'facet' => (object)$facets
);

echo "<pre>";
//print_r($query);
echo '</pre>';

$solr_response  = SolrIndex::getSolrResponse($query);
if(isset($solr_response->response->docs)) $docs = $solr_response->response->docs;
if(isset($solr_response->facets)) $facets_response = $solr_response->facets;



// OK let's get started on rendering the page
require_once('header.php');
?>

<div class="container-lg">
    <form role="form" method="GET" action="search">
        <div class="row">
            <div class="col">
                <div data-bs-toggle="offcanvas" style="float: right;">
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasResponsive" aria-controls="offcanvasResponsive">Filter</button>
                </div>
                <?php
                
                    require_once('search_box.php');

                    if($docs){
                        echo "<p><strong>Records: </strong>" . number_format($solr_response->response->numFound) . "</p>";
                        echo "<ul>";
                            foreach($docs as $doc){
                                echo "<li id=\"{$doc->wfo_id_s}\">";
                                echo "<a href=\"/{$doc->wfo_id_s}\">";
                                echo $doc->full_name_string_html_s;
                                echo "</a> [";
                                echo $doc->role_s;
                                echo "]</li>";
                            }
                        echo "</ul>";
                    }else{
                        echo "<p>Nothing found</p>";
                    }

                    echo '<pre>';
                    //print_r($query);
                    //print_r($solr_response);
                    //print_r($facets_response);
                    //print_r($docs);
                    echo '</pre>';                
                
                ?>


            </div>
            <div class="col-4 bg-light offcanvas-lg offcanvas-end " style="padding: 0px;" tabindex="-1"
                id="offcanvasResponsive" aria-labelledby="offcanvasResponsiveLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasResponsiveLabel">Filter Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                        data-bs-target="#offcanvasResponsive" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="accordion" style="width: 100%;" id="accordionPanelsStayOpenExample">

                        <?php

    foreach($facets_response as $f_name => $f){

        if($f_name == 'count') continue;
        
        $facet_details = new FacetDetails($f_name);

        // calculate if we are collapsed or not
        // any value is ticked then we render as open
        $collapsed = 'collapsed';
        $collapse = 'collapse';
        if(@$request[$f_name]){
            foreach($f->buckets as $bucket){
                if(in_array($bucket->val, $request[$f_name])){
                    $collapsed = '';
                    $collapse = '';
                    break;
                }
            }
        }


        // we do an accordion item
        echo '<div class="accordion-item">';

        // header
        echo '<h2 class="accordion-header">';
        echo "<button class=\"accordion-button $collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#$f_name\" aria-expanded=\"true\" aria-controls=\"collapseOne\">";
        echo $facet_details->getFacetName();
        echo '</button>';
        echo '</h2>';
       
        // body
        echo "<div id=\"$f_name\" class=\"accordion-collapse $collapse\" data-bs-parent=\"#accordionExample\">";
        echo '<div class="accordion-body">';

        foreach($f->buckets as $bucket){

            if(@$request[$f_name] && in_array($bucket->val, $request[$f_name])){
                $checked = 'checked';
            }else{
                $checked = '';
            }

            $count = number_format($bucket->count, 0);

            echo '<div class="mb-3">';
            echo '<input class="form-check-input"  type="checkbox"';
            echo "value=\"$bucket->val\" id=\"{$bucket->val}\" $checked name=\"{$f_name}[]\"";
            echo 'onchange="this.form.submit()"';
            echo "/>";
            echo "<label class=\"form-check-label\" for=\"{$bucket->val}\">&nbsp;";
            echo $facet_details->getFacetValueName($bucket->val) . " - {$count}";
            echo "</label>";                
            echo '</div>';

        }


        echo '</div>'; // end of body
        echo '</div>'; // end of collapseOne

        // end of accordion-item
        echo '</div>'; 

   

    }

?>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
require_once('footer.php');
?>