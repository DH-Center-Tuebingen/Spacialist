<?php

namespace App\File;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
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
     * Returns the disk name.
     * @return string The disk name, e.g. 'local', 'public', etc.
     */
    public function getDisk(): string {
        return $this->disk;
    }

    /**
     * Returns the directory path.
     * @return string The directory path, e.g. 'avatars'
     */
    public function getDirectory(): string {
        return $this->directory;
    }
    
    public function getDirectoryPath(string $subpath = ""): string {
        $path = Str::finish($this->directory, DIRECTORY_SEPARATOR) . $subpath;
        return Storage::disk($this->disk)->path($path);
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

    // TODO: resource is not allowed as type (php 8.3), thus $file only typehinted in docblock
    /**
     * Stores a file inside the directory.
     *
     * @param string $filename The filename
     * @param UploadedFile|string|resource $file The file
     * @return string The path to the file
     */
    public function store(string $filename, $file): string|false {
        if($file instanceof UploadedFile) {
            return $file->storeAs($this->directory, $filename, $this->disk);
        } else {
            $filepath = Str::finish($this->directory, DIRECTORY_SEPARATOR) . $filename;
            Storage::disk($this->disk)->put($filepath, $file);
            return $filepath;
        }
    }

    /**
     * Downloads a file inside the directory.
     *
     * @param string $filepath The path to the file
     * @return JsonResponse|BinaryFileResponse The file as BinaryFileResponse or Response if the file is not inside the directory.
     */
    function download(string $filepath): JsonResponse | BinaryFileResponse {
        if($this->contains($filepath)) {
            return $this->createFileResponse($filepath);
        }
        return self::notFound();
    }

    /**
     * Downloads a file relative to the directory.
     *
     * @param string $filepath The path to the file without the directory prefix.
     * @return JsonResponse|BinaryFileResponse The file as BinaryFileResponse or Response if the file is not inside the directory.
     */
    function downloadRelative(string $filepath): JsonResponse | BinaryFileResponse {
        $storagePath = Str::finish($this->directory, DIRECTORY_SEPARATOR) . $filepath;
        return $this->download($storagePath);
    }

    function createFileResponse(string $filepath): JsonResponse | BinaryFileResponse {
        $mime = Storage::disk($this->disk)->mimeType($filepath);
        $path = Storage::disk($this->disk)->path($filepath);
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'. $filepath . '"'
        ]);
    }

    private static function notFound(): JsonResponse {
        return response()->json([
            'error' => __('File not found.')
        ], 404);
    }
}