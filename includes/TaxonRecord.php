<?php

/**
 * This is a wrapper around a SOLR doc representing taxa and names
 * It loads itself directly from the SOLR index.
 */
class TaxonRecord{

    private ?Object $solrDoc = null;
    private ?Array $usages = null;

    // private properties available through GraphQL Type

    private ?String $id = null;
    private ?String $title = null;
    private ?String $wfoId = null;
    private ?String $classificationId = null;
    
    private ?String $stableUri = null;
    
    private ?String $fullNameStringHtml = null;
    private ?String $fullNameStringPlain = null;
    private ?String $fullNameStringNoAuthorsPlain = null;
    private ?String $nameString = null;
    private ?String $genusString = null;
    private ?String $speciesString = null;

    private ?String $authorsString = null;
    private ?String $authorsStringHtml = null;

    private ?String $nomenclaturalStatus = null;
    private ?String $role = null;
    
    private ?String $rank = null;

    private ?String $citationMicro = null;
    private ?String $comment = null;

    private bool $isName;

    private array $identifiersOther = array();
    private array $wfoIdsDeduplicated = array();

    private ?TaxonRecord $currentUsage = null;
    private ?TaxonRecord $parent = null;
    private ?Array $synonyms = null;
    private ?Array $unplacedNames = null;
    private int $childCount = -1; 

    private ?String $wfoPath = null;

    // we hold a list of the snippet metadata
    // so we only have to call for it once
    private ?Array $snippetMetadata = null;

