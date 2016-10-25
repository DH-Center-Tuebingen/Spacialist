<?php

namespace App\Http\Controllers;
use App\User;
use \DB;
use Illuminate\Http\Request;

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
        $file->move($url, $filename);
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
            imagejpeg($scaled, $thumbUrl);
        } else {
            copy($fileUrl, $thumbUrl);
        }
        if($mime === IMAGETYPE_JPEG || $mime === IMAGETYPE_TIFF_II || $mime === IMAGETYPE_TIFF_MM && ($exif = exif_read_data($fileUrl, 'ANY_TAG', true)) !== false) {
            $make = $exif['IFD0']['Make'];
            $model = $exif['IFD0']['Model'];
            $orientation = $exif['IFD0']['Orientation'];
            $copyright = $exif['IFD0']['Copyright'];
            $description = $exif['IFD0']['ImageDescription'];
            $dateOrig = strtotime($exif['EXIF']['DateTimeOriginal']);
            $dateOrig = date('Y-m-d H:i:s', $dateOrig);
            $fileType = $exif['FILE']['FileType'];
            $mimeType = $exif['FILE']['MimeType'];
            $model = $model . " ($make)";
        } else {
            $make = NULL;
            $model = NULL;
            $orientation = 0;
            $copyright = NULL;
            $description = NULL;
            $dateOrig = date('Y-m-d H:i:s');
        }
        $mod = date('Y-m-d H:i:s', filemtime($fileUrl));
        $id = \DB::table('ph_photo')
            ->insertGetId([
                'modified' => $mod,
                'name' => $filename,
                'thumb' => $thumbName,
                'cameraname' => $model,
                'orientation' => $orientation,
                'copyright' => $copyright,
                'description' => $description,
                'film_id' => 1,
                'photographer_id' => 1,
                'create_time' => $dateOrig
            ]);
        return response()->json($this->getImageById($id));
    }

    public function getImage($id) {
        return response()->json($this->getImageById($id));
    }

    private function getImageById($id) {
        $img = \DB::table('ph_photo as ph')
                ->join('ph_film as film', 'ph.film_id', 'film.id')
                ->select('ph.id as id', 'ph.modified as modified', 'ph.name as filename', 'ph.thumb as thumbname', 'ph.cameraname', 'ph.create_time as created', 'ph.orientation', 'ph.description', 'ph.copyright', 'ph.photographer_id', 'film.id as film_id', 'film.modified as film_modified', 'film.name as filmname', 'film.url as url')
                ->where('ph.id', $id)
                ->first();
        $img->url = storage_path() . '/' . $img->url;
        $img->thumb_url = $img->url . $img->thumbname;
        $img->url = $img->url . $img->filename;
        $img->filesize = filesize($img->url);
        $img->modified = strtotime($img->modified) * 1000;
        $img->created = strtotime($img->created) * 1000;
        return $img;
    }

    //TODO add 'linking images' functionality
    public function getByContext($id) {
        $find = DB::table('finds')->get()->where('id', $id)->first();
        return response()->json();
    }

    public function getAll() {
        $images = DB::table('ph_photo as ph')
                    ->join('ph_film as film', 'ph.film_id', 'film.id')
                    ->select("ph.id as id", "ph.modified as modified", "ph.name as filename", "ph.thumb as thumbname", "ph.cameraname", "ph.create_time as created", "ph.orientation", "ph.description", "ph.copyright", "ph.photographer_id", "film.id as film_id", "film.modified as film_modified", "film.name as filmname", "film.url as url")
                    ->get();
        foreach($images as &$img) {
            $img->thumb_url = storage_path($img-> url . $img->thumbname);
            $img->url = storage_path(img->url . $img->filename);
            if(file_exists($img->url) && is_file($img->url)) $img->filesize = filesize($img->url);
            $img->modified = strtotime($img->modified) * 1000;
            $img->created = strtotime($img->created) * 1000;
        }
        return response()->json($images);
    }
}
