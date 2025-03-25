<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\UserlistAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// !!!! Currently this test is only testing the fromImport function!!!
class UserlistAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(UserlistAttribute::class);
        UserlistAttribute::fromImport($input);
    }

    #[DataProvider('truthyProvider')]
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, UserlistAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        UserlistAttribute::fromImport($input);
    }

    /**
     * Test export of attribute (attribute value id = xx)
     *
     * @return void
     */
    public function testParseExport() {
        $testValue = AttributeValue::find(76);
        $parseResult = AttributeBase::serializeExportData($testValue);

        $this->assertEquals('admin', $parseResult);
    }

    public static function truthyProvider() {
        return [
            "empty string" => ["", null],
            "single user" => ["admin", "[1]"],
            "multiple users" => ["admin;johndoe", "[1,2]"],
            "multiple users with spaces" => [" admin ; johndoe ", "[1,2]"],
            "single deleted user" => ["garyguest", "[3]"],
            "multiple users with single deleted user" => ["admin;garyguest", "[1,3]"],
        ];
    }

    public static function falsyProvider() {
        return [
            "fails on integer" => [1],
            "fails on float" => [1.1],
            "fails on boolean" => [true],
            "fails on incorrect user" => ["unknown"],
            "fails on any incorrect user" => ["unknown,unknown2"],
        ];
    }
}