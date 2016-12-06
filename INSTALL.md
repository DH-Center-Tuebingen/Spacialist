# Installation
We recommend a recent unix/linux-based OS. Please check if your desired OS meets the following requirements. If not, we recommend debian (8.5 aka _jessie_ or later) or Ubuntu (16.04 LTS aka _Xenial Xerus_ or later). For better PHP performance we recommend a system with PHP 7.x support such as Ubuntu 16.04. Note: Installation on Windows 10 with PHP 5.6 was also successfully tested, but you will need to adjust the commands in these instructions by yourself to your local Windows version equivalents.

## Requirements
The following packages you should be able to install from your package manager:
- git
- Apache (or any other web server-software, e.g. nginx)
- PHP (`>= 5.6.4`) with the following extensions installed and enabled:
  - Imagick
  - memcached (on Windows this will not work -- see later)
  - mbstring
- libapache2-mod-php (on Unix systems)
- [Composer](https://getcomposer.org)
- PostGIS (`>= 2.0`)
- PostgreSQL (`>= 9.1.0`)
- ImageMagick
- ufraw
- memcached (extension DLL not available for Windows at the moment, see later)
- Python 3.x
  - pip
  - rdflib
  - psycopg2
  - getopt
- unzip
- php-pgsql
- phpunit
- nodejs
- npm
- bower (run `npm install -g bower` on the command line, **Please note**: You may have to link your `nodejs` executable to the new command `node`. e.g. `sudo ln -s /usr/bin/nodejs /usr/bin/node`)

Beside these packages we use a couple of packages you have to install on your own.
- Lumen (PHP-Framework), currently included in the Spacialist repository, so no need to install.
- [GeoServer](http://geoserver.org/) for hosting your own geo maps

## Setup
### Package Installation

1. Install all the required packages. For debian-based/apt systems you can use the following command

    ```bash
    sudo apt-get install git apache2 libapache2-mod-php unzip php composer postgresql postgis imagemagick php-pgsql php-imagick php-memcached php-mbstring ufraw memcached python3 python-pip python-rdflib python-psycopg2 phpunit nodejs npm
    ```
    
2. Clone This Repository

    ```bash
    git clone https://github.com/eScienceCenter/Spacialist
    ```

3. Download Dependencies

    ```bash
    cd Spacialist
    bower install
    cd lumen
    composer install
    ```

**Please note**: During the `composer install` you might get an error regarding an unsecure installation. To fix this you have to edit your `composer.json` file (only edit this file if you know what you're doing) in the `lumen` folder to disable secure HTTP connections. Add `"secure-http": false` or set `"secure-http": true` to `false` if the line already exists.

After editing the `composer.json` you have to re-run `composer` with
```bash
composer update
```

### Proxy Setup
To communicate with Lumen, Spacialist requires the API folder to be in the Spacialist folder. If you run Spacialist under `yourdomain.tld/Spacialist`, the Lumen API has to be `yourdomain.tld/Spacialist/api`.

Since Lumen has a sub-folder as document root `lumen/public`, it won't work to simply copy Lumen to your webserver's root directory.
One solution is to setup a proxy on the same machine and re-route all requests from `/Spacialist/api` to Lumen's public folder (e.g. `/var/www/html/Spacialist/lumen/public`).

1. Enable the webserver's proxy packages and the rewrite engine

    ```bash
    sudo a2enmod proxy proxy_http rewrite
    ```
    
2. Add a new entry to your hosts file, because your proxy needs a (imaginary) domain.

    ```bash
    sudo nano /etc/hosts
    # Add an entry to "redirect" a domain to your local machine (localhost)
    127.0.0.1 spacialist-lumen.tld # or anything you want
    ```
    
3. Add a new vHost file to your apache

    ```bash
    cd /etc/apache2/site-available
    sudo nano spacialist-lumen.conf
    ```
     
    Paste the following snippet into the file:
    ```apache
    <VirtualHost *:80>
      ServerName spacialist-lumen.tld
      ServerAdmin webmaster@localhost
      DocumentRoot /var/www/html/Spacialist/lumen/public
    
      DirectoryIndex index.php

      <Directory "/var/www/html/Spacialist/lumen/public">
        AllowOverride All
        Require all granted
      </Directory>
    </VirtualHost>
    ```
    
4. Add the proxy route to your default vHost file (e.g. `/etc/apache2/sites-available/000-default.conf`)

    ```apache
    ProxyPass "/spacialist_api" "http://spacialist-lumen.tld"
    ProxyPassReverse "/spacialist_api" "http://spacialist-lumen.tld"
    ```
    
5. Enable the new vHost file and restart the webserver

    ```bash
    sudo a2ensite spacialist-lumen.conf
    sudo service apache2 restart
    ```

### Configure Lumen
Lumen should now work, but to test it you need to create a `.env` file which stores the Lumen configuration. 
Inside the `lumen`-subfolder in the Spacialist installation, create the `.env` file: 
```bash
cd /var/www/html/Spacialist/lumen
sudo nano .env
```

Then paste this configuration (Please edit some of the configuration settings `*` to match your installation). **Note**: on Windows, memchached extension DLL seems unavailable. Use `CACHE_DRIVER=array` instead where indicated:
```
APP_ENV=local
APP_DEBUG=true
APP_KEY=* #this needs to be a 32 digit random key. Use an online generator or run php artisan jwt:generate twice

# Your database setup. pgsql is PostgreSQL. Host, port, database, username and password need to be configured first (e.g. using your database server's commands).
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=*
DB_USERNAME=*
DB_PASSWORD=*

CACHE_DRIVER=memcached # on Windows memcached extension unavailable, but it seems to work with "array"
QUEUE_DRIVER=sync

JWT_SECRET=* #same as APP_KEY, run php artisan jwt:generate
JWT_TTL=* #the time to live (in minutes) of your user tokens. Default is 60 (minutes).
JWT_REFRESH_TTL=* #the ttl (in minutes) in which you can generate a new token. Default is two weeks
JWT_BLACKLIST_GRACE_PERIOD=* #a time span in seconds which allows you to use the same token several times in this time span without blacklisting it (good for async api calls)
```

After the `.env` file has been configured you should run the migrations to setup your database.
```bash
php artisan migrate
```

To test your installation, simply open `http://yourdomain.tld/spacialist_api`. You should see a website with Lumen's current version.
Example:
```
Lumen (5.3.2) (Laravel Components 5.3.*)
```

### Optional Lumen Installation
Spacialist ships with Lumen preinstalled. If you ever have or want to install it on your own, please follow these instructions.

**Please note**: This manual is based on version 5.3 of Lumen. If you want to use a different version, please check the [official lumen manual](https://lumen.laravel.com/docs/)

1. Use `composer` to install the lumen executable

    ```bash
    composer global require "laravel/lumen-installer"
    ```
    
2. Change directory to the desired installation path (e.g. `/var/www/html/`)
3. Run `lumen new lumen` (you can replace the second "lumen" with any name you want. This is the folder name in which lumen will be installed). If the command `lumen` is not found, you can add it to your `PATH` or use the absolute path of the executable
    1. Change directory to `/usr/local/bin`
    2. Run `sudo ln -s /home/<your name>/.config/composer/vendor/bin/lumen lumen`
    3. **Alternatively** run `/home/<your name>/.config/composer/vendor/bin/lumen new lumen` instead of `lumen new lumen`
4. If the new Lumen application has been created successfully you can now start configuring your project
5. Change directory to your newly created project folder
6. Copy the existing `.env.example` file to `.env`
7. Add the `APP_KEY` to the `.env` file. The `APP_KEY` is a 32 char long random string. To generate such a string you can use your OS build-in method (if present) or use an online generator. Add the generated key to your `.env` file. (e.g. `APP_KEY=KEvtfHdL3Xl3xfYxJcfGp8FhrJz4hxKF`)
8. In the `.env` file you have to set your database connection information as well (the value for the `DB_CONNECTION` key for PostgreSQL is `pgsql`)

### GeoServer Installation
Additional geographical data are included through a GeoServer (see [installation/linux.html](http://docs.geoserver.org/latest/en/user/installation/linux.html)).

1. Download the GeoServer software: http://geoserver.org/release/stable/ (for Unix/Linux-based OS use the _platform independent binary_)
2. Install Java Runtime `sudo apt-get install openjdk-8-jre`
3. Create a new directory for GeoServer `sudo mkdir /usr/share/geoserver`
4. Unpack the binary into the newly created folder `unzip geoserver-2.9.1-bin.zip -d /usr/share/geoserver`
5. Set the correct folder permissions `sudo chown -R www-data: /usr/share/geoserver/` and `sudo chmod -R g+w /usr/share/geoserver` (**Please note**: Your current user has to be in your webserver's group (on Ubuntu it is the group `www-data`), otherwise you'll get errors on startup due to missing permissions. Alternatively you can use your own username in the `chown` command to fix this)
6. Change directory to `/usr/share/geoserver/geoserver-2.9.1/bin`
7. Run `./startup.sh`

Your server should now be ready. To check if it's running visit http://localhost:8080/geoserver Use the following credentials to login:
- User: **admin**
- Password: **geoserver**

#### Install the Importer Extension
1. Download extension from http://geoserver.org/release/2.9.1/ (Extensions > Miscellaneous > Importer)
2. Unzip the extension to the GeoServer's webapps lib folder `
unzip ~/Downloads/geoserver-2.9.1-importer-plugin.zip -d /usr/share/geoserver/geoserver-2.9.1/webapps/geoserver/WEB-INF/lib
` (Overwrite the `commons-fileupload-1.2.1.jar` file)
