<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\PercentageAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class PercentageAttributeTest extends TestCase {

 /**
  * @dataProvider truthyProvider
  */
 public function testFromImportTruthy($input) {
   $this->expectNotToPerformAssertions(PercentageAttribute::class);
    PercentageAttribute::fromImport($input);
 }

  /**
  * @dataProvider truthyProvider
  */
  public function testFromImportReturnValues($input, $expected) {
    $this->assertEquals($expected, PercentageAttribute::fromImport($input));
  }

 /**
  * @dataProvider falsyProvider
  */
 public function testFromImportFalsy($input) {
   $this->expectException(InvalidDataException::class);
   PercentageAttribute::fromImport($input);
 }

 public static function truthyProvider() {
   return [
        "empty" => ["", null],
        "zero" => ["0", 0],
        "fifty" => ["50", 50],
        "hundred" => ["100", 100], 
        "integer zero" => [0, 0],
        "integer fifty" => [50, 50],
        "integer hundred" => [100, 100],
   ];
 }

 public static function falsyProvider() {
   return [
        "boolean" => [true],
        "float" => [1.1],
        "negative integer" => [-1],
        "negative float" => [-1.1],
        "integer over hundred" => [101],
        "string is float" => ["1.1"],
        "string is negative integer" => ["-1"],
        "string is negative float" => ["-1.1"],
        "string is over hundred" => ["101"],
   ];
 }
}