<?php

namespace App\Plugins\File\App;

use App\EntityFile;
use App\FileTag;
use App\ThConcept;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelDataWindowOffsetException;
use lsolesen\pel\PelException;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelJpegInvalidMarkerException;
use lsolesen\pel\PelTag;
use wapmorgan\UnifiedArchive\UnifiedArchive;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class File extends Model
{
    use SearchableTrait;
    use LogsActivity;

    protected $table = 'files';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'modified',
        'created',
        'cameraname',
        'thumb',
        'copyright',
        'description',
        'mime_type',
        'user_id',
    ];

    protected $appends = [
        'category',
        'exif'
    ];

    protected $searchable = [
        'columns' => [
            'name' => 10,
            'description' => 8,
            'cameraname' => 1,
            'copyright' => 1,
        ],
    ];

    // protected static $logOnlyDirty = true;
    // protected static $logFillable = true;
    // protected static $logAttributes = ['id'];
    // protected static $ignoreChangedAttributes = ['user_id'];

    private const THUMB_SUFFIX = "_thumb";
    private const THUMB_WIDTH = 256;
    private const EXP_SUFFIX = ".jpg";
    private const EXP_FORMAT = "jpg";

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    private function createOrUpdateThumbnail($filename, $extension, $url) {
        $baseFilename = substr($filename, 0, strlen($filename) - (strlen($extension) + 1));
        if(isset($this->thumb)) {
            $thumbFilename = $this->thumb;
        } else {
            $thumbFilename = self::getUniqueFilename($baseFilename . self::THUMB_SUFFIX, self::EXP_SUFFIX);
        }

        $imageInfo = getimagesize($url);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo[2];//$imageInfo['mime'];
        if($width > self::THUMB_WIDTH) {
            switch($mime) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($url);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($url);
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($url);
                    break;
                default:
                    // use imagemagick to convert from unsupported file format to jpg, which is supported by native php
                    $im = new \Imagick($url);
                    $uniqueName = self::getUniqueFilename($baseFilename, self::EXP_SUFFIX);
                    $url = sp_get_storage_file_path($uniqueName);
                    $im->setImageFormat(self::EXP_FORMAT);
                    $im->writeImage($url);
                    $im->clear();
                    $im->destroy();
                    $image = imagecreatefromjpeg($url);
            }
            $scaled = imagescale($image, self::THUMB_WIDTH);
            ob_start();
            imagejpeg($scaled);
            $image = ob_get_clean();
            Storage::put(
                $thumbFilename,
                $image
            );
        } else {
            Storage::copy($filename, $thumbFilename);
        }
        $this->thumb = $thumbFilename;
        $this->save();
    }

    private function getContainingFiles($files, $archive, $prefix = '') {
        $tree = new \stdClass();
        $tree->children = [];
        foreach($files as $file) {
            // explode folders
            $parentFolders = explode("/", $file);
            $currentFile = array_pop($parentFolders);
            $currentFolderString = '';
            $currentFolder = $tree;
            foreach($parentFolders as $pf) {
                $currentFolderString .= "$pf/";
                $index = false;
                for($i=0; $i<count($currentFolder->children); $i++) {
                    $curr = $currentFolder->children[$i];
                    if($curr->isDirectory && $curr->cleanFilename == $pf) {
                        $index = $i;
                        break;
                    }
                }
                if($index === false) {
                    $newFolder = new \stdClass();
                    $newFolder->children = [];
                    $newFolder->isDirectory = true;
                    $newFolder->path = $currentFolderString;
                    $newFolder->compressedSize = 0;
                    $newFolder->uncompressedSize = 0;
                    $newFolder->modificationTime = 0;
                    $newFolder->isCompressed = false;
                    $newFolder->cleanFilename = $pf;
                    $currentFolder->children[] = $newFolder;
                    $index = count($currentFolder->children) - 1;
                }
                $currentFolder = $currentFolder->children[$index];
            }
            $data = $archive->getFileData($prefix.$file);
            $data->isDirectory = false;
            $data->cleanFilename = $currentFile;
            $currentFolder->children[] = $data;
        }
        return $tree->children;
    }

    public function asHtml() {
        if(!$this->isDocument() && !$this->isSpreadsheet() && !$this->isPresentation()) {
            return [
                'error' => __('HTML not supported for file type', ['mime' => $this->mime_type])
            ];
        }
        $tempFile = tempnam(sys_get_temp_dir(), 'Spacialist_html_');

        if($this->isDocument()) {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load(sp_get_storage_file_path($this->name));
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $writer->save($tempFile);
        } else if($this->isSpreadsheet()) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(sp_get_storage_file_path($this->name));
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
            $writer->save($tempFile);
        } else  if($this->isPresentation()) {
            return ['error' => 'Presentations not yet supported!'];
        }
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        return $content;
    }

    public static function filter($page, $filters, $type, $entity_id, $skip = 0) {
        $files = self::with(['entities', 'tags'])
            ->orderBy('id', 'asc');
        if($type == 'unlinked') {
            $files->doesntHave('entities');
            $path = '/file?t=unlinked';
        } else if($type == 'linked') {
            $files->whereHas('entities', function(Builder $query) use($entity_id) {
                $query->where('id', $entity_id);
            });
            $path = '/file?t=linked';
        } else {
            $path = '/file?t=all';
        }
        $files->where(function($query) use ($filters) {
            self::applyFilters($query, $filters);
        });
        $files = $files->paginate();

        $files = new LengthAwarePaginator(
            $files->getCollection()->slice($skip),
            $files->total(),
            $files->perPage(),
            $files->currentPage(),
            $files->getOptions()
        );
        $files->withPath($path);

        foreach($files as &$file) {
            $file->setFileInfo();
        }

        return $files;
    }

    public function setContent($fileObject) {
        $filehandle = fopen($fileObject->getRealPath(), 'r');
        Storage::put(
            $this->name,
            $filehandle
        );
        fclose($filehandle);
        $lastModified = date('Y-m-d H:i:s', Storage::lastModified($this->name));

        $this->mime_type = $fileObject->getMimeType();
        $this->modified = $lastModified;
        $this->save();

        if($this->isImage()) {
            $fileUrl = sp_get_storage_file_path($this->name);
            $ext = $fileObject->getClientOriginalExtension();
            $this->createOrUpdateThumbnail($this->name, $ext, $fileUrl);
        } else {
            if(isset($this->thumb)) {
                Storage::delete($this->thumb);
                $this->thumb = null;
                $this->save();
            }
        }
    }

    public function rename($newName)
    {
        if (Storage::exists($newName)) {
            return false;
        }

        Storage::move($this->name, $newName);
        $this->name = $newName;
        if ($this->isImage()) {
            $nameNoExt = pathinfo($newName, PATHINFO_FILENAME);
            $newThumb = self::getUniqueFilename($nameNoExt . self::THUMB_SUFFIX, self::EXP_SUFFIX);
            Storage::move($this->thumb, $newThumb);
            $this->thumb = $newThumb;
        }
        return $this->save();
    }

    public function link($eid, $user) {
        $link = new EntityFile();
        $link->file_id = $this->id;
        $link->entity_id = $eid;
        $link->user_id = $user->id;
        $link->save();
    }

    public function unlink($eid) {
        EntityFile::where('entity_id', $eid)
            ->where('file_id', $this->id)
            ->delete();
    }

    public function deleteFile() {
        Storage::delete($this->name);
        if(isset($this->thumb)) {
            Storage::delete($this->thumb);
        }
        $this->delete();
    }

    public function setFileInfo()
    {
        $this->url = sp_get_public_url($this->name);
        if ($this->isImage()) {
            $this->thumb_url = sp_get_public_url($this->thumb);
        }

        $content = Storage::get($this->name);
        if(isset($content)) {
            $this->size = Storage::size($this->name);
            $this->modified_unix = Storage::lastModified($this->name);
        }

        $this->created_unix = strtotime($this->created);
    }

    public function getArchiveFileList() {
        if(!$this->isArchive()) return [];
        $path = sp_get_storage_file_path($this->name);
        $archive = UnifiedArchive::open($path);
        $fileList = $this->getContainingFiles($archive->getFileNames(), $archive);

        return $fileList;
    }

    public function getArchivedFileContent($filepath) {
        $path = sp_get_storage_file_path($this->name);
        $archive = UnifiedArchive::open($path);
        $content = $archive->getFileContent($filepath);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $content, FILEINFO_MIME_TYPE);
        return [
            'content' => base64_encode($content),
            'mime_type' => $mime_type,
        ];
    }

    public function isImage()
    {
        return Str::startsWith($this->mime_type, 'image/');
    }

    public function isAudio()
    {
        return Str::startsWith($this->mime_type, 'audio/');
    }

    public function isVideo()
    {
        return Str::startsWith($this->mime_type, 'video/');
    }

    public function isPdf()
    {
        return $this->mime_type == 'application/pdf' ||
        Str::endsWith($this->name, '.pdf');
    }

    public function isXml()
    {
        return in_array($this->mime_type, ['application/xml', 'text/xml', 'text/xml-external-parsed-entity']) ||
            Str::endsWith($this->name, '.xml');
    }

    public function isHtml()
    {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function is3d()
    {
        $is = false;
        $mimeTypes = ['model/vnd.collada+xml', 'model/gltf-binary', 'model/gltf+json', 'chemical/x-pdb', 'text/x-gcode', 'application/sla'];
        $extensions = ['.dae', '.obj', '.pdb', '.gltf', '.glb', '.fbx', '.gcode', '.g', '.stl', '.ply'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isDicom()
    {
        $is = false;
        $mimeTypes = ['application/dicom', 'application/dicom+xml'];
        $extensions = ['.dcm', '.dicom'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isArchive()
    {
        $mimeTypes = ['application/gzip', 'application/zip', 'application/x-gtar', 'application/x-tar', 'application/x-ustar', 'application/x-rar-compressed', 'application/x-bzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-compress'];
        $extensions = ['.zip', '.gz', '.gtar', '.tar', '.tgz', '.ustar', '.rar', '.bz', '.bz2', '.xz', '.7z', '.z'];
        $is = in_array($this->mime_type, $mimeTypes);
        if(!$is) {
            foreach ($extensions as $ext) {
                if (Str::endsWith($this->name, $ext)) {
                    $is = true;
                    break;
                }
            }
        }
        if($is) {
            $path = sp_get_storage_file_path($this->name);
            $is = UnifiedArchive::canOpen($path);
        }
        return $is;
    }

    public function isText()
    {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv', 'text/x-markdown', 'text/markdown'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv'];
        $is = Str::startsWith($this->mime_type, 'text/');
        if ($is) return true;
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isDocument()
    {
        $mimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        $extensions = ['.doc', '.docx', '.odt'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isSpreadsheet()
    {
        $mimeTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-latex', 'application/x-tex'];
        $extensions = ['.xls', '.xlsx', '.ods'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isPresentation()
    {
        $mimeTypes = ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'];
        $extensions = ['.ppt', '.pptx', '.odp'];
        $is = in_array($this->mime_type, $mimeTypes);
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
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
        $content = Storage::get($this->name);
        if(!isset($content)) return null;

        try {
            $data = new PelDataWindow($content);
        } catch(PelDataWindowOffsetException $e) {
            return null;
        }
        // Only JPEG is currently supported
        if(!PelJpeg::isValid($data)) {
            return null;
        }
        try {
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
        } catch(PelException $e) {
            return null;
        }
    }

    // Appends

    public function getCategoryAttribute()
    {
        if ($this->isImage()) return 'image';
        if ($this->isAudio()) return 'audio';
        if ($this->isVideo()) return 'video';
        if ($this->isPdf()) return 'pdf';
        if ($this->is3d()) return '3d';
        if ($this->isDicom()) return 'dicom';
        if ($this->isArchive()) return 'archive';
        if ($this->isDocument()) return 'document';
        if ($this->isSpreadsheet()) return 'spreadsheet';
        if ($this->isPresentation()) return 'presentation';
        if ($this->isHtml()) return 'html';
        if ($this->isXml()) return 'xml';
        if ($this->isText()) return 'text';
        return 'undefined';
    }

    public function getExifAttribute() {
        return $this->getExifData();
    }

    // Relations
    
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entities() {
        return $this->belongsToMany('App\Entity', 'entity_files', 'file_id', 'entity_id');
    }

    public function tags() {
        return $this->belongsToMany('App\ThConcept', 'file_tags', 'file_id', 'concept_id');
    }

    // Statics

    private static function getUniqueFilename($filename, $extension) {
        $cnt = 0;
        $cutoff = 0;
        if(preg_match('/.*\.(\d+)\.?$/', $filename, $matches)) {
            if(count($matches) > 1) {
                $cnt = intval($matches[1]);
            }
            // cut off number and leading '.'
            $cutoff += strlen($matches[1]) + 1;
        }
        if(Str::endsWith($filename, '.')) {
            $cutoff++;
        }
        if($cutoff > 0) {
            $filename = substr($filename, 0, -$cutoff);
        }
        if(Str::startsWith($extension, '.')) {
            $extension = substr($extension, 1);
        }
        if($cnt > 0) {
            $uniqueFilename = "$filename.$cnt.$extension";
            $cnt++;
        } else {
            $uniqueFilename = "$filename.$extension";
        }
        while(Storage::exists($uniqueFilename)) {
            $uniqueFilename = "$filename.$cnt.$extension";
            $cnt++;
        }
        return $uniqueFilename;
    }

    public static function createFromUpload($input, $user, $metadata) {
        $filename = $input->getClientOriginalName();
        $ext = $input->getClientOriginalExtension();
        // filename without extension and trailing '.'
        $baseFilename = substr($filename, 0, strlen($filename) - (strlen($ext) + 1));
        $filename = self::getUniqueFilename($baseFilename, $ext);
        $filehandle = fopen($input->getRealPath(), 'r');
        Storage::put(
            $filename,
            $filehandle
        );
        fclose($filehandle);

        $mimeType = $input->getMimeType();
        $lastModified = date('Y-m-d H:i:s', Storage::lastModified($filename));

        $file = new File();
        $file->modified = $lastModified;
        $file->user_id = $user->id;
        $file->mime_type = $mimeType;
        $file->name = $filename;
        $file->created = $lastModified;

        if(isset($metadata)) {
            if(isset($metadata['copyright'])) {
                $file->copyright = $metadata['copyright'];
            }
            if(isset($metadata['description'])) {
                $file->description = $metadata['description'];
            }
        }

        $file->save();
        if(isset($metadata)) {
            if(isset($metadata['tags'])) {
                foreach($metadata['tags'] as $tid) {
                    try {
                        ThConcept::findOrFail($tid);
                    } catch(ModelNotFoundException $e) {
                        return response()->json([
                            'error' => __('This tag does not exist')
                        ], 400);
                    }
                    $tag = new FileTag();
                    $tag->file_id = $file->id;
                    $tag->concept_id = $tid;
                    $tag->save();
                }
            }
        }

        if($file->isImage()) {
            $fileUrl = sp_get_storage_file_path($filename);
            $imageInfo = getimagesize($fileUrl);
            $mime = $imageInfo[2];//$imageInfo['mime'];

            $file->createOrUpdateThumbnail($filename, $ext, $fileUrl);

            if($mime === IMAGETYPE_JPEG || $mime === IMAGETYPE_TIFF_II || $mime === IMAGETYPE_TIFF_MM) {
                $exif = @exif_read_data($fileUrl, 'ANY_TAG', true);
                if($exif !== false) {
                    if(sp_has_exif($exif, 'IFD0', 'Make')) {
                        $make = $exif['IFD0']['Make'];
                    }
                    if(sp_has_exif($exif, 'IFD0', 'Model')) {
                        $model = $exif['IFD0']['Model'];
                    } else {
                        $model = '';
                    }
                    if(isset($make)) {
                        $model = $model . " ($make)";
                    }
                    $file->cameraname = $model;

                    if(sp_has_exif($exif, 'IFD0', 'Copyright')) {
                        $copyright = $exif['IFD0']['Copyright'];
                    } else {
                        $copyright = '';
                    }
                    $file->copyright = $copyright;

                    if(sp_has_exif($exif, 'IFD0', 'ImageDescription')) {
                        $description = $exif['IFD0']['ImageDescription'];
                    } else {
                        $description = '';
                    }
                    $file->description = $description;

                    if(sp_has_exif($exif, 'EXIF', 'DateTimeOriginal')) {
                        $dateOrig = strtotime($exif['EXIF']['DateTimeOriginal']);
                        $dateOrig = date('Y-m-d H:i:s', $dateOrig);
                        $file->created = $dateOrig;
                    }
                }
            }
            $file->save();
        }
        $file = File::find($file->id);
        return $file;
    }

    public static function createArchiveFromList($fileList) {
        $dt = date('dmYHis');
        $zip = '/tmp/exported-files-'.$dt.'.zip';
        // Convert Laravel file collection to [
        //     path => name
        // ];
        $nodes = [];
        foreach($fileList as $f) {
            $path = sp_get_storage_file_path($f->name);
            $nodes[$path] = $f->name;
        }
        UnifiedArchive::archiveFiles($nodes, $zip);
        return [
            'path' => $zip,
            'type' => 'application/zip'
        ];
    }

    public static function applyFilters($builder, $filters) {
        foreach($filters as $col => $fs) {
            // Do not parse empty filters
            if(!isset($fs) || empty($fs)) {
                continue;
            }
            switch($col) {
                case 'filetypes':
                    foreach($fs as $f) {
                        switch($f) {
                            case 'image':
                                $builder = self::addImageFilter($builder, true);
                                break;
                            case 'audio':
                                $builder = self::addAudioFilter($builder, true);
                                break;
                            case 'video':
                                $builder = self::addVideoFilter($builder, true);
                                break;
                            case 'pdf':
                                $builder = self::addPdfFilter($builder, true);
                                break;
                            case 'xml':
                                $builder = self::addXmlFilter($builder, true);
                                break;
                            case 'html':
                                $builder = self::addHtmlFilter($builder, true);
                                break;
                            case '3d':
                                $builder = self::add3dFilter($builder, true);
                                break;
                            case 'dicom':
                                $builder = self::addDicomFilter($builder, true);
                                break;
                            case 'archive':
                                $builder = self::addArchiveFilter($builder, true);
                                break;
                            case 'text':
                                $builder = self::addTextFilter($builder, true);
                                break;
                            case 'document':
                                $builder = self::addDocumentFilter($builder, true);
                                break;
                            case 'spreadsheet':
                                $builder = self::addSpreadsheetFilter($builder, true);
                                break;
                            case 'presentation':
                                $builder = self::addPresentationFilter($builder, true);
                                break;
                        }
                    }
                    break;
                case 'cameras':
                    $builder->where(function($query) use($fs) {
                        foreach($fs as $f) {
                            $query->orWhere('cameraname', $f);
                        }
                    });
                    break;
                case 'dates':
                    $builder->where(function($query) use($fs) {
                        foreach($fs as $f) {
                            $f = (object) $f;
                            switch($f->is) {
                                case 'date':
                                    $query->orWhereDate('created', $f->value);
                                    break;
                                case 'year':
                                    $query->orWhereYear('created', $f->value);
                                    break;
                            }
                        }
                    });
                    break;
                case 'name':
                    if(!isset($fs['value'])) break;
                    $op = $fs['case'] ? 'like' : 'ilike';
                    $q = $fs['regex'] ? Str::replace('*', '%', $fs['value']) : '%'.$fs['value'].'%';
                    $builder->orWhere('name', $op, $q);
                    break;
                case 'metadata':
                    if(!isset($fs['value'])) break;
                    $op = $fs['case'] ? 'like' : 'ilike';
                    $q = $fs['regex'] ? Str::replace('*', '%', $fs['value']) : '%'.$fs['value'].'%';
                    $builder
                        ->orWhere('copyright', $op, $q)
                        ->orWhere('description', $op, $q);
                    break;
                case 'tags':
                    $builder->whereHas('tags', function($query) use ($fs) {
                        $query->where(function($subquery) use ($fs) {
                            foreach($fs as $f) {
                                $subquery->orWhere('concept_id', $f);
                            }
                        });
                    });
                    break;
            }
        }
        return $builder;
    }

    public static function addCategoryFilter($mimes, $extensions, $mimeWildcards = null, $query = null, $or = false) {
        if(!isset($query)) {
            $query = self::WhereIn('mime_type', $mimes);
        } else {
            if($or) $query->orWhereIn('mime_type', $mimes);
            else $query->whereIn('mime_type', $mimes);
        }
        if(isset($mimeWildcards)) {
            foreach($mimeWildcards as $mime) {
                $query->orWhere('mime_type', 'ilike', $mime.'%');
            }
        }

        foreach($extensions as $ext) {
            $query->orWhere('name', 'ilike', '%'.$ext);
        }
        return $query;
    }

    public static function addImageFilter($query, $or = false) {
        return self::addCategoryFilter([], [], ['image/'], $query, $or);
    }

    public static function addAudioFilter($query, $or = false) {
        return self::addCategoryFilter([], [], ['audio/'], $query, $or);
    }

    public static function addVideoFilter($query, $or = false) {
        return self::addCategoryFilter([], [], ['video/'], $query, $or);
    }

    public static function addPdfFilter($query, $or = false) {
        return self::addCategoryFilter(['application/pdf'], ['.pdf'], null, $query, $or);
    }

    public static function addXmlFilter($query, $or = false) {
        $mimeTypes = ['application/xml', 'text/xml', 'text/xml-external-parsed-entity'];
        $extensions = ['.xml'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addHtmlFilter($query, $or = false) {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function add3dFilter($query, $or = false) {
        $mimeTypes = ['model/vnd.collada+xml', 'model/gltf-binary', 'model/gltf+json', 'chemical/x-pdb', 'text/x-gcode', 'application/sla'];
        $extensions = ['.dae', '.obj', '.pdb', '.gltf', '.glb', '.fbx', '.gcode', '.g', '.stl', '.ply'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addDicomFilter($query, $or = false) {
        $mimeTypes = ['application/dicom', 'application/dicom+xml'];
        $extensions = ['.dcm', '.dicom'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addArchiveFilter($query, $or = false) {
        $mimeTypes = ['application/gzip', 'application/zip', 'application/x-gtar', 'application/x-tar', 'application/x-ustar', 'application/x-rar-compressed', 'application/x-bzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-compress'];
        $extensions = ['.zip', '.gz', '.gtar', '.tar', '.tgz', '.ustar', '.rar', '.bz', '.bz2', '.xz', '.7z', '.z'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addTextFilter($query, $or = false) {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv', 'text/x-markdown', 'text/markdown'];
        $mimeWildcards = ['text/'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv'];
        return self::addCategoryFilter($mimeTypes, $extensions, $mimeWildcards, $query, $or);
    }

    public static function addDocumentFilter($query, $or = false) {
        $mimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        $extensions = ['.doc', '.docx', '.odt'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addSpreadsheetFilter($query, $or = false) {
        $mimeTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-latex', 'application/x-tex'];
        $extensions = ['.xls', '.xlsx', '.ods'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addPresentationFilter($query, $or = false) {
        $mimeTypes = ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'];
        $extensions = ['.ppt', '.pptx', '.odp'];
        return self::addCategoryFilter($mimeTypes, $extensions, null, $query, $or);
    }

    public static function getCategories($locale = 'en')
    {
        \App::setLocale($locale);
        return [
            ['key' => 'image', 'label' => __('Image')],
            ['key' => 'audio', 'label' => __('Audio File')],
            ['key' => 'video', 'label' => __('Video File')],
            ['key' => 'pdf', 'label' => __('PDF')],
            ['key' => 'xml', 'label' => __('XML')],
            ['key' => 'html', 'label' => __('HTML')],
            ['key' => '3d', 'label' => __('3D File')],
            ['key' => 'dicom', 'label' => __('DICOM File')],
            ['key' => 'archive', 'label' => __('Archive')],
            ['key' => 'text', 'label' => __('Text File')],
            ['key' => 'document', 'label' => __('Office Documents')],
            ['key' => 'spreadsheet', 'label' => __('Spreadsheets')],
            ['key' => 'presentation', 'label' => __('Presentation Files')],
        ];
    }

    public static function getCamerasUsed()
    {
        return File::distinct()
            ->orderBy('cameraname', 'asc')
            ->whereNotNull('cameraname')
            ->pluck('cameraname');
    }

    public static function getDatesCreated($mode = 'date')
    {
        $hasDates = false;
        $hasYears = false;

        if($mode == 'date' || $mode == 'full') {
            $dates = File::distinct()
                ->select(\DB::raw("DATE(created) AS created_date"))
                ->orderBy('created_date', 'asc')
                ->pluck('created_date');
            $dates = $dates->map(function($d) {
                return [
                    'is' => 'date',
                    'value' => $d
                ];
            });
            $hasDates = true;
        }
        if($mode == 'year' || $mode == 'full') {
            $years = File::distinct()
                ->select(\DB::raw("EXTRACT(year from created) AS created_year"))
                ->orderBy('created_year', 'asc')
                ->pluck('created_year');
            $years = $years->map(function($y) {
                return [
                    'is' => 'year',
                    'value' => $y
                ];
            });
            $hasYears = true;
        }

        if($hasDates && !$hasYears) {
            return $dates;
        } else if(!$hasDates && $hasYears) {
            return $years;
        } else if($hasDates && $hasYears) {
            return $dates->concat($years);
        }
    }
}
