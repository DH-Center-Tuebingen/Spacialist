<?php

namespace App\Models\Plugin;

use App\Models\Plugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * For normal migrations Laravel is using the migrations table to track which migrations have been run.
 * However, since plugins can be installed and uninstalled at any time, we need a separate table to track
 * the migrations for each plugin.
 * 
 * This is necesarry to ensure when a plugin is updated, what migrations have already been run and what
 * still need to be run.
 */
class Migration extends Model
{

    protected $table = 'plugin_migrations';

    protected $fillable = [
        'plugin_id',
        'migration',
        'batch'
    ];
    
    static function run(Plugin $plugin){
        self::exec($plugin);
    }

    static function rollback(Plugin $plugin){
        self::exec($plugin, true);
    }

    static function exec(Plugin $plugin, $rollback = false){
        //Determine the next batch number
        $nextBatch = self::max('batch') + 1;
        $migrations = self::getMissingMigrations($plugin, $rollback);
        foreach($migrations as $migration) {
            $migrationInstance = self::getMigrationClassName($plugin->name, $migration);
            try{
                call_user_func([$migrationInstance, $rollback ? 'rollback' : 'migrate']);
                if($rollback) {
                    //Remove the record of the migration
                    self::where('plugin_id', $plugin->id)
                        ->where('migration', $migration)
                        ->delete();
                } else {
                    //Record the migration as run
                    self::create([
                        'plugin_id' => $plugin->id,
                        'migration' => $migration,
                        'batch' => $nextBatch
                    ]);
                }
            } catch(\Exception $e) {
                info("Failed to run migration for plugin {$plugin->name}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    static function getMigrationClassName($pluginName, $migrationFile): object {
        preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $migrationFile, $matches);
        if(count($matches) != 3) {
            throw new \Exception("Invalid migration file name: $migrationFile");
        }
        $className = Str::studly($matches[2]);
        require($plugin->getPath("Migration/$migrationFile"));
        $prefixedClassName = "App\\Plugins\\$pluginName\\Migration\\$className";
        return new $prefixedClassName();
    }

    static function getMigrationPath(Plugin $plugin): string {
        return $plugin->getPath("Migration");
    }

    static function getMigrationState(Plugin $plugin): array {
        $ranMigrations = self::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $allMigrations = self::getMigrationList($plugin);

        $migrations = [];
        foreach($allMigrations as $migration) {
            $migrations[] = [
                'name' => $migration,
                'ran' => in_array($migration, $ranMigrations)
            ];
        }

        return $migrations;
    }

    static function getMissingMigrations(Plugin $plugin, $rollback = false): array {
        $ranMigrations = self::where('plugin_id', $plugin->id)
            ->pluck('migration')
            ->toArray();

        $allMigrations = self::getMigrationList($plugin);

        if($rollback){
            $missingMigrations = array_intersect($allMigrations, $ranMigrations);
        }else{
            $missingMigrations = array_diff($allMigrations, $ranMigrations);
        }

        return array_values($missingMigrations);
    }

    static function getMigrationList(Plugin $plugin, $rollback = false){
        $path = self::getMigrationPath($plugin);
        if(file_exists($path) && is_dir($path)) {
            $migrations = collect(File::files($path))->map(function($f) {
                return $f->getFilename();
            });

            $migrations = $migrations->filter(function($f) {
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
}
