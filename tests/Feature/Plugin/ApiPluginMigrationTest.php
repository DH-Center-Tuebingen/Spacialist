<?php

namespace Tests\Feature\Plugin;

use App\Models\Plugin\Migration as PluginMigration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\Templates\MigrationTemplate;
use Tests\Support\PluginGenerator;
use Tests\TestCase;

/**
 * Feature tests for the plugin migration API.
 *
 * Routes under test:
 *   GET  /api/v1/plugin/migrate/{plugin}/check          – getMigrationState  (auth only)
 *   POST /api/v1/plugin/migrate/{plugin}                – migrate             (plugin_write)
 *   POST /api/v1/plugin/migrate/{plugin}/rollback       – rollback            (plugin_write)
 *   POST /api/v1/plugin/migrate/{plugin}/force_add      – addMigrationToDatabase (plugin_write)
 */
class ApiPluginMigrationTest extends TestCase {

    private const PLUGIN_NAME = 'MigPlugin';
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
    
    private function createDefaultTemplate(){
        return $this->modifyDefaultTemplate(MigrationTemplate::createFrom(self::PLUGIN_NAME, self::PLUGIN_UUID));
    }
    
    protected function modifyDefaultTemplate(MigrationTemplate $template): MigrationTemplate {
        return $template->setMigrationPathXml('CustomMigrationsDirectory');
    } 

