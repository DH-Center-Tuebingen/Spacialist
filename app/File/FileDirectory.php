<?php

namespace App\File;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/***
 * This class manages a single directory inside a disk.
 * It ensures that all files that are uploaded or accessed will
 * be stored inside the specified directory. 
 */
class FileDirectory {

    private string $disk;
    private string $directory;

    public function __construct(string $disk, string $directory) {
        $this->disk = $disk;
        $this->directory = $directory;
    }


    /**
     * Validates if a file is inside the directory.
     * 
     * @param string $filepath The path to the file
     * @return bool True if the file is inside the directory, false otherwise.
     */
    public function validate(string $filepath): bool {
        return Str::startsWith($filepath, $this->directory);
    }

    /**
     * Deletes a file relative to the disk path, if it
     * is inside the directory path.
     * 
     * Otherwise it will be ignored.
     * 
     * @param string $filepath The path to the file
     * @return void
     */
    public function deleteRaw(string | null $filepath): void {
        if(!$filepath){
            return;
        }
        if($this->validate($filepath)){
            Storage::disk($this->disk)->delete($filepath);
        }
    }

    /**
     * Deletes a file with the filename inside the directory.
     * 
     * @param string $file The filename
     * @return void
     */
    function delete(string $file): void {
        Storage::disk($this->disk)->delete($this->directory . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * Stores a file inside the directory.
     * 
     * @param string $filename The filename
     * @param $file The file
     * @return string The path to the file
     */
    function store(string $filename, $file): string{
        return $file->storeAs($this->directory, $filename, $this->disk);
    }

    /**
     * Downloads a file inside the directory.
     * 
     * @param string $filepath The path to the file
     * @return File|null The file or null if the file is not inside the directory.
     */
    function download(string $filepath): StreamedResponse | null {
        if($this->validate($filepath)){
            return Storage::disk($this->disk)->download($filepath);
        }
        return null;
    }
}