# Installation
We recommend a rather new unix/linux-based OS. Please check if your desired OS meets the following requirements. If not, we recommend debian (8.5 aka _jessie_ or later) or Ubuntu (16.04 LTS aka _Xenial Xerus_ or later). For better PHP-performance we recommend a system with PHP 7.x support such as Ubuntu 16.04.

## Requirements
The following packages you should be able to install from your package manager:
- Apache (or any other web server-software, e.g. nginx)
- PHP
  - Imagick
  - memcached
  - mbstring
- composer
- PostGIS (`>= 2.0`)
- PostgreSQL (`>= 9.1.0`)
- ImageMagick
- ufraw
- memcached
- Python 3.x
  - pip
  - rdflib
  - psycopg2
  - getopt
- unzip
- php-pgsql
- libapache2-mod-php

Beside these packages we use a couple of packages you have to install on your own.
- Lumen (PHP-Framework)
- Geoserver

## Setup
### Package installation
1. Install all the required packages. For debian-based/apt systems you can use the following command
```bash
sudo apt-get install apache2 libapache2-mod-php unzip php composer postgresql postgis imagemagick php-pgsql php-imagick php-memcached php-mbstring ufraw memcached python3 python-pip python-rdflib python-psycopg2
```

### Lumen installation
Spacialist ships with Lumen preinstalled. If you ever have or want to install it on your own, please follow these instructions:

**Please note**: This manual is based on version 5.3 of lumen. If you want to use a different version, please check the [official lumen manual](https://lumen.laravel.com/docs/)
1. Use `composer` to install the lumen executable
```bash
composer global require "laravel/lumen-installer"
```
2. Change directory to the desired installation path (e.g. `/var/www/html/`)
3. Run `lumen new lumen` (you can replace the second "lumen" with any name you want. This is the folder name in which lumen will be installed). If the command `lumen` is not found, you can add it to your `PATH` or use the absolute path of the executable
    1. Change directory to `/usr/local/bin`
    2. Run `sudo ln -s /home/<your name>/.config/composer/vendor/bin/lumen lumen`
    3. **Alternatively** run `/home/<your name>/.config/composer/vendor/bin/lumen new lumen` instead of `lumen new lumen`
4. If the new lumen application has been created successfully you can now start configuring your project
5. Change directory to your newly created project folder
6. Copy the existing `.env.example` file to `.env`
7. Add the `APP_KEY` to the `.env` file. The `APP_KEY` is a 32 char long random string. To generate such a string you can use your OS build-in method (if present) or use an online generator. Add the generated key to your `.env` file. (e.g. `APP_KEY=KEvtfHdL3Xl3xfYxJcfGp8FhrJz4hxKF`)
8. In the `.env` file you have to set your database connection information as well (the value for the `DB_CONNECTION` key for PostgreSQL is `pgsql`)

### Geoserver installation
Additional geographical data are included through a Geoserver (see [installation/linux.html](http://docs.geoserver.org/latest/en/user/installation/linux.html)).

1. Download the Geoserver software: http://geoserver.org/release/stable/ (for Unix/Linux-based OS use the _platform independent binary_)
2. Install Java Runtime `sudo apt-get install openjdk-8-jre`
3. Create a new directory for Geoserver `sudo mkdir /usr/share/geoserver`
4. Unpack the Binary into the newly created folder `unzip geoserver-2.9.1-bin.zip -d /usr/share/geoserver`
5. Set the correct folder permissions `sudo chown -R www-data: /usr/share/geoserver/` and `sudo chmod -R g+w /usr/share/geoserver` (**Please note**: Your current user has to be in your webserver's group (on Ubuntu it is the group `www-data`), otherwise you'll get errors on startup due to missing permissions. Alternatively you can use your own username in the `chown` command to fix this)
6. Change directory to `/usr/share/geoserver/geoserver-2.9.1/bin`
7. Run `./startup.sh`

Your server should now be ready. To check if it's running visit http://localhost:8080/geoserver Use the following credentials to login:
- User: **admin**
- Password: **geoserver**

* das hier geht aber (siehe [gistutor.com](http://www.gistutor.com/geoserver/11-beginner-geoserver-tutorials/22-how-to-install-geoserver-202-onto-a-linux-fedora-server-using-the-binary-installer.html)):


#### Install importer-extension
1. Download extension from http://geoserver.org/release/2.9.1/ (Extensions > Miscellaneous > Importer)
2. Unzip the extension to the geoserver's webapps lib folder `
unzip ~/Downloads/geoserver-2.9.1-importer-plugin.zip -d /usr/share/geoserver/geoserver-2.9.1/webapps/geoserver/WEB-INF/lib
` (Overwrite the `commons-fileupload-1.2.1.jar` file)

