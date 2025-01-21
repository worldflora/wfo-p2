<?php

/**
 * Holds the details necessary to 
 * decorate a facet and its facet values.
 * 
 */
class FacetDetails{

    private $facetId = null;
    private $solrFieldName = null;
    private $facetCache = null;
    private $index = null;

    public function __construct($facet_id){

        if( preg_match('/_ss$/', $facet_id) || preg_match('/_s$/', $facet_id) ){
            $this->solrFieldName = $facet_id;
        }
 
        // convert solr index fields to facet ids
        $this->facetId = preg_replace('/_ss$/', '',$facet_id);
        $this->facetId = preg_replace('/_s$/', '',$this->facetId);

        // we used the cached values if they exist
        if(isset($_SESSION['facets_cache']) && isset($_SESSION['facets_cache'][$this->facetId])){
            $this->facetCache = $_SESSION['facets_cache'][$this->facetId];
        }

        // single version of the index link
        $this->index = new SolrIndex();

    }

    public function getFacetName(){

        global $search_facets;

        // we have it cached from the index
        if($this->facetCache) return $this->facetCache->name;

        
        // it isn't in the cache from the facet service
        // we must be looking at a locally define one based 
        // on a solr field
        foreach($search_facets as $sf){
            if($sf->field_name == $this->solrFieldName){
                return $sf->label;
            }
        }
        
        // it isn't cached so we make it from the id
        $matches = array();
        if(preg_match('/^placed_in_(.+)/', $this->facetId, $matches)){
            return ucfirst($matches[1]);
        }

        // giving up and returning just the id
        $name = str_replace('_', ' ', $this->facetId);
        $name = ucfirst($name);
        return $name;

    }

    public function getFacetValueName($value_id){

        global $language_codes;

        // if it is in the cache as a facet server defined thing return that
        if($this->facetCache && isset($this->facetCache->facet_values->{$value_id})) return $this->facetCache->facet_values->{$value_id}->name;

        // if the name is recognised as data source load it from the index
        if(preg_match('/^wfo-(s|f)s-[0-9]+$/', $value_id)){

            $doc = $this->index->getSolrDoc($value_id);
            if($doc){
                $data = json_decode($doc->json_t);
                return $data->name;
            }
            return 'Data source: ' . $value_id;
        }

        // if it is a two letter language code the return that - could have clashes but unlikely
        if(strlen($value_id) == 2){
            if(isset($language_codes[$value_id])) return $language_codes[$value_id];
        }
        
        // give up an return the value itself
        return $value_id;
    }


    public function getFacetValueLink($value_id){
        if($this->facetCache && isset($this->facetCache->facet_values->{$value_id})) return $this->facetCache->facet_values->{$value_id}->link_uri;
        return null;
    }

    public function getFacetValueCode($value_id){
        if($this->facetCache && isset($this->facetCache->facet_values->{$value_id})) return $this->facetCache->facet_values->{$value_id}->code;
        return null;
    }


}