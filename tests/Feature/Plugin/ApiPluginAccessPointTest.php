<?php

namespace Tests\Feature\Plugin;

use App\Models\Plugin\AccessPoint;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginAccessPointtest extends TestCase {

    private const PLUGIN_NAME = 'AccessPointPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000003';

    private ?PluginGenerator $generator = null;

    static function defaultAccessPointTemplate(array $accessPoints = []): PluginTemplate {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();

        if(count($accessPoints) > 0) {
            $template->addXml("accesspoints", "accesspoint", $accessPoints);
        }

        return $template;
    }

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-07-20 10:15:30');
    }

    private function ensureTeardown() {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        Carbon::setTestNow();
        $this->ensureTeardown();
    }

    function testInstallCreatesAccessPointFromManifest() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseHas('plugin_service_access_points', [
                'plugin_id' => $template->plugin->id,
                'identifier' => $accessPoint['id'],
                'path' => $accessPoint['path'],
                'label' => 'plugin.' . $template->plugin->slugName() . '.' . $accessPoint['label'],
            ]);
        });
    }

    function testInstallCreatesMultipleAccessPointsFromManifest() {
        $accessPoints = [
            ['id' => 'access_point_a', 'label' => 'a.label', 'path' => '/path_a'],
            ['id' => 'access_point_b', 'label' => 'b.label', 'path' => '/path_b'],
        ];
        $template = self::defaultAccessPointTemplate($accessPoints)->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoints) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            foreach($accessPoints as $accessPoint) {
                $this->assertDatabaseHas('plugin_service_access_points', [
                    'plugin_id' => $template->plugin->id,
                    'identifier' => $accessPoint['id'],
                    'path' => $accessPoint['path'],
                    'label' => 'plugin.' . $template->plugin->slugName() . '.' . $accessPoint['label'],
                ]);
            }
        });
    }

    function testInstallWithEmptyAccessPointsCreatesNone() {
        $template = self::defaultAccessPointTemplate()->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseCount('plugin_service_access_points', 0);
        });
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidAccessPointProvider')]
    function testInstallSkipsInvalidAccessPointDefinition($accessPoint) {
        $template = self::defaultAccessPointTemplate([$accessPoint])->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            // An invalid access point definition should be skipped, but must not fail the whole installation.
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseCount('plugin_service_access_points', 0);
        });
    }

    static function invalidAccessPointProvider() {
        return [
            "Missing id" => [['id' => '', 'label' => 'example.label', 'path' => '/example_path']],
            "Missing label" => [['id' => 'example_access_point', 'label' => '', 'path' => '/example_path']],
            "Missing path" => [['id' => 'example_access_point', 'label' => 'example.label', 'path' => '']],
        ];
    }

    function testInstallSkipsAccessPointWithAlreadyExistingIdentifier() {
        $sharedIdentifier = 'shared_access_point';

        $firstTemplate = self::defaultAccessPointTemplate([
            ['id' => $sharedIdentifier, 'label' => 'first.label', 'path' => '/first_path'],
        ])->generate("plugin.xml");

        $secondTemplate = new PluginTemplate(
            name: 'OtherAccessPointPlugin',
            uuid: Str::uuid()->toString(),
            version: '1.0.0'
        );
        $secondTemplate->addBasic()
            ->addXml("accesspoints", "accesspoint", [
                ['id' => $sharedIdentifier, 'label' => 'second.label', 'path' => '/second_path'],
            ])
            ->created()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$firstTemplate, $secondTemplate], function () use ($firstTemplate, $secondTemplate, $sharedIdentifier) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$secondTemplate->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseCount('plugin_service_access_points', 1);
            $this->assertDatabaseHas('plugin_service_access_points', [
                'identifier' => $sharedIdentifier,
                'label' => 'plugin.' . $firstTemplate->plugin->slugName() . '.first.label',
                'path' => '/first_path',
            ]);
        });
    }

    function testInstallRequiresPluginWritePermission() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->useUserWithPermissions(['plugin_read']);
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(403);

            $this->assertDatabaseCount('plugin_service_access_points', 0);
        });
    }

    function testUninstallRemovesAccessPoints() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $this->assertDatabaseHas('plugin_service_access_points', [
                'plugin_id' => $template->plugin->id,
                'identifier' => $accessPoint['id'],
            ]);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('plugin_service_access_points', [
                'plugin_id' => $template->plugin->id,
            ]);
        });
    }

    function testUninstallRequiresPluginWritePermission() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $this->useUserWithPermissions(['plugin_read']);
            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(403);

            $this->assertDatabaseHas('plugin_service_access_points', [
                'plugin_id' => $template->plugin->id,
                'identifier' => $accessPoint['id'],
            ]);
        });
    }

    // -----------------------------------------------------------------------
    // User assignment of access points
    // -----------------------------------------------------------------------

    function testUserCanBeAssignedNewAccessPoint() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($accessPoint) {
            $targetUser = User::factory()->create(['accesspoints' => []]);
            $this->useUserWithPermissions(['users_roles_write']);

            $response = $this->userRequest()
                ->patch("/api/v1/user/{$targetUser->id}", [
                    'accesspoints' => [$accessPoint['id']],
                ]);

            $response->assertStatus(200);
            $this->assertEquals([$accessPoint['id']], User::find($targetUser->id)->accesspoints);
        });
    }

    function testUserAccessPointsCanBeUpdated() {
        $accessPoints = [
            ['id' => 'access_point_a', 'label' => 'a.label', 'path' => '/path_a'],
            ['id' => 'access_point_b', 'label' => 'b.label', 'path' => '/path_b'],
        ];
        $template = self::defaultAccessPointTemplate($accessPoints)->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($accessPoints) {
            $targetUser = User::factory()->create(['accesspoints' => [$accessPoints[0]['id']]]);
            $this->useUserWithPermissions(['users_roles_write']);

            $response = $this->userRequest()
                ->patch("/api/v1/user/{$targetUser->id}", [
                    'accesspoints' => [$accessPoints[1]['id']],
                ]);

            $response->assertStatus(200);
            $this->assertEquals([$accessPoints[1]['id']], User::find($targetUser->id)->accesspoints);
        });
    }

    function testUserAccessPointsCanBeRemoved() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($accessPoint) {
            $targetUser = User::factory()->create(['accesspoints' => [$accessPoint['id']]]);
            $this->useUserWithPermissions(['users_roles_write']);

            $response = $this->userRequest()
                ->patch("/api/v1/user/{$targetUser->id}", [
                    'accesspoints' => [],
                ]);

            $response->assertStatus(200);
            $this->assertEquals([], User::find($targetUser->id)->accesspoints);
        });
    }

    function testUserKeepsAccessPointsWhenPluginIsDisabled() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $targetUser = User::factory()->create(['accesspoints' => [$accessPoint['id']]]);
            $this->useUserWithPermissions(['plugin_write']);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertEquals([$accessPoint['id']], User::find($targetUser->id)->accesspoints);
        });
    }

    function testUserStillHasAccessPointsWhenPluginWasReenabled() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $targetUser = User::factory()->create(['accesspoints' => [$accessPoint['id']]]);
            $this->useUserWithPermissions(['plugin_write']);

            $this->userRequest()->post("/api/v1/plugin/uninstall/{$template->plugin->id}")->assertStatus(200);
            $response = $this->userRequest()->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertEquals([$accessPoint['id']], User::find($targetUser->id)->accesspoints);
        });
    }

    function testUserAccessPointsAreRemovedWhenPluginIsRemoved() {
        $accessPoint = ['id' => 'example_access_point', 'label' => 'example.label', 'path' => '/example_path'];
        $template = self::defaultAccessPointTemplate([$accessPoint])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template, $accessPoint) {
            $targetUser = User::factory()->create(['accesspoints' => [$accessPoint['id'], 'another_access_point']]);
            $this->useUserWithPermissions(['plugin_write', 'plugin_delete']);

            $this->userRequest()->post("/api/v1/plugin/uninstall/{$template->plugin->id}")->assertStatus(200);
            $response = $this->userRequest()->delete("/api/v1/plugin/remove/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertEquals(['another_access_point'], User::find($targetUser->id)->accesspoints);
        });
    }
}
