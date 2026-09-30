# WFO Website V2.


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

- An instance of Apache SOLR that contains all the data displayed by the PHP application
- A PHP front end application that runs the website and a simple administrative API for updating the index

These two components can be installed on the same machine or on separate machines. For production use it would be better to have them on separate machines but one machine could probably run them fine if sufficiently specified. The PHP application communicates with the SOLR index over HTTP and so the two machines should sit on the same LAN with good communications speeds.

For development and testing it is possible to run the PHP application locally and point it at a remote index somewhere on the internet. It will run slowly.

It should be possible to have multiple front end applications use the same SOLR index provided only one of them does index updating. This can be controlled by restricting the API access key to only one instance. Users also need to maintain PHP sessions. If multiple front end instances exist a session sharing mechanism would need to be established e.g. by using sticky sessions on the load balancer or Redis or MemCache as shared sessions stores.

### Hardware

This install process has been tested on VMWARE FUSION with virtual machines running on a laptop. Production specifications are to be determined and will depend on OS. Initial suggestion are as follows:

- __SOLR Server:__ This will need a minimum 100G of disk space, ideally 500G. The WFO Plant List json file is 7.5G before it is imported into the index and twice that amount of data will be added. There will also be times when the index will double in size as we swap from one classification to another. The RAM requirements for a Java application are difficult to estimate because it depends so much on how the index is built and request loading. A suggested starting point is 10G.

- __Frontend Server__ This machine will be less memory and storage intensive. A suggested starting point is 100G of disk space and 5G of memory. It will depend on the request rate. One bottle neck is the creation of PHP sessions which could fill a disk during a bot attack.

### OS Software

Default platform tested here is __Ubuntu Server 26.04.1 LTS__ but other OS setups would probably work.

- Starting with a fresh install of __Ubuntu Server 26.04.1 LTS__.
- `sudo apt install net-tools` - for convenience.
- `sudo apt install zip` - required.

### Apache SOLR 10.0 Setup

It is best to get the SOLR index running and initialized with Plant List data first.

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

If you want to remove the classification from the index you can do it like this

```
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8983/solr/wfo/update' --data-binary '{"delete":{"query":"classification_id_s:2026-06"} }' --user wfo:long-and-complex-password
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8983/solr/wfo/update' --data-binary '{"commit":{} }' --user wfo:long-and-complex-password
```

Congratulations the SOLR server is now up and running and populated with the current Plant List classification. The next step is to set up the PHP front end and connect it to the index. After that a web service will call the API on the front end to populate the index with the text content (descriptions) of the taxa in the classification.

### PHP Frontend Setup

The frontend can either run on the same machine as the SOLR index or a separate machine that has fast HTTP access to the SOLR instance.

#### Prerequisites

Install Apache2 and PHP. The list of modules includes a few that may not be in use now but are likely to be used with code updates in the future.

```
sudo apt install apache2
sudo apt install php libapache2-mod-php
sudo apt install php-bz2 php-curl php-dom php-gd php-mbstring php-simplexml php-xml php-xmlreader php-xmlwriter php-xsl php-zip php-bcmath php-sqlite3
sudo a2enmod headers rewrite proxy proxy_http
```

#### Install code

We assume the application will live in `/var/wfo-home` alongside any other WFO infrastructure applications that may be placed on the same machine. Having a consistent approach is helpful even if they are on separate machines.

Ownership of the wfo-home/ directory should not be a user or a group could be established for the purpose if multiple users will be administering the code. This is a sysadmin decision.

There must be a `downloads` directory within the `www` directory that is writeable by the webserver (www-data). It is used to build the downloadable checklists when requested by the user.

```
cd /var/
sudo mkdir wfo-home/
sudo chown -R <user>:root wfo-home/
cd wfo-home
git submodule init
git submodule update
mkdir www/downloads
sudo chown -R <user>:www-data www/downloads
cd ..
cp wfo-p2/wfo_p2_secrets.php .
nano wfo_p2_secrets.php
```

We have now installed the code and opened the config file (which will contain credentials that shouldn't go in GitHub) for editing.

- The URL to the SOLR instance. Check the IP address is that of the machine the SOLR server is running on or localhost if it is the current machine. Check the name of the SOLR core within the path is correct. If the core was named as above it should be `wfo`. e.g. `http://192.168.28.132:8983/solr/wfo`
-  The username and password for SOLR as configured above.
-  The classification version. This will be the same as you imported above. e.g. `2026-06`
-  The api_bearer_token is used for communication between web services when updating the index and can be set once the basic application is running.

Before moving on to configuring Apache try and run the application using PHP's built in webserver. __Obviously this is for testing and dev only.__

```
cd /var/wfo-home/wfo-p2/www
php -S <server-ip-address>:1965 -c php.ini index.php
```

The website should be available on port 1965 at the server IP address, or localhost if you set that. There will be errors in the faceted searching because we haven't populated the index fully yet but you should be able to search for a plant name like "Rhododendron" and get a response if the frontend is talking to the index correctly. `ctrl-c` to kill the webserver.

N.B. To update the code you need to pull in the submodules as well as the main repository

```
cd /var/wfo-home/wfo-p2
git pull --recurse-submodules
```

#### Configuring Apache

This is more or a systems administration section than application installation. There is an `.htaccess` file in /var/wfo-home/wfo-p2/www that gives mod-rewrite rules for the application itself so you need to make sure that is honoured. Otherwise it is just a regular virtual host set up for a www directory in the application.

An HTTP (port 80 version would look like this)

```
<VirtualHost *:80>

      ServerName test01.worldfloraonline.org

      ServerAdmin rhyam@rbge.org.uk
      DocumentRoot /var/wfo-home/wfo-p2/www

      <Directory />
             Options FollowSymLinks
             AllowOverride None
      </Directory>

      <Directory /var/wfo-home/wfo-p2/www/>
             Options Indexes FollowSymLinks
             AllowOverride All
             Require all granted
      </Directory>

      ErrorLog ${APACHE_LOG_DIR}/wfo-website-error.log
      CustomLog ${APACHE_LOG_DIR}/wfo-website-access.log combined

      # we would never actually server on plain http but redirect to https version
      RewriteEngine on
      RewriteCond %{SERVER_NAME} =test01.worldfloraonline.org
      RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]

</VirtualHost>
```

A identical VirtualHost configuration file for HTTPS requests but with the redirects replaced with links to the SSL certificates should be created manually or using `certbot` and the Let's Encrypt service. Sites can be enable and disabled using the `a2ensite` command.