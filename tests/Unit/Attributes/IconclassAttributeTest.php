<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\IconclassAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!

class IconclassAttributeTest extends TestCase
{

    /**
     * @dataProvider truthyProvider
     */
    public function testFromImportTruthy($input)
    {
        $this->expectNotToPerformAssertions(IconclassAttribute::class);
        IconclassAttribute::fromImport($input);
    }

    /**
     * @dataProvider truthyProvider
     */
    public function testFromImportReturnValues($input, $expected)
    {
        $this->assertEquals($expected, IconclassAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
     */
    public function testFromImportFalsy($input)
    {
        $this->expectException(InvalidDataException::class);
        IconclassAttribute::fromImport($input);
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
