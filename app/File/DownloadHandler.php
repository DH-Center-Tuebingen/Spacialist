<?php

namespace App\File;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadHandler {
    public static function makeFileResponse(string $filepath): BinaryFileResponse|Response {
        if(!isset($filepath) || !Storage::exists($filepath)) {
            return response()->noContent();
        }
        $mime = Storage::mimeType($filepath);
        $path = Storage::path($filepath);
        return response()
            ->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filepath.'"'
            ]);
    }
}