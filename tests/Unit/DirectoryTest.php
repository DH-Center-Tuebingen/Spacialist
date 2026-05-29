<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\File\Directory;

class DirectoryTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Storage::persistentFake('test');
        Storage::disk('test')->makeDirectory('test');
        Storage::disk('test')->makeDirectory('test/dir1');
        Storage::disk('test')->put('test/dir1/file_1.txt', 'Test content #1');
        Storage::disk('test')->makeDirectory('test/dir2');
        Storage::disk('test')->put('test/dir2/file_2.txt', 'Test content #2');
        Storage::disk('test')->makeDirectory('external');
        Storage::disk('test')->put('external/file.txt', 'External file content #1');
        Storage::disk('test')->makeDirectory('external/subdir');
        Storage::disk('test')->put('external/subdir/file.txt', 'External file content #2');

        Storage::disk('test')->makeDirectory('application');
        Storage::disk('test')->put('application/file.txt', 'Application file content');
    }


    /**
     * Capture the file stream from a response
     *
     * @param Illuminate\Http\JsonResponse $response The response object
     * @return string The content of the file stream
     */
    private function captureFileStream($response){
        ob_start();
        $response->sendContent();
        return ob_get_clean();
    }

    private function assertFileContents($response, $expectedContent)
    {
        $content = $this->captureFileStream($response);
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * Test sp_column_names helper
     *
     * @return void
     */
    public function testDirectoryConstruction()
    {
        $directory = new Directory('test', 'test');
        $this->assertEquals('test', $directory->getDirectory());
        $this->assertEquals('test', $directory->getDisk());
    }

    public function testDefaulDisk()
    {
        $directory = new Directory('test');
        $this->assertEquals('local', $directory->getDisk());
    }

    public function testContains()
    {
        $directory = new Directory('test', 'test');
        $this->assertTrue($directory->contains('test/dir1/file_1.txt'));
        $this->assertFalse($directory->contains('test/file.txt'));
    }

    public function testDelete()
    {
        $directory = new Directory('test', 'test');

        // Delete a file inside the directory
        $this->assertTrue($directory->delete('test/dir1/file_1.txt'));
        $this->assertFalse(Storage::disk('test')->exists('test/dir1/file_1.txt'));

        // Try to delete a file outside the directory
        $this->assertFalse($directory->delete('external/file.txt'));
        $this->assertTrue(Storage::disk('test')->exists('external/file.txt'));

        // Try to delete external file in subdirectory
        $this->assertFalse($directory->delete('external/subdir/file.txt'));
        $this->assertTrue(Storage::disk('test')->exists('external/subdir/file.txt'));
    }

    public function testDownload()
    {
        $directory = new Directory('test', 'test');

        // Download a file inside the directory
        $response = $directory->download('test/dir1/file_1.txt');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertFileContents($response, 'Test content #1');

        // Try to download a file outside the directory
        $response = $directory->download('external/file.txt');
        $this->assertStatus($response, 404);

        // Try to download a file in subdirectory
        $response = $directory->download('external/subdir/file.txt');
        $this->assertStatus($response, 404);
    }

    public function testDownloadRelative(){
        $directory = new Directory('test', 'test');

        // Download a file inside the directory with relative path
        $response = $directory->downloadRelative('dir1/file_1.txt');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertFileContents($response, 'Test content #1');

        // Try to download a file outside the directory with relative path
        $response = $directory->downloadRelative('external/file.txt');
        $this->assertStatus($response, 404);

        // Try to download a file in subdirectory with relative path
        $response = $directory->downloadRelative('external/subdir/file.txt');
        $this->assertStatus($response, 404);
    }

    public function testStore(){
        $directory = new Directory('test', 'test');

        $uniqueFile = Carbon::now()->timestamp . '_tmp_test_file.txt';

        // Store a file inside the directory
        $filePath = $directory->store('stored_test_file.txt', UploadedFile::fake()->create($uniqueFile, 100));
        $this->assertTrue(Storage::disk('test')->exists($filePath));
    }

    public function testPut(){
        $directory = new Directory('test', 'test');

        $path = Storage::disk('test')->path("application/file.txt");
        $filehandle = fopen($path, 'r');

        // Store a file inside the directory using put
        $filePath = $directory->store('put_test_file.txt', $filehandle);
        $this->assertTrue(Storage::disk('test')->exists($filePath));
    }
}
