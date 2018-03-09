<?php

namespace App;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use lsolesen\pel\Pel;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelDataWindowOffsetException;
use lsolesen\pel\PelJpegInvalidMarkerException;
use wapmorgan\UnifiedArchive\UnifiedArchive;

class File extends Model
{
    protected $table = 'photos';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'modified',
        'cameraname',
        'photographer_id',
        'created',
        'thumb',
        'orientation',
        'copyright',
        'description',
        'lasteditor',
    ];

    protected $appends = [
        'category',
        'exif'
    ];

    private $imageMime = 'image/';

    public static function getPaginate($page) {
        $files = self::with(['contexts'])->paginate(35);
        $files->withPath('/file');

        foreach($files as &$file) {
            $file->url = Helpers::getFullFilePath($file->name);
            if($file->isImage()) {
                $file->thumb_url = Helpers::getFullFilePath($file->thumb);
            }

            try {
                Storage::get($file->name);
                $file->size = Storage::size($file->name);
                $file->modified = Storage::lastModified($file->name);
            } catch(FileNotFoundException $e) {
            }

            $file->created = strtotime($file->created);
        }

        return $files;
    }

    public function getZipContent() {
        $path = '.' . Helpers::getStorageFilePath($this->name);
        $archive = UnifiedArchive::open($path);
        return $this->getFileTreeFromZip($archive->getFileNames(), $archive);
    }

    public function getZipFileContent($filepath) {
        $path = '.' . Helpers::getStorageFilePath($this->name);
        $archive = UnifiedArchive::open($path);
        return base64_encode($archive->getFileContent($filepath));
    }

    private function getFileTreeFromZip($files, $archive, $prefix = '') {
        $tree = [];
        $subfolders = [];
        $folders = [];
        foreach($files as $file) {
            $isInSubfolder = false;
            foreach($subfolders as $fkey) {
                if(starts_with($file, $fkey)) {
                    $isInSubfolder = true;
                    $subname = substr($file, strlen($fkey));
                    $folders[$fkey][] = $subname;
                    break;
                }
            }
            if($isInSubfolder) continue;
            $isDirectory = false;
            // check if "file" is folder
            if(ends_with($file, '/')) {
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


    public static function getCategory($mimes, $extensions, $mimeWildcards = null) {
        $query = self::WhereIn('mime_type', $mimes);
        if(isset($mimeWildcards)) {
            foreach($mimeWildcards as $mime) {
                $query->orWhere('mime_type', 'ilike', $mime.'%');
            }
        }

        foreach($extensions as $ext) {
            $query->orWhere('name', 'ilike', '%'.$ext);
        }
        return $query->get();
    }

    public static function getImages() {
        return self::getCategory([], [], ['image/']);
    }

    public static function getAudio() {
        return self::getCategory([], [], ['audio/']);
    }

    public static function getVideo() {
        return self::getCategory([], [], ['video/']);
    }

    public static function getPdfs() {
        return self::getCategory(['application/pdf'], ['.pdf']);
    }

    public static function getXmls() {
        $mimeTypes = ['application/xml', 'text/xml', 'text/xml-external-parsed-entity'];
        $extensions = ['.xml'];
        return self::getCategory($mimeTypes, $extensions);
    }

    public static function getHtmls() {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        return self::getCategory($mimeTypes, $extensions);
    }

    public static function get3d() {
        $mimeTypes = [];
        $extensions = ['.dae', '.obj', '.pdb', '.gltf'];
        return self::getCategory($mimeTypes, $extensions);
    }

    public static function getArchives() {
        $mimeTypes = ['application/gzip', 'application/zip', 'application/x-gtar', 'application/x-tar', 'application/x-ustar', 'application/x-rar-compressed', 'application/x-bzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-compress'];
        $extensions = ['.zip', '.gz', '.gtar', '.tar', '.tgz', '.ustar', '.rar', '.bz', '.bz2', '.xz', '.7z', '.z'];
        return self::getCategory($mimeTypes, $extensions);
    }

    public static function getTexts() {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex'];
        $mimeWildcards = ['text/'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv', '.xml'];
        return self::getCategory($mimeTypes, $extensions, $mimeWildcards);
    }

    public function getCategoryAttribute() {
        if($this->isImage()) return 'image';
        if($this->isAudio()) return 'audio';
        if($this->isVideo()) return 'video';
        if($this->isPdf()) return 'pdf';
        if($this->isXml()) return 'xml';
        if($this->isHtml()) return 'html';
        if($this->is3d()) return '3d';
        if($this->isArchive()) return 'archive';
        if($this->isText()) return 'text';
        return 'undefined';
    }

    public function getExifAttribute() {
        return $this->getExifData();
    }

    public function contexts() {
        return $this->belongsToMany('App\Context', 'context_photos', 'photo_id', 'context_id');
    }

    //TODO: this relationship is not working right now due to not referencing the id on ThConcept
    // as soon as id's are referenced this needs to be fixed
    public function tags() {
        return $this->belongsToMany('App\ThConcept', 'photo_tags', 'photo_id', 'concept_url');
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

    private function getExifData() {
        if(!$this->isImage()) return null;
        try {
            $content = Storage::get($this->name);
            $data = new PelDataWindow($content);
        } catch(FileNotFoundException $e) {
            return null;
        } catch(PelDataWindowOffsetException $e) {
            return null;
        }
        try {
            PelJpeg::isValid($data);
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
        } catch(PelDataWindowOffsetException $e) {
            return null;
        } catch(PelJpegInvalidMarkerException $e) {
            return null;
        }
        try {
            PelTiff::isValid($data);
        } catch(PelDataWindowOffsetException $e) {
            return null;
        }
        return null;
    }

    public function isImage() {
        return starts_with($this->mime_type, 'image/');
    }

    public function isAudio() {
        return starts_with($this->mime_type, 'audio/');
    }

    public function isVideo() {
        return starts_with($this->mime_type, 'video/');
    }

    public function isPdf() {
        return $this->mime_type == 'application/pdf' ||
            ends_with($this->name, '.pdf');
    }

    public function isXml() {
        return in_array($this->mime_type, ['application/xml', 'text/xml', 'text/xml-external-parsed-entity']) ||
            ends_with($this->name, '.xml');
    }

    public function isHtml() {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        $is = in_array($this->mime_type, $mimeTypes);
        if(!$is) {
            foreach($extensions as $ext) {
                if(ends_with($this->name, $ext)) {
                    $is = true;
                    break;
                }
            }
        }
        return $is;
    }

    public function is3d() {
        $is = false;
        $extensions = ['.dae', '.obj', '.pdb', '.gltf'];
        foreach($extensions as $ext) {
            if(ends_with($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isArchive() {
        $mimeTypes = ['application/gzip', 'application/zip', 'application/x-gtar', 'application/x-tar', 'application/x-ustar', 'application/x-rar-compressed', 'application/x-bzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-compress'];
        $extensions = ['.zip', '.gz', '.gtar', '.tar', '.tgz', '.ustar', '.rar', '.bz', '.bz2', '.xz', '.7z', '.z'];
        $is = in_array($this->mime_type, $mimeTypes);
        if(!$is) {
            foreach($extensions as $ext) {
                if(ends_with($this->name, $ext)) {
                    $is = true;
                    break;
                }
            }
        }
        return $is;
    }

    public function isText() {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv', '.xml'];
        $is = starts_with($this->mime_type, 'text/');
        if(!$is) {
            $is = in_array($this->mime_type, $mimeTypes);
        }
        if(!$is) {
            foreach($extensions as $ext) {
                if(ends_with($this->name, $ext)) {
                    $is = true;
                    break;
                }
            }
        }
        return $is;
    }
}
