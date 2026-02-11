<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Plugin;
use App\Entity;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestDox;


class ApiPluginTest extends TestCase {

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

    private static function getScopePlugin(): array {
        return [
            'id' => 3,
            'name' => 'ScopePlugin',
            'version' => '3.2.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174004',
            'update_available' => null,
            'installed_at' => null,
            'created_at' => '2020-08-01T08:00:00.000000Z',
            'updated_at' => '2020-08-01T08:00:00.000000Z',
        ];
    }

    private static function getUnregisteredPlugin(): array {
        return [
            'id' => 3,
            'name' => 'UnregisteredPlugin',
            'version' => '1.0.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174003',
            'update_available' => null,
            'installed_at' => null,
            'created_at' => '2020-08-01T08:00:00.000000Z',
            'updated_at' => '2020-08-01T08:00:00.000000Z',
        ];
    }


    private function getTestPlugins(): array {
        return [
            $this->getFooPlugin(),
            $this->getBarPlugin()
        ];
    }

    private function mockPluginDirectory($plugin): void {
        $required_fields = ['name', 'uuid', 'version'];

        $missing_fields = [];
        foreach($required_fields as $field) {
            if(!isset($plugin[$field]) || empty($plugin[$field])) {
                $missing_fields[] = $field;
            }
        }

        if(count($missing_fields) > 0) {
            throw new \Exception("Plugin is missing required fields: " . implode(", ", $missing_fields));
        }

        $structure = [
            "package.json" => $this->generatePackageJSON($plugin),
            "CHANGELOG.md" => "# Changelog",
            "js" => [
                strtolower($plugin['name']) . "-" . $plugin['uuid'] . ".js" => "// Plugin main JS file"
            ],
            "App" => [
                "info.xml" => $this->generateInfoXml($plugin),
            ]
        ];

        // Create plugin directory structure
        $pluginDir = base_path(config('app.plugin_directory')) . '/' . $plugin['name'];
        if(!file_exists($pluginDir)) {
            mkdir($pluginDir, 0755, true);
        }

        // Process structure array
        foreach($structure as $name => $content) {
            $path = $pluginDir . '/' . $name;

            if(is_array($content)) {
                // Create directory and process contents
                if(!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                foreach($content as $subName => $subContent) {
                    file_put_contents($path . '/' . $subName, $subContent);
                }
            } else {
                // Create file with content
                file_put_contents($path, $content);
            }
        }
    }

    private function generatePackageJSON($plugin): string {
        return <<<JSON
{
    "name": "package_{$plugin['name']}",
    "pluginName": "{$plugin['name']}",
    "version": "{$plugin['version']}",
    "private": true,
    "type": "module"
}
JSON;
    }

    private function generateInfoXml($plugin): string {
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence = isset($plugin['licence']) ? $plugin['licence'] : "";
        $authors = "";
        if(!isset($plugin["authors"])) {
            $plugin["authors"] = [];
        }

        foreach($plugin["authors"] as $author) {
            $authors .= "        <author>{$author}</author>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<info>
    <!-- Must match id in JS SpPS.register call in kebab-case -->
    <name>{$plugin['name']}</name>
    <title>{$plugin['name']} Plugin</title>
    <description>{$description}</description>
    <version>{$plugin['version']}</version>
    <licence>{$licence}</licence>
    <authors>{$authors}</authors>
</info>
XML;
    }

	#[TestDox('GET    /v1/plugin : Get Plugins')]
    public function testGetPlugins(): void {
        $response = $this->userRequest()
            ->get('/api/v1/plugin');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson($this->getTestPlugins());
    }

    #[TestDox('GET    /v1/plugin/<id> : Install Plugin')]
    public function testInstallPlugin(): void {
        Carbon::setTestNow('2020-05-15 05:25:06');
        $response = $this->userRequest()
            ->get('/api/v1/plugin/2');

        $response->assertStatus(200);

        $installedBarPlugin = $this->getBarPlugin();
        $installedBarPlugin['installed_at'] = '2020-05-15T05:25:06.000000Z';
        $installedBarPlugin['updated_at'] = '2020-05-15T05:25:06.000000Z';
        $response->assertJson([
            'plugin' => $installedBarPlugin,
            'install_location' => 'barplugin-123e4567-e89b-12d3-a456-426614174002.js',
        ]);
        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('PATCH    /v1/plugin/<id> : Update Plugin')]
    public function testUpdatePlugin(): void {
        Carbon::setTestNow('2020-07-20 10:15:30');
        $response = $this->userRequest()
            ->patch('/api/v1/plugin/1');

        $response->assertStatus(200);

        $updatedFooPlugin = $this->getFooPlugin();
        $updatedFooPlugin['version'] = '2.2.0';
        $updatedFooPlugin['update_available'] = null;
        $updatedFooPlugin['updated_at'] = '2020-07-20T10:15:30.000000Z';
        $updatedFooPlugin['changelog'] = 'New version 2.2.0';
        $response->assertJson($updatedFooPlugin);
        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('DELETE    /v1/plugin/<id> : Uninstall Plugin')]
    public function testUninstallPlugin(): void {
        Carbon::setTestNow('2020-07-20 10:15:30');
        $response = $this->userRequest()
            ->delete('/api/v1/plugin/1');

        $response->assertStatus(200);

        $uninstalledFooPlugin = $this->getFooPlugin();
        $uninstalledFooPlugin['installed_at'] = null;
        $uninstalledFooPlugin['updated_at'] = '2020-07-20T10:15:30.000000Z';
        $response->assertJson([
            'plugin' => $uninstalledFooPlugin,
            'uninstall_location' => 'fooplugin-123e4567-e89b-12d3-a456-426614174000.js',
        ]);

        // Reset time after test
        Carbon::setTestNow();
    }

    #[TestDox('DELETE    /v1/plugin/remove/<id> : Remove Plugin')]
    public function testRemovePlugin(): void {

        Carbon::setTestNow('2020-07-20 10:15:30');

        // Create a plugin entry in the database for the unregistered plugin
        $plugin = Plugin::forceCreate([
            'name' => 'UnregisteredPlugin',
            'version' => '1.0.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174003',
            'installed_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-07-20 10:15:30', 'UTC'),
        ]);

        // We need to create a mock plugin directory for the plugin to be removed
        $this->mockPluginDirectory($this->getUnregisteredPlugin());

        $directoryWasCreated = file_exists('tests/assets/Plugins/UnregisteredPlugin');
        $this->assertTrue($directoryWasCreated);

        $response = $this->userRequest()
            ->delete("/api/v1/plugin/remove/{$plugin->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'uninstall_location' => 'unregisteredplugin-123e4567-e89b-12d3-a456-426614174003.js',
        ]);
        $this->assertDatabaseMissing('plugins', [
            'name' => 'UnregisteredPlugin',
        ]);

        //File is missing
        $directoryWasRemoved = file_exists('tests/assets/Plugins/UnregisteredPlugin');
        $this->assertFalse($directoryWasRemoved);

        //Other plugins are still there
        $this->assertDatabaseHas('plugins', [
            'name' => 'FooPlugin',
        ]);
        $this->assertDatabaseHas('plugins', [
            'name' => 'BarPlugin',
        ]);

        $fooPluginDirectoryExists = file_exists('tests/assets/Plugins/FooPlugin');
        $this->assertTrue($fooPluginDirectoryExists);

        $barPluginDirectoryExists = file_exists('tests/assets/Plugins/BarPlugin');
        $this->assertTrue($barPluginDirectoryExists);

        // Reset time after test
        Carbon::setTestNow();
    }

