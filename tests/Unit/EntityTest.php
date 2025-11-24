<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\Entity;

class EntityTest extends TestCase
{
    /**
     * Test getting all children of an entity (id=2)
     *
     * @return void
     */
    public function testGetAllChildren()
    {
        $entity = Entity::find(2);
        $childrenArray = $entity->getAllChildren();
        $this->assertEquals(4, count($childrenArray));
        $this->assertArraySubset([
            [
                '_name' => 'Befund 1',
                '_parent' => 'Site A',
                '_entity_type' => 'Feature',
                '_entity_type_id' => 4,
            ],
            [
                '_name' => 'Inv. 1234',
                '_parent' => 'Site A\\\\Befund 1',
                '_entity_type' => 'Pottery',
                '_entity_type_id' => 5,
            ],
            [
                '_name' => 'Inv. 124',
                '_parent' => 'Site A\\\\Befund 1',
                '_entity_type' => 'Pottery',
                '_entity_type_id' => 5,
            ],
            [
                '_name' => 'Inv. 31',
                '_parent' => 'Site A\\\\Befund 1',
                '_entity_type' => 'Stone',
                '_entity_type_id' => 6,
                12 => 3.5,
            ],
        ], $childrenArray);
    }

    /**
     * Test relations of an entity (id=3 and id=1)
     *
     * @return void
     */
    public function testRelations()
    {
        $entity = Entity::with(['child_entities', 'entity_type', 'root_entity', 'bibliographies', 'attributes'])->find(3);

        $this->assertEquals(0, $entity->child_entities->count());
        $this->assertEquals(5, $entity->entity_type->id);
        $this->assertEquals($entity->entity_type_id, $entity->entity_type->id);
        $this->assertEquals(2, $entity->root_entity->id);
        $this->assertEquals(0, $entity->bibliographies->count());
        $this->assertEquals(7, $entity->attributes->count());
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
                'id' => 3,
                'pivot' => [
                    'json_val' => '[{"id": 18, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rot#20171220100515"}, {"id": 20, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/grun#20171220100524"}]',
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
            3, 2, 1
        ], $entity->parentIds);
        $this->assertArraySubset([
            'Inv. 1234', 'Befund 1', 'Site A'
        ], $entity->parentNames);
    }
}