    /**
     * Create a new wrapper around a SOLR doc
     */
    public function __construct($init_val){

        if(is_object($init_val)){

            // we have been given the solr doc (probably from a search)
            $this->solrDoc = $init_val;

            // there is a flag to say if we are supposed to be a name
            // or a taxon on loading.
            if(isset($this->solrDoc->asName) && $this->solrDoc->asName){
                // we are a name
                $this->isName = true;
            }else{
                // we are a taxon
                $this->isName = false;
            }

        }else{

            // if we are passed an unqualified WFO (getting name) then we qualify it with the 
            // current version 
            if(preg_match('/^wfo-[0-9]{10}$/', $init_val)){
                $init_val_full = $init_val . '-' . WFO_DEFAULT_VERSION;
                $this->isName = true;
            }else{
                $init_val_full = $init_val;
                $this->isName = false;
            }

            // load it by id
            $solr_query_uri = SOLR_QUERY_URI . '/get?id=' . $init_val_full;
            $ch = SolrIndex::getCurlHandle($solr_query_uri);
            $response = SolrIndex::runCurlRequest($ch);
            if(isset($response->body)){
                $body = json_decode($response->body);
                if(isset($body->doc)){
                    $this->solrDoc = $body->doc;
                }
            }

            // if we haven't got it yet try and load it by it being a deduplicated wfo ID
            // it will be a name
            
            if(!$this->solrDoc){
                $potential_dedupe_wfo = substr($init_val, 0, 14);
                $query = array( 
                    'query' => "wfo_id_deduplicated_ss:$potential_dedupe_wfo",
                    'filter' => ['classification_id_s:' . WFO_DEFAULT_VERSION ],
                );
                $docs = SolrIndex::getSolrDocs($query);
                if(count($docs) > 0){
                    $this->solrDoc = $docs[0];
                }
            }

        }

        if(!$this->solrDoc) return; // failed to load.

        // customize on if we are a name or not
        if($this->isName ){
            $this->isName = true;
            $this->id = $this->solrDoc->wfo_id_s;
            $this->classificationId = null;
            $this->stableUri = 'https://list.worldfloraonline.org/' . $this->solrDoc->wfo_id_s;
        }else{
            $this->isName = false;
            $this->id = $this->solrDoc->id;
            $this->classificationId = $this->solrDoc->classification_id_s;
            $this->stableUri = 'https://list.worldfloraonline.org/' . $this->solrDoc->id;
        }

        // initialize all the simple fields that don't need another call
        
        // common properties
        $this->title                = $this->solrDoc->full_name_string_plain_s;
        $this->wfoId                = $this->solrDoc->wfo_id_s;
        $this->fullNameStringHtml   = $this->solrDoc->full_name_string_html_s;
        $this->fullNameStringPlain  = $this->solrDoc->full_name_string_plain_s;
        $this->fullNameStringNoAuthorsPlain     = isset($this->solrDoc->full_name_string_no_authors_plain_s) ? $this->solrDoc->full_name_string_no_authors_plain_s : null;
        $this->nameString           = $this->solrDoc->name_string_s;
        $this->genusString          = isset($this->solrDoc->genus_string_s) ? $this->solrDoc->genus_string_s : null;
        $this->speciesString        = isset($this->solrDoc->species_string_s) ? $this->solrDoc->species_string_s : null;
        $this->authorsString        = isset($this->solrDoc->authors_string_s) ? $this->solrDoc->authors_string_s: null;
        $this->authorsStringHtml    = isset($this->solrDoc->authors_string_html_s) ? $this->solrDoc->authors_string_html_s: null;
        $this->nomenclaturalStatus  = isset($this->solrDoc->nomenclatural_status_s) ? $this->solrDoc->nomenclatural_status_s : null;
        $this->role                 = $this->solrDoc->role_s;
        $this->rank                 = $this->solrDoc->rank_s;
        $this->citationMicro        = isset($this->solrDoc->citation_micro_s) ? $this->solrDoc->citation_micro_s : null;
        $this->comment              = isset($this->solrDoc->comment_t) ? $this->solrDoc->comment_t : null;


        // identifiers other
        if(isset($this->solrDoc->identifiers_other_kind_ss)){
            // had index glitches where kinds and values have been different numbers
            // so this hack but shouldn't happen.
            $lowest = min(count($this->solrDoc->identifiers_other_kind_ss), count($this->solrDoc->identifiers_other_value_ss));
            for ($i=0; $i < $lowest; $i++) { 
                $identifier = array();
                $identifier['kind'] = $this->solrDoc->identifiers_other_kind_ss[$i];
                $identifier['value'] = $this->solrDoc->identifiers_other_value_ss[$i];
                $this->identifiersOther[] = (object)$identifier;
            }
        }

        // dedupe wfo ids
        if(isset($this->solrDoc->wfo_id_deduplicated_ss)){
            $this->wfoIdsDeduplicated = $this->solrDoc->wfo_id_deduplicated_ss;
        }

        // we have wfo path which is a useful hint for where this name might be
        if(isset($this->solrDoc->name_path_s)){
            switch ($this->role) {
                case 'deprecated':
                    $this->wfoPath = 'DEPRECATED';
                    break;
                case 'unplaced':
                    $this->wfoPath = 'UNPLACED';
                    break;
                case 'synonym':
                    $this->wfoPath = $this->solrDoc->name_path_s . "$" . str_replace(' ', '/', $this->solrDoc->full_name_string_alpha_s);
                    break;
                case 'accepted':
                    $this->wfoPath = $this->solrDoc->name_path_s;
                    break;
                default:
                    $this->wfoPath = '';
                    break;
            }
        }else{
            $this->wfoPath = '';
        }

    }

    /**
     * Check we have loaded the document from SOLR
     */
    public function exists(){
        return $this->solrDoc ? true : false;
    }

    public function getAcceptedWfoId(){
        return substr($this->getAcceptedId(), 0, 14);
    }

    public function getAcceptedId(){
        if(!$this->exists()) return null;
        if(!isset($this->solrDoc->accepted_id_s)) return null;
        return $this->solrDoc->accepted_id_s;
    }

    public function getBasionymWfoId(){
        return substr($this->getBasionymId(), 0, 14);
    }

    public function getBasionymId(){
        if(!$this->exists() || !isset($this->solrDoc->basionym_id_s)) return null;
        return $this->solrDoc->basionym_id_s;
    }


    public function getUsages(){

        if(!$this->exists()) return array();

        // if we haven't previously loaded them
        if(!$this->usages){
            $query = array(
                'query' => 'wfo_id_s:' . $this->getWfoId(),
                'sort' => 'classification_id_s asc'
            );
            $this->usages = $this->loadTaxonRecords($query, false);
        }

        return $this->usages;

    }

