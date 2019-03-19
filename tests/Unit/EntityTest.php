<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Entity;

class EntityTest extends TestCase
{
    /**
     * Test relations of an entity (id=3 and id=1)
     *
     * @return void
     */
    public function testRelations()
    {
        $entity = Entity::with(['child_entities', 'entity_type', 'geodata', 'root_entity', 'bibliographies', 'attributes', 'files'])->find(3);

        $this->assertEquals(0, $entity->child_entities->count());
        $this->assertEquals(5, $entity->entity_type->id);
        $this->assertEquals($entity->entity_type_id, $entity->entity_type->id);
        $this->assertNull($entity->geodata);
        $this->assertEquals(2, $entity->root_entity->id);
        $this->assertEquals(0, $entity->bibliographies->count());
        $this->assertEquals(6, $entity->attributes->count());
        $this->assertEquals(2, $entity->files->count());
        $this->assertEquals(3, count($entity->parentIds));
        $this->assertEquals(3, count($entity->parentNames));
        $this->assertArraySubset([
            [
                'id' => 2,
                'pivot' => [
                    'int_val' => 35,
                ],
            ],
            [
                'id' => 4,
                'pivot' => [
                    'int_val' => 1,
                ],
            ],
            [
                'id' => 5,
                'pivot' => [
                    'json_val' => '[{"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}, {"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
                ],
            ],
            [
                'id' => 9,
                'pivot' => [
                    'json_val' => '{"B": 4, "H": 5, "unit": "cm"}',
                ],
            ],
            [
                'id' => 11,
                'pivot' => [
                    'int_val' => 7,
                ],
            ],
            [
                'id' => 12,
                'pivot' => [
                    'dbl_val' => '12.5',
                ],
            ],
        ], $entity->attributes->toArray());
        $this->assertArraySubset([
            [
                'id' => 4,
                'name' => 'spacialist_screenshot.png',
            ],
            [
                'id' => 6,
                'name' => 'test_archive.zip',
            ],
        ], $entity->files->toArray());
        $this->assertArraySubset([
            3, 2, 1
        ], $entity->parentIds);
        $this->assertArraySubset([
            'Inv. 1234', 'Befund 1', 'Site A'
        ], $entity->parentNames);
    }
}
