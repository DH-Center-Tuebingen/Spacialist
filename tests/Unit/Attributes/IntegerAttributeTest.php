<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\IntegerAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!
class IntegerAttributeTest extends TestCase {
    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
    $this->expectNotToPerformAssertions(IntegerAttribute::class);
        IntegerAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, IntegerAttribute::fromImport($input));
    }

    /**
    * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
    $this->expectException(InvalidDataException::class);
    IntegerAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
                "empty string" => ["", null],
                "zero" => [0, 0],
                "positive integer" => [1, 1],
                "negative integer" => [-1, -1],
                "string integer" => ["1", 1],
                "big integer (max int on 32 bit system)" => ["2147483647", 2147483647],
                "big negative integer (max int on 32 bit system)" => ["-2147483647", -2147483647],
        ];
    }

    public static function falsyProvider() {
        return [
                "fail when integer is too big (max int on 64 bit system +1)" => ["9223372036854775808"],
                "fail when float" => [1.1],
                "fail when string" => ["string"],
                "fail when boolean" => [true],
        ];
    }
}