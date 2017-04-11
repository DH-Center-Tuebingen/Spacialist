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
        $user = \Auth::user();
        if(!$user->can('manage_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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
                'lasteditor' => $user['name'],
                'photographer_id' => 1
            ]);
        return response()->json($this->getImageById($id));
    }

    public function link(Request $request) {
        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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
                'lasteditor' => $user['name']
            ]);
        return response()->json();
    }

    public function unlink(Request $request) {
        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json($this->getImageById($id));
    }

    private function getImageById($id) {
        $user = \Auth::user();
        $img = \DB::table('photos as ph')
                ->select('id', 'modified', 'created', 'name as filename', 'thumb as thumbname', 'cameraname', 'orientation', 'description', 'copyright', 'photographer_id')
                ->where('id', $id)
                ->first();
        if($img == null) return null;
        $img->url = 'images/' . $img->filename;
        $img->thumb_url = 'images/' . $img->thumbname;
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

    public function getImagePreviewObject($id) {
        $img = $this->getImageById($id);
        // try to get file to check if it exists
        try {
            $file = Storage::get($img->thumb_url);
            return 'data:image/jpeg;base64,' . base64_encode($file);
        } catch(FileNotFoundException $e) {
            return response()->json([
                'error' => 'image not found'
            ]);
        }
    }

    public function getImageObject($id) {
        $img = $this->getImageById($id);
        $file = Storage::get($img->url);
        return 'data:image/jpeg;base64,' . base64_encode($file);
    }

    public function getAll() {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $images = DB::table('photos as ph')
                    ->select("ph.id as id", "ph.modified", "ph.created", "ph.name as filename", "ph.thumb as thumbname", "ph.cameraname", "ph.orientation", "ph.description", "ph.copyright", "ph.photographer_id")
                    ->orderBy('id', 'asc')
                    ->get();
        foreach($images as &$img) {
            $img->url = 'images/' . $img->filename;
            $img->thumb_url = 'images/' . $img->thumbname;
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
        Storage::delete($pathPrefix . $photo->thumb);
        $photo->delete();
    }

    public function setProperty(Request $request) {
        $id = $request->get('photo_id');
        $prop = $request->get('property');
        $val = $request->get('value');
        $photo = Photo::find($id);
        $photo->{$prop} = $val;
        $photo->save();
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

    public function addTag(Request $request) {
        $user = \Auth::user();
        if(!$user->can('edit_photo_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $photoId = $request->get('photo_id');
        $tagId = $request->get('tag_id');

        $url = ThConcept::find($tagId)->concept_url;

        $tag = new PhotoTag();
        $tag->photo_id = $photoId;
        $tag->concept_url = $url;
        $tag->save();
    }

    public function removeTag(Request $request) {
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
