<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\EntityAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class EntityAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(EntityAttribute::class);
        EntityAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, EntityAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        EntityAttribute::fromImport($input);
    }

    /**
     * Test export of attribute (attribute value id = 35)
     *
     * @return void
     */
    public function testParseExport() {
        $testValue = AttributeValue::find(35);
        $parseResult = AttributeBase::serializeExportData($testValue);

        $this->assertEquals('Aufschluss', $parseResult);
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
            "fail when input is not a string (int)" => [1],
            "fail when input is not a string (bool)" => [true],
            "fail when input is not a valid concept/label in the vocabulary" => ["Fund"],
            "fail when case is not correct" => ["inv. 1234"],
        ];
    }
}