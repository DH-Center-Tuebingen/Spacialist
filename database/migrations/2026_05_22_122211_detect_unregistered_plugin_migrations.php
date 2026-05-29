<?php

use Illuminate\Database\Migrations\Migration;


/**
 * This migration is required after implementing the PluginMigration system.
 * The app may have already installed plugins, that already ran it's migrations.
 * Therefore we iterate over the legacy migration file of every plugin.
 * When the plugin is installed and has missing migrations, they will be set
 * inside the migration table.
 */
class DetectUnregisteredPluginMigrations extends Migration {

    private function scanPluginsForExistingMigrationFile(callable $callback) {
        // Read all plugins from plugin directory
        $pluginDirectory = realpath("app/Plugins");

        if($pluginDirectory && is_dir($pluginDirectory)) {
            foreach(scandir($pluginDirectory) as $pluginDirName) {
                if($pluginDirName === '.' || $pluginDirName === '..')
                    continue;
                if(is_dir($pluginDirectory . DIRECTORY_SEPARATOR . $pluginDirName)) {
                    $migrationPath = $pluginDirectory . DIRECTORY_SEPARATOR . $pluginDirName . DIRECTORY_SEPARATOR . 'Migration';
                    if(is_dir($migrationPath)) {
                        $callback($migrationPath, $pluginDirName);
                    }
                }
            }
        }
    }

    public function scanMigrationsInPluginDirectory(string $migrationPath, callable $callback) {
        $migrationFiles = scandir($migrationPath);
        foreach($migrationFiles as $migrationFile) {
            if($migrationFile === '.' || $migrationFile === '..')
                continue;

            $callback($migrationFile);
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void {
        $this->scanPluginsForExistingMigrationFile(function (string $migrationPath, string $pluginName) {
            $this->scanMigrationsInPluginDirectory($migrationPath, function ($migrationFile) use ($migrationPath, $pluginName) {
            
                $plugin = DB::table('plugins')->where('name', $pluginName)->first();
                $migrationExists = DB::table('plugin_service_migrations')->where('migration', $migrationFile)->exists();
                
                if($plugin && $plugin->installed_at && !$migrationExists) {
                    DB::table('plugin_service_migrations')->insert([
                        'plugin_id' => $plugin->id,
                        'migration' => $migrationFile,
                        'batch' => 0,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // We don't delete the migration records. 
    }
}

return new DetectUnregisteredPluginMigrations();
