# Migration

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
