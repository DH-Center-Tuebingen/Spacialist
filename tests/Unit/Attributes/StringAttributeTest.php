<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\StringAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class StringAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(StringAttribute::class);
        StringAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, StringAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        StringAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "empty string" => ["", null],
            "string" => ["test", "test"],
        ];
    }

    public static function falsyProvider() {
        return [
            "fails on integer" => [1],
            "fails on float" => [1.1],
            "fails on boolean" => [true],
        ];
    }
}