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
        "fails on import" => [false],
        "fails on 0" => [0],
        "fails on integer" => [1],
        "fails on negative integer" => [-1],
        "fails on string" => ['ok'],
        "fails on string 0" => ['0'],
        "fails on string" => ['kauderwelsch'],
        "fails on float" => [1.1],
        "fails on float string" => ["1.1"],
    ];
 }
}