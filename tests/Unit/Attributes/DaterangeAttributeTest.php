<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\DaterangeAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class DaterangeAttributeTest extends TestCase {

 /**
  * @dataProvider truthyProvider
  */
 public function testFromImportTruthy($input) {
     $this->expectNotToPerformAssertions(DaterangeAttribute::class);
      DaterangeAttribute::fromImport($input);
 }

 /**
  * @dataProvider falsyProvider
  */
 public function testFromImportFalsy($input) {
     $this->expectException(InvalidDataException::class);
     DaterangeAttribute::fromImport($input);
 }

 public static function truthyProvider() {
     return [
        
     ];
 }

 public static function falsyProvider() {
     return [
        ["2024-10-30; 2024-10-25"], // End cannot be before start.
        ["1727876634"],
        ["2024-10-30 10:00:00"],
        ["24-10-10"],
        ["1727876634"],
        [true],
        [false],
        [0],
        [1727876634],
        [-1727876634],
        [1727876634.2312],
        ["24-10-10;24-10-11"],
        
     ];
 }
}