<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\EpochAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EpochAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(EpochAttribute::class);
        EpochAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, EpochAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        EpochAttribute::fromImport($input);
    }

    /**
     * Test export of epoch attribute (attribute value id = 62)
     *
     * @return void
     */
    public function testParseExport() {
        $testValue = AttributeValue::find(62);
        $parseResult = EpochAttribute::parseExport(json_encode($testValue->getValue()));

        $this->assertEquals('-340;-300;Eisenzeit', $parseResult);
    }
    /**
     * Test export value of epoch attribute value without epoch key (id=62)
     *
     * @return void
     */
    public function testParseExportWihtoutEpoch() {
        $testValue = AttributeValue::find(62);
        // remove epoch key from json data
        $jsonVal = json_decode($testValue->json_val);
        $jsonVal->epoch = null;
        // and store value
        $testValue->json_val = json_encode($jsonVal);

        $parseResult = EpochAttribute::parseExport(json_encode($testValue->getValue()));
        $this->assertEquals('-340;-300;', $parseResult);

    }

    public static function truthyProvider() {
        $baseData = [
            "start" => 100,
            "startLabel" => "bc",
            "end" => 100,
            "endLabel" => "ad",
        ];

        $epochData = $baseData;
        $epochData["epoch"] = ["id"=>59, "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/steinzeit#20171220165355"];
        $epochData = json_encode($epochData);

        $noEpochData = $baseData;
        $noEpochData["epoch"] = null;
        $noEpochData = json_encode($noEpochData);

        return [
            "no value" => ["", null],
            "corret value" => ["-100;100;Steinzeit", $epochData],
            "correct value with no epoch" => ["-100;100;", $noEpochData],
            //TODO :: need english epoch, this is currently not in the test data
        ];
    }

    public static function falsyProvider() {
        return [
            "missing data" => ["-100;100"],
            "epoch does not exist" => ["-100;100;Moderne"],
            "floating point" => ["-100.5;100;Steinzeit"],
        ];
    }
}