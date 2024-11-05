<?php

namespace Tests\Unit\Attributes;

use App\AttributeTypes\BooleanAttribute;
use Tests\TestCase;

class BooleanAttributeTest extends TestCase {
    /**
     * @dataProvider truthyProvider
     */
    public function testFromImportTruthy($input) {
        $this->assertTrue(BooleanAttribute::fromImport($input));
    }

    /**
     * @dataProvider falsyProvider
     */
    public function testFromImportFalsy($input) {
        $this->assertFalse(BooleanAttribute::fromImport($input));
    }

    public static function truthyProvider() {
        return [
            [true],
            [1],
            ['1'],
            ['true'],
            ['t'],
            ['x'],
            ['wahr'],
            ['w'],
        ];
    }

    public static function falsyProvider() {
        return [
            [false],
            [0],
            [-1],
            ['ok'],
            ['0'],
            ['kauderwelsch'],
        ];
    }
}