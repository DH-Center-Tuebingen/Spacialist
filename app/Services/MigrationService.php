<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use App\Interfaces\IPluggable;
use App\Models\Plugin\Migration as PluginMigration;
use App\Plugin;

class MigrationService implements IPluggable {

    protected function getPath(): string {
        return 'plugin-migrations';
    }

    public function install(Plugin $plugin): void {
        $this->run($plugin);
    }

    public function update(Plugin $plugin): void {
        $this->run($plugin);
    }

    public function uninstall(Plugin $plugin): void {
        // Nothing needs to be done here...
    }

    public function remove(Plugin $plugin): void {
        $this->rollback($plugin);
    }

    public function inspect(Plugin $plugin): array {
        $ranMigrations = PluginMigration::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $allMigrations = $this->getMigrationList($plugin);
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

    function exec(Plugin $plugin, $rollback = false) {
        //Determine the next batch number
        $nextBatch = PluginMigration::getNextBatchNumber();
        $migrations = $this->getMissingMigrations($plugin, $rollback);
        foreach($migrations as $migration) {
            $migrationInstance = $this->getMigrationClassName($plugin, $migration);
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

    function getMigrationList(Plugin $plugin, $rollback = false) {
        $path = $this->getMigrationPath($plugin);
        if(file_exists($path) && is_dir($path)) {
            $migrations = collect(File::files($path))->map(function ($f) {
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
        }

        return [];
    }

    function getMigrationPath(Plugin $plugin, ?string $subpath = null): string {
        $path = $plugin->getPath("Migrations");
        if($subpath) {
            $path .= '/' . $subpath;
        }
        return  $path;
    }

    function getMigrationClassName(Plugin $plugin, $migrationFile): object {
        preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $migrationFile, $matches);
        if(count($matches) != 3) {
            throw new \Exception("Invalid migration file name: $migrationFile");
        }
        $className = Str::studly($matches[2]);
        require($this->getMigrationPath($plugin, $migrationFile));
        $prefixedClassName = "App\\Plugins\\$plugin->name\\Migrations\\$className";
        return new $prefixedClassName();
    }

    function getMissingMigrations(Plugin $plugin, $rollback = false): array {
        $ranMigrations = PluginMigration::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $allMigrations = $this->getMigrationList($plugin);

        if($rollback) {
            $missingMigrations = array_intersect($allMigrations, $ranMigrations);
        } else {
            $missingMigrations = array_diff($allMigrations, $ranMigrations);
        }

        return array_values($missingMigrations);
    }

}