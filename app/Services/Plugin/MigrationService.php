<?php

namespace App\Services\Plugin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use App\Models\Plugin\Migration as PluginMigration;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Support\Log\PluginLog;
use Illuminate\Database\Migrations\Migration;


/**
 * Plugins can define Migrations by declaring their directory inside their manifest file.
 * All migrations from all files are loaded and executed in the order of their creation as defined
 * by their file name, which must follow the Laravel migration file naming convention (e.g. "2024_01_01_000000_create_users_table.php").
 * 
 * LEGACY: The 'MyPlugin/Migration' directory is by default evaluated for migration files. This will be removed in a future version.
 * 
 * ```xml
 *     <migrations path="Migration" />
 * ``` 
 */
class MigrationService extends PluginService {

    protected function getPath(): string {
        return 'plugin-migrations';
    }

    public function install(Plugin $plugin): void {
        $this->run($plugin);
    }

    public function update(Plugin $plugin): void {
        $this->run($plugin);
    }

    public function remove(Plugin $plugin): void {
        $this->rollback($plugin);
    }

    public function inspect(Plugin $plugin): array {
        $ranMigrations = PluginMigration::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $directory = $this->getMigrationDirectory($plugin);
        $allMigrations = $this->getMigrationList($directory);
        $migrations = [];
        foreach($allMigrations as $migration) {
            $migrations[] = [
                'name' => $migration,
                'ran' => in_array($migration, $ranMigrations)
            ];
        }

        return $migrations;
    }

    public function run(Plugin $plugin): void {
        $this->exec($plugin);
    }

    public function rollback(Plugin $plugin): void {
        $this->exec($plugin, true);
    }

    protected function exec(Plugin $plugin, $rollback = false) {
        //Determine the next batch number
        $nextBatch = PluginMigration::getNextBatchNumber();
        $directory = $this->getMigrationDirectory($plugin);
        $migrations = $this->getMissingMigrations($plugin, $rollback);
        
        info(" MISSING MIGRATIONS: " . implode(", ", $migrations));
        
        foreach($migrations as $migration) {
            $migrationInstance = $this->getMigrationClassName($plugin, $directory, $migration);
            try {
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
            } catch(\Exception $e) {
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
        $content = $manifest->getContent();
        if(array_key_exists('migrations', $content)) {
            if(
                array_key_exists('@attributes', $content['migrations']) &&
                array_key_exists('path', $content['migrations']['@attributes']) &&
                !empty($content['migrations']['@attributes']['path'])
            ) {
                return PluginDirectory::byPlugin($plugin)->getPluginPath($content['migrations']['@attributes']['path']);
            } else {
                PluginLog::logWarning("Plugin {$plugin->name} has an invalid migration path defined in its manifest. Expected format: <migrations path=\"Migrations\" />");
            }
        }

        return null;
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

    function getMigrationDirectory(Plugin $plugin): string {
        $migrationDirectory = $this->getManifestMigration($plugin);

        if(!$migrationDirectory) {
            $migrationDirectory = $this->getLegacyMigrationPath($plugin);
        }

        return $migrationDirectory;
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
            info("FILE EXISTS");
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

            info($migrations->values()->toArray());
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
     * @param Plugin $plugin - The plugin to get the migration path for
     * @param mixed $migrationFile - optional name of specific migration file, e.g. "2024_01_01_000000_create_users_table.php"
     * @return string - The path to the plugin's migration directory or to a specific migration file if $migrationFile is provided
     */
    function getLegacyMigrationPath(Plugin $plugin, ?string $migrationFile = null): string {
        $pluginDir = new PluginDirectory($plugin);
        $path = $pluginDir->getPluginPath("Migration");
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
     * @param mixed $pluginNamespacePath - The namespace path to the migration file, e.g. "Subdir\Database\Migration"
     * @param mixed $migrationFile - The migration file name, e.g. "2024_01_01_000000_create_users_table.php"
     * @throws \Exception throws an exeption if the migration has in incompatible name
     * @return mixed - LEGACY - This should return an instance of "Illuminate\Database\Migrations\Migration" currently plugins don't comply with that,
     *                 this will be changed in a future version.  
     */
    function getMigrationClassName(Plugin $plugin, string $directory, $migrationFile) {
        preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $migrationFile, $matches);
        if(count($matches) != 3) {
            throw new \Exception("Invalid migration file name: $migrationFile");
        }
        $className = Str::studly($matches[2]);
        require(Str::finish($directory, '/') . '/' . $migrationFile);
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

        $directory = $this->getMigrationDirectory($plugin);
        $allMigrations = $this->getMigrationList($directory);

        if($rollback) {
            $missingMigrations = array_intersect($allMigrations, $ranMigrations);
        } else {
            $missingMigrations = array_diff($allMigrations, $ranMigrations);
        }

        return array_values($missingMigrations);
    }

}