<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Plugin;
use App\Entity;
use App\Plugin\PluginDirectory;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\Support\Templates\ScopeTemplate;
use Tests\Support\PluginDirectoryGenerator;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;

class ApiPluginTest extends TestCase {

    private PluginGenerator $generator;
    private ?string $tmpdir = null;


    private static function getFooPlugin(): array {
        return [
            'id' => 1,
            'name' => 'FooPlugin',
            'version' => '1.0.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'update_available' => '2.2.0',
            'installed_at' => '2020-02-20T22:44:12.000000Z',
            'created_at' => '2020-01-10T11:22:34.000000Z',
            'updated_at' => '2020-03-30T03:33:45.000000Z'
        ];
    }

    private static function getBarPlugin(): array {
        return [
            'id' => 2,
            'name' => 'BarPlugin',
            'version' => '2.1.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174002',
            'update_available' => null,
            'installed_at' => null,
            'created_at' => '2020-04-14T04:40:04.000000Z',
            'updated_at' => '2020-06-16T06:36:27.000000Z',
        ];
    }

    private function removeTmpDir() {

        if(!$this->tmpdir || !is_dir($this->tmpdir)) {
            return;
        }

        if(!str_starts_with($this->tmpdir, sys_get_temp_dir())) {
            // Safety check to prevent accidental deletion of important files
            throw new \Exception("Attempting to remove a directory outside of the system temp directory. Aborting for safety. Directory: " . $this->tmpdir);
        }

        $it = new RecursiveDirectoryIterator($this->tmpdir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->tmpdir);
        $this->tmpdir = null;
    }

    protected function setUp(): void {
        parent::setUp();
        
        // The refresh database trait seems not to reset the id sequence
        // therefore we do it manually here to ensure the ids of the test plugins are always the same.
        DB::statement("ALTER SEQUENCE IF EXISTS plugins_id_seq RESTART");
        $fooTemplate = PluginTemplate::fromSlugArray(static::getFooPlugin())->addBasic()->setChangelog("Foo Plugin Changelog")->generate();
        $barTemplate = PluginTemplate::fromSlugArray(static::getBarPlugin())->addBasic()->skipInstall()->generate();

        $this->generator = new PluginGenerator([
            $fooTemplate,
            $barTemplate,
        ]);
        $this->generator->setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->generator->tearDown();
        $this->removeTmpDir();
    }

    private function getTestPlugins(): array {
        return [
            $this->getFooPlugin(),
            $this->getBarPlugin(),
        ];
    }

    #[TestDox('GET           /v1/plugin : Get Plugins')]
    public function testGetPlugins(): void {
        $response = $this->userRequest()
            ->get('/api/v1/plugin');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJson($this->getTestPlugins());
    }

    #[TestDox('GET           /v1/plugin/{id}/changelog : Get Plugin Changelog')]
    public function testGetChangelog(): void {
        $response = $this->userRequest()
            ->get('/api/v1/plugin/1/changelog');
        $response->assertStatus(200);
        $response->assertSee('Foo Plugin Changelog');
    }

    #[TestDox('GET           /v1/plugin/<id> : Install Plugin')]
    public function testInstallPlugin(): void {
        Carbon::setTestNow('2020-05-15 05:25:06');

        $this->useUserWithPermissions(['plugin_write']);

        $response = $this->userRequest()
            ->get('/api/v1/plugin/2');

        $response->assertStatus(200);

        $installedBarPlugin = $this->getBarPlugin();
        $installedBarPlugin['installed_at'] = '2020-05-15T05:25:06.000000Z';
        $installedBarPlugin['updated_at'] = '2020-05-15T05:25:06.000000Z';
        $response->assertJson([
            'plugin' => $installedBarPlugin,
            'scripts' => ['api/download/plugin/barplugin-123e4567-e89b-12d3-a456-426614174002.js'],
        ]);
        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('GET [403]     /v1/plugin/<id> : Install Plugin Fails Without Permission - "plugin_write"')]
    public function testInstallPluginFailsWithoutPermission(): void {
        Carbon::setTestNow('2020-05-15 05:25:06');

        $this->useUserWithPermissions(['plugin_read', 'plugin_delete', 'plugin_share']);
        $response = $this->userRequest()
            ->get('/api/v1/plugin/2');

        $response->assertStatus(403);
        // Reset time after test
        Carbon::setTestNow();
    }

    /**
     * Helper to setup the pre upload state for the upload test
     * 1) Mocks backup plugin
     * 2) Generates the upload file
     */
    private function uploadPluginSetup() {
        $updatedFooPlugin = $this->getFooPlugin();
        $updatedFooPlugin['version'] = '2.2.0';
        $updatedFooPlugin['update_available'] = null;
        $template = PluginTemplate::fromSlugArray($updatedFooPlugin)
            ->addBasic()
            ->setChangelog('updated')
            ->generate();

        $tmpdir = sys_get_temp_dir() . '/' . uniqid('plugin_test_', true);
        if(mkdir($tmpdir, 0755, true)) {
            $this->removeTmpDir();
            $this->tmpdir = $tmpdir;
        } else {
            $this->fail("Failed to create temporary directory for plugin zip file.");
        }

        $zipPath = $tmpdir . "/foo-plugin-v-2_2_0.zip";
        PluginDirectoryGenerator::mockPluginZipFile($template, $zipPath);

        return new UploadedFile($zipPath, 'foo-plugin-v-2_2_0.zip', 'application/zip', null, true);
    }

