# Installation
We recommend a recent unix/linux-based OS. Please check if your desired OS meets the following requirements. If not, we recommend debian (8.5 aka _jessie_ or later) or Ubuntu (16.04 LTS aka _Xenial Xerus_ or later). For Giza and later PHP 7.0+ is required. Note: Installation on Windows 10 with PHP 5.6 was also successfully tested, but you will need to adjust the commands in these instructions by yourself to your local Windows version equivalents.

## Requirements
The following packages you should be able to install from your package manager:
- git
- Apache (or any other web server-software, e.g. nginx)
- PHP (`>= 7.0.0`) with the following extensions installed and enabled:
  - Imagick
  - memcached (on Windows this will not work -- see later)
  - mbstring
  - gd
- libapache2-mod-php (on Unix systems)
- [composer](https://getcomposer.org)
- PostGIS (`>= 2.0`)
- PostgreSQL (`>= 9.1.0`)
- ImageMagick
- ufraw
- memcached (extension DLL not available for Windows at the moment, see later)
- unzip
- php-pgsql
- phpunit
- nodejs
- npm

Beside these packages we use a couple of packages you have to install on your own.
- Laravel (PHP-Framework), currently included in the Spacialist repository, so no need to install.
- [GeoServer](http://geoserver.org/) for hosting your own geo maps

## Setup
### Package Installation

1. Install all the required packages. For debian-based/apt systems you can use the following command

    ```bash
    sudo apt-get install git apache2 libapache2-mod-php unzip php composer postgresql postgis imagemagick php-pgsql php-imagick php-memcached php-mbstring php-gd ufraw memcached phpunit nodejs npm
    ```

2. Clone This Repository

    ```bash
    git clone https://github.com/eScienceCenter/Spacialist
    ```

3. Download Dependencies

    ```bash
    cd Spacialist
    npm install
    composer install
    ```

**Please note**: During the `composer install` you might get an error regarding an unsecure installation. To fix this you have to edit your `composer.json` file (only edit this file if you know what you're doing) in the `lumen` folder to disable secure HTTP connections. Add `"secure-http": false` or set `"secure-http": true` to `false` if the line already exists.

After editing the `composer.json` you have to re-run `composer` with
```bash
composer update
```

### Proxy Setup
To communicate with Laravel, Spacialist requires the API folder to be in the Spacialist folder. If you run Spacialist under `yourdomain.tld/Spacialist`, the Laravel API has to be `yourdomain.tld/Spacialist/api`.

Since Laravel has a sub-folder as document root `laravel/public`, it won't work to simply copy Laravel to your webserver's root directory.
One solution is to setup a proxy on the same machine and re-route all requests from `/Spacialist/api` to Laravel's public folder (e.g. `/var/www/html/Spacialist/laravel/public`).

1. Enable the webserver's proxy packages and the rewrite engine

    ```bash
    sudo a2enmod proxy proxy_http rewrite
    ```

2. Add a new entry to your hosts file, because your proxy needs a (imaginary) domain.

    ```bash
    sudo nano /etc/hosts
    # Add an entry to "redirect" a domain to your local machine (localhost)
    127.0.0.1 spacialist-laravel.tld # or anything you want
    ```

3. Add a new vHost file to your apache

    ```bash
    cd /etc/apache2/sites-available
    sudo nano spacialist-laravel.conf
    ```

    Paste the following snippet into the file:
    ```apache
    <VirtualHost *:80>
      ServerName spacialist-laravel.tld
      ServerAdmin webmaster@localhost
      DocumentRoot /var/www/html/Spacialist/public

      DirectoryIndex index.php

      <Directory "/var/www/html/Spacialist/public">
        AllowOverride All
        Require all granted
      </Directory>
    </VirtualHost>
    ```

4. Add the proxy route to your default vHost file (e.g. `/etc/apache2/sites-available/000-default.conf`)

    ```apache
    ProxyPass "/Spacialist/api" "http://spacialist-laravel.tld"
    ProxyPassReverse "/Spacialist/api" "http://spacialist-laravel.tld"
    ```

5. Enable the new vHost file and restart the webserver

    ```bash
    sudo a2ensite spacialist-laravel.conf
    sudo service apache2 restart
    ```

### Configure Laravel
Laravel should now work, but to test it you need to create a `.env` file which stores the Laravel configuration.
Inside the installation folder, create the `.env` file:
```bash
cd /var/www/html/Spacialist
sudo nano .env
```

Then paste this configuration (Please edit some of the configuration settings `*` to match your installation). **Note**: on Windows, memchached extension DLL seems unavailable. Use `CACHE_DRIVER=array` instead where indicated:
```
APP_NAME=Spacialist
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:<32bit-key> #this needs to be a 32 digit random key. Use 'php artisan key:generate'

# Your database setup. pgsql is PostgreSQL. Host, port, database, username and password need to be configured first (e.g. using your database server's commands).
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=*
DB_USERNAME=*
DB_PASSWORD=*

BROADCAST_DRIVER=log
CACHE_DRIVER=memcached # on Windows memcached extension unavailable, but it seems to work with "array"
SESSION_DRIVER=file
QUEUE_DRIVER=sync

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
```

#### Protected Files (Deprecated?)
Your uploaded files are stored in a public folder. To increase security it is recommended to define a random path in your `.env` file. The matching key is `SP_FILE_PATH`. You also have to create the path on your system (Do not actually create the last part of the path, you have to create it as a softlink later).

**Example:**
```bash
# added to .env file
SP_FILE_PATH=mysecret/anothersecret/privateFolderXYZ
```
```bash
cd /var/www/html/Spacialist/public
mkdir -p storage/mysecret/anothersecret
cd storage/mysecret/anothersecret
ln -s /var/www/html/Spacialist/storage/app/images privateFolderXYZ
```

### Migrations

After the `.env` file has been configured you should run the migrations to setup your database.
```bash
php artisan migrate
```

To test your installation, simply open `http://yourdomain.tld/Spacialist/api`. You should see a website with Laravel's current version.
Example:
```
Laravel (5.5.33)
```

#### External storage
Laravel supports different filesystems. Some of the most popular adapters:
- AWS S3
- Dropbox
- Rackspace
- SFTP
- WebDAV

To enable one of these adapters you need to add the driver to your `composer.json`. For a list of available adapters, see [here](https://github.com/thephpleague/flysystem). To use one of the drivers add it to the `config => filesystems => disks` array in `bootstrap/app.php`. The `local` driver is already configured and set as default. To switch to another default adapter simply add the configuration to the `disks` array and set the `default` to the key of your added driver.

For further informations regarding the cotent of `config => filesystems => disks` see these documentations:
- [Laravel Filesystem](https://laravel.com/docs/5.4/filesystem)
- [Laravel Sample Config](https://github.com/laravel/laravel/blob/master/config/filesystems.php)
- [Flysystem Github Page](https://github.com/thephpleague/flysystem)
- [Flysystem Laravel Integration](https://github.com/GrahamCampbell/Laravel-Flysystem)

##### Example:
```php
// bootstrap/app.php
<?php
....
config([
    "filesystems" => [
        'default' => 'ftp',
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => storage_path('app'),
            ],
            'ftp' => [
                'driver'   => 'ftp',
                'host'     => 'ftp.example.com',
                'username' => 'your-username',
                'password' => 'your-password',
                // Optional FTP Settings...
                // 'port'     => 21,
                // 'root'     => '',
                // 'passive'  => true,
                // 'ssl'      => true,
                // 'timeout'  => 30,
            ]
        ],
    ],
]);
....
?>
```

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
