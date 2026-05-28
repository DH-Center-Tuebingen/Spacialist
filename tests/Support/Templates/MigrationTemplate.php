<?php

namespace Tests\Support\Templates;

use Tests\Support\PluginTemplate;

/**
 * A plugin template that sets up a plugin with migration files in its Migration/ directory.
 * Plugins are uninstalled by default to prevent PluginManager::install from running the
 * MigrationService and potentially failing due to a missing or empty migration directory.
 *
 * Each migration class is placed in the namespace:
 *   App\Plugins\{PluginName}\Migration
 *
 * Important: PHP's require (used by MigrationService) can only declare a class once per
 * process. Use a unique plugin name per test method that actually executes migrations to
 * avoid "Cannot declare class … already in use" fatal errors across tests.
 */
class MigrationTemplate extends PluginTemplate {
    
    protected $migrationDirectory = 'Migration';

    public function __construct(
        string $name = 'MigrationPlugin',
        string $uuid = '123e4567-e89b-12d3-a456-426614174010',
        string $version = '1.0.0',
    ) {
        parent::__construct($name, $uuid, $version);
        // Keep uninstalled so PluginManager::install is not called during setUp().
        // The plugin record is still saved to the database by PluginGenerator::setUp().
        $this->skipInstall();
    }

    public static function createFrom(
        string $name,
        string $uuid,
        string $version = '1.0.0',
    ): static {
        return new static($name, $uuid, $version);
    }

    // -----------------------------------------------------------------------
    // Migration file helpers
    // -----------------------------------------------------------------------

    /**
     * Build the PHP source for a migration class.
     * Uses the plugin name to produce the correct namespace so that
     * MigrationService::getMigrationClassName() can resolve it.
     *
     * The migrate() / rollback() methods are intentionally no-ops: the tests
     * focus on migration TRACKING (plugin_service_migrations table) rather
     * than on DDL execution.
     */
    protected function getMigrationContent(string $className): string {
        $pluginName = $this->plugin->name;
        // Table name is unique per migration file so individual migrations can be
        // asserted independently. E.g. Schema::hasTable('mig_migrunplugin_createfirsttable').
        $tableName = 'test-pm-' . strtolower($className);
        return <<<PHP
<?php

namespace App\Plugins\\{$pluginName}\\{$this->migrationDirectory};

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class {$className} {
    public function migrate(): void {
        if(!Schema::hasTable('{$tableName}')) {
            Schema::create('{$tableName}', function (Blueprint \$table) {
                \$table->id();
                \$table->timestamps();
            });
        }
    }
    public function rollback(): void {
        Schema::dropIfExists('{$tableName}');
    }
}
PHP;
    }

    /**
     * Add a single migration file inside the plugin's Migration/ directory.
     *
     * @param string $filename  Full filename, e.g. "2024_01_01_000000_create_items_table.php"
     * @param string $className Studly-case class name, e.g. "CreateItemsTable"
     */
    public function addMigrationFile(string $filename, string $className): static {
        $this->addFile("{$this->migrationDirectory}/{$filename}", $this->getMigrationContent($className));
        return $this;
    }

    /**
     * Add two migration files dated sequentially so that ordering tests can
     * verify that migrations are processed in ascending file-name order.
     */
    public function addDefaultMigrations(): static {
        return $this
            ->addMigrationFile('2024_01_01_000000_create_first_table.php', 'CreateFirstTable')
            ->addMigrationFile('2024_01_02_000000_create_second_table.php', 'CreateSecondTable');
    }

    /**
     * Add a single migration file for simpler, single-migration test scenarios.
     */
    public function addSingleMigration(): static {
        return $this->addMigrationFile(
            '2024_06_01_000000_create_plugin_table.php',
            'CreatePluginTable'
        );
    }
    
    public function setMigrationPathXml(string $src): static {
        $this->migrationDirectory = $src;
        $this->addXml('migrations', null, [['src' => $src]]);
        return $this;
    }
}
