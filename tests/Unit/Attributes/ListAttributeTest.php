<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\ListAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class ListAttributeTest extends TestCase {

 /**
  * @dataProvider truthyProvider
  */
 public function testFromImportTruthy($input) {
   $this->expectNotToPerformAssertions(ListAttribute::class);
    ListAttribute::fromImport($input);
 }

  /**
  * @dataProvider truthyProvider
  */
  public function testFromImportReturnValues($input, $expected) {
    if($expected != null)
      $expected = json_encode($expected);
    
    $this->assertEquals($expected, ListAttribute::fromImport($input));
  }

 /**
  * @dataProvider falsyProvider
  */
 public function testFromImportFalsy($input) {
   $this->expectException(InvalidDataException::class);
   ListAttribute::fromImport($input);
 }

 public static function truthyProvider() {
   return [
      "empty value" => ["", null],
      "single item" => ["item", ["item"]],
      "multiple items" => ["item1;item2", ["item1", "item2"]],
      "multiple items with spaces" => [" item1 ; item2 ", ["item1", "item2"]],
      "list with empty values" => ["item1;;item2", ["item1", "", "item2"]],
   ];
 }

 public static function falsyProvider() {
   return [
      "boolean" => [true],
      "integer" => [1],
      "float" => [1.1],
   ];
 }
}