    public function getCurrentUsage(){

        if(!$this->exists()) return null;

        $current_me = new TaxonRecord($this->solrDoc->wfo_id_s . "-" . WFO_DEFAULT_VERSION);
        
        if(isset($current_me->solrDoc->accepted_id_s)){
            // we are a synonym - return the accepted name we belong to
            return new TaxonRecord($current_me->solrDoc->accepted_id_s);
        }elseif($current_me->getRole() == 'accepted'){
            // we are an accepted name of a taxon
            return $current_me;
        }else{
            return null;
        }

    }

    public function getReplaces(){

        if(!$this->exists()) return null;
        if($this->getIsName()) return null;

        $replaces = null;
        $usages = $this->getUsages(); // these are in order

        foreach($usages as $use){
            if($use->getIsName()) continue; // only looking at taxa
            if($use->getClassificationId() == $this->getClassificationId()) break; // reached current times
            $replaces = $use;
        }

        return $replaces;

    }

    public function getReplacedBy(){

        if(!$this->exists()) return null;
        if($this->isName) return null;

        $replaced_by = null;
        $usages = $this->getUsages(); // these are in order
        $usages = array_reverse($usages);

        foreach($usages as $use){
            if($use->getIsName()) continue; // only looking at taxa
            if($use->getClassificationId() == $this->getClassificationId()) break; // reached current times
            $replaced_by = $use;
        }

        return $replaced_by;

    }

    public function getParent(){


        if(!$this->exists()) return null;
        if($this->getIsName()) return null;


        if(!$this->parent && isset($this->solrDoc->parent_id_s)){
            $this->parent = new TaxonRecord($this->solrDoc->parent_id_s);
        } 

        return $this->parent;

    }

    public function getPath(){
        $path = array($this);
        if($this->getParent()){
            return array_merge($path, $this->getParent()->getPath());
        }else{
            return $path;
        }

    }

    public function getChildren($limit = -1, $offset = 0){

        if(!$this->exists()) return null;
        if($this->isName) return null;

        $query = array(
            'query' => 'parent_id_s:' . $this->solrDoc->id,
            'offset' => $offset,
            'sort' => 'full_name_string_alpha_s asc'
        );

        // -1 is unlimited but in Solr you just miss the parameter 
        if($limit >= 0){
            $query['limit'] = $limit;
        }else{
            $query['limit'] = 10000;
        }

        return $this->loadTaxonRecords($query, false);

    }

    public function getChildCount(){
                
        if($this->childCount !== -1) return $this->childCount;

        $query = array(
            'query' => 'parent_id_s:' . $this->solrDoc->id,
            'fields' => 'id',
            'limit' => 0,
            'offset' => 0,
        );
        $response = SolrIndex::getSolrResponse($query);

        if(isset($response->response->numFound)){
            $this->childCount = $response->response->numFound;
        }

        return $this->childCount;

    }

    public function getSynonyms(){

        if(!$this->exists()) return null;
        if($this->isName) return null;

        if(!$this->synonyms){

            $query = array(
                'query' => 'accepted_id_s:' . $this->solrDoc->id,
                'limit' => 1000000,
                'sort' => 'id asc'
            );

            $this->synonyms = $this->loadTaxonRecords($query);

        }

        return $this->synonyms;

    }

    public function getUnplacedNames(){

        if(!$this->exists()) return null;
        if($this->isName) return null;
        if($this->getRank() != 'genus') return null;
        
        if(!$this->unplacedNames){

            $query = array(
                'query' => 'genus_string_s:' . $this->solrDoc->name_string_s,
                'filter' => array('role_s:unplaced', 'classification_id_s:' . $this->solrDoc->classification_id_s),
                'limit' => 1000000,
                'sort' => 'full_name_string_plain_s asc'
            );

            $this->unplacedNames = $this->loadTaxonRecords($query);

        }

        return $this->unplacedNames;

    }


    public function getNomenclaturalReferences(){
        if(!$this->exists()) return null;
        return $this->getReferences('nomenclatural');
    }

    public function getTaxonomicReferences(){
        if(!$this->exists()) return null;
        if($this->getIsName()) return null;
        return $this->getReferences('taxonomic');
    }

    public function getTreatmentReferences(){
        if(!$this->exists()) return null;
        return $this->getReferences('treatment');
    }

