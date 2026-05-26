<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginAttributeTest extends TestCase {

    private ?PluginGenerator $generator = null;

    private function getAttributePlugin(): array {
        return [
            'name' => 'AttributePlugin',
            'version' => '1.0.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174005',
        ];
    }

    private function getAttributeTemplate() {

        $attributeTemplate = PluginTemplate::fromSlugArray($this->getAttributePlugin())->addBasic();
        $attributeTemplate->addXml("attributes", "attribute", [
            ["src" => "Attributes/AttributeA"],
            ["src" => "Attributes/AttributeB"],
        ]);
        $namespace = "namespace App\Plugins\AttributePlugin\Attributes;\n";
        $attributeTemplate->addFile("Attributes/AttributeA.php", "<?php\n$namespace\nuse App\\AttributeTypes\\AttributeBase;\nclass AttributeA {\n    // Attribute A implementation\n}\n");
        $attributeTemplate->addFile("Attributes/AttributeB.php", "<?php\n$namespace\nuse App\\AttributeTypes\\AttributeBase;\nclass AttributeB {\n    // Attribute B implementation\n}\n");
        return $attributeTemplate->generate();
    }

    private function ensureTeardown() {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }
    }

    protected function setUp(): void {
        parent::setUp();
        DB::statement("ALTER SEQUENCE IF EXISTS plugins_id_seq RESTART");
        Carbon::setTestNow('2020-07-20 10:15:30');
    }

    protected function tearDown(): void {
        parent::tearDown();
        Carbon::setTestNow();
        $this->ensureTeardown();
    }


    public function testGetPluginAttributesPlugin(): void {
        $template = $this->getAttributeTemplate();
        $this->generator = new PluginGenerator([$template]);
        $this->generator->use(function () use ($template) {
            $response = $this->userRequest()->get('/api/v1/plugin');
            $response->assertStatus(200);
            $response->assertJson([
                [
                    "id" => 1,
                    "name" => "AttributePlugin",
                    'uuid' => '123e4567-e89b-12d3-a456-426614174005',
                    'created_at' => '2020-07-20T10:15:30.000000Z',
                    'updated_at' => '2020-07-20T10:15:30.000000Z',
                    'registeredAttributes' => [
                         "App\\Plugins\\AttributePlugin\\Attributes\\AttributeA",
                         "App\\Plugins\\AttributePlugin\\Attributes\\AttributeB"
                    ]
                ]
            ]);
        }, true);
    }


    public function testInstallPluginWithAttributes(): void {
        $template = $this->getAttributeTemplate();
        $template->skipInstall();
        $this->generator = new PluginGenerator([$template]);
        $this->generator->use(function () use ($template) {
            $response = $this->userRequest()->get("/api/v1/plugin/1");
            $this->assertStatus($response, 200);
        }, true);
    }
}