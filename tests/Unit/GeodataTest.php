<?php

namespace Tests\Unit;

use App\Geodata;
use Clickbar\Magellan\Data\Geometries\Dimension;
use Clickbar\Magellan\Exception\UnknownWKTTypeException;
use Tests\TestCase;

class GeodataTest extends TestCase
{
    /**
     * Test fromWKT function
     *
     * @return void
     */
    public function testFromWktWithTypo() {
        $this->expectException(UnknownWKTTypeException::class);
        Geodata::fromWKT('PIONT(1 2)');
    }

    /**
     * Test fromWKT function
     *
     * @return void
     */
    public function testFromWktWith3dPoint() {
        $point3d = Geodata::fromWKT('POINT Z(1 2 3)');
        $this->assertEquals(1, $point3d->getX());
        $this->assertEquals(2, $point3d->getY());
        $this->assertEquals(3, $point3d->getZ());
        $this->assertEquals(Dimension::DIMENSION_3DZ, $point3d->getDimension());
        $this->assertFalse($point3d->hasSrid());
    }

    /**
     * Test fromWKT function
     *
     * @return void
     */
    public function testFromWktWith2dPointAndSrid() {
        // FIXME: currently Magellan uses a Singleton/Service Container approach which leads to
        // a problem in case an unsupported/wrong WKT-Type is provided
        // For this wrong type the singleton's $dimension is set to 2D
        // if later a non-2D-WKT-type is provided, a Exception is thrown, because
        // the internal $dimension of the singleton is still 2D and does (of course)
        // not match e.g. 3DZ
        // $typoData = Geodata::fromWKT('PIONT(1 2)');
        // $this->assertNull($typoData);

        $sridPoint2d = Geodata::fromWKT('SRID=4326;POINT(1 2)');
        $this->assertEquals(1, $sridPoint2d->getLongitude());
        $this->assertEquals(2, $sridPoint2d->getLatitude());
        $this->assertNull($sridPoint2d->getAltitude());
        $this->assertEquals(Dimension::DIMENSION_2D, $sridPoint2d->getDimension());
        $this->assertTrue($sridPoint2d->hasSrid());
    }
}
