<?php

namespace Tests\Feature\Plugin;

use App\VersionInfo;
use Illuminate\Support\Str;
use Tests\Support\PluginTemplate;
use Carbon\Carbon;
use Tests\TestCase;

use Tests\Support\PluginGenerator;

use PHPUnit\Framework\Attributes\DataProvider;


class ApiPluginDependencyTest extends TestCase {
    
    private const PLUGIN_NAME = 'DependencyPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000002';

    private ?PluginGenerator $generator = null;

    static function defaultDependencyTemplate($coreMin = null , $coreMax= null, $pluginDependencies = [], $addEmpty=false) {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();
        
        if($coreMin || $coreMax){    
            $coreDependency = ['_tag' => 'core'];
        
            if($coreMin){
                $coreDependency['min'] = $coreMin;
            }
            
            if($coreMax){
                $coreDependency['max'] = $coreMax;
            }
            
            $pluginDependencies[] = $coreDependency;
        }
        
        if(count($pluginDependencies) > 0 || $addEmpty) {        
            $template->addXml("dependencies", 'plugin', $pluginDependencies);
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

    function testInstallWithEmptyDependencies() {
        $this->app->instance(VersionInfo::class, new VersionInfo(0,11,0));
        
        $template = self::defaultDependencyTemplate()->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");
                
            $response->assertStatus(200);
            
            $installedResponse = $this->userRequest()
                ->get("/api/v1/plugin");
            
            $installedResponse->assertStatus(200);
            $installedResponse->assertJsonFragment([
                'name' => static::PLUGIN_NAME,
                'uuid' => static::PLUGIN_UUID,
            ]);
        });
    }
    