    public function getReferences($context = null){

        $references = array();

        if(isset($this->solrDoc->reference_uris_ss)){

            for ($i=0; $i < count($this->solrDoc->reference_uris_ss); $i++) { 
                
                $ref = array();
                $ref_context = isset($this->solrDoc->reference_contexts_ss[$i]) ?   $this->solrDoc->reference_contexts_ss[$i] : null;

                // the index might contain 'name' for 'nomeclatural' and 'taxon' for 'taxonomic'.
                // convert to the new names for contexts
                if($ref_context == 'name') $ref_context = 'nomenclatural';
                if($ref_context == 'taxon') $ref_context = 'taxonomic';
                
                // we only return appropriate references for the context                
                if($context == 'nomenclatural' && $ref_context != 'nomenclatural') continue;
                if($context == 'taxonomic' && $ref_context != 'taxonomic') continue;
                if($context == 'treatment' && $ref_context != 'treatment') continue;
                
                // always present
                $ref['uri'] =  isset($this->solrDoc->reference_uris_ss[$i]) ?   $this->solrDoc->reference_uris_ss[$i] : null;
                $ref['context'] = $ref_context;
                $ref['kind'] = isset($this->solrDoc->reference_kinds_ss[$i]) ?  $this->solrDoc->reference_kinds_ss[$i] : null;
                $ref['label'] = isset($this->solrDoc->reference_labels_ss[$i]) ? $this->solrDoc->reference_labels_ss[$i] : null;

                // optional values
                $ref['thumbnailUri'] = isset($this->solrDoc->reference_thumbnail_uris_ss[$i]) && $this->solrDoc->reference_thumbnail_uris_ss[$i] != '-' ? $this->solrDoc->reference_thumbnail_uris_ss[$i] : null;
                $ref['comment'] = isset($this->solrDoc->reference_comments_ss[$i]) && $this->solrDoc->reference_comments_ss[$i] != '-' ? $this->solrDoc->reference_comments_ss[$i] : null;

                $references[] = (object)$ref;

            }
        }

        return $references;

    }

    /**
     * Returns the TEN and any editors of this 
     * taxon in a uniform format
     */
    public function getExperts(){

        $experts = array();

        if(isset($this->solrDoc->ten_name_s)){
            $experts[] = (object)array(
                'name' => "<a target=\"taxonomic_expert\" href=\"{$this->solrDoc->ten_uri_s}\">{$this->solrDoc->ten_name_s}</a>",
                'description' => @$this->solrDoc->ten_comment_s
            );
        }

        if(isset($this->solrDoc->editors_name_ss)){
            for ($i=0; $i < count($this->solrDoc->editors_name_ss) ; $i++) { 
                $experts[] = (object)array(
                    'name' => "<a target=\"taxonomic_expert\"  aria-label=\"View ORCID record\" href=\"https://orcid.org/{$this->solrDoc->editors_orcid_ss[$i]}\">{$this->solrDoc->editors_name_ss[$i]}</a>"
                    . " <a target=\"taxonomic_expert\"  aria-label=\"View ORCID record\" href=\"https://orcid.org/{$this->solrDoc->editors_orcid_ss[$i]}\"><img src=\"images/ORCID-iD_icon_unauth_24x24.png\" alt=\"ORCID iD\"/></a>",
                    'description' => "" 
                );
            }

        }

        return $experts;

    }

    public function loadTaxonRecords($query, $as_name = true){

        $records = array();

        $docs = SolrIndex::getSolrDocs($query);
        foreach($docs as $doc){
            $doc->asName = $as_name;
            $records[] = new TaxonRecord($doc);
        }

        return $records;

    }

    /**
     * Just for names that are unplaced
     * returns a list of names with the same genus
     * 
     */
    public function getAssociatedGenusNames(){

        if($this->getRole() != 'unplaced') return null;
        if(!$this->getGenusString()) return null;

        $results = array();

        $query = array(
            'query' => 'name_string_s:' . $this->getGenusString(),
            'filter' => array("classification_id_s:" . WFO_DEFAULT_VERSION, "rank_s:genus"),
            'limit' => 1000000,
            'sort' => 'full_name_string_plain_s asc'
        ); 

        $docs = SolrIndex::getSolrDocs($query);

        foreach($docs as $doc){
            $doc->asName = true;
            $results[] = new TaxonRecord($doc);
        }
        
        return $results;

    }

