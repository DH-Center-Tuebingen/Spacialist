<?php
namespace Tests\Unit\Attributes;

use App\AttributeTypes\SqlAttribute;
use App\Exceptions\InvalidDataException;
use Tests\TestCase;


// !!!! Currently this test is only testing the fromImport function!!!

class SqlAttributeTest extends TestCase {

 /**
  * @dataProvider falsyProvider
  */
 public function testFromImportFalsy($input) {
    $this->expectException(InvalidDataException::class);
    SqlAttribute::fromImport($input);
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