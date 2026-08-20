# WFO Portal V2.

This is a mockup of how a version 2 of the WFO portal could work. We are currently taking it forward as a potential replacement for the main portal.

## Design principles

1. Keep it as simple as possible because complexity costs resources to maintain.
2. The portal is a view onto a single SOLR index. There is no SQL database.
3. The SOLR index contains:
   1. Nomenclature and classification data from a WFO Plant List data release that was authored in the Rhakhis system.
   2. Faceting data and snippets of text from the WFO Fyllo data curation system. This drives sub-setting of lists, descriptive data and basic mapping.
4. How does data get into the SOLR index?
   1. The classification data from Rhakhis is imported as single file from the command line every six months.
   2. The faceting and text data is pushed through an API by a service running on Airflow.
   3. Other ways of populating the SOLR index will be explored in the future - such as peer to peer networking.
5. Images will be pulled in from an image service with metadata treated as text sources.
6. There are therefore only three layers:
   1. SOLR Index + possible file cache to optimize calls to text and image services.
   2. PHP page rendering layer. This is kept as simple as possible so that layer three can be outsourced and updated easily.
   3. Bootstrap CSS for branding.

## Submodules

There are two separate repositories embedded within this one so as to provide separation of concerns:

1. www/theme contains the Bootstrap 5 CCS theme used by the site. This should facilitate a separate team to build new looks for the site.
2. www/pages contains "static" text and image content that is about the WFO organizational structure.

## Installation

### Prerequisites - Hardware

### Prerequisites - Software

### Front end (PHP)

1. `mkdir wfo_home` 
2. `cd wfo_home`
3. `git clone https://github.com/worldflora/wfo-p2.git`
4. `cd wfo-p2`
5. `git submodule init`
6. `git submodule update`
7. `cd ..`
8. `cp wfo-p2/wfo_p2_secrets.php .` (edit this template to add the SOLR connection care not to add any spaces at the top!)
9.  `cd wfo-p2/www`
10. `./dev_start.sh` (This will run the site on localhost for development and testing but not for production use.)

__The site will not run if it is not connected to an appropriate SOLR Index__

### Populating the SOLR Index