    #[TestDox('POST          /v1/plugin : Upload Plugin')]
    public function testUploadPlugin(): void {
        Carbon::setTestNow('2020-07-20 10:15:30');

        $this->useUserWithPermissions('plugin_create');

        $plugin = Plugin::where('name', 'FooPlugin')->first();
        $this->assertNotNull($plugin);
        $this->assertEquals('1.0.0', $plugin->version);

        $backupDirectory = PluginDirectory::getPath('_backups/' . $plugin->name);
        $backupFooPlugin = PluginTemplate::fromSlugArray($this->getFooPlugin())
            ->addBasic()
            ->setChangelog('backup')
            ->generate();

        PluginDirectoryGenerator::mockPluginDirectory($backupFooPlugin, $backupDirectory);

        $activePluginDirectory = PluginDirectory::getPath($plugin->name);

        $this->assertEquals('backup', file_get_contents($backupDirectory . '/CHANGELOG.md'));
        $this->assertEquals('Foo Plugin Changelog', file_get_contents($activePluginDirectory . '/CHANGELOG.md'));

        $file = $this->uploadPluginSetup();

        $response = $this->userRequest()
            ->post('/api/v1/plugin', [
                "file" => $file,
            ]);

        $response->assertStatus(200);

        $updatedFooPlugin['updated_at'] = '2020-07-20T10:15:30.000000Z';
        $response->assertJson($updatedFooPlugin);

        $this->assertEquals('Foo Plugin Changelog', file_get_contents($backupDirectory . '/CHANGELOG.md'));
        $this->assertEquals('updated', file_get_contents($activePluginDirectory . '/CHANGELOG.md'));

        // Reset time after test
        Carbon::setTestNow();
    }


    #[TestDox('POST [403]    /v1/plugin : Plugin upload fails without permission - "plugin_write"')]
    public function testUploadPluginFailsWithoutPermission(): void {
        Carbon::setTestNow('2020-07-20 10:15:30');
        $this->useUserWithoutPermission('plugin', 'c');

        $file = $this->uploadPluginSetup();

        $response = $this->userRequest()
            ->post('/api/v1/plugin', [
                "file" => $file,
            ]);

        $response->assertStatus(403);
    }

    #[TestDox('DELETE        /v1/plugin/<id> : Uninstall Plugin')]
    public function testUninstallPlugin(): void {
        Carbon::setTestNow('2020-07-20 10:15:30');

        $this->useUserWithPermissions('plugin_write');

        $response = $this->userRequest()
            ->delete('/api/v1/plugin/1');

        $response->assertStatus(200);

        $uninstalledFooPlugin = $this->getFooPlugin();
        $uninstalledFooPlugin['installed_at'] = null;
        $uninstalledFooPlugin['updated_at'] = '2020-07-20T10:15:30.000000Z';
        $response->assertJson([
            'plugin' => $uninstalledFooPlugin,
            'scripts' => ['api/download/plugin/fooplugin-123e4567-e89b-12d3-a456-426614174000.js'],
        ]);

        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('DELETE [403]  /v1/plugin/<id> : Uninstall Plugin Fails Without Permission - "plugin_write"')]
    public function testUninstallFailsWithoutPermission(): void {
        $this->useUserWithoutPermission('plugin', 'w');

        Carbon::setTestNow('2020-07-20 10:15:30');
        $response = $this->userRequest()
            ->delete('/api/v1/plugin/1');

        $response->assertStatus(403);

        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('DELETE        /v1/plugin/remove/<id> : Remove Plugin')]
    public function testRemovePlugin(): void {

        $this->useUserWithPermissions('plugin_delete');

        Carbon::setTestNow('2020-07-20 10:15:30');

        $pluginFirectory = config('app.plugin_directory');
        
        $directoryExists = file_exists("$pluginFirectory/BarPlugin");
        $this->assertTrue($directoryExists);

        $response = $this->userRequest()
            ->delete("/api/v1/plugin/remove/2");

        $response->assertStatus(200);
        $response->assertJson([
            'scripts' => ['api/download/plugin/barplugin-123e4567-e89b-12d3-a456-426614174002.js'],
        ]);
        $this->assertDatabaseMissing('plugins', [
            'name' => 'BarPlugin',
        ]);

        //File is missing
        $directoryWasRemoved = file_exists("$pluginFirectory/BarPlugin");
        $this->assertFalse($directoryWasRemoved);

        //Other plugins are still there
        $this->assertDatabaseHas('plugins', [
            'name' => 'FooPlugin',
        ]);

        $fooPluginDirectoryExists = file_exists("$pluginFirectory/FooPlugin");
        $this->assertTrue($fooPluginDirectoryExists);

        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('DELETE [403]  /v1/plugin/remove/<id> : Removing Plugin Fails Without Permission - "plugin_delete"')]
    public function testRemoveInstalledPluginFails() {
        $this->useUserWithoutPermission('plugin', 'd');

        Carbon::setTestNow('2020-07-20 10:15:30');

        $response = $this->userRequest()
            ->delete("/api/v1/plugin/remove/1");

        $response->assertStatus(403);
    }


    #[TestDox('GET           /v1/plugin/migrate/<id>/check : Get Migration State')]
    public function testGetPluginMigrationState() {
        $response = $this->userRequest()
            ->get('/api/v1/plugin/migrate/1/check');

        $response->assertStatus(200);
    }
}