    /**
     * Returns all the text snippets in a 
     * structure that is useful for endering
     * by combining the 5 multivalue solr fields
     * 
     */
    public function getTextSnippets(){

        global $language_codes;

        $index = new SolrIndex();

        $snippets = array();

        // we don't have any snippets in the object
        if(!@$this->solrDoc->snippet_text_categories_ss) return $snippets;

        // have we loaded the snippet metadata yet?
        if(!$this->snippetMetadata) $this->loadSnippetMetadata();

        // work through the snippets in this record
        for ($i=0; $i < count($this->solrDoc->snippet_text_categories_ss); $i++) { 

            // we don't return categories of snippets that have been overridden e.g. link-outs 
            if(in_array($this->solrDoc->snippet_text_categories_ss[$i], OVERRIDDEN_SNIPPET_CATEGORIES)) continue;

            $snippet_id = $this->solrDoc->snippet_text_ids_ss[$i];

            // add each one arranged by text category
            $snippet = array();
            $snippet['id'] = $snippet_id;
            $snippet['language_code'] = $this->solrDoc->snippet_text_languages_ss[$i];
            $snippet['language_label'] = $language_codes[$this->solrDoc->snippet_text_languages_ss[$i]];
            $snippet['body'] = $this->solrDoc->snippet_text_bodies_txt[$i];


            // safely add metadata - may have failed to be indexed or delayed or something
            if(isset($this->snippetMetadata[$snippet_id]) && $this->snippetMetadata[$snippet_id] != null){

                $snippet['imported'] = $this->snippetMetadata[$snippet_id]->snippet->modified_dt;
                $snippet['described_wfo_id'] = $this->snippetMetadata[$snippet_id]->snippet->wfo_id_s;
                $snippet['source_id'] = 'wfo-ss-' . $this->snippetMetadata[$snippet_id]->snippet->source_id_s;
                
                if(isset($this->snippetMetadata[$snippet_id]->source_full)){
                    $snippet['source_name'] = $this->snippetMetadata[$snippet_id]->source_full->name;
                }
                
            }else{
                $snippet['imported'] = 'not set';
                $snippet['described_wfo_id'] = 'not set';
                $snippet['source_id'] = 'not set';
                $snippet['source_name'] = 'not set';
            }

            // finally put it in as an object
            $snippets[$this->solrDoc->snippet_text_categories_ss[$i]][] = (object)$snippet;

        }


        return $snippets;
    }

    /**
     * Returns all the linkout snippets
     * for this record. Similar to text snippets
     * but different fields for use in the UI
     * 
     */
    public function getLinkOuts(){
        
        $index = new SolrIndex();

        $link_outs = array();

        // we don't have any snippets in the object
        if(!@$this->solrDoc->snippet_text_categories_ss) return $link_outs;

        // have we loaded the snippet metadata yet?
        if(!$this->snippetMetadata) $this->loadSnippetMetadata();

        // work through the snippets in this record
        for ($i=0; $i < count($this->solrDoc->snippet_text_categories_ss); $i++) { 

            // we are only interested in link-out snippets
            if($this->solrDoc->snippet_text_categories_ss[$i] != 'link-out') continue;

            $snippet_id = $this->solrDoc->snippet_text_ids_ss[$i];

            // add each one arranged by text category
            $lo = array();
            $lo['id'] = $snippet_id;
            $lo['uri'] = $this->solrDoc->snippet_text_bodies_txt[$i];


            // safely add metadata - may have failed to be indexed or delayed or something
            if(isset($this->snippetMetadata[$snippet_id]) && $this->snippetMetadata[$snippet_id] != null){

               // print_r($this->snippetMetadata);

                $lo['imported'] = $this->snippetMetadata[$snippet_id]->snippet->modified_dt;
                $lo['described_wfo_id'] = $this->snippetMetadata[$snippet_id]->snippet->wfo_id_s;
                $lo['source_id'] = 'wfo-ss-' . $this->snippetMetadata[$snippet_id]->snippet->source_id_s;
                
                if(isset($this->snippetMetadata[$snippet_id]->source_full)){
                    $lo['source_name'] = $this->snippetMetadata[$snippet_id]->source_full->name;
                }
                
            }else{
                $lo['imported'] = 'not set';
                $lo['described_wfo_id'] = 'not set';
                $lo['source_id'] = 'not set';
                $lo['source_name'] = 'not set';
            }

            // finally put it in as an object
            $link_outs[] = (object)$lo;

        }

        return $link_outs;

    }

