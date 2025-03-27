<?php

/**
 * This is designed to sit in a 
 * session whilst a download file 
 * is generated.
 * 
 */
class ListExporter{

    private int $phase = 1; // 1 is importing to SQLite 2 is generating the download file
    private bool $overCapacity = false;
    private bool $finished = false;
    private int $offset = 0;
    private int $total = 0;
    private ?string $filePath = null;
    private ?string $sqlitePath = null;
    private ?string $format = null;

    // keep track where we are writing the HTML file
    private bool $inSynonyms = false; 
    private int $depth = 0;
    private int $rootDepth = 0;

    public function __construct($format){

        // check we have space to work first
        $this->curateDownloadDirectory();

        $this->format = $format;
    
        // we create a file to write to and hold onto it.
        $this->filePath = LIST_DOWNLOAD_DIR . 'wfo-download-'. session_id() . '.' . $format;

        // we need to use an SQLite db to build the common
        // ancestor subtree
        $this->sqlitePath = LIST_DOWNLOAD_DIR . session_id() . '.sqlite';
        @unlink($this->sqlitePath); // 
        $this->db = new SQLite3($this->sqlitePath, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

        $this->db->query('CREATE TABLE IF NOT EXISTS "records" (
            "wfo_id" TEXT,
            "name" TEXT,
            "role" TEXT,
            "rank" TEXT,
            "parent_id" TEXT,
            "path" TEXT,
            "featured" INT,
            "body" TEXT,
            UNIQUE("wfo_id")
        )');
    
    }


    /**
     * When this is serialized to the session
     * we close the files but keep a handle on 
     * some of the variables 
     */
    public function __sleep(){
        $this->db->close();
        // the fields we perist
        return array('overCapacity', 'finished', 'offset', 'total', 'format', 'filePath', 'sqlitePath', 'phase', 'inSynonyms', 'depth', 'rootDepth');
    }
    
    /**
     * When we unserialise we re-open the files 
     * to append more data to them
     */
    public function __wakeup(){
        $this->db = new SQLite3($this->sqlitePath, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    }

    public function getDownloadUrl(){
        if(file_exists($this->filePath . '.zip')) return $this->filePath . '.zip';
        else return null;
    }

    /**
     * We need to be very careful maintaining 
     * the downloads directory as it could become full of requests. 
     * We therefore don't rely on a cron job to empty it but each download request
     * will do a quick scan and delete anything too old.
     */
    private function curateDownloadDirectory(){

        // does it exist?
        if(!file_exists(LIST_DOWNLOAD_DIR)) mkdir(LIST_DOWNLOAD_DIR, 0777, true);

        // delete anything in it that is more than LIST_DOWNLOAD_FILE_TTL minutes old
        // get a handle on the current size of remaining files

        $total_bytes = 0;

        foreach (glob(LIST_DOWNLOAD_DIR . "*") as $file) {
            if(time() - filectime($file) > LIST_DOWNLOAD_FILE_TTL * 60){
                unlink($file);
            }else{
                $total_bytes += filesize($file);
            }
        }
        
        // is it too big?
        $total_megabytes = $total_bytes / 1000000;
        if($total_megabytes > LIST_DOWNLOAD_DIR_MAX_SIZE){
            $this->overCapacity = true;
            $this->finished = true;
        }else{
            $this->overCapacity = false;
        }
    
    }


    public function getMessage(){

        if($this->overCapacity){
            return "<p>Sorry we are over download capacity at the moment. Please try again later.</p>";
        }

        $out = '<div style="width: 100%;">';
       

        if($this->finished){
            $out .= "<p>File created: <a href=\"{$this->filePath}.zip\" >Click here to download.</a></p>";

            if($this->format == 'csv'){
                $out .= "<p>The file is a UTF-8 encoded CSV (comma separated values) table in a zip archive.</p>";
            }

            if($this->format == 'html'){
                $out .= "<p>The file is an HTML page in a zip archive that can be opened in a web browser or word processor. It is UTF-8 encoded.</p>";
            }
        }else{
            $total_pretty = number_format($this->total, 0);
            $offset_pretty = number_format($this->offset, 0);
            $percent = round(($this->offset/$this->total)*100);
            if($percent > 100) $percent = 100;
    
            if($this->phase == 1){
                $title = "Creating data cache.";
            }else{
                $title = "Generating file.";
            }
    
            $out .= "<span>&nbsp;{$title}</span>";
            $out .= '</div>';
    
            $out .= '<div style="width: 100%; border: solid black 1px; background-color: gray;">';
            $out .= '<div style="width: '. $percent .'%; border: none; background-color: blue; color: white;">';
            if($percent > 10) $out .= "<span>&nbsp;</span><span style=\"float: right;\">{$percent}%&nbsp;</span>";
            else $out .= "<span>&nbsp;</span>";
            $out .= '</div>';
            $out .= '</div>';
    
            $out .= '<div style="width: 100%;">';
            $out .= "<span>&nbsp;{$offset_pretty}</span>";
            $out .= "<span style=\"float: right;\">{$total_pretty}&nbsp;</span>";
        }

        $out .= '</div>';
        
        return $out; 

//        return print_r($this, true);

    }

    public function isFinished(){
        return $this->finished;
    }

    public function page(){
        if($this->phase == 1) $this->pageSqlite();
        else $this->pageFile();
    }

    /**
     * Here we import all the query results
     * into a sqlite database along with the
     * common ancestors of the associated
     * taxa.
     */
    private function pageSqlite(){

        // the last query the user ran
        $query = @$_SESSION['last_solr_query'];

        // limit to the size we want to output
        $query['limit'] = 1000;
        $query['offset'] = $this->offset;
        $query['fields'] = array(
            'id',
            'wfo_id_s',
            'full_name_string_plain_s',
            'full_name_string_html_s',
            'rank_s',
            'role_s',
            'name_path_s',
            'parent_id_s',
            'accepted_id_s'
        );

        $data = SolrIndex::getSolrResponse($query);
        $docs = $data->response->docs;
        $this->total = $data->response->numFound;

        // have we done all the pages
        if(count($docs) == 0){
            $this->phase = 2; // move to phase 2
            $this->offset = 0; // reset the offset
            $this->limit = 0; // reset the limit
            return;
        } 
        
        $this->db->exec('BEGIN'); // do them all in one transaction for speed
        foreach($docs as $doc){
            $this->sqliteAddRecord($doc, true);
        }
        $this->db->exec('COMMIT');

        $this->offset += 1000;

    }

    private function sqliteAddRecord($record, $featured = false){

        // do nothing if it is already there 
        $row = $this->db->querySingle("SELECT * FROM `records` WHERE `wfo_id` = '{$record->wfo_id_s}'", true);
        if($row){
            // the taxon is already there but not flagged as featured when it is featured
            // then we update the record. This is needed because a featured record (in the query results)
            // may have been added previously as unfeatured because it is the parent of another taxon
            // in the query - crazy stuff :)
            if($featured && !$row['featured']){
                $this->db->query("UPDATE `records` SET featured = 1  WHERE `wfo_id` = '{$record->wfo_id_s}'");
            }
            return; // our work here is done.
        }

        // the record isn't there so let us prepare to add it
        $statement = $this->db->prepare('INSERT OR IGNORE INTO "records" (
            "wfo_id",
            "name",
            "role",
            "rank",
            "parent_id",
            "path",
            "featured",
            "body"
        )VALUES(
            :wfo_id,
            :name,
            :role,
            :rank,
            :parent_id,
            :path,
            :featured,
            :body
        )');


        $statement->bindValue(':wfo_id', $record->wfo_id_s);
        $statement->bindValue(':name', $record->full_name_string_plain_s);
        $statement->bindValue(':role', $record->role_s);
        $statement->bindValue(':rank', $record->rank_s);

        // we use parent_id for both syns and accepted relationships
        if($record->role_s == 'accepted') $statement->bindValue(':parent_id', isset($record->parent_id_s) ? substr($record->parent_id_s, 0, 14) : null ); // strip the qualifier
        if($record->role_s == 'synonym') $statement->bindValue(':parent_id', isset($record->accepted_id_s) ? substr($record->accepted_id_s, 0, 14) : null ); // strip the qualifier

        $statement->bindValue(':path', isset($record->name_path_s) ? $record->name_path_s : null);
        $statement->bindValue(':featured', $featured ? 1 : 0);
        $statement->bindValue(':body', json_encode($record));
        $statement->execute();

        // now we have added it we should check that its ancestors (accepted or parents) are there
        if(isset($record->parent_id_s)) $this->sqliteAddAncestor($record->parent_id_s);
        if(isset($record->accepted_id_s)) $this->sqliteAddAncestor($record->accepted_id_s);
        
    }

    /**
     * Will add a parent/accepted record to the
     * db if it isn't already there.
     */
    private function sqliteAddAncestor($doc_id){
            $ancestor_id = substr($doc_id, 0, 14);
            $row = $this->db->querySingle("SELECT * FROM `records` WHERE `wfo_id` = '{$ancestor_id}'", true);
            if(!$row){
                $doc = SolrIndex::getSolrDoc($doc_id);
                if($doc) $this->sqliteAddRecord($doc, false);
            }
    }

    /**
     * Here we export the data from the Sqlite db to 
     * an appropriate file format for download
     * 
     */
    private function pageFile(){
        if($this->format == 'csv') $this->pageCsv();
        else $this->pageHtml();
    }

    private function pageCsv(){

        // does the output file exist?
        if(!file_exists($this->filePath)){

            // open it and insert the header stuff
            $out = fopen($this->filePath, 'w');

            // put a bom at the start
            fwrite($out, "\xEF\xBB\xBF");

            // put a header row in
            fputcsv($out, array(
                'wfo_id',
                'scientific_name',
                'taxonomic_status',
                'named_in_list',
                'rank',
                'parent_id',
                'accepted_id',
                'name_path',
                'name_no_authors',
                'authors',
                'micro_citation',
                'nomenclatural_status'
            ));

            // check we are starting at the beginning of the db
            $this->offset = 0;
            $this->total = $this->db->querySingle("SELECT count(*) from records;");

        }else{
            // just open it for append
            $out = fopen($this->filePath, 'a');
        }

        $response = $this->db->query("SELECT * FROM `records` order by `path` NULLS LAST, `role`, `name` LIMIT 1000 offset {$this->offset} ");
        $row_count = 0;
        while ($row = $response->fetchArray()) {

            $row_count++;

            $csv_row = array();
            $csv_row[] = $row['wfo_id'];
            $csv_row[] = $row['name'];
            $csv_row[] = $row['role'];
            $csv_row[] = $row['featured'];
            $csv_row[] = $row['rank'];

            if($row['parent_id']){
                if($row['role'] == 'accepted'){
                    $csv_row[] = $row['parent_id'];
                    $csv_row[] = null;
                }else{
                    $csv_row[] = null;
                    $csv_row[] = $row['parent_id'];
                }
            }else{
                $csv_row[] = null;
                $csv_row[] = null;
            }

            $csv_row[] = $row['path'];

            // now some fluff from the rest of the record
            $json = json_decode($row['body']);

            $csv_row[] = @$json->full_name_string_no_authors_plain_s;
            $csv_row[] = @$json->authors_string_s;
            $csv_row[] = @$json->citation_micro_t;
            $csv_row[] = @$json->nomenclatural_status_s;

            fputcsv($out,$csv_row);

        }

        // close of the last lists
        fclose($out);

        if($row_count == 0){

            // check a zip file doesn't already exist
            @unlink($this->filePath . '.zip');

            $zip = new ZipArchive;
            $zip->open($this->filePath . '.zip', ZIPARCHIVE::CREATE);
            $zip->addFile($this->filePath, basename($this->filePath));
            $zip->close();

            // dispose of the csv file
            @unlink($this->filePath);

            // dispose of the sqlite file
            @unlink($this->sqlitePath);

            $this->finished = true;

        }else{
            $this->offset += 1000;
        }
    }

    private function pageHtml(){

        
         // we only want to start when we get to the common ancestor
        // not at the code root
        $common_ancestor_id = $this->getCommonAncestorId();

        // does the output file exist?
        if(!file_exists($this->filePath)){

            // open it and insert the header stuff
            $out = fopen($this->filePath, 'w');
            
            // add a bom for UTF-8 encoding
            fwrite($out, "\xEF\xBB\xBF");

            $this->writeHtmlHeader($out);

            // check we are starting at the beginning of the db
            $this->offset = 0;
            $this->total = $this->db->querySingle("SELECT count(*) from records;");

        }else{
            // just open it for appending
            $out = fopen($this->filePath, 'a');
        }

        // sort by the name path and then role. 
        // synonyms have the same name path as their accepted names so they 
        // will come after the accepted name 
        $response = $this->db->query("SELECT * FROM `records` where role in ('accepted', 'synonym') order by `path`, `role`, `name` limit 1000 offset {$this->offset} ");
        $row_count = 0;
        $reached_common_ancestor = false;
        while ($row = $response->fetchArray()) {

            $row_count++;

            if($this->offset == 0 && !$reached_common_ancestor){

                if($row['wfo_id'] !=  $common_ancestor_id){
                    continue;
                }else{
                    $reached_common_ancestor = true;
                    $this->depth = substr_count($row['path'], '/');
                    $this->rootDepth = $this->depth;
                } 
            }

            // how deep are we?
            $new_depth = substr_count($row['path'], '/');
            if($new_depth > $this->depth){
                fwrite($out, str_repeat('<li><ul>',  $new_depth - $this->depth ));
            };
            if($new_depth < $this->depth){
                fwrite($out, str_repeat('</ul></li>',  $this->depth - $new_depth ));
            };
            $this->depth = $new_depth;

            // we are starting a run of synonyms
            if($row['role'] == 'synonym' && !$this->inSynonyms){
                $this->inSynonyms = true;
                fwrite($out, '<li><strong>syns:</strong><ul>');
            }

            // we are past the end of the synonyms
            if($row['role'] == 'accepted' && $this->inSynonyms){
                $this->inSynonyms = false;
                fwrite($out, '</ul></li>');
            }

            $this->writeTaxonName($out, $row, !$this->inSynonyms);

            // hold the taxon path so we know if we need to do synonyms or not
            $this->lastTaxonPath = $row['path'];


        }

        if($row_count == 0){

            // close of the last lists
            fwrite($out, str_repeat('</ul></li>',  $this->depth - $this->rootDepth));

            $this->writeUnplaced($out);
            $this->writeDeprecated($out);
            $this->writeHtmlFooter($out);
            fclose($out);

            @unlink($this->filePath . '.zip');
            
            $zip = new ZipArchive;
            $zip->open($this->filePath . '.zip', ZIPARCHIVE::CREATE);
            $zip->addFile($this->filePath, basename($this->filePath));
            $zip->close();

            @unlink($this->filePath);
            
            $this->finished = true;
            $this->offset = 0;

        }else{
            $this->offset += 1000;
        }
        
    }

    private function writeUnplaced($out){

        fwrite($out, "<h2>Unplaced Names</h2>");
        fwrite($out, "<p>Names that have not been placed in the classification by a WFO taxonomist yet.</p>");
        $response = $this->db->query("SELECT * FROM `records` where role = 'unplaced' order by `name`;");
        fwrite($out, '<ul>');
        $row_count = 0;
        while ($row = $response->fetchArray()) {
            $this->writeTaxonName($out, $row, false);
            $row_count++;
        }
        if($row_count == 0) fwrite($out, '<li>None</li>');
        fwrite($out, '</ul>');
        $response->finalize();
    }

    private function writeDeprecated($out){

        fwrite($out, "<h2>Deprecated Names</h2>");
        fwrite($out, "<p>Names that can't be placed because they were created in error or represent an unused rank.</p>");
        
        $response = $this->db->query("SELECT * FROM `records` where role = 'deprecated' order by `name`;");
        fwrite($out, '<ul>');
        $row_count = 0;
        while ($row = $response->fetchArray()) {
            $this->writeTaxonName($out, $row, false);
            $row_count++;
        }
        if($row_count == 0) fwrite($out, '<li>None</li>');
        fwrite($out, '</ul>');
        $response->finalize();

    }

    private function writeTaxonName($out, $row, $abbreviate_genus = true){

            // get the full data for the record
            $json = json_decode($row['body']);

            $class = $row['featured'] ? 'featured' : 'not-featured';

            fwrite($out, '<li>');
        
            $display_name = $json->full_name_string_html_s;

            // replace the genus name if there is one (we are below genus level)
            if(isset($json->genus_string_s) && $abbreviate_genus){
                $display_name = str_replace($json->genus_string_s, substr($json->genus_string_s, 0, 1) . '.', $display_name);      
            }
        
            fwrite($out,  "<strong class=\"{$class}\">$display_name</strong>");
            fwrite($out,  "&nbsp;[{$json->rank_s}]&nbsp;");
            fwrite($out,  @$json->citation_micro_s);
            fwrite($out,  "&nbsp;<a href=\"https://list.worldfloraonline.org/{$json->wfo_id_s}\" target=\"wfo_list\">{$json->wfo_id_s}</a>");

            fwrite($out, '</li>');

    }


    public function getCommonAncestorId($wfo_id = null){


        // we don't have taxon id to work with
        // so we start at the root of all
        if(!$wfo_id){
            $wfo_id = $this->db->querySingle("select wfo_id from records where `rank` = 'code' and parent_id is null;");
        }
        
        $number_kids = $this->db->querySingle("SELECT count(*) FROM `records` WHERE `parent_id` = '$wfo_id';");

        error_log("{$wfo_id} : {$number_kids}");

        if($number_kids > 1){
            return $wfo_id;
        }else{
            $child_wfo = $this->db->querySingle("SELECT wfo_id FROM `records` WHERE `parent_id` = '$wfo_id';");
            return $this->getCommonAncestorId($child_wfo);
        }
    
    
    }

    private function writeHtmlHeader($out){

        fwrite($out, '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>WFO Checklist Download</title>
    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
        }
        .featured{
            color: green;
        }
        .wfo-name-authors{
            color: gray;
        }
        ul {
            list-style-type: none;
        }
    </style>
  </head>
  <body>
  <h1>WFO Checklist Download</h1>
  <p>Exported ' . date("F d Y @ H:i:s") . '. Names highlighted in <span class="featured">green</span> were in the search results, other names give their context within the current classification.</p>
  <h2>Classification</h2>
  <ul>
  ' );

    }

    private function writeHtmlFooter($out){

        fwrite($out, '
        </ul>
        </body>
</html>
' );

    }

    

}