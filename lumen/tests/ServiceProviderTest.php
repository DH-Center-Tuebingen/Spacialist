<?php

use Illuminate\Support\Facades\Validator;

class ServiceProviderTest extends TestCase
{
    public function testValidations() {
        $rules = [
            'field1' => 'geom_type'
        ];
        // Test unsupported geom types
        $data = [
            'field1' => 'not supported'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $data = [
            'field1' => 1
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());

        // Test supported geom types
        $data = [
            'field1' => 'Point'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'Linestring'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'Polygon'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());

        // Test wrong hex codes
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => 123123
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => 'red'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => '#ab-1000'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => '#fffffg'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());

        // Test actual hex codes
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => '#ffaa80'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $rules = [
            'field1' => 'color'
        ];
        $data = [
            'field1' => '#000022'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());

        // Test unsupported boolean_string
        $rules = [
            'field1' => 'boolean_string'
        ];
        $data = [
            'field1' => 'wahr'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $data = [
            'field1' => '2'
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $data = [
            'field1' => 2
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());
        $data = [
            'field1' => [false]
        ];
        $v = Validator::make($data, $rules);
        $this->assertFalse($v->passes());

        // Test accepted boolean_string values
        $data = [
            'field1' => 0
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 1
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => '0'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => '1'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => false
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => true
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'false'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'true'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'FALSE'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
        $data = [
            'field1' => 'TRUE'
        ];
        $v = Validator::make($data, $rules);
        $this->assertTrue($v->passes());
    }
}
