<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\UserlistAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;

// !!!! Currently this test is only testing the fromImport function!!!
class UserlistAttributeTest extends TestCase {
    /**
     * @dataProvider truthyProvider
    */
    public function testFromImportTruthy($input) {
        $this->expectNotToPerformAssertions(UserlistAttribute::class);
        UserlistAttribute::fromImport($input);
    }

    /**
    * @dataProvider truthyProvider
    */
    public function testFromImportReturnValues($input, $expected) {
        $this->assertEquals($expected, UserlistAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
    */
    public function testFromImportFalsy($input) {
        $this->expectException(InvalidDataException::class);
        UserlistAttribute::fromImport($input);
    }

    public static function truthyProvider() {
        return [
            "empty string" => ["", null],
            "single user" => ["admin", "[1]"],
            "multiple users" => ["admin;first_user", "[1,2]"],
            "multiple users with spaces" => [" admin ; first_user ", "[1,2]"],
            "single deleted user" => ["deleted_user", "[3]"],
            "multiple users with single deleted user" => ["admin;deleted_user", "[1,3]"],
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