    #[DataProvider('coreDependencySuccessProvider')]
    function testInstallWithCoreDependencySuccessfully($coreMin, $coreMax) {
        $this->app->instance(VersionInfo::class, new VersionInfo(0,11,0));
        
        info("CORE MAX => " . $coreMax);
        $template = self::defaultDependencyTemplate(coreMin:$coreMin, coreMax:$coreMax)->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");
                
            $response->assertStatus(200);
            
            $installedResponse = $this->userRequest()
                ->get("/api/v1/plugin?installed=1");
            
            $installedResponse->assertStatus(200);
            $installedResponse->assertJsonFragment([
                'name' => static::PLUGIN_NAME,
                'uuid' => static::PLUGIN_UUID,
            ]);
        });
    }
    
    static function coreDependencySuccessProvider() {
        return [
            // "No limits" => [null, null],
            // "Min 0.0.1" => ['0.0.1', null],
            // "Max 99.0" => [null, '99.0.0'],
            // "Min 0.0.1 & Max 99.0" => ['0.0.1', '99.0.0'],
            // "Min version is current version" => ["0.11.0", null],
            "Max version is current version" => [null, "0.11.0"],
        ];
    }
    
    #[DataProvider('coreDependencyFailProvider')]
    function testInstallWithCoreDependencyFails($coreMin, $coreMax) {
        $this->app->instance(VersionInfo::class, new VersionInfo(0,11,0));
        
        $template = self::defaultDependencyTemplate(coreMin:$coreMin, coreMax:$coreMax)->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");
                
            $response->assertStatus(422);
            
            $installedResponse = $this->userRequest()
                ->get("/api/v1/plugin?installed=1");
            
            $installedResponse->assertStatus(200);
            $installedResponse->assertJsonMissing([
                'name' => static::PLUGIN_NAME,
            ]);
        });
    }
    
    static function coreDependencyFailProvider() {
        return [
            "Min too high" => ['99.0', null],
            "Max too low" => [null, '0.0.1'],
            "Min and Max out of range (lower)" => ['0.0.1', '0.0.2'],
            "Min and Max out of range (higher)" => ['99.0.1', '99.0.2'],
        ];
    }
    
    
    #[DataProvider('pluginDependencySuccessProvider')]
    function testInstallWithPluginDependencySuccessfully(...$plugins) {
        $this->app->instance(VersionInfo::class, new VersionInfo(0,11,0));
          
        $currentVersion = "1.1.1";
        $others = [];
        foreach($plugins as $plugin) {        
            $other = new PluginTemplate(
                name: $plugin['name'],
                uuid: Str::uuid()->toString(),
                version: $currentVersion
            );
            
            $other->addBasic()->generate("plugin.xml");
            $others[] = $other;
        }
        
    
        $template = self::defaultDependencyTemplate(pluginDependencies: $plugins)
            ->created()
            ->generate("plugin.xml");

        $templates = array_merge($others, [$template]);
        
        
        $this->generator = PluginGenerator::with($templates, function () use ($template) {
                    
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");
                
            $response->assertStatus(200);
            
            $installedResponse = $this->userRequest()
                ->get("/api/v1/plugin?installed=1");
            
            $installedResponse->assertStatus(200);
            $installedResponse->assertJsonFragment([
                'name' => static::PLUGIN_NAME,
                'uuid' => static::PLUGIN_UUID,
            ]);
        });
    }
    
    static function pluginDependencySuccessProvider() {
        $currentVersion = "1.1.1";
        return [
            "One > no limits" => [['name' => 'a', 'min' => null, 'max' => null]],
            "One > Min 0.0.1" => [['name' => 'a', 'min' => '0.0.1', 'max' => null]],
            "One > Max 99.0" => [['name' => 'a', 'min' => null, 'max' => '99.0.0']],
            "One > Min 0.0.1 & Max 99.0" => [['name' => 'a', 'min' => '0.0.1', 'max' => '99.0.0']],
            "One > Min version is current version" => [['name' => 'a', 'min' => $currentVersion, 'max' => null]],
            "One > Max version is current version" => [['name' => 'a', 'min' => null, 'max' => $currentVersion]],
            "Two > no limits" => [
                ['name' => 'a', 'min' => null, 'max' => null],
                ['name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > Min 0.0.1" => [
                ['name' => 'a', 'min' => '0.0.1', 'max' => null],
                ['name' => 'b', 'min' => '0.0.1', 'max' => null],
            ],
            "Two > Max 99.0" => [
                ['name' => 'a', 'min' => null, 'max' => '99.0.0'],
                ['name' => 'b', 'min' => null, 'max' => '99.0.0'],
            ],
            "Two > Min 0.0.1 & Max 99.0" => [
                ['name' => 'a', 'min' => '0.0.1', 'max' => '99.0.0'],
                ['name' => 'b', 'min' => '0.0.1', 'max' => '99.0.0'],
            ],
            "Two > Min version is current version" => [
                ['name' => 'a', 'min' => $currentVersion, 'max' => null],
                ['name' => 'b', 'min' => $currentVersion, 'max' => null],
            ],
            "Two > Max version is current version" => [
                ['name' => 'a', 'min' => null, 'max' => $currentVersion],
                ['name' => 'b', 'min' => null, 'max' => $currentVersion],
            ],
        ];
    }
    
    
    #[DataProvider('pluginDependencyFailureProvider')]
    function testInstallWithPluginDependencyFails(...$plugins) {        
        $this->app->instance(VersionInfo::class, new VersionInfo(0,11,0));
        
        $currentVersion = app(VersionInfo::class)->getReleaseRaw();
        $others = [];
        foreach($plugins as $plugin) {
            if($plugin['_missing'] ?? false) {
                continue;
            }
            
            $other = new PluginTemplate(
                name: $plugin['name'],
                uuid: Str::uuid()->toString(),
                version: $currentVersion
            );
            
            $other->addBasic()->generate("plugin.xml");
            $others[] = $other;
        }
        
    
        $template = self::defaultDependencyTemplate(pluginDependencies: $plugins)->created()->generate("plugin.xml");
        $templates = array_merge($others, [$template]);
        
        
        $this->generator = PluginGenerator::with($templates, function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");
            $response->assertStatus(422);
            
            $installedResponse = $this->userRequest()
                ->get("/api/v1/plugin?installed=1");
            
            $installedResponse->assertStatus(200);
            $installedResponse->assertJsonMissing([
                'name' => static::PLUGIN_NAME,
            ]);
        });
    }
    
    static function pluginDependencyFailureProvider() {
        return [
            "One > no limits but missing" => [['_missing' => true, 'name' => 'a', 'min' => null, 'max' => null]],
            "One > Min too high 99.0.0" => [['name' => 'a', 'min' => '99.0.0', 'max' => null]],
            "One > Max too low 0.0.1" => [['name' => 'a', 'min' => null, 'max' => '0.0.1']],
            "Two > A no limits but missing" => [
                ['_missing' => true, 'name' => 'a', 'min' => null, 'max' => null],
                ['name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > B no limits but missing" => [
                ['name' => 'a', 'min' => null, 'max' => null],
                ['_missing' => true, 'name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > A and B no limits but missing" => [
                ['_missing' => true, 'name' => 'a', 'min' => null, 'max' => null],
                ['_missing' => true, 'name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > A min too high" => [
                ['name' => 'a', 'min' => "99.0.0", 'max' => null],
                ['name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > A max too low" => [
                ['name' => 'a', 'min' => null, 'max' => "0.0.1"],
                ['name' => 'b', 'min' => null, 'max' => null],
            ],
            "Two > B min too high" => [
                ['name' => 'a', 'min' => null, 'max' => null],
                ['name' => 'b', 'min' => "99.0.0", 'max' => null],
            ],
            "Two > B max too low" => [
                ['name' => 'a', 'min' => null, 'max' => null],
                ['name' => 'b', 'min' => null, 'max' => "0.0.1"],
            ],
        ];
    }
    

}