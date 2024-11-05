<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\EntityAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!
class EntityAttributeTest extends TestCase {
    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(EntityAttribute::class);
        EntityAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, EntityAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        EntityAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "no value" => ["", null],
            "value" => ["Inv. 1234", 3],
            "value with whitespace" => [" Inv. 1234 ", 3],

        ];
    }

    public static function falsyProvider() {
        return [
            "fail when input is not a string" => [1],
            "fail when input is not a string" => [true],
            "fail when input is not a valid concept/label in the vocabulary" => ["Fund"],
            "fail when case is not correct" => ["inv. 1234"],
        ];
    }
}