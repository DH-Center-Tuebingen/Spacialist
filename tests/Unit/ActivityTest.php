<?php

namespace Tests\Unit;

use Tests\TestCase;


class ActivityTest extends TestCase
{
    const models = [
        ['id' => "App\\Attribute", 'label' => 'Attribute'],
        ['id' => "App\\AttributeValue", 'label' => 'AttributeValue'],
        ['id' => "App\\AvailableLayer", 'label' => 'AvailableLayer'], // TODO move to Map Plugin
        ['id' => "App\\Bibliography", 'label' => 'Bibliography'],
        ['id' => "App\\Entity", 'label' => 'Entity'],
        ['id' => "App\\EntityAttribute", 'label' => 'EntityAttribute'],
        ['id' => "App\\EntityFile", 'label' => 'EntityFile'], // TODO move to File Plugin
        ['id' => "App\\EntityType", 'label' => 'EntityType'],
        ['id' => "App\\EntityTypeRelation", 'label' => 'EntityTypeRelation'],
        ['id' => "App\\Reference", 'label' => 'Reference'],
        ['id' => "App\\Role", 'label' => 'Role'],
        ['id' => "App\\ThBroader", 'label' => 'ThBroader'],
        ['id' => "App\\ThConcept", 'label' => 'ThConcept'],
        ['id' => "App\\ThConceptLabel", 'label' => 'ThConceptLabel'],
        ['id' => "App\\ThLanguage", 'label' => 'ThLanguage'],
        ['id' => "App\\User", 'label' => 'User'],
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
