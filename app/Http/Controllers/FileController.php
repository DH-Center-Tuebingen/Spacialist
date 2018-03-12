<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // GET

    public function getFiles($page = 1) {
        $files = File::getPaginate($page);
        return response()->json($files);
    }

    public function getArchiveFileList($id) {
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }
        $content = $file->getArchiveFileList();
        return response()->json($content);

    }

    public function downloadArchivedFile(Request $request, $id) {
        $this->validate($request, [
            'p' => 'required|string'
        ]);

        $filepath = $request->get('p');

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ]);
        }
        $content = $file->getArchivedFileContent($filepath);
        return response($content);
    }

    // POST

    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $filehandle = fopen($file->getRealPath(), 'r');
        Storage::put(
            'images/' . $filename,
            $filehandle
        );
        fclose($filehandle);
        $url = storage_path() . '/app/images';
        //$file->move($url, $filename);
        $fileUrl = $url . '/' . $filename;
        $fileMime = 'image/';
        $mimeType = $file->getMimeType();
        $isImage = substr($mimeType, 0, strlen($fileMime)) === $fileMime;
        // check if current file is image
        if($isImage) {
            $THUMB_SUFFIX = "_thumb";
            $THUMB_WIDTH = 256;
            $EXP_SUFFIX = ".jpg";
            $EXP_FORMAT = "jpg";
            $ext = $file->getClientOriginalExtension();
            $cleanName = substr($filename, 0, strlen($filename)-strlen($ext)-1);
            $thumbName = $cleanName . $THUMB_SUFFIX . $EXP_SUFFIX;
            $thumbUrl = $url . '/' . $thumbName;

            $imageInfo = getimagesize($fileUrl);
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo[2];//$imageInfo['mime'];
            if($width > $THUMB_WIDTH) {
                switch($mime) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($fileUrl);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($fileUrl);
                        break;
                    case IMAGETYPE_GIF:
                        $image = imagecreatefromgif($fileUrl);
                        break;
                    default:
                        //echo "is of UNSUPPORTED type " . $imageInfo['mime'];
                        // use imagemagick to convert from unsupported file format to jpg, which is supported by native php
                        $im = new Imagick($fileUrl);
                        $fileUrl = $url . '/' . $cleanName . $EXP_SUFFIX;
                        $im->setImageFormat($EXP_FORMAT);
                        $im->writeImage($fileUrl);
                        $im->clear();
                        $im->destroy();
                        $image = imagecreatefromjpeg($fileUrl);
                }
                $scaled = imagescale($image, $THUMB_WIDTH);
                ob_start();
                imagejpeg($scaled/*, $thumbUrl*/);
                $image = ob_get_clean();
                Storage::put(
                    'images/' . $thumbName,
                    $image
                );
            } else {
                Storage::copy('images/' . $filename, 'images/' . $thumbName);
            }
        }

        $mod = date('Y-m-d H:i:s', filemtime($fileUrl));
        $exifFound = false;
        if($isImage && ($mime === IMAGETYPE_JPEG || $mime === IMAGETYPE_TIFF_II || $mime === IMAGETYPE_TIFF_MM)) {
            $exif = @exif_read_data($fileUrl, 'ANY_TAG', true);
            if($exif !== false) {
                if($this->exifDataExists($exif, 'IFD0', 'Make')) {
                    $make = $exif['IFD0']['Make'];
                }
                if($this->exifDataExists($exif, 'IFD0', 'Model')) {
                    $model = $exif['IFD0']['Model'];
                } else {
                    $model = '';
                }
                if($this->exifDataExists($exif, 'IFD0', 'Orientation')) {
                    $orientation = $exif['IFD0']['Orientation'];
                } else {
                    $orientation = 0;
                }
                if($this->exifDataExists($exif, 'IFD0', 'Copyright')) {
                    $copyright = $exif['IFD0']['Copyright'];
                } else {
                    $copyright = '';
                }
                if($this->exifDataExists($exif, 'IFD0', 'ImageDescription')) {
                    $description = $exif['IFD0']['ImageDescription'];
                } else {
                    $description = '';
                }
                if($this->exifDataExists($exif, 'EXIF', 'DateTimeOriginal')) {
                    $dateOrig = strtotime($exif['EXIF']['DateTimeOriginal']);
                    $dateOrig = date('Y-m-d H:i:s', $dateOrig);
                } else {
                    $dateOrig = $mod;
                }
                if($this->exifDataExists($exif, 'FILE', 'FileType')) {
                    $fileType = $exif['FILE']['FileType'];
                }
                if($this->exifDataExists($exif, 'FILE', 'MimeType')) {
                    $mimeType = $exif['FILE']['MimeType'];
                }
                if(isset($make)) $model = $model . " ($make)";
                $exifFound = true;
            }
        }

        $file = new File();
        $file->modified = $mod;
        $file->lasteditor = 'Admin'; // TODO
        $file->mime_type = $mimeType;
        $file->name = $filename;

        if($isImage) {
            $file->thumb = $thumbName;
            $file->photographer_id = 1;
        }
        if($exifFound) {
            $file->created = $dateOrig;
            $file->cameraname = $model;
            $file->orientation = $orientation;
            $file->copyright = $copyright;
            $file->description = $description;
        } else {
            $file->created = $mod;
        }

        $file->save();

        return response()->json($file);
    }

    // DELETE

    public function delete($id) {
        try {
            $file = File::findOrFail($id);
            $pathPrefix = 'images/';
            Storage::delete($pathPrefix . $file->name);
            if(isset($file->thumb)) {
                Storage::delete($pathPrefix . $file->thumb);
            }
            $file->delete();
        } catch(ModelNotFoundException $e) {
        }
    }
}
