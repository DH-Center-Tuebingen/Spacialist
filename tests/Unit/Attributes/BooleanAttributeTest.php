<?php

namespace Tests\Unit\Attributes;

use App\AttributeTypes\BooleanAttribute;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BooleanAttributeTest extends TestCase {
    #[DataProvider('truthyProvider')]
    public function testFromImportTruthy($input) {
        $this->assertTrue(BooleanAttribute::fromImport($input));
    }

    #[DataProvider('falsyProvider')]
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