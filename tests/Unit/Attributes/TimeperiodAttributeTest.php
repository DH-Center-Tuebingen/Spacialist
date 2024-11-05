<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\TimeperiodAttribute;
use App\DataTypes\TimePeriod;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!
class TimeperiodAttributeTest extends TestCase {
    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(TimeperiodAttribute::class);
        TimeperiodAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        // Convert the TimePeriod object to a json string
        if($expected != null){
            $expected = json_encode($expected);
        }
        $this->assertEquals($expected, TimeperiodAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        TimeperiodAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "empty string" => ["", null],
            "string" => ["-100;200", new TimePeriod(-100, 200)],
            "string with spaces" => [" -100 ; 200 ", new TimePeriod(-100, 200)],
        ];
    }

    public static function falsyProvider() {
        return [
            "fails on integer" => [1],
            "fails on float" => [1.1],
            "fails on boolean" => [true],
            "fails on string without semicolon" => ["-100"],
            "fails on string with more than one semicolon" => ["-100;200;300"],
            "fails on string with negative values" => ["-100;-200"],
            "fails on string with non-numeric values (1)" => ["string;200"],
            "fails on string with non-numeric values (2)" => ["-100;string"],
            "failsl if one value is a float (1)" => ["-100.5;200"],
            "failsl if one value is a float (2)" => ["-100;200.1"],
        ];
    }
}