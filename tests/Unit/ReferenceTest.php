<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\Reference;

class ReferenceTest extends TestCase
{
    /**
     * Test relations of an reference (id=3)
     *
     * @return void
     */
    public function testRelations()
    {
        $ref = Reference::with(['entity', 'attribute', 'bibliography'])->find(3);

        $this->assertEquals(1, $ref->entity->id);
        $this->assertEquals(13, $ref->attribute->id);
        $this->assertEquals(1323, $ref->bibliography->id);
    }

    /**
     * Test get last editor of first reference entry
     *
     * @return void
     */
    public function testGetReferenceEntryLasteditor()
    {
        $ref = Reference::first();
        $this->assertEquals(1, $ref->user->id);
    }
}
