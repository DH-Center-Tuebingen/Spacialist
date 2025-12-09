<?php

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\File\Directory;


beforeEach(function () {
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
});

/**
 * Capture the file stream from a response
 *
 * @param Illuminate\Http\JsonResponse $response The response object
 * @return string The content of the file stream
 */
function captureFileStream($response)
{
    ob_start();
    $response->sendContent();
    return ob_get_clean();
}

function assertFileContents($response, $expectedContent)
{
    $content = captureFileStream($response);
    expect($content)->toEqual($expectedContent);
}

test('directory construction', function () {
    $directory = new Directory('test', 'test');
    expect($directory->getDirectory())->toEqual('test');
    expect($directory->getDisk())->toEqual('test');
});

test('defaul disk', function () {
    $directory = new Directory('test');
    expect($directory->getDisk())->toEqual('local');
});

test('contains', function () {
    $directory = new Directory('test', 'test');
    expect($directory->contains('test/dir1/file_1.txt'))->toBeTrue();
    expect($directory->contains('test/file.txt'))->toBeFalse();
});

test('delete', function () {
    $directory = new Directory('test', 'test');

    // Delete a file inside the directory
    expect($directory->delete('test/dir1/file_1.txt'))->toBeTrue();
    expect(Storage::disk('test')->exists('test/dir1/file_1.txt'))->toBeFalse();

    // Try to delete a file outside the directory
    expect($directory->delete('external/file.txt'))->toBeFalse();
    expect(Storage::disk('test')->exists('external/file.txt'))->toBeTrue();

    // Try to delete external file in subdirectory
    expect($directory->delete('external/subdir/file.txt'))->toBeFalse();
    expect(Storage::disk('test')->exists('external/subdir/file.txt'))->toBeTrue();
});

test('download', function () {
    $directory = new Directory('test', 'test');

    // Download a file inside the directory
    $response = $directory->download('test/dir1/file_1.txt');
    expect(200)->toEqual($response->getStatusCode());
    assertFileContents($response, 'Test content #1');

    // Try to download a file outside the directory
    $response = $directory->download('external/file.txt');
    $this->assertStatus($response, 404);

    // Try to download a file in subdirectory
    $response = $directory->download('external/subdir/file.txt');
    $this->assertStatus($response, 404);
});

test('download relative', function () {
    $directory = new Directory('test', 'test');

    // Download a file inside the directory with relative path
    $response = $directory->downloadRelative('dir1/file_1.txt');
    expect(200)->toEqual($response->getStatusCode());
    assertFileContents($response, 'Test content #1');

    // Try to download a file outside the directory with relative path
    $response = $directory->downloadRelative('external/file.txt');
    $this->assertStatus($response, 404);

    // Try to download a file in subdirectory with relative path
    $response = $directory->downloadRelative('external/subdir/file.txt');
    $this->assertStatus($response, 404);
});

test('store', function () {
    $directory = new Directory('test', 'test');

    $uniqueFile = Carbon::now()->timestamp . '_tmp_test_file.txt';

    // Store a file inside the directory
    $filePath = $directory->store('stored_test_file.txt', UploadedFile::fake()->create($uniqueFile, 100));
    expect(Storage::disk('test')->exists($filePath))->toBeTrue();
    info("Stored file at: " . $filePath);
});

test('put', function () {
    $directory = new Directory('test', 'test');

    $path = Storage::disk('test')->path("application/file.txt");
    $filehandle = fopen($path, 'r');

    // Store a file inside the directory using put
    $filePath = $directory->store('put_test_file.txt', $filehandle);
    expect(Storage::disk('test')->exists($filePath))->toBeTrue();
});
