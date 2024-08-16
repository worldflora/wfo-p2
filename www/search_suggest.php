<?php
// this renders the "drop down" box below the search box when people type.
// it is called by ajax

// we are a standalone script so we have to include the config
// and any other requirements
require_once('../config.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/TaxonRecord.php');

// render nothing if we haven't been passed a string
if(!@$_GET['q']) exit;

$query_string = $_GET['q'];

// render nothing for strings less than 3 characters
if(strlen($query_string) < 3) exit;

// OK, we have something to workwith so call the index

$name = trim($query_string);
$name = ucfirst($name); // all names start with an upper case letter
$name = str_replace(' ', '\ ', $name);
$name = $name . "*";

$filters = array();
$filters[] = 'classification_id_s:' . WFO_DEFAULT_VERSION;
$filters[] = '-role_s:deprecated'; 

$query = array(
    'query' => "full_name_string_alpha_s:$name",
    'filter' => $filters,
    'sort' => 'full_name_string_alpha_t_sort asc',
    'limit' => 100
);

$index = new SolrIndex();
$docs  = $index->getSolrDocs($query);

// if we don't find anything we don't render
if(!$docs) exit;

// we have got something so we render it
$visible_lines = count($docs);
if($visible_lines > 10) $visible_lines = 10;

echo '<div class="input-group" style="width: 90%; margin-bottom: 1em; margin-top: 0.1em;">';
echo "<select class=\"form-control\" size=\"$visible_lines\"  onchange=\"window.location = this.value\">";

foreach ($docs as $doc) {
    $record = new TaxonRecord($doc);
    echo "<option value=\"{$record->getWfoId()}\" >";
    echo $record->getFullNameStringPlain();
    echo '</option>';
}

echo '</select>';
echo '</div>';