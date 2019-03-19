<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $type = EntityType::with(['layer', 'entities', 'attributes', 'sub_entity_types', 'thesaurus_concept'])->find(4);

        $this->assertEquals(5, $type->layer->id);
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
    }
}
