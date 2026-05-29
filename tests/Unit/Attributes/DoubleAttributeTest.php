<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\DoubleAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class DoubleAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(DoubleAttribute::class);
        DoubleAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, DoubleAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        DoubleAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            ["", null],
            [0, "0"],
            [123, "123"],
            [-42, "-42"],
            [8.23, "8.23"],
            [13e3, "13000"],
            [2.3E-3,"0.0023"]
        ];
    }

    public static function falsyProvider() {
        return [
             // insert failing tests
            ["a"],
            [true],
            [false],
            ["123,23"] // Don't allow comma as decimal separator
        ];
    }
}