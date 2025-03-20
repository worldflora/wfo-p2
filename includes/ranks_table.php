<?php


// the rank table - having this in code saves a lot of sql queries and joins because
// it is used everywhere.
// -- READ -- THIS --
// this should be kept in syn with the enumeration values in the db
// ALSO IN THE WFO-PLANT-LIST repository and elsewhere!
$ranks_table = array(

    "code" => array(
			"faceted" => false,
      "children" => array("kingdom", "phylum"), // permissible ranks for child taxa
      "abbreviation" => "ICN", // official abbreviation
      "plural" => "Code",
      "aka" => array() // alternative representations for import
    ),
  
    "kingdom" => array(
			"faceted" => false,
      "children" => array("subkingdom", "phylum"), // permissible ranks for child taxa
      "abbreviation" => "King.", // official abbreviation
      "plural" => "Kingdoms",
      "aka" => array() // alternative representations for import
    ),
  
    "subkingdom" => array(
			"faceted" => false,
      "children" => array("phylum", "class", "order","family", "superorder"), // permissible ranks for child taxa
      "abbreviation" => "subking.", // official abbreviation
      "plural" => "Subkingdoms",
      "aka" => array() // alternative representations for import
    ),
  
    "phylum" => array(
			"faceted" => true,
      "children" => array("class", "order", "family", "superorder"), // permissible ranks for child taxa
      "abbreviation" => "phyllum", // official abbreviation
      "plural" => "Phyla",
      "aka" => array() // alternative representations for import
    ),
  
    "class" => array(
			"faceted" => true,
      "children" => array("subclass", "order", "family","superorder"), // permissible ranks for child taxa
      "abbreviation" => "class", // official abbreviation
      "plural" => "Classes",
      "aka" => array() // alternative representations for import
    ),
  
    "subclass" => array(
			"faceted" => false,
      "children" => array("order", "family", "superorder"), // permissible ranks for child taxa
      "abbreviation" => "subclass.", // official abbreviation
      "plural" => "Subclasses",
      "aka" => array() // alternative representations for import
    ),
  
    "superorder" => array(
			"faceted" => false,
      "children" => array("order"), // permissible ranks for child taxa
      "abbreviation" => "superord.", // official abbreviation
      "plural" => "Superorders",
      "aka" => array() // alternative representations for import
    ),
  
    "order" => array(
			"faceted" => true,
      "children" => array("suborder", "family"), // permissible ranks for child taxa
      "abbreviation" => "ord.", // official abbreviation
      "plural" => "Orders",
      "aka" => array() // alternative representations for import
    ),
  
    "suborder" => array(
			"faceted" => false,
      "children" => array("family"), // permissible ranks for child taxa
      "abbreviation" => "subord.", // official abbreviation
      "plural" => "Suborders",
      "aka" => array() // alternative representations for import
    ),
  
    "family" => array(
			"faceted" => true,
      "children" => array("supertribe", "subfamily", "tribe", "genus"), // permissible ranks for child taxa
      "abbreviation" => "fam.", // official abbreviation
      "plural" => "Families",
      "aka" => array() // alternative representations for import
    ),
  
    "subfamily" => array(
			"faceted" => false,
      "children" => array("supertribe", "tribe", "genus"), // permissible ranks for child taxa
      "abbreviation" => "subfam.", // official abbreviation
      "plural" => "Subfamilies",
      "aka" => array() // alternative representations for import
    ),
  
    "supertribe" => array(
			"faceted" => false,
      "children" => array("tribe"), // permissible ranks for child taxa
      "abbreviation" => "suptr.", // official abbreviation
      "plural" => "Supertribes",
      "aka" => array('supertrib.') // alternative representations for import
    ),
  
    "tribe" => array(
			"faceted" => true,
      "children" => array("subtribe", "genus"), // permissible ranks for child taxa
      "abbreviation" => "tr.", // official abbreviation
      "plural" => "Tribes",
      "aka" => array('trib.') // alternative representations for import
    ),
  
    "subtribe" => array(
			"faceted" => false,
      "children" => array("genus"), // permissible ranks for child taxa
      "abbreviation" => "subtr.", // official abbreviation
      "plural" => "Subtribes",
      "aka" => array('subtrib.', 'subtrib') // alternative representations for import
    ),
  
    "genus" => array(
			"faceted" => true,
      "children" => array("subgenus", "section", "series", "species"), // permissible ranks for child taxa
      "abbreviation" => "gen.", // official abbreviation
      "plural" => "Genera",
      "aka" => array() // alternative representations for import
    ),
  
    "subgenus" => array(
			"faceted" => false,
      "children" => array("section", "series", "species"), // permissible ranks for child taxa
      "abbreviation" => "subg.", // official abbreviation
      "plural" => "Subgenera",
      "aka" => array('subgen.') // alternative representations for import
    ),
  
    "section" => array(
			"faceted" => false,
      "children" => array("subsection", "series", "species"), // permissible ranks for child taxa
      "abbreviation" => "sect.", // official abbreviation
      "plural" => "Sections",
      "aka" => array("sect",  "nothosect.") // alternative representations for import
    ),
    
    "subsection" => array(
			"faceted" => false,
      "children" => array("series", "species"), // permissible ranks for child taxa
      "abbreviation" => "subsect.", // official abbreviation
      "plural" => "Subsections",
      "aka" => array() // alternative representations for import
    ),
  
    "series" => array(
			"faceted" => false,
      "children" => array("subseries", "species"), // permissible ranks for child taxa
      "abbreviation" => "ser.", // official abbreviation
      "plural" => "Series",
      "aka" => array() // alternative representations for import
    ),
  
    "subseries" => array(
			"faceted" => false,
      "children" => array("species"), // permissible ranks for child taxa
      "abbreviation" => "subser.", // official abbreviation
      "plural" => "Subseries",
      "aka" => array() // alternative representations for import
    ),
  
    "species" => array(
			"faceted" => false,
      "children" => array("subspecies", "variety", "form", "prole", "lusus"), // permissible ranks for child taxa
      "abbreviation" => "sp.", // official abbreviation
      "plural" => "Species",
      "aka" => array("nothospecies", "spec.") // alternative representations for import
    ),
  
    "subspecies" => array(
			"faceted" => false,
      "children" => array("variety", "form", "prole", "lusus"), // permissible ranks for child taxa
      "abbreviation" => "subsp.", // official abbreviation
      "plural" => "Subspecies",
      "aka" => array("nothosubspecies", "nothosubsp.", "subsp.", "subsp", "ssp", "ssp.", "subspec.") // alternative representations for import
    ),
  
    "prole" => array(
			"faceted" => false,
      "children" => array(), // permissible ranks for child taxa
      "abbreviation" => "prol.", // official abbreviation
      "plural" => "Proles",
      "aka" => array("race", "proles") // alternative representations for import
    ),
  
    "variety" => array(
			"faceted" => false,
      "children" => array("subvariety", "form", "prole", "lusus"), // permissible ranks for child taxa
      "abbreviation" => "var.", // official abbreviation
      "plural" => "Varieties",
      "aka" => array("nothovar.", "var.", "var") // alternative representations for import
    ),
  
    "subvariety" => array(
			"faceted" => false,
      "children" => array("form"), // permissible ranks for child taxa
      "abbreviation" => "subvar.", // official abbreviation
      "plural" => "Subvarieties",
      "aka" => array("subvar") // alternative representations for import
    ),
  
    "form" => array(
			"faceted" => false,
      "children" => array("subform"), // permissible ranks for child taxa
      "abbreviation" => "f.", // official abbreviation
      "plural" => "Forms",
      "aka" => array("forma", "f") // alternative representations for import
    ),
  
    "subform" => array(
			"faceted" => false,
      "children" => array(), // permissible ranks for child taxa
      "abbreviation" => "subf.", // official abbreviation
      "plural" => "Subforms",
      "aka" => array("subforma") // alternative representations for import
    ),
  
    "lusus" => array(
			"faceted" => false,
      "children" => array(), // permissible ranks for child taxa
      "abbreviation" => "lus.", // official abbreviation
      "plural" => "Lusus",
      "aka" => array("lus", "lusus naturae") // alternative representations for import
    ),
  
    "unranked" => array(
			"faceted" => false,
      "children" => array(), // permissible ranks for child taxa = none
      "abbreviation" => "unranked", // official abbreviation
      "plural" => "Unranked",
      "aka" => array("unr.", "infraspec.", "infrasec.", "infragen." ) // alternative representations for import
    )
  
  );