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

There are two components required. 

- A PHP front end application that runs the website and a simple administrative API for updating the index
- An instance of Apache SOLR that contains all the data displayed by the PHP application

These two components can be installed on the same machine or on separate machines. For production use it would be better to have them on separate machines but one machine could probably run them fine if sufficiently specified. The PHP application communicates with the SOLR index over HTTP and so the two machines should sit on the same LAN with good communications speeds.

For development and testing it is possible to run the PHP application locally and point it at a remote index somewhere on the internet. It will run slowly.

It should be possible to have multiple front end applications use the same SOLR index provided only one of them does index updating. This can be controlled by restricting the API access key to only one instance. Users also need to maintain PHP sessions. If multiple front end instances exist a session sharing mechanism would need to be established e.g. by using sticky sessions on the load balancer or Redis or MemCache as shared sessions stores.

### Hardware

This install process has been tested on VMWARE FUSION with 5meg RAM and 20G of disk space. Production specifications are to be determined and will depend on OS.


### OS Software

Default platform tested here is __Ubuntu Server 26.04.1 LTS__ but other OS setups would probably work.

- Starting with a fresh install of __Ubuntu Server 26.04.1 LTS__.
- sudo apt install net-tools - for convenience.
- sudo apt install zip

### Apache SOLR 10.0 setup

SOLR is a Java application so we need a virtual machine. The Ubuntu 26 packaged one is suitable.

```
sudo apt-get update
sudo apt install openjdk-25-jdk -y
java -version
```

This should return 25+ and will probably be 25. e.g.

```
openjdk version "25.0.4.1" 2026-08-18
OpenJDK Runtime Environment (build 25.0.4.1+1-1-26.04.4-Ubuntu)
OpenJDK 64-Bit Server VM (build 25.0.4.1+1-1-26.04.4-Ubuntu, mixed mode, sharing)
```

For reference SOLR install instructions are here: https://solr.apache.org/guide/solr/latest/deployment-guide/installing-solr.html

Set by step we do this:

Download the binary package from the Apache SOLR site (https://solr.apache.org/downloads.html). Change the download and file name if using a later dot release.
 
```
cd 
wget -O solr-10.0.0.tgz https://www.apache.org/dyn/closer.lua/solr/solr/10.0.0/solr-10.0.0.tgz?action=download
```

Unzip the file to get access to the install script, run the installer and then delete unzipped directory.

```
tar -xvzf solr-10.0.0.tgz
sudo ./solr-10.0.0/bin/install_solr_service.sh solr-10.0.0.tgz
rm -rf solr-10.0.0
```

SOLR should now be running. You can check it like this.

```
sudo systemctl status solr
```

The SOLR Web UI will also be running on port 8983 but will only be available from the local host. To view it from another machine you need to alter the config file. (__Subnet mask should be set appropriately and probably not 0.0.0.0 outside testing.__)

```
sudo nano /etc/default/solr.in.sh
#SOLR_HOST_BIND="127.0.0.1"
SOLR_HOST_BIND="0.0.0.0"
sudo systemctl restart solr
```

You should now see it in your browser on http://<host name\>:8983/ In production routing rules should make sure the machine is only visible to other machines that need it. i.e. set the net mask appropriately.

At this point we have an empty Apache SOLR instance running.

Create a core (index) called __wfo__ to load the data in.

```
sudo su - solr -c "/opt/solr/bin/solr create -c wfo"
```

There will be a warning about autoCreateFields in production use that we ignore for now. We could maybe turn this off once index if fully populated but before that point we rely on autofield creation because our data is so heterogenous.

Refresh the Web UI and select the WFO core (left panel);

Select 'Schema' on the left then the 'Add Copy Field' button at the top. Add a copy from `*` and to `_text_`. This will mean all the data we add also gets added to a generic field called `_text_`.

Lock it down with basicAuth in addition to the protection by IP routing done above.

```
sudo /opt/solr/bin/solr auth enable --type basicAuth --credentials wfo:long-and-complex-password --block-unknown true
```

Refresh the Web UI and you'll be asked for a username and password. Keep a note of the password!

#### Importing The Plant List

The classification (1.7 million names arranged into a hierarchy) is loaded into the index as a single file. These classification files are generated as part of a six monthly data release cycle. Each data release is deposited in the Zenodo.org archive. This URL will always go to the latest version of the repository <https://doi.org/10.5281/zenodo.7460141>. The file that is needed will be named like this `plant_list_2026-06.json.zip`.

__You will always need to get the URL of the latest version by visiting the repository at <https://doi.org/10.5281/zenodo.7460141>__ and change the values in the commands below, including the long-and-complex-password

Download the latest version, unzip it and import it into the index using curl like this:

```
unzip plant_list_2026-06.json.zip
curl -H 'Content-type:application/json' 'http://localhost:8983/solr/wfo/update?commit=true' -X POST -T plant_list_2026-06.json --user wfo:long-and-complex-password
rm plant_list_2026-06.json
```

The curl command above will take about half and hour to run. You may want to wrap it in a nohup if you're on a slow machine and your terminal might time out. The response should be something like this:

```
{
  "responseHeader":{
    "rf":1,
    "status":0,
    "QTime":485850
   }
}
```

Go back to the Web UI for SOLR. Make sure the `wfo` core is selected. Select `Query` and just run the default `*:*` query. The response should have a numFound or around 1.7 million documents.

Congratulations the SOLR server is now up and running and populated with the current Plant List classification. The next step is to set up the PHP front end and connect it to the index. After that a web service will call the API on the front end to populate the index with the text content (descriptions) of the taxa in the classification.

#### PHP Modules enabled

- SQLite3



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

For downloads to work there must be a downloads directory writeable by the webserver. Do something like this:

1. `cd wfo_home/wfo-p2/www/`
2. `mkdir downloads`
3. `sudo chown -R <user>:www-data downloads/`

### Populating the SOLR Index



