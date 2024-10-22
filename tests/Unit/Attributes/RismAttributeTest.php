<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\RismAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class RismAttributeTest extends TestCase {

    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(RismAttribute::class);
        RismAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, RismAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        RismAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "empty string" => ["", null],
            "number as string" => ["600146721", "600146721"],
            "number as string with spaces" => [" 600146721 ", "600146721"],
            "number can start with 0" => ["00000600146721", "00000600146721"],
        ];
    }

    public static function falsyProvider() {
        return [
             // insert failing tests
            "fails on integer value" => [1],
            "fails on float" => [1.1],
            "fails on float string" => ["1.1"],
            "fails on boolean" => [true],
            "fails on non-numeric string" => ["string"],
            "fails on negative integer" => [-1],
            "fails on negative integer string" => ["-1"],
        ];
    }
}