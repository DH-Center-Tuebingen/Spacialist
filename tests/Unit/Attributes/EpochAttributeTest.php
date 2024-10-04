<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\EpochAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class EpochAttributeTest extends TestCase {

    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(EpochAttribute::class);
        EpochAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, EpochAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        EpochAttribute::fromImport($input);
    }

    public static function truthyProvider() {

        $epochData = json_encode([
            "start" => 100,
            "startLabel" => "bc",
            "end" => 100,
            "endLabel" => "ad",
            "epoch" => ["id"=>59, "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/steinzeit#20171220165355"]
        ]);

        return [
            "no value" => ["", null],
            "corret value" => ["-100;100;Steinzeit", $epochData],
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