    /**
     * Generate the plugin directory on disk, save the plugin record to the DB,
     * and return the plugin's database ID for use in API URLs.
     */
    private function setupPlugin(MigrationTemplate $template): int {
        $this->generator = new PluginGenerator([$template->addBasic()->generate()]);
        $this->generator->setUp();
        return $this->generator->getPlugin($template->plugin->name)->id;
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/plugin/migrate/{plugin}/check
    // -----------------------------------------------------------------------
    public function testGetMigrationStateForPluginWithNoMigrations(): void {
        $template = $this->createDefaultTemplate();
        $pluginId = $this->setupPlugin($template);
        $response = $this->userRequest()->get("/api/v1/plugin/migrate/{$pluginId}/check");

        $this->assertStatus($response, 200);
        $response->assertExactJson([]);
    }

    public function testGetMigrationStateShowsPendingMigrations(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);
        $response = $this->userRequest()->get("/api/v1/plugin/migrate/{$pluginId}/check");

        $this->assertStatus($response, 200);
        $response->assertJsonCount(2);
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => false],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => false],
        ]);

        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertFalse(Schema::hasTable('test-pm-createsecondtable'));
    }

    public function testGetMigrationStateShowsAlreadyRanMigrations(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();

        $pluginId = $this->setupPlugin($template);

        // Simulate the first migration having already been run.
        PluginMigration::create([
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
            'batch' => 1,
        ]);

        $response = $this->userRequest()->get("/api/v1/plugin/migrate/{$pluginId}/check");

        $this->assertStatus($response, 200);
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => true],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => false],
        ]);
        
        // The first mmigration did not run, it was just marked as ran in the DB
        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertFalse(Schema::hasTable('test-pm-createsecondtable'));
    }

    public function testGetMigrationStateReturnsInAscendingFilenameOrder(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);
        $response = $this->userRequest()->get("/api/v1/plugin/migrate/{$pluginId}/check");

        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertCount(2, $data);
        // First entry must be the chronologically earlier file.
        $this->assertEquals('2024_01_01_000000_create_first_table.php', $data[0]['name']);
        $this->assertEquals('2024_01_02_000000_create_second_table.php', $data[1]['name']);
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/plugin/migrate/{plugin}
    // -----------------------------------------------------------------------

    public function testRunMigrationsMarksThemAsRan(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);
        
        $this->assertDatabaseMissing('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
        ]);
        $this->assertDatabaseMissing('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_02_000000_create_second_table.php',
        ]);
        
        $this->useUserWithPermissions(['plugin_write']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}");

        $this->assertStatus($response, 200);
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => true],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => true],
        ]);
        
        $this->assertDatabaseHas('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
        ]);
        $this->assertDatabaseHas('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_02_000000_create_second_table.php',
        ]);
        // Verify the migration code actually ran (not just tracked).
        $this->assertTrue(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertTrue(Schema::hasTable('test-pm-createsecondtable'));
    }

    public function testRunMigrationsOnlyRunsPendingOnes(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        // Mark the first migration as already run directly in the DB.
        PluginMigration::create([
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
            'batch' => 1,
        ]);

        $this->useUserWithPermissions(['plugin_write']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}");

        $this->assertStatus($response, 200);
        // Both migrations appear in the state; only the second was newly run.
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => true],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => true],
        ]);
        // Exactly two records exist – one pre-existing, one just created.
        $count = DB::table('plugin_service_migrations')->where('plugin_id', $pluginId)->count();
        $this->assertEquals(2, $count);

        // First migration was skipped (table absent); only the second actually ran.
        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertTrue(Schema::hasTable('test-pm-createsecondtable'));
    }

    public function testRunMigrationsRequiresPluginWritePermission(): void {
        $template = $this->createDefaultTemplate();
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_read']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}");

        $this->assertStatus($response, 403);

        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertFalse(Schema::hasTable('test-pm-createsecondtable'));
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/plugin/migrate/{plugin}/rollback
    // -----------------------------------------------------------------------

    public function testRollbackMigrationsMarksThemAsPending(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations()
            ->install();
            
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_write']);
        // First run all migrations so tables are created and records are tracked.
        $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}");
        $this->assertTrue(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertTrue(Schema::hasTable('test-pm-createsecondtable'));

        // Now roll back and verify both tables are dropped and records removed.
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/rollback");

        $this->assertStatus($response, 200);
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => false],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => false],
        ]);
        $this->assertDatabaseMissing('plugin_service_migrations', ['plugin_id' => $pluginId]);
        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertFalse(Schema::hasTable('test-pm-createsecondtable'));
    }

    public function testRollbackRequiresPluginWritePermission(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations()
            ->install();
        $pluginId = $this->setupPlugin($template);

        $this->assertTrue(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertTrue(Schema::hasTable('test-pm-createsecondtable'));

        $this->useUserWithPermissions(['plugin_read']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/rollback");

        $this->assertStatus($response, 403);

        $this->assertTrue(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertTrue(Schema::hasTable('test-pm-createsecondtable'));
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/plugin/migrate/{plugin}/force_add
    // -----------------------------------------------------------------------

    /**
     * force_add inserts a DB record for the named migration WITHOUT executing
     * the PHP migrate() method.
     */
    public function testForceAddRecordsMigrationWithoutRunningIt(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_write']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/force_add", [
            'name' => '2024_01_01_000000_create_first_table.php',
        ]);

        $this->assertStatus($response, 200);
        $response->assertJson([
            ['name' => '2024_01_01_000000_create_first_table.php', 'ran' => true],
            ['name' => '2024_01_02_000000_create_second_table.php', 'ran' => false],
        ]);
        $this->assertDatabaseHas('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
        ]);
        // Second migration must NOT have been touched.
        $this->assertDatabaseMissing('plugin_service_migrations', [
            'plugin_id' => $pluginId,
            'migration' => '2024_01_02_000000_create_second_table.php',
        ]);
        // force_add must NOT execute the migration code — table must be absent.
        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
    }

    public function testForceAddRejectsUnknownMigrationName(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_write']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/force_add", [
            'name' => '9999_99_99_000000_nonexistent_migration.php',
        ]);

        $this->assertStatus($response, 400);
        $response->assertJsonStructure(['error']);
    }

    public function testForceAddRejectsAlreadyRanMigration(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        // Mark the first migration as already tracked.
        PluginMigration::create([
            'plugin_id' => $pluginId,
            'migration' => '2024_01_01_000000_create_first_table.php',
            'batch' => 1,
        ]);

        $this->useUserWithPermissions(['plugin_write']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/force_add", [
            'name' => '2024_01_01_000000_create_first_table.php',
        ]);

        $this->assertStatus($response, 400);
        $response->assertJsonStructure(['error']);
    }

    public function testForceAddRequiresPluginWritePermission(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_read']);
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/force_add", [
            'name' => '2024_01_01_000000_create_first_table.php',
        ]);

        $this->assertStatus($response, 403);

        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
    }

    public function testForceAddRequiresNameParameter(): void {
        $template = $this->createDefaultTemplate()
            ->addDefaultMigrations();
        $pluginId = $this->setupPlugin($template);

        $this->useUserWithPermissions(['plugin_write']);
        // Send request without the required 'name' field.
        $response = $this->userRequest()->post("/api/v1/plugin/migrate/{$pluginId}/force_add", []);

        // Laravel validation returns 422 for missing required fields.
        $this->assertStatus($response, 422);

        $this->assertFalse(Schema::hasTable('test-pm-createfirsttable'));
        $this->assertFalse(Schema::hasTable('test-pm-createsecondtable'));
    }
}
