<?php

namespace App\File;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/***
 * This class manages a single directory inside a disk.
 * It ensures that all files that are uploaded or accessed will
 * be stored inside the specified directory.
 */
class Directory {
    private string $disk;
    private string $directory;

    public function __construct(string $directory, string $disk = 'local') {
        $this->disk = $disk;
        $this->directory = $directory;
    }

    /**
     * Validates if a file is inside the directory.
     *
     * @param string $filepath The path to the file
     * @return bool True if the file is inside the directory, false otherwise.
     */
    public function contains(string $filepath): bool {
        return
            Str::startsWith($filepath, $this->directory) &&
            Storage::disk($this->disk)->exists($filepath);
    }

    /**
     * Deletes a file relative to the disk path, if it
     * is inside the directory path.
     *
     * Otherwise it will be ignored.
     *
     * @param string $filepath The path to the file
     * @return bool True if the file was deleted, false otherwise.
     */
    public function delete(?string $filepath): bool {
        if(!$filepath){
            return false;
        }
        if($this->contains($filepath)){
            return Storage::disk($this->disk)->delete($filepath);
        }
        return false;
    }

    /**
     * Stores a file inside the directory.
     *
     * @param string $filename The filename
     * @param $file The file
     * @return string The path to the file
     */
    public function store(string $filename, $file): string {
        return $file->storeAs($this->directory, $filename, $this->disk);
    }

    /**
     * Downloads a file inside the directory.
     *
     * @param string $filepath The path to the file
     * @return Response|BinaryFileResponse The file as BinaryFileResponse or Response if the file is not inside the directory.
     */
    function download(string $filepath): Response | BinaryFileResponse {
        if($this->contains($filepath)){
            return DownloadHandler::makeFileResponse($filepath);
        }
        return response()->noContent();
    }
}