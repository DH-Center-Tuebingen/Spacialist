<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\EntityType;

class EntityTypeTest extends TestCase
{
    /**
     * Test relations of an entity type (id=4)
     *
     * @return void
     */
    public function testRelations()
    {
        $type = EntityType::find(4);

        $this->assertEquals(1, $type->entities->count());
        $this->assertEquals(2, $type->entities[0]->id);
        $this->assertEquals(5, $type->attributes->count());
        $this->assertEquals(14, $type->attributes[0]->id);
        $this->assertEquals(1, $type->attributes[0]->pivot->position);
        $this->assertEquals(16, $type->attributes[1]->id);
        $this->assertEquals(2, $type->attributes[1]->pivot->position);
        $this->assertEquals(17, $type->attributes[2]->id);
        $this->assertEquals(3, $type->attributes[2]->pivot->position);
        $this->assertEquals(10, $type->attributes[3]->id);
        $this->assertEquals(4, $type->attributes[3]->pivot->position);
        $this->assertEquals(13, $type->attributes[4]->id);
        $this->assertEquals(5, $type->attributes[4]->pivot->position);
        $this->assertEquals(0, $type->sub_entity_types->count());
        $this->assertEquals(2, $type->thesaurus_concept->id);
        $this->assertEquals(false, $type->is_root);
        $this->assertEquals('#FFFF00', $type->color);
        $this->assertEquals('https://spacialist.escience.uni-tuebingen.de/<user-project>/befund#20171220094916', $type->thesaurus_url);
    }

    public function testSubEntityTypeCountCorrect(){
        $type = EntityType::with(['sub_entity_types'])->find(3);
        $this->assertEquals(5, $type->sub_entity_types->count());
    }

    public function testSubEntityTypesAreCorrect(){
        $type = EntityType::with(['sub_entity_types'])->find(3);
        $this->assertEquals([3,4,5,6,7], $type->sub_entity_types->pluck('id')->toArray());
    }

    public function testUpdateWithRelations(){
        info("TestUpdateWithRelations: ");
        $type = EntityType::with(['sub_entity_types'])->find(4);
        $type->updateWithRelations([
            'is_root' => true,
            'color' => '#FF0000',
        ], [3,5]);

        $updated_type = EntityType::with(['sub_entity_types'])->find(4);
        $this->assertEquals(2, $updated_type->sub_entity_types->count());
        $this->assertEquals([3,5], $updated_type->sub_entity_types->pluck('id')->toArray());
        $this->assertEquals(true, $updated_type->is_root);
        $this->assertEquals('#FF0000', $updated_type->color);
    }


}
