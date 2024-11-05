<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\DaterangeAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!
class DaterangeAttributeTest extends TestCase {
    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(DaterangeAttribute::class);
        DaterangeAttribute::fromImport($input);
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        DaterangeAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "correct format" => ["2024-10-25;2024-10-30"],
            "correct if same date" => ["2024-10-25;2024-10-30"],
            "should trim formats" => [" 2024-10-25 ; 2024-10-30 "]
        ];
    }

    public static function falsyProvider() {
        return [
            "end cannot be before start" => ["2024-10-30;2024-10-25"],
            "fails for datetime" => ["2024-10-30 10:00:00;2024-10-30 10:00:00"],
            "fails for year with two letters" => ["24-10-10;24-10-10"],
            "fail for unix timestring" => ["1727876634;1727876634"],
            "fail for boolean true" => [true],
            "fail for boolean false" => [false],
            "fail for numeric 0" => [0],
            "fail for positive numeric unix timestring" => [1727876634],
            "fail for negative numeric unix timestring" =>[-1727876634],
            "fail for positive numeric float value" => [1727876634.2312],
            "fail for negative numeric float value" => [-1727876634.2312],
        ];
    }
}