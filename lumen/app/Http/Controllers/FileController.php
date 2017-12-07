<?php

namespace App\Http\Controllers;
use App\User;
use App\Context;
use App\ContextFile;
use App\File;
use App\FileTag;
use App\ThConcept;
use App\Helpers;
use App\Preference;
use \DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use lsolesen\pel\Pel;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelDataWindowOffsetException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \wapmorgan\UnifiedArchive\UnifiedArchive;

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

    private $imageMime = 'image/';

    // OTHER FUNCTIONS

    private function exifDataExists($exif, $rootKey, $dataKey) {
        return array_key_exists($rootKey, $exif) && array_key_exists($dataKey, $exif[$rootKey]);
    }

    private function extractFromIfd($ifd, &$values) {
        foreach($ifd->getEntries() as $entry) {
            $name = PelTag::getName($entry->getIfdType(), $entry->getTag());
            if($entry->getIfdType() !== PelIfd::IFD0 && $entry->getIfdType() !== PelIfd::IFD1) {
                $key = PelIfd::getTypeName($entry->getIfdType());
                if(!isset($values[$key])) {
                    $values[$key] = [];
                }
                $values[$key][$name] = $entry->getText();
            } else {
                $values[$name] = $entry->getText();
            }
        }
        foreach($ifd->getSubIfds() as $sifd) {
            $this->extractFromIfd($sifd, $values);
        }
        if(!$ifd->isLastIfd()) {
            $this->extractFromIfd($ifd->getNextIfd(), $values);
        }
    }

    private function getExifData($file) {
        $mimeType = $file->mime_type;
        $isImage = substr($mimeType, 0, strlen($this->imageMime)) === $this->imageMime;
        if(!$isImage) return null;
        $url = Helpers::getStorageFilePath($file->filename);
        if(Helpers::startsWith($url, '/')) {
            $url = substr($url, 1);
        }
        if(!\Illuminate\Support\Facades\File::exists($url)) {
            return null;
        }
        $data = new PelDataWindow(file_get_contents($url));
        if(PelJpeg::isValid($data)) {
            $jpg = new PelJpeg();
            $jpg->load($data);
            $app1 = $jpg->getExif();
            if($app1 == null) {
                return null;
            }
            $ifd = $app1->getTiff()->getIfd();
            $values = [];
            $this->extractFromIfd($ifd, $values);
            return $values;
        } else if(PelTiff::isValid($data)) {
        } else {
            return null;
        }
        return null;
    }

    private function getLinkedContexts($file) {
        $links = Context::where('photo_id', $file->id)
            ->join('context_photos', 'context_id', '=', 'contexts.id')
            ->select('name', 'contexts.id')
            ->get();
        return $links;
    }

    private function getFileById($id) {
        $user = \Auth::user();
        $file = \DB::table('photos as ph')
                ->select('id', 'modified', 'created', 'name as filename', 'thumb as thumbname', 'cameraname', 'orientation', 'description', 'copyright', 'photographer_id', 'mime_type')
                ->where('id', $id)
                ->first();
        if($file == null) return null;
        $file->url = Helpers::getFullFilePath($file->filename);
        if(substr($file->mime_type, 0, 6) === 'image/') {
            $file->thumb_url = Helpers::getFullFilePath($file->thumbname);
        }
        $file->linked_files = ContextFile::where('photo_id', '=', $file->id)->get();

        if($user->can('edit_photo_props')) {
            $file->tags = DB::table('photo_tags as p')
            ->join('th_concept as c', 'c.concept_url', '=', 'p.concept_url')
            ->select('c.id', 'p.concept_url as uri')
            ->where('p.photo_id', '=', $file->id)
            ->get();
        }

        // try to get file to check if it exists
        $storageUrl = $file->filename;
        $disk = Helpers::getDisk();
        if(Storage::disk($disk)->exists($storageUrl)) {
            $file->filesize = Storage::disk($disk)->size($storageUrl);
            $file->modified = Storage::disk($disk)->lastModified($storageUrl);
            try {
                $file->exif = $this->getExifData($file);
            } catch(PelDataWindowOffsetException $e) {
                // Do nothing for now
            }
        }
        $file->created = strtotime($file->created);
        $file->linked_contexts = $this->getLinkedContexts($file);
        return $file;
    }

    private function getFileTreeFromZip($files, $archive, $prefix = '') {
        $tree = [];
        $subfolders = [];
        $folders = [];
        foreach($files as $file) {
            $isInSubfolder = false;
            foreach($subfolders as $fkey) {
                if(Helpers::startsWith($file, $fkey)) {
                    $isInSubfolder = true;
                    $subname = substr($file, strlen($fkey));
                    $folders[$fkey][] = $subname;
                    break;
                }
            }
            if($isInSubfolder) continue;
            $isDirectory = false;
            // check if "file" is folder
            if(Helpers::endsWith($file, '/')) {
                $isDirectory = true;
                $subfolders[] = $file;
                $folders[$file] = [];
            } else {
                $isDirectory = false;
            }
            $data = $archive->getFileData($prefix.$file);
            $data->is_directory = $isDirectory;
            $data->clean_filename = $file;
            $tree[$file] = $data;
        }
        foreach($folders as $fkey => $subfiles) {
            $tree[$fkey]->children = $this->getFileTreeFromZip($subfiles, $archive, $prefix.$fkey);
        }
        return $tree;
    }

    // GET

    public function getFiles() {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $files = DB::table('photos as ph')
                    ->select("ph.id as id", "ph.modified", "ph.created", "ph.name as filename", "ph.thumb as thumbname", "ph.cameraname", "ph.orientation", "ph.description", "ph.copyright", "ph.photographer_id", "ph.mime_type")
                    ->orderBy('id', 'asc')
                    ->get();
        foreach($files as &$file) {
            $file->url = Helpers::getFullFilePath($file->filename);
            if(substr($file->mime_type, 0, 6) === 'image/') {
                $file->thumb_url = Helpers::getFullFilePath($file->thumbname);
            }
            $file->linked_files = ContextFile::where('photo_id', '=', $file->id)->get();

            if($user->can('edit_photo_props')) {
                $file->tags = DB::table('photo_tags as p')
                ->join('th_concept as c', 'c.concept_url', '=', 'p.concept_url')
                ->select('c.id', 'p.concept_url as uri')
                ->where('p.photo_id', '=', $file->id)
                ->get();
            }

            // try to get file to check if it exists
            $storageUrl = $file->filename;
            $disk = Helpers::getDisk();
            if(Storage::disk($disk)->exists($storageUrl)) {
                $file->filesize = Storage::disk($disk)->size($storageUrl);
                $file->modified = Storage::disk($disk)->lastModified($storageUrl);
                try {
                    $file->exif = $this->getExifData($file);
                } catch(PelDataWindowOffsetException $e) {
                    // Do nothing for now
                }
            }
            $file->created = strtotime($file->created);
            $file->linked_contexts = $this->getLinkedContexts($file);
        }
        return response()->json($files);
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

    public function getFileFromLink($filename) {
        $file = File::where('name', $filename)->orWhere('thumb', $filename)->first();
        if(isset($file)) {
            return response(Storage::disk(Helpers::getDisk())->get($file->name))
                ->header('Content-Type', $file->mime_type);
        }
        return '';
    }

    public function getAvailableTags() {
        $tagObj = Preference::where('label', 'prefs.tag-root')->value('default_value');
        $tagUri = json_decode($tagObj)->uri;
        $tags = DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id, c2.concept_url as uri
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                JOIN th_concept c2 ON c2.id = br.narrower_id
                WHERE c.concept_url = '$tagUri'
                UNION
                SELECT br.narrower_id as id, concept_url as uri
                FROM top t, th_broaders br
                JOIN th_concept c ON c.id = br.narrower_id
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
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
        $files = DB::table('context_photos as cp')
            ->join('photos as p', 'p.id', '=', 'cp.photo_id')
            ->where('cp.context_id', '=', $id)
            ->get();
        return response()->json([
            'files' => $files
        ]);
    }

    public function getZipContents($id) {
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ]);
        }
        $path = '.' . Helpers::getStorageFilePath($file->name);
        $archive = UnifiedArchive::open($path);
        $files = $this->getFileTreeFromZip($archive->getFileNames(), $archive);
        return response()->json($files);
    }

    public function downloadInnerZipFile($id, $filepath) {
        // $filepath = urldecode($filepath);
        \Log::info("Going to download: " . $filepath);
        $user = \Auth::user();
        if(!$user->can('view_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ]);
        }
        $path = '.' . Helpers::getStorageFilePath($file->name);
        $archive = UnifiedArchive::open($path);
        $content = base64_encode($archive->getFileContent($filepath));
        return response($content);
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
        $realPath = $file->getRealPath();
        $filehandle = fopen($realPath, 'r');
        Storage::disk(Helpers::getDisk())->put(
            $filename,
            $filehandle
        );
        fclose($filehandle);
        $fileUrl = Helpers::getFullFilePath($filename);
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

            $imageInfo = getimagesize($realPath);
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo[2];//$imageInfo['mime'];
            if($width > $THUMB_WIDTH) {
                switch($mime) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($realPath);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($realPath);
                        break;
                    case IMAGETYPE_GIF:
                        $image = imagecreatefromgif($realPath);
                        break;
                    default:
                        //echo "is of UNSUPPORTED type " . $imageInfo['mime'];
                        // use imagemagick to convert from unsupported file format to jpg, which is supported by native php
                        $im = new Imagick($realPath);
                        $fileUrl = $url . '/' . $cleanName . $EXP_SUFFIX;
                        $im->setImageFormat($EXP_FORMAT);
                        $im->writeImage($fileUrl);
                        $im->clear();
                        $im->destroy();
                        $image = imagecreatefromjpeg($fileUrl);
                }
                $scaled = imagescale($image, $THUMB_WIDTH);
                ob_start();
                imagejpeg($scaled);
                $image = ob_get_clean();
                Storage::disk(Helpers::getDisk())->put(
                    $thumbName,
                    $image
                );
            } else {
                Storage::disk(Helpers::getDisk())->copy($filename, $thumbName);
            }
        }

        $mod = date('Y-m-d H:i:s', Storage::disk(Helpers::getDisk())->lastModified($filename));
        // $mod = date('Y-m-d H:i:s', filemtime('../'.$fileUrl));
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
        $file->lasteditor = $user['name'];
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

        return response()->json($this->getFileById($file->id));
    }

    // PATCH

    public function patchFileProperty(Request $request, $id) {
        $this->validate($request, [
            'property' => 'required',
            'value' => 'required'
        ]);

        $prop = $request->get('property');
        $val = $request->get('value');
        $file = File::find($id);
        $file->{$prop} = $val;
        $file->save();
    }

    // PUT

    public function link(Request $request) {
        $this->validate($request, [
            'file_id' => 'required|integer',
            'ctxId' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $fileId = $request->get('file_id');
        $ctxId = $request->get('ctxId');

        $link = ContextFile::firstOrNew([
                'photo_id' => $fileId,
                'context_id' => $ctxId,
        ]);
        $link->lasteditor = $user['name'];
        $link->save();
        return response()->json();
    }

    public function addTag(Request $request) {
        $this->validate($request, [
            'file_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('edit_photo_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $fileId = $request->get('file_id');
        $tagId = $request->get('tag_id');

        $url = ThConcept::find($tagId)->concept_url;

        FileTag::firstOrCreate([
            'photo_id' => $fileId,
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
        $file = File::find($id);
        Storage::disk(Helpers::getDisk())->delete($file->name);
        if($file->thumb != null) Storage::disk(Helpers::getDisk())->delete($file->thumb);
        $file->delete();
    }

    public function unlink($fid, $cid) {
        $user = \Auth::user();
        if(!$user->can('link_photos')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        ContextPhoto::where([
            ['photo_id', '=', $fid],
            ['context_id', '=', $cid]
        ])->delete();
        return response()->json();
    }

    public function removeTag($fid, $tid) {
        $user = \Auth::user();
        if(!$user->can('edit_photo_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $url = ThConcept::find($tid)->concept_url;

        $tag = FileTag::where([
            [ 'photo_id', '=', $fid ],
            [ 'concept_url', '=', $url ]
        ])->delete();
    }
}