    // TODO: Add upload plugin test

    public function testScopePlugin(): void {
        // Set time for installed_at and updated_at
        Carbon::setTestNow('2020-07-20 10:15:30');
        Plugin::where('id', 3)->update([
            'installed_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        // We reboot the model to register the plugin scopes
        self::rebootModel(Entity::class);

        $response = $this->userRequest()
            ->get('/api/v1/search/entity?q=');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'id' => 7,
            'name' => 'Site B',
            'entity_type_id' => 3,
        ]);

        $response->assertJsonFragment([
            'id' => 1,
            'name' => 'Site A',
            'entity_type_id' => 3,
        ]);

        // Test if works after uninstalling the plugin
        Plugin::where('id', 3)->update([
            'installed_at' => null,
            'updated_at' => Carbon::now(),
        ]);
        self::rebootModel(Entity::class);
        // Re-run the search query
        $response = $this->userRequest()
            ->get('/api/v1/search/entity?q=');

        $response->assertStatus(200);
        $response->assertJsonCount(8, 'data');

        // Reset time after test
        Carbon::setTestNow();
    }

    /**
     * Test getting the migration state of a plugin.
     *
     * @return void
     */
    public function testGetPluginMigrationState()
    {
        $response = $this->userRequest()
            ->get('/api/v1/plugin/migrate/1/check');

        info($response->getContent());
        $response->assertStatus(200);
    }
}