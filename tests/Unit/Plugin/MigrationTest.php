<?php
namespace Tests\Unit\Plugin;

use App\Plugin;
use App\Services\Plugin\MigrationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Tests\Support\Templates\MigrationTemplate;
use Tests\Support\PluginGenerator;
use Tests\TestCase;

class MigrationTest extends TestCase {

    private const PLUGIN_NAME = 'MigUnitPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000001';

    private ?PluginGenerator $generator = null;

    protected function setUp(): void {
        parent::setUp();
        // Reset the ID sequence so plugin IDs are deterministic.
        DB::statement("ALTER SEQUENCE IF EXISTS plugins_id_seq RESTART");
    }

    protected function tearDown(): void {
        $this->ensureTeardown();
        parent::tearDown();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function ensureTeardown(): void {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }
    }

    /**
     * Generate the plugin directory on disk, save the plugin record to the DB,
     * and return the plugin's database ID for use in API URLs.
     */
    private function setupPlugin(MigrationTemplate $template, bool $deprecatedManifest = false): Plugin {
        $template->addBasic();
        if($deprecatedManifest) {
            $template->setLegacyManifest();
        }
        $this->generator = new PluginGenerator([$template->generate()]);
        $this->generator->setUp();
        return $this->generator->getPlugin($template->plugin->name);
    }

    public function testLegacyMigrationPath(): void {
        $this->assertStringEndsWith(
            'Plugin/Migration',
            MigrationService::getLegacyMigrationPath("Plugin")
        );
    }

    public function testLegacyMigrationPathWithFile(): void {
        $this->assertStringEndsWith(
            'Plugin/Migration/000_Migration',
            MigrationService::getLegacyMigrationPath("Plugin", '000_Migration')
        );
    }

    public function testGetManifestMigration(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )->addXml('migrations', null, [
                    ['src' => 'CustomMigration'],
                ]);

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);
        $path = $migrationService->getManifestMigration($plugin);
        $this->assertNotNull($path);
        $this->assertEquals('CustomMigration', $path);
    }
    
    public function testGetLegacyManifestMigration(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )->addXml('migrations', null, [
                    ['src' => 'CustomMigration'],
                ]);

        $plugin = $this->setupPlugin($template, true);
        $migrationService = app(MigrationService::class);
        $path = $migrationService->getManifestMigration($plugin);
        $this->assertNotNull($path);
        $this->assertEquals('CustomMigration', $path);
    }

    public function testGetManifestMigrationWithMultipleDefintions(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )->addXml('migrations', null, [
                    ['src' => 'Migrations_a'],
                    ['src' => 'Migrations_b']
                ]);

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Plugin {$plugin->name} has multiple <migrations> tags in its manifest, but only one is allowed.");
        $migrationService->getManifestMigration($plugin);
    }

    public function testGetMigrationList(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )
            ->addDefaultMigrations();

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);
        $migrationDirectory = $migrationService->getAbsoluteMigrationDirectory($plugin);
        $migrations = $migrationService->getMigrationList($migrationDirectory);

        $this->assertCount(2, $migrations);
        $this->assertEquals('2024_01_01_000000_create_first_table.php', $migrations[0]);
        $this->assertEquals('2024_01_02_000000_create_second_table.php', $migrations[1]);
    }

    public function testGetMigrationListForRollback(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )
            ->addDefaultMigrations();

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);
        $migrationDirectory = $migrationService->getAbsoluteMigrationDirectory($plugin);
        $migrations = $migrationService->getMigrationList($migrationDirectory, true);

        $this->assertCount(2, $migrations);
        $this->assertEquals('2024_01_02_000000_create_second_table.php', $migrations[0]);
        $this->assertEquals('2024_01_01_000000_create_first_table.php', $migrations[1]);
    }
    
    public function testValidMigrationClassName(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )
            ->addDefaultMigrations();

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);
        
        $absoluteDirectory = base_path(config('app.plugin_directory') . '/' . $plugin->name . '/Migration');
        $classInstance = $migrationService->getMigrationClassName($plugin, $absoluteDirectory, '2024_01_01_000000_create_first_table.php');
        $this->assertEquals('CreateFirstTable', class_basename(get_class($classInstance)));
    }

    public function testInvalidMigrationClassName(): void {
        $template = MigrationTemplate::createFrom(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID
        )
            ->addDefaultMigrations();

        $plugin = $this->setupPlugin($template);
        $migrationService = app(MigrationService::class);;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid migration file name: invalid_migration_name.php");

        
        $migrationService->getMigrationClassName($plugin, "Doesn't matter", 'invalid_migration_name.php');
    }


}