<?php

namespace App\Services\Plugin;

use App\Enums\LifecycleOperation;
use App\Exceptions\PluginLifecycleException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use App\Models\Plugin\Migration as PluginMigration;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;


/**
 * Plugins can define Migrations by declaring their directory inside their manifest file.
 * All migrations from all files are loaded and executed in the order of their creation as defined
 * by their file name, which must follow the Laravel migration file naming convention (e.g. "2024_01_01_000000_create_users_table.php").
 * 
 * LEGACY: The 'MyPlugin/Migration' directory is by default evaluated for migration files. This will be removed in a future version.
 * 
 * ```xml
 *     <migrations src="Migration" />
 * ``` 
 */
class MigrationService extends PluginService {
    
    const LEGACY_MIGRATION_DIRECTORY = 'Migration'; 

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $this->run($plugin, LifecycleOperation::INSTALLATION);
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->run($plugin, LifecycleOperation::UPDATE);
    }

    public function remove(Plugin $plugin, PluginManifest $manifest): void {
        $this->rollback($plugin, LifecycleOperation::REMOVE);
    }

    public function inspect(Plugin $plugin): array {
        $ranMigrations = PluginMigration::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $directory = $this->getMigrationDirectory($plugin);
        $absoluteDirectory = PluginDirectory::fromPlugin($plugin)->getAbsolutePluginPath($directory);
        $allMigrations = $this->getMigrationList($absoluteDirectory);
        $migrations = [];
        foreach($allMigrations as $migration) {
            $migrations[] = [
                'name' => $migration,
                'ran' => in_array($migration, $ranMigrations)
            ];
        }

        return $migrations;
    }
    
    public function run(Plugin $plugin, ?LifecycleOperation $operation = null): void {
        $this->exec($plugin, operation: $operation);
    }

    public function rollback(Plugin $plugin, ?LifecycleOperation $operation = null): void {
        $this->exec($plugin, true, $operation);
    }

    protected function exec(Plugin $plugin, bool $rollback = false, ?LifecycleOperation $operation = null) {
        //Determine the next batch number
        $nextBatch = PluginMigration::getNextBatchNumber();
        $directory = $this->getMigrationDirectory($plugin);
        $pluginDir = PluginDirectory::fromPlugin($plugin);
        $absoluteDirectory = $pluginDir->getAbsolutePluginPath($directory);
        if(!file_exists($absoluteDirectory) || !is_dir($absoluteDirectory)) {
            
            //Legacy support: If the default migration directory does not exist, we assume there are no migrations to run and return early.
            if($directory === self::LEGACY_MIGRATION_DIRECTORY) {
                return;
            }
        
            throw new PluginLifecycleException($plugin, "Migration directory not found: '$absoluteDirectory'", $operation);
        }
        
        $migrations = $this->getMissingMigrations($plugin, $rollback);        
        foreach($migrations as $migration) {
            $migrationInstance = $this->getMigrationClassName($plugin, $absoluteDirectory, $migration);
            try{
                call_user_func([$migrationInstance, $rollback ? 'rollback' : 'migrate']);
                if($rollback) {
                    //Remove the record of the migration
                    PluginMigration::where('plugin_id', $plugin->id)
                        ->where('migration', $migration)
                        ->delete();
                } else {
                    //Record the migration as run
                    PluginMigration::create([
                        'plugin_id' => $plugin->id,
                        'migration' => $migration,
                        'batch' => $nextBatch
                    ]);
                }
            }catch(\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * 
     * 
     * @param Plugin $plugin
     * @throws \Exception
     * @return string|null
     */
    public function getManifestMigration(Plugin $plugin): string|null {
        $manifest = PluginManifest::fromPlugin($plugin);
        $xmlNodes = $manifest->getTagNodes('migrations');
        
        if(!$xmlNodes || count($xmlNodes) === 0) {
            return null;
        }
        
        if(count($xmlNodes) > 1) {
            throw new \Exception("Plugin {$plugin->name} has multiple <migrations> tags in its manifest, but only one is allowed.");
        }
        
        $attributes = $xmlNodes[0]['attributes'] ?? [];
        $src = $attributes['src'] ?? trim((string) ($xmlNodes[0]['text'] ?? ''));

        return $src !== '' ? $src : null;
    }

    /**
     * Adds a migration entry to the database without running it.
     * This is required for legacy plugins that were not yet using the
     * plugin migration system and ran their migrations without supervision.
     * 
     * @param mixed $migrationName
     * @param Plugin $plugin
     * @return void
     */
    public function set($migrationName, Plugin $plugin): void {
        //Determine the next batch number
        $nextBatch = PluginMigration::getNextBatchNumber();
        //Record the migration as run   
        PluginMigration::create([
            'plugin_id' => $plugin->id,
            'migration' => $migrationName,
            'batch' => $nextBatch
        ]);
    }

    /**
     * Gets the migration directory for the plugin. If the manifest defines a migration directory, 
     * it will be returned, otherwise the legacy migration directory will be used.
     * 
     * @param Plugin $plugin
     * @return string - The migration directory path defined in the manifest or the legacy migration directory if not defined in the manifest.
     */
    public function getMigrationDirectory(Plugin $plugin): string {
        $migrationDirectory = $this->getManifestMigration($plugin);

        if(!$migrationDirectory) {
            $migrationDirectory = self::LEGACY_MIGRATION_DIRECTORY;
        }
        return $migrationDirectory;
    }
    
    /**
     * Get's the absolute path to the migration directory of the plugin. 
     * 
     * 
     * @param Plugin $plugin
     * @return string
     */
    public function getAbsoluteMigrationDirectory(Plugin $plugin): string {
        $migrationDirectory = $this->getMigrationDirectory($plugin);
        return PluginDirectory::fromPlugin($plugin)->getAbsolutePluginPath($migrationDirectory);
    }

    /**
     * Get's a list of migration files in the plugin on the filesystem.
     * 
     * @param Plugin $plugin - The plugin to get the migration list for
     * @param mixed $rollback - If true, get the list in descending order. Required for rollbacks to get the last migration first.
     * @return array - A list of migration file names, e.g. ["2024_01_01_000000_create_users_table.php", "2024_01_02_000000_create_posts_table.php"]
     */
    function getMigrationList(string $migrationDirectory, $rollback = false): array {
        if(file_exists($migrationDirectory) && is_dir($migrationDirectory)) {
            $migrations = collect(File::files($migrationDirectory))->map(function ($f) {
                return $f->getFilename();
            });

            $migrations = $migrations->filter(function ($f) {
                preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $f, $matches);
                return count($matches) == 3;
            });

            if($rollback) {
                $migrations = $migrations->sortDesc();
            } else {
                $migrations = $migrations->sort();
            }

            return $migrations->values()->toArray();
        } else {
            return [];
        }
    }

    function resolveDirectoryNamspace(Plugin $plugin, string $migrationDirectory):string {
        $normalizedPath = str_replace('/', '\\', Str::after($migrationDirectory, PluginDirectory::getPathByName($plugin->name)));
        // Transform the path into a namespace e.g. app/plugins/my-plugin to App\Plugins\MyPlugin
        $namespace = collect(explode('\\', $normalizedPath))
            ->filter()
            ->map(fn($part) => Str::studly(str_replace('-', '_', $part)))
            ->implode('\\');

        return $namespace;
    }

    /**
     * Get path to the plugin's migration directory (./Migration)
     * 
     * @param string $pluginName - The plugin to get the migration path for
     * @param mixed $migrationFile - optional name of specific migration file, e.g. "2024_01_01_000000_create_users_table.php"
     * @return string - The path to the plugin's migration directory or to a specific migration file if $migrationFile is provided
     */
    public static function getLegacyMigrationPath(string $pluginName, ?string $migrationFile = null): string {
        $pluginDir = new PluginDirectory($pluginName);
        $path = $pluginDir->getPluginPath(self::LEGACY_MIGRATION_DIRECTORY);
        if($migrationFile) {
            $path .= '/' . $migrationFile;
        }
        return $path;
    }

    /**
     * Validates if the migration file does match the Laravel migration file naming convention.
     * If it's a valid migration, the migration class will be returned, otherwise an exception will be thrown.
     * 
     * @param Plugin $plugin - The plugin the migration belongs to
     * @param string $directory - Absolute path to the migration directory.
     * @param string $migrationFile - The migration file name, e.g. "2024_01_01_000000_create_users_table.php"
     * @throws \Exception throws an exeption if the migration has in incompatible name
     * @return mixed - LEGACY - This should return an instance of "Illuminate\Database\Migrations\Migration" currently plugins don't comply with that,
     *                 this will be changed in a future version.  
     */
    function getMigrationClassName(Plugin $plugin, string $directory, string $migrationFile) {
        preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $migrationFile, $matches);
        if(count($matches) != 3) {
            throw new \Exception("Invalid migration file name: $migrationFile");
        }
        $className = Str::studly($matches[2]);
        $migrationPath = Str::finish($directory, '/') . $migrationFile;
        require_once($migrationPath);
        $pluginNamespacePath = $this->resolveDirectoryNamspace($plugin, $directory);
        $prefixedClassName = "App\\Plugins\\$plugin->name\\$pluginNamespacePath\\$className";
        return new $prefixedClassName();
    }


    /**
     * Get's all plugin migrations in the database and compares them with all file
     * migrations and retuns all migrations that are not yet in the database.  
     * 
     * @param Plugin $plugin - The plugin to check for missing migrations
     * @param mixed $rollback - If true, get all that are in the database and on the file system. To get a list of migrations that can be rolled back.
     * @return array - A list of migration file names that are missing in the database (or that can be rolled back if $rollback is true)
     */
    function getMissingMigrations(Plugin $plugin, $rollback = false): array {
        $ranMigrations = PluginMigration::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $absoluteDirectory = $this->getAbsoluteMigrationDirectory($plugin);
        $allMigrations = $this->getMigrationList($absoluteDirectory);

        if($rollback) {
            $missingMigrations = array_intersect($allMigrations, $ranMigrations);
        } else {
            $missingMigrations = array_diff($allMigrations, $ranMigrations);
        }

        return array_values($missingMigrations);
    }

}