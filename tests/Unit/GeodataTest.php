<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Geodata;

class GeodataTest extends TestCase
{
    /**
     * Test relations of a geodata object (id=2)
     *
     * @return void
     */
    public function testRelations()
    {
        $geodata = Geodata::with(['entity'])->find(2);

        $this->assertEquals(1, $geodata->entity->id);
        $this->assertEquals('Site A', $geodata->entity->name);
    }

    /**
     * Test parseWkt function
     *
     * @return void
     */
    public function testParseWkt()
    {
        $data = Geodata::parseWkt('PIONT(1 2)');
        $this->assertNull($data);

        $data = Geodata::parseWkt('POINT Z(1 2 3)');
        $this->assertEquals(2, $data->getLat());
        $this->assertEquals(1, $data->getLng());
        $this->assertEquals(3, $data->getAlt());
    }

    /**
     * Test get last editor of first geodata
     *
     * @return void
     */
    public function testGetGeodataLasteditor()
    {
        $geodata = Geodata::first();
        $this->assertEquals(1, $geodata->user->id);
    }
}
