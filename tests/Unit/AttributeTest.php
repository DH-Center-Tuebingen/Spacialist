<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\Attribute;

class AttributeTest extends TestCase
{
    /**
     * Test relations of attributes (id=5 and id=6)
     *
     * @return void
     */
    public function testRelations()
    {
        $attribute = Attribute::with(['children', 'entities', 'entity_types', 'parent', 'thesaurus_root_concept', 'thesaurus_concept'])->find(6);

        $this->assertTrue($attribute->children->isEmpty());
        $this->assertTrue($attribute->entities->isEmpty());
        $this->assertTrue($attribute->entity_types->isEmpty());
        $this->assertEquals(5, $attribute->parent->id);
        $this->assertEquals(33, $attribute->thesaurus_root_concept->id);
        $this->assertEquals(33, $attribute->thesaurus_concept->id);

        $attribute = Attribute::with(['children', 'entities', 'entity_types', 'parent', 'thesaurus_root_concept', 'thesaurus_concept'])->find(5);

        $this->assertEquals(3, $attribute->children->count());
        $this->assertEquals(3, $attribute->entities->count());
        $this->assertEquals(1, $attribute->entity_types->count());
        $this->assertNull($attribute->parent);
        $this->assertNull($attribute->thesaurus_root_concept);
        $this->assertEquals(32, $attribute->thesaurus_concept->id);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            ['id' => 6],
            ['id' => 7],
            ['id' => 8],
        ], $attribute->children->toArray(),
        ['id']
        );
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            [
                'id' => 4,
                'pivot' => [
                    'json_val' => '[{"6": {"id": 37, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/schulter#20171220105500"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}]',
                ],
            ],
            [
                'id' => 3,
                'pivot' => [
                    'json_val' => '[{"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}, {"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
                ],
            ],
            [
                'id' => 8,
                'pivot' => [
                    'json_val' => '[{"6": {"id": 39, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/boden#20171220105508"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
                ],
            ],
        ], $attribute->entities->toArray(),
        ['id', 'pivot']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            ['id' => 5],
        ], $attribute->entity_types->toArray(),
        ['id']);
    }
}