<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\DropdownMultipleAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class DropdownMultipleAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(DropdownMultipleAttribute::class);
        DropdownMultipleAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportCheckReturnValues($input, $expected) {
        $this->assertEquals($expected, DropdownMultipleAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        DropdownMultipleAttribute::fromImport($input);
    }

    public function testParseExport() {
        $testValue = AttributeValue::find(78);
        $parseResult = AttributeBase::serializeExportData($testValue);

        $this->assertEquals('rot;grün', $parseResult);
    }

    public static function truthyProvider() {
        $find = [
            "id" => 3,
            "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921",
        ];

        $pottery = [
            "id" => 9,
            "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/keramik#20171220095651",
        ];

        $dataJson = json_encode([$find, $pottery]);

        return [
            "no value" => ["", null],
            "value in english" => ["Find;Pottery", $dataJson],
            "value in german" => ["Fundobjekt;Keramik", $dataJson],
            "value with whitespace" => [" Find ; Pottery ", $dataJson],
            "value with different languages" => ["Find;Keramik", $dataJson]
        ];
    }

    public static function falsyProvider() {
        return [
            "fail when input is not a string (int)" => [1],
            "fail when input is not a string (bool)" => [true],
            "fail when one input is not a valid concept/label in the vocabulary" => ["Fund;Pottery"],
            "fail when case sensitivity is not considered" => ["find;pottery"],
        ];
    }
}