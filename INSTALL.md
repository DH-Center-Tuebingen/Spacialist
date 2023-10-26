# Installation
We recommend a recent unix/linux-based OS. Please check if your desired OS meets the following requirements. If not, we recommend debian (9.5 aka _Stretch_ or later) or Ubuntu (18.04 LTS aka _Bionic Beaver_ or later). For Giza and later at least PHP 7.1.3 is required. Note: Installation on Windows 10 with PHP 5.6 was also successfully tested, but you will need to adjust the commands in these instructions by yourself to your local Windows version equivalents.

## Requirements
The following packages you should be able to install from your package manager:
- git
- Apache (or any other web server-software, e.g. nginx)
- PHP (`>= 8.0.2`) with the following extensions installed and enabled:
  - Imagick
  - memcached (on Windows this will not work -- see later)
  - mbstring
  - gd
  - xml
  - zip
- libapache2-mod-php (on Unix systems)
- [composer](https://getcomposer.org)
- PostGIS (`>= 2.5`)
- PostgreSQL (`>= 13`)
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

## Migration from < 0.6 (Lumen- and Angular-based releases)
There are no additional database migrations steps required. Laravel's migration command should take care of database changes. **But you must** update to the latest pre-0.6 release before switching to 0.6+.
However, since we switched to a different code base, you have to get the new dependencies (see _Download Dependencies_ in [Package Installation](INSTALL.md#package-installation)).
You should also check for changes in [Proxy Setup](INSTALL.md#proxy-setup) and [Configure Laravel](INSTALL.md#configure-laravel).
After switching to the new branch/release, you should get rid of the old dependencies.
**Before** downloading the new dependencies, you should do the following steps:
1. Copy `.env` file from `lumen` folder to the root folder (`mv lumen/.env .env`)
2. Remove entire `lumen` folder (`rm -rf lumen`)
3. Remove `bower_components` (if coming from a very old version) and `node_modules` (`rm -rf bower_components node_modules`)

## Migration from >= 0.6 and < 0.9 (Federsee, Giza, Helgö)
Some parts of Spacialist (Map, Files) have been released as separate Plugin. Thus, migrations have changed and only migrating from scratch or from the latest pre-0.9-Release (Helgö) is supported.
However, since we switched to a different code base, you have to get the new dependencies (see _Download Dependencies_ in [Package Installation](INSTALL.md#package-installation)).

## Setup
### Package Installation

1. Install all the required packages. For debian-based/apt systems you can use the following command

    ```bash
    sudo apt-get install git apache2 libapache2-mod-php unzip php composer postgresql postgis imagemagick php-pgsql php-imagick php-memcached php-mbstring php-gd php-zip php-xml ufraw memcached phpunit nodejs npm unzip
    ```
    If the required php modules are not enabled by default, you can enable them with
    ```bash
    a2enmod mod_proxy
    a2enmod mod_rewrite
    systemctl restart apache2.service
    ```

2. Clone This Repository

    ```bash
    git clone https://github.com/DH-Center-Tuebingen/Spacialist
    ```

3. Download Dependencies

    **Please note**: Before moving on, check your versions of composer (`composer -v`) and npm (`npm -v`). Older versions can cause problems (composer < 1.8, npm < 6.x). Update them manually if your OS has outdated versions.

    ```bash
    cd Spacialist
    npm install
    composer install
    ```

### Proxy Setup
Since Laravel has a sub-folder as document root `Spacialist/public`, it won't work to simply copy Laravel to your webserver's root directory.
One solution is to setup a proxy on the same machine and re-route all requests from `/Spacialist` to Laravel's public folder (e.g. `/var/www/html/Spacialist/public`).

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
    ProxyPass "/Spacialist" "http://spacialist-laravel.tld"
    ProxyPassReverse "/Spacialist" "http://spacialist-laravel.tld"
    ```

5. Enable the new vHost file and restart the webserver

    ```bash
    sudo a2ensite spacialist-laravel.conf
    sudo service apache2 restart
    ```

### Configure Laravel
In your `config/app.php` you have to adjust the `APP_URL` key. Replace `http://localhost` with the URL of your instance.
E.g. `https://spacialist.mydomain.tld`

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
MAIL_FROM_ADDRESS=webmaster@localhost
MAIL_FROM_NAME=Webmaster

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=

JWT_SECRET=ase64:<32bit-key> #this needs to be a 32 digit random key. Use 'php artisan jwt:secret'
JWT_BLACKLIST_GRACE_PERIOD=0

MIX_APP_PATH=
```

#### Send Mails
If you want to send mails to your users, you have to adjust the `MAIL_*` settings to match a smtp server from where you can send mails.

### Configure JavaScript
Spacialist is based on several JavaScript libraries, which are bundled using Webpack (configuration is done using Laravel Mix, a webpack-wrapper for Laravel). Only the zipped releases contain the already bundled JavaScript libraries. All other users have to run webpack to bundle these libraries.

Before running webpack, you have to adjust the public path in the mix config file `webpack.mix.js`. To do so, set your path using the `MIX_APP_PATH` variable in `.env` file.

```bash
MIX_APP_PATH=Spacialist/subfolder/instance/
```

Now you can run webpack using

```bash
npm run dev
# or
npm run prod
```
depending on whether you want a debugging-friendly development build or an optimized production-ready build.

### Migrations

After the `.env` file has been configured you should run the migrations to setup your database.
```bash
php artisan migrate
```

After your tables are set up, you need to create a new user to login and set the necessary permissions for this account. Luckily, Spacialist comes with a pre-defined account and permissions. Only thing you have to do is run
```bash
php artisan db:seed
```

We also have some demo data to play around with or get familiar with Spacialist. To add the demo data, run this command instead of `php artisan db:seed`:
```bash
php artisan db:seed --class=DemoSeeder
```

Now you can login with:
- **Email**: `admin@admin.com`
- **Password**: `admin`

**Important**: Since this is the same default password for all instances, we **strongly recommend** to change your password to something more secure. Even better is to create a new admin account with your actual email address and **delete** this default account.

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
