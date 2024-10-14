<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\SerialAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class SerialAttributeTest extends TestCase {

 /**
  * @dataProvider falsyProvider
  */
 public function testFromImportFalsy($input) {
   $this->expectException(InvalidDataException::class);
   SerialAttribute::fromImport($input);
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