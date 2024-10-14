<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\EntityMultipleAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class EntityMultipleAttributeTest extends TestCase {

    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(EntityMultipleAttribute::class);
        EntityMultipleAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, EntityMultipleAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        EntityMultipleAttribute::fromImport($input);
    }



    public static function truthyProvider() {

        $dataJson = json_encode([3, 4]);

        return [
            "no value" => ["", null],
            "value in english" => ["Inv. 1234;Inv. 124", $dataJson],
            "value with whitespace" => [" Inv. 1234 ; Inv. 124 ", $dataJson],
        ];
    }

    public static function falsyProvider() {
        return [
            "fail when input is not a string" => [1],
            "fail when input is not a string" => [true],
            "fail when one input is not a valid entity name" => ["Inv. 1234;invalid entity"],
            "fail when case sensitivity is not considered" => ["Inv. 1234;inv. 124"],
        ];
    }
}