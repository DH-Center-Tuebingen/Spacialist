<?php

namespace Tests\Feature;

use App\Entity;
use App\Plugin;
use App\Services\PluginManager;
use Carbon\Carbon;
use Tests\TestCase;

use Tests\Support\Templates\ScopeTemplate;
use Tests\Support\PluginGenerator;

class ApiPluginScopeTest extends TestCase {

    private ?PluginGenerator $generator = null;

    private function getScopePlugin(): array {
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

    private function getRestrictToType3Template() {
        return ScopeTemplate::fromSlugArray($this->getScopePlugin())->addBasic()->addEntityScope()->generate();
    }

    private function ensureTeardown() {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }
    }

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-07-20 10:15:30');
    }

    protected function tearDown(): void {
        parent::tearDown();
        Carbon::setTestNow();
        $this->ensureTeardown();
    }


    public function testScopePlugin(): void {

        $response = $this->userRequest()
            ->get('/api/v1/search/entity?q=');

        // Before plugin installation 
        // we get all 8 entitiies.
        $response->assertStatus(200);
        $response->assertJsonCount(8, 'data');

        $this->generator = new PluginGenerator([$this->getRestrictToType3Template()]);
        $this->generator->setUp();


        // As this all happens in the same cycle,
        // we need to manually reboot the model to trigger
        // the boot method and apply the new plugin scope.
        // In a real scenario, this would be handled automatically 
        // as the plugin would be installed in a separate request/cycle.
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

        $plugin = $this->generator->getPlugin('ScopePlugin');
        if(!$plugin) {
            throw new \Exception("Plugin not found in generator plugin map.");
        }
        app(PluginManager::class)->uninstall($plugin);
        self::rebootModel(Entity::class);
        

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
    }


}