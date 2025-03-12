# WFO Portal V2.

This is a mockup of how a version 2 of the WFO portal could work. We are currently taking it forward as a potential replacement for the main portal.

## Design principles

1. Keep it as simple as possible because complexity cost resources to maintain.
2. The portal is a view onto a single SOLR index. There is no SQL database. The portal knows nothing about how data gets into the SOLR index. It is merely a rendering layer.
3. The SOLR index contains:
   1. Nomenclature and classification data from the WFO Plant List data releases.
   2. Faceting data from the WFO Faceting service. This drives subsetting of lists as well as basic mapping.
   3. In the future text sources will be indexed but not stored in the SOLR index.
4. Text from digitized floras will be pulled in from a text service at render time (possibly cached).
5. Images will be pulled in from an image service following the same model as the text service.
6. There are therefore only three layers:
   1. SOLR Index + possible file cache to optimise calls to text and image services.
   2. PHP page rendering layer. This is kept as simple as possible so that layer three can be outsourced and updated easily.
   3. Bootstrap CSS for branding. Modularised as much as possible.