    /**
     * Various places use metadata about text snippets
     * we only want to load these once and we do it here
     * 
     */
    private function loadSnippetMetadata(){

        // we don't have any snippets in the object so don't load any metadata
        if(!@$this->solrDoc->snippet_text_categories_ss){
            $this->snippetMetadata = (object)array();
            return;
        }else{
            // initialise it
            $this->snippetMetadata = array();
        }

        // an index to call
        $index = new SolrIndex();

        /*
            efficiency note: We could reduce the number of index calls by pooling the 
            IDs for snippets and sources and then making two or even just one call for the 
            objects - but this would make the function much more complex at this stage of dev.
        */
        for ($i=0; $i < count($this->solrDoc->snippet_text_categories_ss); $i++) { 

            // metadata simple call
            $meta_id = $this->solrDoc->snippet_text_ids_ss[$i];

            $snippet_meta = $index->getSolrDoc($meta_id);
            if($snippet_meta){

                // add the snippet data
                $this->snippetMetadata[$meta_id] = array();
                $this->snippetMetadata[$meta_id]['snippet'] = $snippet_meta;
                $this->snippetMetadata[$meta_id]['snippet_full'] = json_decode($snippet_meta->json_t);

                // add the source metadata as well if we have it
                $source_meta = $index->getSolrDoc( 'wfo-ss-' . $snippet_meta->source_id_s);
                if($source_meta){
                    $this->snippetMetadata[$meta_id]['source'] = $source_meta;
                    $this->snippetMetadata[$meta_id]['source_full'] = json_decode($source_meta->json_t);
                }

                // convert it to an object for easy of use
                $this->snippetMetadata[$meta_id] = (object)$this->snippetMetadata[$meta_id];

            }else{

                // Oh know we have failed to load metadata. Still add a item so the reciever can deal with it
                $this->snippetMetadata[$meta_id] = null;
            }

        }

    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of title
     */ 
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the value of wfoId
     */ 
    public function getWfoId()
    {
        return $this->wfoId;
    }

    /**
     * Get the value of isName
     */ 
    public function getIsName()
    {
        return $this->isName;
    }

    /**
     * Get the value of classificationId
     */ 
    public function getClassificationId()
    {
        return $this->classificationId;
    }

    /**
     * Get the value of rank
     */ 
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Get the value of fullNameStringHtml
     */ 
    public function getFullNameStringHtml()
    {
        return $this->fullNameStringHtml;
    }

    /**
     * Get the value of fullNameStringPlain
     */ 
    public function getFullNameStringPlain()
    {
        return $this->fullNameStringPlain;
    }

    /**
     * Get the value of authorsString
     */ 
    public function getAuthorsString()
    {
        return $this->authorsString;
    }

    /**
     * Get the value of genusString
     */ 
    public function getGenusString()
    {
        return $this->genusString;
    }

    /**
     * Get the value of speciesString
     */ 
    public function getSpeciesString()
    {
        return $this->speciesString;
    }

    /**
     * Get the value of citationMicro
     */ 
    public function getCitationMicro()
    {
        return $this->citationMicro;
    }

    /**
     * Get the value of comment
     */ 
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get the value of role
     */ 
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get the value of identifiersOther
     */ 
    public function getIdentifiersOther()
    {
        return $this->identifiersOther;
    }

    /**
     * Get the value of wfoIdsDeduplicated
     */ 
    public function getWfoIdsDeduplicated()
    {
        return $this->wfoIdsDeduplicated;
    }

    /**
     * Get the value of stableUri
     */ 
    public function getStableUri()
    {
        return $this->stableUri;
    }

    /**
     * Get the value of fullNameStringNoAuthorsPlain
     */ 
    public function getFullNameStringNoAuthorsPlain()
    {
        return $this->fullNameStringNoAuthorsPlain;
    }

    /**
     * Get the value of fullNameStringNoAuthorsHtml
     */ 
    public function getFullNameStringNoAuthorsHtml()
    {
        // this one doesn't exist in the index so we 
        // create it for completeness
        $new_name = preg_replace('/<span\s*class="wfo-name-authors"\s*>.+<\/span>/', '', $this->fullNameStringHtml);
        $new_name = preg_replace('/<span\s*class="wfo-list-authors"\s*>.+<\/span>/', '', $new_name); // two ways to encode. prat!
        return trim($new_name);

    }


    /**
     * TaxonConcepts only will return 
     * their name
     */
    public function getName(){
  
        if($this->isName) return null;

        if(!$this->id) return null;

        // the name based on the 10 digit wfo id
        $name_id = substr($this->id, 0, 14);
        $name = new TaxonRecord($name_id); 
        return $name;

    }

    /**
     * Get the value of nameString
     */ 
    public function getNameString()
    {
        return $this->nameString;
    }

    public function getWfoPath()
    {
        return $this->wfoPath;
    }


    /**
     * Get the value of nomenclaturalStatus
     */ 
    public function getNomenclaturalStatus()
    {
        return $this->nomenclaturalStatus;
    }

    /**
     * Get the value of authorsStringHtml
     */ 
    public function getAuthorsStringHtml()
    {
        return $this->authorsStringHtml;
    }


    public function getStats(){

        global $ranks_table;

        // no stats for names
        if($this->isName) return null;

        // no stats for taxa without paths
        if(!isset($this->solrDoc->name_descendent_path)) return null;

        // return value
        $stats = array();

        $query = array(
            'query' => 'name_descendent_path:' . $this->solrDoc->name_descendent_path, 
            'filter' => 'classification_id_s:' . $this->solrDoc->classification_id_s,
            'facet' => array(
                "role" => array(
                    "type" => "terms",
                    "field" => "role_s",
                    'limit' => 10,
                    'facet' => array(
                        "rank" => array(
                            "type" => "terms",
                            "field" => "rank_s",
                            'limit' => 100
                        )
                    )
                ),
                "rank" => array(
                    "type" => "terms",
                    "field" => "rank_s",
                    'limit' => 100,
                    'facet' => array(
                        "role" => array(
                            "type" => "terms",
                            "field" => "role_s",
                            'limit' => 10
                        )
                    )
                ),
            ),
            'limit' => 0
        );


          

        // if we are a genus then we include unplaced names
        if($this->solrDoc->rank_s == 'genus'){
            $query['query'] .= " OR (genus_string_s:{$this->solrDoc->name_string_s} AND role_s:unplaced) ";
        }

        $response = SolrIndex::getSolrResponse($query);

        if(isset($response->facets)){

            foreach($response->facets as $upper_facet_name => $upper_facet){
                if($upper_facet_name == 'count') continue; 
                foreach ($upper_facet->buckets as $upper_bucket) {
                    $stats[] = new TaxonConceptStat($upper_facet_name ."-". $upper_bucket->val, "Total names with $upper_facet_name:'{$upper_bucket->val}'", $upper_bucket->count);
                    foreach($upper_bucket as $lower_facet_name => $lower_facet){
                        if($lower_facet_name == 'val') continue; 
                        if($lower_facet_name == 'count') continue;
                        foreach($lower_facet->buckets as $lower_bucket){
                            $stats[] = new TaxonConceptStat($upper_facet_name ."-". $upper_bucket->val ."-". $lower_facet_name ."-". $lower_bucket->val, "Total names with $upper_facet_name:'{$upper_bucket->val}' and $lower_facet_name:'{$lower_bucket->val}'", $lower_bucket->count);    
                        }
                    }
                }
            }

        }

        // if we are above the level of genus then we include unplaced names within ranks
        // we need to do a pseudo join for this
        $genus_level = array_search('genus', array_keys($ranks_table));
        $family_level = array_search('family', array_keys($ranks_table));
        $our_level =  array_search($this->getRank(), array_keys($ranks_table));

        if($our_level < $genus_level && $our_level >= $family_level){

            // get a list of the genera below our level
            $query = array(
                'query' => 'name_descendent_path:' . $this->solrDoc->name_descendent_path,
                'limit' => 1000,
                'filter' => array(
                    'classification_id_s:' . $this->solrDoc->classification_id_s,
                    'rank_s:genus'
                ),
                'fields' => 'name_string_s'
            );

            $response = SolrIndex::getSolrResponse($query);

            $genus_names = array();
            foreach ($response->response->docs as $doc){
                $genus_names[] = trim($doc->name_string_s);
            }

            // only if there are genera below us
            if(count($genus_names) > 0){

                $genus_names = '(' . implode(' OR ', $genus_names) . ')';

                // look for unplaced names with those genera
                $query = array(
                    'query' => 'genus_string_s:' . $genus_names,
                    'limit' => 1000,
                    'filter' => array(
                        'classification_id_s:' . $this->solrDoc->classification_id_s,
                        'role_s:unplaced'
                    ),
                    'facet' => array(
                        "rank" => array(
                            "type" => "terms",
                            "field" => "rank_s",
                            'limit' => 100,
                        )
                    ),
                    'limit' => 0
                );
                $response = SolrIndex::getSolrResponse($query);

                error_log(print_r($response, true));

                // put int he total;
                $stats[] = new TaxonConceptStat("role-unplaced", "Total names with role:'unplaced'", $response->facets->count);

                // put the facets in
                if(isset($response->facets->rank)){
                        foreach ($response->facets->rank->buckets as $bucket) {
                            // increase the totals for the existing rank count
                            for ($i=0; $i < count($stats); $i++) { 
                                if($stats[$i]->id == 'rank-' . $bucket->val){
                                    $stats[$i]->value += $bucket->count;
                                }
                            }
                    
                            // add in our own stats for this bucket
                            $stats[] = new TaxonConceptStat("role-unplaced-rank-{$bucket->val}", "Total names with role:'unplaced' and rank '{$bucket->val}'", $bucket->count);
                            $stats[] = new TaxonConceptStat("rank-{$bucket->val}-role-unplaced", "Total names with rank '{$bucket->val}' and role'unplaced'", $bucket->count);
                        }
                }


            }


        }

        return $stats;
    }


    public function getFacets(){

        $out = array();

        if(!$this->solrDoc) return $out;
        
        // work through the solr doc and find the properties
        // that look like they are facet scores
        foreach($this->solrDoc as $prop => $val){
            $matches = array();
            if(preg_match('/^(wfo-f-[0-9]+)_s/', $prop, $matches)){

                // set up the facet
                $prop_prefix = $matches[1];
                $out[$prop_prefix] = array();
                $out[$prop_prefix]['facet_values'] = array();

                // add the values
                foreach ($this->solrDoc->{$prop} as $fv) {
                    $out[$prop_prefix]['facet_values'][$fv] = array();
                    $out[$prop_prefix]['facet_values'][$fv]['provenance'] = array();

                    // and their provenance 
                    $prov_prop = $fv . '_provenance_ss';
                    foreach($this->solrDoc->{$prov_prop} as $prov){
                        $out[$prop_prefix]['facet_values'][$fv]['provenance'][]  = $prov;
                    }

                }
                
            }
        } // fin building the structure

        // if we've not been indexed then we are empty
        if(!$out) return $out;

        foreach($out as $f_id => $f){
            $details = new FacetDetails($f_id);

            $fob = (object)array(
                'id' => $f_id,
                'name' => $details->getFacetName(),
                'facet_values' => array()
            );

            foreach($f['facet_values'] as $fv_id => $fv){
                $fob->facet_values[$fv_id] = (object)array(
                    'id' => $fv_id,
                    'name' => $details->getFacetValueName($fv_id),
                    'link' => $details->getFacetValueLink($fv_id),
                    'code' => $details->getFacetValueCode($fv_id),
                    'provenance' => $fv['provenance']
                );
            }
            $out[$f_id] = $fob;
            
        }

        return $out;
    }

    /**
     * Used when we call for the contents 
     * make up of the taxon
     */
    public function getNameDescendentPath(){
        if(!isset($this->solrDoc)) return null;
        if(!isset($this->solrDoc->name_descendent_path)) return null;
        return $this->solrDoc->name_descendent_path;
    }

}