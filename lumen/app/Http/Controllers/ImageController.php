<?php

namespace App\Http\Controllers;
use App\User;
use \DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
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

    private function exifDataExists($exif, $rootKey, $dataKey) {
        return array_key_exists($rootKey, $exif) && array_key_exists($dataKey, $exif[$rootKey]);
    }

    public function uploadImage(Request $request) {
        if(!$request->hasFile('file') || !$request->file('file')->isValid()) return response()->json('null');
        $THUMB_SUFFIX = "_thumb";
        $THUMB_WIDTH = 256;
        $EXP_SUFFIX = ".jpg";
        $EXP_FORMAT = "jpg";
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        $cleanName = substr($filename, 0, strlen($filename)-strlen($ext)-1);
        $thumbName = $cleanName . $THUMB_SUFFIX . $EXP_SUFFIX;
        $url = storage_path() . '/app/images';
        //$file->move($url, $filename);
        Storage::put(
            'images/' . $filename,
            file_get_contents($file->getRealPath())
        );
        $fileUrl = $url . '/' . $filename;
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

        $mod = date('Y-m-d H:i:s', filemtime($fileUrl));
        $exifFound = false;
        if($mime === IMAGETYPE_JPEG || $mime === IMAGETYPE_TIFF_II || $mime === IMAGETYPE_TIFF_MM) {
            $exif = exif_read_data($fileUrl, 'ANY_TAG', true);
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

        if(!$exifFound) {
            $dateOrig = $mod;
            $model = '';
            $orientation = 0;
            $copyright = '';
            $description = '';
        }

        $lasteditor = 'postgres';

        $id = \DB::table('photos')
            ->insertGetId([
                'modified' => $mod,
                'created' => $dateOrig,
                'name' => $filename,
                'thumb' => $thumbName,
                'cameraname' => $model,
                'orientation' => $orientation,
                'copyright' => $copyright,
                'description' => $description,
                'lasteditor' => $lasteditor,
                'photographer_id' => 1
            ]);
        return response()->json($this->getImageById($id));
    }

    public function link(Request $request) {
        if(!$request->has('imgId') || !$request->has('ctxId')) {
            return response()->json([
                'error' => 'Either the ID for the image or the context is missing.'
            ]);
        }
        $imgId = $request->get('imgId');
        $ctxId = $request->get('ctxId');

        DB::table('context_photos')
            ->insert([
                'photo_id' => $imgId,
                'context_id' => $ctxId,
                'lasteditor' => 'postgres'
            ]);
        return response()->json();
    }

    public function unlink(Request $request) {
        if(!$request->has('imgId') || !$request->has('ctxId')) {
            return response()->json([
                'error' => 'Either the ID for the image or the context is missing.'
            ]);
        }
        $imgId = $request->get('imgId');
        $ctxId = $request->get('ctxId');

        DB::table('context_photos')
            ->where([
                ['photo_id', '=', $imgId],
                ['context_id', '=', $ctxId]
            ])
            ->delete();
        return response()->json();
    }

    public function getImage($id) {
        return response()->json($this->getImageById($id));
    }

    private function getImageById($id) {
        $img = \DB::table('photos as ph')
                ->select('ph.id as id', 'ph.modified', 'ph.created', 'ph.name as filename', 'ph.thumb as thumbname', 'ph.cameraname', 'ph.orientation', 'ph.description', 'ph.copyright', 'ph.photographer_id')
                ->where('ph.id', $id)
                ->first();
        if($img == null) return null;
        $img->url = 'images/' . $img->filename;
        $img->thumb_url = 'images/' . $img->thumbname;
        $img->filesize = Storage::size($img->url);
        $img->modified = Storage::lastModified($img->url);
        $img->created = strtotime($img->created);
        return $img;
    }

    public function getByContext($id) {
        $images = DB::table('context_photos as cp')
            ->join('photos as p', 'p.id', '=', 'cp.photo_id')
            ->where('cp.context_id', '=', $id)
            ->get();
        return response()->json([
            'images' => $images
        ]);
    }

    public function getImagePreviewObject($id) {
        $img = $this->getImageById($id);
        $file = Storage::get($img->thumb_url);
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }

    public function getImageObject($id) {
        $img = $this->getImageById($id);
        $file = Storage::get($img->url);
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }

    public function getAll() {
        $images = DB::table('photos as ph')
                    ->select("ph.id as id", "ph.modified", "ph.created", "ph.name as filename", "ph.thumb as thumbname", "ph.cameraname", "ph.orientation", "ph.description", "ph.copyright", "ph.photographer_id")
                    ->get();
        foreach($images as &$img) {
            $img->url = 'images/' . $img->filename;
            $img->thumb_url = 'images/' . $img->thumbname;
            $img->filesize = Storage::size($img->url);
            $img->modified = Storage::lastModified($img->url);
            $img->created = strtotime($img->created);
        }
        return response()->json($images);
    }
}
