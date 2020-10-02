<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    const models = [
        ['id' => "App\\Attribute", 'label' => 'Attribute'],
        ['id' => "App\\AttributeValue", 'label' => 'AttributeValue'],
        ['id' => "App\\AvailableLayer", 'label' => 'AvailableLayer'],
        ['id' => "App\\Bibliography", 'label' => 'Bibliography'],
        ['id' => "App\\Entity", 'label' => 'Entity'],
        ['id' => "App\\EntityAttribute", 'label' => 'EntityAttribute'],
        ['id' => "App\\EntityFile", 'label' => 'EntityFile'],
        ['id' => "App\\EntityType", 'label' => 'EntityType'],
        ['id' => "App\\EntityTypeRelation", 'label' => 'EntityTypeRelation'],
        ['id' => "App\\File", 'label' => 'File'],
        ['id' => "App\\FileTag", 'label' => 'FileTag'],
        ['id' => "App\\Geodata", 'label' => 'Geodata'],
        ['id' => "App\\Preference", 'label' => 'Preference'],
        ['id' => "App\\Reference", 'label' => 'Reference'],
        ['id' => "App\\Role", 'label' => 'Role'],
        ['id' => "App\\ThBroader", 'label' => 'ThBroader'],
        ['id' => "App\\ThConcept", 'label' => 'ThConcept'],
        ['id' => "App\\ThConceptLabel", 'label' => 'ThConceptLabel'],
        ['id' => "App\\ThLanguage", 'label' => 'ThLanguage'],
        ['id' => "App\\User", 'label' => 'User'],
        ['id' => "App\\UserPreference", 'label' => 'UserPreference'],
    ];

    /**
     * Test get list of loggable models
     *
     * @return void
     */
    public function testLoggableModels()
    {
        $compModels = sp_loggable_models();
        $this->assertEquals(self::models, $compModels);
    }
}
