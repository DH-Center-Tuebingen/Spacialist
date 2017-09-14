<?php

namespace App\Http\Controllers;
use App\User;
use App\ContextPhoto;
use App\Photo;
use App\PhotoTag;
use App\ThConcept;
use \DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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

    // OTHER FUNCTIONS

    private function exifDataExists($exif, $rootKey, $dataKey) {
        return array_key_exists($rootKey, $exif) && array_key_exists($dataKey, $exif[$rootKey]);
    }

    private function getFileById($id) {
        $user = \Auth::user();
        $img = \DB::table('photos as ph')
                ->select('id', 'modified', 'created', 'name as filename', 'thumb as thumbname', 'cameraname', 'orientation', 'description', 'copyright', 'photographer_id', 'mime_type')
                ->where('id', $id)
                ->first();
        if($img == null) return null;
        $img->url = Storage::disk('public')->url(env('SP_IMAGE_PATH') .'/'. $img->filename);
        if(substr($img->mime_type, 0, 6) === 'image/');
        $img->thumb_url = Storage::disk('public')->url(env('SP_IMAGE_PATH') .'/'. $img->thumbname);
        $img->linked_images = ContextPhoto::where('photo_id', '=', $img->id)->get();

        if($user->can('edit_photo_props')) {
            $img->tags = DB::table('photo_tags as p')
            ->join('th_concept as c', 'c.concept_url', '=', 'p.concept_url')
            ->select('c.id')
            ->where('p.photo_id', '=', $img->id)
            ->get();
        }

        // try to get file to check if it exists
        try {
            Storage::get($img->url);
            $img->filesize = Storage::size($img->url);
            $img->modified = Storage::lastModified($img->url);
        } catch(FileNotFoundException $e) {
        }
        $img->created = strtotime($img->created);
        return $img;
    }

    // GET

    public function getFiles() {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $images = DB::table('photos as ph')
                    ->select("ph.id as id", "ph.modified", "ph.created", "ph.name as filename", "ph.thumb as thumbname", "ph.cameraname", "ph.orientation", "ph.description", "ph.copyright", "ph.photographer_id", "ph.mime_type")
                    ->orderBy('id', 'asc')
                    ->get();
        foreach($images as &$img) {
            $img->url = Storage::disk('public')->url(env('SP_IMAGE_PATH') .'/'. $img->filename);
            if(substr($img->mime_type, 0, 6) === 'image/');
            $img->thumb_url = Storage::disk('public')->url(env('SP_IMAGE_PATH') .'/'. $img->thumbname);
            $img->linked_images = ContextPhoto::where('photo_id', '=', $img->id)->get();

            if($user->can('edit_photo_props')) {
                $img->tags = DB::table('photo_tags as p')
                ->join('th_concept as c', 'c.concept_url', '=', 'p.concept_url')
                ->select('c.id')
                ->where('p.photo_id', '=', $img->id)
                ->get();
            }

            // try to get file to check if it exists
            try {
                Storage::get($img->url);
                $img->filesize = Storage::size($img->url);
                $img->modified = Storage::lastModified($img->url);
            } catch(FileNotFoundException $e) {
            }
            $img->created = strtotime($img->created);
        }
        return response()->json($images);
    }

    public function getFile($id) {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json($this->getFileById($id));
    }

    public function getAvailableTags() {
        $tags = DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id,
                    (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as label
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                WHERE c.concept_url = 'http://thesaurus.archeoinf.de/SpTestthesaurus/false_126'
                UNION
                SELECT br.narrower_id as id,
                    (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as label
                FROM top t, th_broaders br
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
            ORDER BY label
        ");

        return response()->json([
            'tags' => $tags
        ]);
    }

    public function getByContext($id) {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $images = DB::table('context_photos as cp')
            ->join('photos as p', 'p.id', '=', 'cp.photo_id')
            ->where('cp.context_id', '=', $id)
            ->get();
        return response()->json([
            'images' => $images
        ]);
    }

    // POST

    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $user = \Auth::user();
        if(!$user->can('manage_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

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
        $imgMime = 'image/';
        $mimeType = $file->getMimeType();
        $isImage = substr($mimeType, 0, strlen($imgMime)) === $imgMime;
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

        $photo = new Photo();
        $photo->modified = $mod;
        $photo->lasteditor = $user['name'];
        $photo->mime_type = $mimeType;
        $photo->name = $filename;

        if($isImage) {
            $photo->thumb = $thumbName;
            $photo->photographer_id = 1;
        }
        if($exifFound) {
            $photo->created = $dateOrig;
            $photo->cameraname = $model;
            $photo->orientation = $orientation;
            $photo->copyright = $copyright;
            $photo->description = $description;
        } else {
            $photo->created = $mod;
        }

        $photo->save();

        return response()->json($this->getFileById($photo->id));
    }

    // PATCH

    public function patchFileProperty(Request $request, $id) {
        $this->validate($request, [
            'property' => 'required',
            'value' => 'required'
        ]);

        $prop = $request->get('property');
        $val = $request->get('value');
        $photo = Photo::find($id);
        $photo->{$prop} = $val;
        $photo->save();
    }

    // PUT

    public function link(Request $request) {
        $this->validate($request, [
            'imgId' => 'required|integer',
            'ctxId' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $imgId = $request->get('imgId');
        $ctxId = $request->get('ctxId');

        $link = ContextPhoto::firstOrNew([
                'photo_id' => $imgId,
                'context_id' => $ctxId,
        ]);
        $link->lasteditor = $user['name'];
        $link->save();
        return response()->json();
    }

    public function addTag(Request $request) {
        $this->validate($request, [
            'photo_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('edit_photo_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $photoId = $request->get('photo_id');
        $tagId = $request->get('tag_id');

        $url = ThConcept::find($tagId)->concept_url;

        PhotoTag::firstOrCreate([
            'photo_id' => $photoId,
            'concept_url' => $url
        ]);
    }

    // DELETE

    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('manage_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $photo = Photo::find($id);
        $pathPrefix = 'images/';
        Storage::delete($pathPrefix . $photo->name);
        if($photo->thumb != null) Storage::delete($pathPrefix . $photo->thumb);
        $photo->delete();
    }

    public function unlink($pid, $cid) {
        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        DB::table('context_photos')
            ->where([
                ['photo_id', '=', $pid],
                ['context_id', '=', $cid]
            ])
            ->delete();
        return response()->json();
    }

    public function removeTag(Request $request) {
        $this->validate($request, [
            'photo_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('edit_photo_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $photoId = $request->get('photo_id');
        $tagId = $request->get('tag_id');

        $url = ThConcept::find($tagId)->concept_url;

        $tag = PhotoTag::where([
            [ 'photo_id', '=', $photoId ],
            [ 'concept_url', '=', $url ]
        ])->delete();
    }
}
