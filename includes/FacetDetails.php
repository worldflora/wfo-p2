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

        // if it is in the cache as a facet server defined thing return that
        if($this->facetCache && isset($this->facetCache->facet_values->{$value_id})) return $this->facetCache->facet_values->{$value_id}->name;

        // if it is a two letter language code the return that - could have clashes but unlikely
        if(strlen($value_id) == 2){
            require_once('../includes/language_codes.php');
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