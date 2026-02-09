# Plugins

Plugins are a powerful way to extend the functionality of your application without modifying the core codebase. They allow you to add new features without the need to modify the core application, making it easier to integrate custom functionality for different projects.

## Plugin Structure

Each plugin should be placed in the `app/Plugins/` directory with the following structure:

```
app/Plugins/YourPlugin/
├── App/
│   ├── info.xml              # Plugin metadata
│   ├── permissions.json      # (Optional) Plugin permissions
│   └── role-presets.json     # (Optional) Role presets
├── composer.json             # PHP dependencies
├── package.json              # JavaScript dependencies
├── routes/                   # (Optional) Plugin routes
├── src/                      # Frontend source files
├── Scopes/                   # (Optional) Model scopes
├── js/
│   └── script.js             # Compiled plugin script
└── CHANGELOG.md              # Version history
```

## Features

### PHP Dependencies

Plugins can declare their own PHP dependencies using a standard `composer.json` file. The main application uses the [Composer Merge Plugin](https://github.com/wikimedia/composer-merge-plugin) to automatically discover and install all plugin dependencies when you run `composer install` or `composer update`.

#### How It Works

1. **Each plugin has its own `composer.json`** in its root directory
2. **Dependencies are automatically merged** into the main application's composer.json
3. **All packages are installed** in the main application's `vendor/` directory
4. **Composer handles version resolution** automatically

#### Creating a Plugin composer.json

Create a `composer.json` file in your plugin's root directory:

```json
{
    "name": "spacialist-plugin/your-plugin-name",
    "description": "Description of your plugin",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your.email@example.com"
        }
    ],
    "require": {
        "php": ">=8.2.0",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "App\\Plugins\\YourPluginName\\": "App/"
        }
    }
}
```

#### Important Notes

- **Package naming**: Use the format `spacialist-plugin/your-plugin-name` (kebab-case)
- **PHP version**: Must be compatible with the main application (>=8.2.0)
- **Autoloading**: Follow PSR-4 standard with namespace `App\Plugins\YourPluginName\`
- **Version constraints**: Use semantic versioning (e.g., `^7.0`, `~2.5`, `>=1.0.0`)

#### Installing Dependencies

After creating or modifying a plugin's `composer.json`, run:

```bash
composer update
```

This will:
- Discover all plugin `composer.json` files
- Merge their dependencies
- Resolve version conflicts
- Install all packages to `vendor/`

#### Version Conflicts

If multiple plugins require different versions of the same package, Composer will:
1. Attempt to find a compatible version that satisfies all constraints
2. Display an error if no compatible version exists
3. You'll need to update plugin requirements to compatible versions

#### Example: Adding a New Dependency

**Before:**
```json
{
    "require": {
        "php": ">=8.2.0"
    }
}
```

**After:**
```json
{
    "require": {
        "php": ">=8.2.0",
        "symfony/yaml": "^6.0",
        "league/csv": "^9.8"
    }
}
```

Then run `composer update` to install the new packages.

### Metadata File (info.xml)

The `App/info.xml` file contains essential plugin metadata:

```xml
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<info>
    <name>YourPlugin</name>
    <title>Your Plugin Title</title>
    <description>Description of what your plugin does</description>
    <version>1.0.0</version>
    <licence>MIT</licence>
    <authors>
        <author>Your Name</author>
    </authors>
    <compatibility>
        <spacialist>0.12</spacialist>
        <php>8.2</php>
    </compatibility>
    <dependencies>
        <!-- List other plugins this plugin depends on -->
    </dependencies>
    <permissions>
        <!-- Declare what features your plugin uses -->
        <!-- <client /> -->
        <!-- <migration /> -->
        <!-- <routes /> -->
        <!-- <public /> -->
    </permissions>
    <accesspoints>
        <!-- Define custom routes/pages -->
        <!-- <accesspoint id="custom-page" label="Custom Page" path="/custom" /> -->
    </accesspoints>
    <scopes>
        <!-- Register model scopes -->
        <!-- <scope src="YourScope.php" on="App\Entity" /> -->
    </scopes>
    <attributes>
        <!-- Register custom attributes -->
    </attributes>
</info>
```

#### Key Fields

- **name**: Internal identifier (must match directory name)
- **title**: Display name shown in UI
- **version**: Semantic version (major.minor.patch)
- **compatibility**: Minimum required versions
- **permissions**: Required features (migration, routes, etc.)

### Permissions

Define plugin-specific permissions in `App/permissions.json`:

```json
{
    "plugin_group": [
        {
            "name": "create",
            "display_name": "Create items",
            "description": "Allow creating new items"
        },
        {
            "name": "delete",
            "display_name": "Delete items",
            "description": "Allow deleting items"
        }
    ]
}
```

Permissions are automatically registered during plugin installation.

### Routes

Create custom API routes in `routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/plugin-data', 'App\Plugins\YourPlugin\App\Controllers\DataController@index');
    Route::post('/plugin-data', 'App\Plugins\YourPlugin\App\Controllers\DataController@store');
});
```

### Migrations

Place database migrations in `App/Migrations/`:

```php
<?php

use App\MigrationBase;
use Illuminate\Database\Schema\Blueprint;

return new class extends MigrationBase
{
    public function up()
    {
        $this->schema()->create('plugin_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema()->dropIfExists('plugin_table');
    }
};
```

### Model Scopes

Add global scopes in `Scopes/` and register them in `info.xml`:

```php
<?php

namespace App\Plugins\YourPlugin\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class YourScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Apply your scope logic
    }
}
```

Register in `info.xml`:
```xml
<scopes>
    <scope src="YourScope.php" on="App\Entity" />
</scopes>
```

### Frontend Integration

Build your frontend with Vite and export it to `js/script.js`. The plugin system will automatically load this file when the plugin is activated.

### Changelog

Maintain a `CHANGELOG.md` file to track version history:

```markdown
# Changelog

## Version 1.1.0 - 2026-02-09
### Added
- New feature X
- Support for Y

### Fixed
- Bug in Z

## Version 1.0.0 - 2026-01-01
- Initial release
```

## Development Workflow

1. **Create plugin directory** in `app/Plugins/YourPlugin`
2. **Add composer.json** with your PHP dependencies
3. **Add package.json** with your JavaScript dependencies
4. **Create info.xml** with plugin metadata
5. **Run composer update** to install PHP dependencies
6. **Run npm install** to install JavaScript dependencies
7. **Develop your plugin** (backend, frontend, migrations, etc.)
8. **Build frontend**: `npm run build`
9. **Test your plugin** in development mode
10. **Install via admin interface** when ready

## Best Practices

- **Use semantic versioning** for your plugin versions
- **Document breaking changes** in CHANGELOG.md
- **Specify minimum PHP/Spacialist versions** in info.xml
- **Use PSR-4 autoloading** for PHP classes
- **Keep dependencies minimal** to avoid conflicts
- **Test with different plugin combinations**
- **Provide clear installation instructions**

## Troubleshooting

### Dependency Conflicts

If you encounter version conflicts:

```bash
composer update --with-all-dependencies
```

Check which plugins require conflicting versions:
```bash
composer why vendor/package
composer why-not vendor/package 2.0
```

### Autoloading Issues

If classes aren't found after adding to composer.json:

```bash
composer dump-autoload
```

### Plugin Not Detected

1. Check that `info.xml` is in `App/` directory
2. Verify XML is valid
3. Ensure directory name matches `<name>` in info.xml
4. Refresh plugin state in admin interface 