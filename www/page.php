<?php

$page_title = "WFO: Static content";

require_once('../includes/Parsedown.php');

$md_file_path = parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH);
$md_file_path = substr($md_file_path, 1); // knock the first / off
$md_file_path = urldecode($md_file_path); // may have accents
$md_file_path = preg_replace('/^\/pages\//', 'static/', $md_file_path);


// if this is a directory then we treat it as an index to the files below 
// it - including the subdirectories
if(is_dir($md_file_path)){

    // if there is an index file in the directory 
    // then we treat that as the header for the file list
    if(file_exists($md_file_path . '/index.md')){
        $md_text = file_get_contents($md_file_path . '/index.md');
        build_index_for_dir($md_file_path, $md_text, 3);
    }else{
        $md_text = '';
    }

}else{
    // not looking at a directory so just render the file
    $md_text = file_get_contents($md_file_path);
}

// is there a header file in the directory that we
// want to add?
$header_path = preg_replace('/\/[^\/]+\.md$/', '/header.md', $md_file_path);
if($header_path != $md_file_path && file_exists($header_path)){
    $header_txt = file_get_contents($header_path);
    $md_text = $header_txt . $md_text;
}

$parser = new Parsedown($md_text);
$parsed_text = $parser->text($md_text);

$page_title = 'WFO: ' . get_title_from_path($path);

require_once('../fragments/header.php');
echo '<div class="container wfo-md">';
echo $parsed_text;
echo '</div>';
require_once('../fragments/footer.php');

/**
 * iterative function for building the diretory listing
 */
function build_index_for_dir($dir, &$md_text, $depth){

    $paths = glob($dir . '/*');

    $files = array();
    $dirs = array();

    // we work through the list first and
    // weed out unwanted files so they don't 
    // mess with the sorting
    $date_order = false;
    foreach($paths as $path){

        // catch the directories
        if(is_dir($path)){
            $dirs[] = $path;
            continue;
        }

        // all are files
        $info = pathinfo($path);
        
        // don't link to the index files
        if($info['basename'] == 'index.md') continue;
        if($info['basename'] == 'header.md') continue;

        // don't link to image files
        if(isset($info['extension'])){
            if(strtolower($info['extension']) == 'jpg') continue;
            if(strtolower($info['extension']) == 'jpeg') continue;
            if(strtolower($info['extension']) == 'png') continue;
        }

        // if any of the files start with a date then
        // we are sorting by reverse order
        if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}_/', $info['basename'])){
            $date_order = true;
        }

        // finally we have a kosha file to link to
        $files[] = $path;
    }

    // directories are always alphabetical ascending
    sort($dirs);

    if($date_order) rsort($files,); // reverse alphabetical and therefore date
    else sort($files); // ascending alphabetical

    // write out the files
    foreach ($files as $path) {
        $title = get_title_from_path($path);
        $md_text .=  "\n  * [{$title}](/$path)";
    }

    // if we have subdirectories build them 
    foreach($dirs as $path){
        $md_text .=  "\n\n";
        $md_text .= str_repeat("#", $depth);
        $md_text .= " ";
        $md_text .= get_title_from_path($path);
        build_index_for_dir($path, $md_text, $depth + 1);
    }

}

function get_title_from_path($path){
    $info = pathinfo($path);
    $title = preg_replace('/_/', ' ', $info['filename']);
    $title = preg_replace('/^[0-9]{4}-[0-9]{2}-[0-9]{2} /', '', $title);
    $title = urldecode($title);
    return $title;
}
