<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\GeographyAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!
class GeographyAttributeTest extends TestCase {
    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
    $this->expectNotToPerformAssertions(GeographyAttribute::class);
        GeographyAttribute::fromImport($input);
    }

    private function assertPoint($expected, $actual) {
        $this->assertEquals($expected[0], $actual->getLng());
        $this->assertEquals($expected[1], $actual->getLat());
    }

    private function assertLineString($expected, $actual) {
        $this->assertEquals(count($expected), count($actual->getPoints()));
        for($i = 0; $i < count($expected); $i++) {
            $this->assertPoint($expected[$i], $actual->getPoints()[$i]);
        }
    }

    private function assertMultiLineString($expected, $actual) {
        $this->assertEquals(count($expected), count($actual->getLineStrings()));
        for($i = 0; $i < count($expected); $i++) {
            $this->assertLineString($expected[$i], $actual->getLineStrings()[$i]);
        }
    }

    private function assertMultiPolygon($expected, $actual) {
        $this->assertEquals(count($expected), count($actual->getPolygons()));

        // Hotfix for MSTAACK, as there is a filter function that remove a (supposedly) empty string that
        // increments the index of the array, leading to a mismatch in the indexes of the arrays.
        $fixedActualValues = array_values($actual->getPolygons());
        for($i = 0; $i < count($expected); $i++) {
            $this->assertMultiLineString($expected[$i], $fixedActualValues[$i]);
        }
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $type, $coordinates) {
        $geo = GeographyAttribute::fromImport($input);

        if($type == null) {
            $this->assertEquals($coordinates, $geo);
        } else {
            switch($type){
                case 'point':
                    $this->assertPoint($coordinates, $geo);
                    break;
                case 'multipoint':
                case 'linestring':
                    $this->assertLineString($coordinates, $geo);
                    break;
                case 'multilinestring':
                case 'polygon':
                    $this->assertMultiLineString($coordinates, $geo);
                    break;
                case 'multipolygon':
                    $this->assertMultiPolygon($coordinates, $geo);
                    break;
            }
        }
    }

    /**
    * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
    $this->expectException(InvalidDataException::class);
    GeographyAttribute::fromImport($input);
    }

    public static function truthyProvider() {
    return [
        "empty" => ["", null, null],
        "point" => ["POINT(1 1)", 'point', [1,1]],
        "linestring" => ["LINESTRING(0 0, 1 1, 2 2)",'linestring', [[0,0], [1,1], [2,2]]],
        "polygon" => ["POLYGON((0 0, 1 1, 1 0, 0 0))",'polygon', [[[0,0], [1,1], [1,0], [0,0]]]],
        "multipoint" => ["MULTIPOINT(0 0, 1 1, 2 2)", 'multipoint', [[0,0], [1,1], [2,2]]],
        "multilinestring" => ["MULTILINESTRING((0 0, 1 1, 2 2), (3 3, 4 4, 5 5))", "multilinestring", [[[0,0], [1,1], [2,2]], [[3,3], [4,4], [5,5]]]],
        "multipolygon" => ["MULTIPOLYGON(((0 0, 1 1, 1 0, 0 0)), ((2 2, 3 3, 3 2, 2 2)))", "multipolygon", [[[[0,0], [1,1], [1,0], [0,0]]], [[[2,2], [3,3], [3,2], [2,2]]]]],
        "point with floating point" => ["POINT(1.15847 1.13687)","point", [1.15847, 1.13687]],
    ];
    }

    public static function falsyProvider() {
        return [
            "invalid point format" => ["POINT(1 "],
            "invalid linestring format" => ["LINESTRING(0 0, 1 1, 2 "],
            "invalid polygon format" => ["POLYGON((0 0, 1 1, 1 0, 0 "],
            "invalid multipoint format" => ["MULTIPOINT(0 0, 1 1, 2 "],
            "invalid multilinestring format" => ["MULTILINESTRING((0 0, 1 1, 2 2), (3 3, 4 4, 5"],
            "invalid multipolygon format" => ["MULTIPOLYGON(((0 0, 1 1, 1 0, 0)), ((2 2, 3 3, 3 2, 2"],
        ];
    }
}