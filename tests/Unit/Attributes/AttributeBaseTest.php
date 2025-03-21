<?php

namespace Tests\Unit\Attributes;

use App\AttributeTypes\AttributeBase;
use Tests\TestCase;

class AttributeBaseTest extends TestCase {
    /**
     * Test parseExport method of attributebase class
     *
     * @return void
     */
    public function testParseExport() {
        $exportValue = AttributeBase::parseExport(1);
        $this->assertEquals('1', $exportValue);
    }
}