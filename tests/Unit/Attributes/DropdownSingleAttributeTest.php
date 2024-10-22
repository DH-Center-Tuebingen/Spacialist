<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\DropdownSingleAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class DropdownSingleAttributeTest extends TestCase {

    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(DropdownSingleAttribute::class);
        DropdownSingleAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, DropdownSingleAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        DropdownSingleAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "no value" => ["", null],
            "value in english" => ["Find","https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
            "value in german" => ["Fundobjekt", "https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
            "value with whitespace" => [" Find ","https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
        ];
    }

    public static function falsyProvider() {
        return [
            "fail when input is not a string" => [1],
            "fail when input is not a string" => [true],
            "fail when input is not a valid concept/label in the vocabulary" => ["Fund"],
            "fail when case is not correct" => ["fundobjekt"],
        ];
    }
}