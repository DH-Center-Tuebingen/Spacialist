<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\DateAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class DateAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(InvalidDataException::class);
        DateAttribute::fromImport($input);
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        DateAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "correct format"=>["2024-10-30"],
            "whitespace should be trimmed" => [" 2024-10-30 "],
        ];
    }

    public static function falsyProvider() {
        return [
            "fail when datetime is passed" =>["2024-10-30 10:00:00"],
            "fail when year has only two letters" => ["24-10-10"],
            "fail for unix timestring" => ["1727876634"],
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