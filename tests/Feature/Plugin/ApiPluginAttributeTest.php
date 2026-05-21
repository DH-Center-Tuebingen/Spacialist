<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

use Tests\Assets\Templates\ScopeTemplate;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;

class ApiPluginAttributeTest extends TestCase {

    private ?PluginGenerator $generator = null;

    private function getAttributePlugin(): array {
        return [
            'id' => 3,
            'name' => 'AttributePlugin',
            'version' => '1.0.0',
            'uuid' => '123e4567-e89b-12d3-a456-426614174005',
            'update_available' => null,
            'installed_at' => null,
            'created_at' => '2020-08-01T08:00:00.000000Z',
            'updated_at' => '2020-08-01T08:00:00.000000Z',
        ];
    }

    private function getAttributeTemplate() {    
        
        $attributeTemplate = PluginTemplate::fromSlugArray($this->getAttributePlugin())->addBasic();
        $attributeTemplate->addXml("attributes", "attribute", [
            ["src" => "Attribute/AttributeA"],
            ["src" => "Attribute/AttributeB"],
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
            $response = $this->userRequest()->get('/api/v1/plugins');

            $this->assertStatus($response, 200);

        }, true);
    }


}