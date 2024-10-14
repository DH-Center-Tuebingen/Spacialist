<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\IconClassAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!

class IconClassAttributeTest extends TestCase
{

    /**
     * @dataProvider truthyProvider
     */
    public function testFromImportTruthy($input)
    {
        $this->expectNotToPerformAssertions(IconClassAttribute::class);
        IconClassAttribute::fromImport($input);
    }

    /**
     * @dataProvider truthyProvider
     */
    public function testFromImportReturnValues($input, $expected)
    {
        $this->assertEquals($expected, IconClassAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
     */
    public function testFromImportFalsy($input)
    {
        $this->expectException(InvalidDataException::class);
        IconClassAttribute::fromImport($input);
    }

    public static function truthyProvider()
    {
        return [
            "empty string" => ["", null],
            "string" => ["string", "string"],
        ];
    }

    public static function falsyProvider()
    {
        return [
            "number" => [4],
        ];
    }
}
