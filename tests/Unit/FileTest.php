<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\File;
use App\EntityFile;

// TODO Move to File Plugin
class FileTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
    // /**
    //  * Test getting subfiles of an entity.
    //  *
    //  * @return void
    //  */
    // public function testGetSubfilesFunction()
    // {
    //     // Test text sub-files for entity (id=1)
    //     $files = File::getSubFiles(1, 'text');
    //     $this->assertEquals(1, $files->count());
    //     $this->assertEquals(3, $files[0]->id);
    //     $this->assertEquals('text1.txt', $files[0]->name);

    //     // Test text sub-files for entity (id=2)
    //     $files = File::getSubFiles(2, 'text');
    //     $this->assertEquals(1, $files->count());
    //     $this->assertEquals(2, $files[0]->id);
    //     $this->assertEquals('text2.txt', $files[0]->name);
    //     // Test image sub-files for entity (id=2)
    //     $files = File::getSubFiles(2, 'image');
    //     $this->assertEquals(1, $files->count());
    //     $this->assertEquals(4, $files[0]->id);
    //     $this->assertEquals('spacialist_screenshot.png', $files[0]->name);
    //     // Test archive sub-files for entity (id=2)
    //     $files = File::getSubFiles(2, 'archive');
    //     $this->assertEquals(1, $files->count());
    //     $this->assertEquals(6, $files[0]->id);
    //     $this->assertEquals('test_archive.zip', $files[0]->name);
    //     // Test empty sub-file categories for entity (id=2)
    //     $files = File::getSubFiles(2, 'audio');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'video');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'pdf');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'xml');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'html');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, '3d');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'dicom');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'document');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'spreadsheet');
    //     $this->assertEquals(0, $files->count());
    //     $files = File::getSubFiles(2, 'presentation');
    //     $this->assertEquals(0, $files->count());
    // }

    // /**
    //  * Test get last editor of first file
    //  *
    //  * @return void
    //  */
    // public function testGetReferenceEntryLasteditor()
    // {
    //     $file = File::first();
    //     $this->assertEquals(1, $file->user->id);
    //     $link = EntityFile::first();
    //     $this->assertEquals(1, $link->user->id);
    // }
}
