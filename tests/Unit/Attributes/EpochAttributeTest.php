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

    /** 
     * Test the serialization of epoch attribute values 
     * @dataProvider serializableTruthyProvider
    */
    public function testSerializeValueSucceeds($testValue) {
        $this->assertEquals(json_encode($testValue), EpochAttribute::unserialize($testValue));
    }
    
    /**
     * Test error handling of serialization of epoch attribute values
     * @dataProvider serializableFalsyProvider
     */
    public function testSerializeValueFails($data, $errorMessage) {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage($errorMessage);
        EpochAttribute::unserialize($data);
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
    
    public static function serializableTruthyProvider() {
        
        $existingEpoch = [
            "id" => 59,
            "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/steinzeit#20171220165355"
        ];
        
        $correctTimespans = [
            "ad-ad" => [
                    "start" => 300,
                    "startLabel" => "ad",
                    "end" => 340,
                    "endLabel" => "ad",
                    "epoch" => null
            ],
            "ad-bc" => [
                    "start" => 340,
                    "startLabel" => "bc",
                    "end" => 300,
                    "endLabel" => "ad",
                    "epoch" => null
            ],
            "bc-bc" => [
                    "start" => 340,
                    "startLabel" => "bc",
                    "end" => 300,
                    "endLabel" => "bc",
                    "epoch" => null
            ],
        ];
        
        $tests = [];
        foreach($correctTimespans as $key => $testValue) {
            $withoutEpochKey = $key . " without epoch";
            $tests[$withoutEpochKey] = [$testValue];
            
            $withEpochKey = $key . " with epoch";
            $testValueWithEpoch = $testValue;
            $testValueWithEpoch["epoch"] = $existingEpoch;
            $tests[$withEpochKey] = [$testValueWithEpoch];
        }
        return $tests;
    }
    
    public static function serializableFalsyProvider() {

        $tests = [];
        
        $startBeforeEndTests = [];

        $startBeforeEndTests["ad-ad"] = [
            "start" => 340,
            "startLabel" => "ad",
            "end" => 300,
            "endLabel" => "ad",
            "epoch" => null,
        ];
        
        $startBeforeEndTests["ad-bc"] = [
            "start" => 300,
            "startLabel" => "ad",
            "end" => 340,
            "endLabel" => "bc",
            "epoch" => null,
        ];
        
        $startBeforeEndTests["bc-bc"] = [
            "start" => 300,
            "startLabel" => "bc",
            "end" => 340,
            "endLabel" => "bc",
            "epoch" => null,
        ];
        
        foreach($startBeforeEndTests as $key => $data) {
            $tests[$key . " start before end"] = [
                $data,
                'Start date of a time period must not be after it\'s end date',
            ];
        }
        
        $specifyAdAndBc = [
            "no start label" => [
                "start" => 300,
                "end" => 340,
                "endLabel" => "bc",
                "epoch" => null,
            ],
            "no end label" => [
                "start" => 300,
                "startLabel" => "bc",
                "end" => 340,
                "epoch" => null,
            ],
            "no labels" => [
                "start" => 300,
                "end" => 340,
                "epoch" => null,
            ],
            // TODO: We should also check when we pass an invalid label
        ];
        
        
        foreach($specifyAdAndBc as $key => $data) {
            $tests[$key . " start before end"] = [
                $data,
                'You have to specify if your date is BC or AD.',
            ];
        }

        return $tests;
    }
}