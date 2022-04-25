<?php

namespace App;

use App\EntityFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelDataWindowOffsetException;
use lsolesen\pel\PelJpegInvalidMarkerException;
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

    public static function applyFilters($builder, $filters) {
        foreach($filters as $col => $fs) {
            // Do not parse empty filters
            if(!isset($fs) || empty($fs)) {
                continue;
            }
            switch($col) {
                case 'categories':
                    foreach($fs as $f) {
                        switch($f) {
                            case 'image':
                                $builder = self::addImages($builder, true);
                                break;
                            case 'audio':
                                $builder = self::addAudio($builder, true);
                                break;
                            case 'video':
                                $builder = self::addVideo($builder, true);
                                break;
                            case 'pdf':
                                $builder = self::addPdfs($builder, true);
                                break;
                            case 'xml':
                                $builder = self::addXmls($builder, true);
                                break;
                            case 'html':
                                $builder = self::addHtmls($builder, true);
                                break;
                            case '3d':
                                $builder = self::add3d($builder, true);
                                break;
                            case 'dicom':
                                $builder = self::addDicom($builder, true);
                                break;
                            case 'archive':
                                $builder = self::addArchives($builder, true);
                                break;
                            case 'text':
                                $builder = self::addTexts($builder, true);
                                break;
                            case 'document':
                                $builder = self::addDocuments($builder, true);
                                break;
                            case 'spreadsheet':
                                $builder = self::addSpreadsheets($builder, true);
                                break;
                            case 'presentation':
                                $builder = self::addPresentations($builder, true);
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
                    $builder->orWhere('name', 'ilike', "%$fs%");
                    break;
                case 'tags':
                    $builder->whereHas('tags', function($query) use ($fs) {
                        $query->where(function($subquery) use ($fs) {
                            foreach($fs as $f) {
                                $f = (object) $f;
                                $subquery->orWhere('concept_id', $f->id);
                            }
                        });
                    });
                    break;
            }
        }
        return $builder;
    }

    public static function getAllPaginate($page, $filters) {
        $files = self::with(['entities', 'tags'])
            ->orderBy('id', 'asc');
        $files->where(function($subQuery) use ($filters) {
            self::applyFilters($subQuery, $filters);
        });
        $files = $files->paginate();
        $files->withPath('/file');

        foreach($files as &$file) {
            $file->setFileInfo();
        }

        return $files;
    }

    public static function getUnlinkedPaginate($page, $filters) {
        $files = self::with(['entities', 'tags'])
            ->orderBy('id', 'asc')
            ->doesntHave('entities');
        $files->where(function($subQuery) use ($filters) {
            self::applyFilters($subQuery, $filters);
        });
        $files = $files->paginate();
        $files->withPath('/file/unlinked');

        foreach($files as &$file) {
            $file->setFileInfo();
        }

        return $files;
    }

    public static function getLinkedPaginate($cid, $page, $filters) {
        $files = self::with(['entities', 'tags'])
            ->whereHas('entities', function($query) use($cid, $filters) {
                $query->where('entity_id', $cid);
                if(array_key_exists('sub_entities', $filters)) {
                    $subs = $filters['sub_entities'];
                    if(isset($subs) && sp_parse_boolean($subs)) {
                        $query->orWhere('root_entity_id', $cid);
                    }
                }
            })
            ->orderBy('id', 'asc');
        $files->where(function($subQuery) use ($filters) {
            self::applyFilters($subQuery, $filters);
        });
        $files = $files->paginate();
        $files->withPath('/file/linked/'.$cid);

        foreach($files as &$file) {
            $file->setFileInfo();
        }

        return $files;
    }

    public static function getFileById($id) {
        $file = self::with(['entities', 'tags'])->findOrFail($id);
        $file->setFileInfo();
        return $file;
    }

    public static function getSubFiles($id, $category) {
        $subFiles = self::with(['tags'])->whereHas('entities', function($query) use ($id) {
            $query->where('root_entity_id', $id);
        });

        $subFiles->where(function($builder) use ($category) {
            if(isset($category)) {
                switch($category) {
                    case 'image':
                        $builder = self::addImages($builder);
                        break;
                    case 'audio':
                        $builder = self::addAudio($builder);
                        break;
                    case 'video':
                        $builder = self::addVideo($builder);
                        break;
                    case 'pdf':
                        $builder = self::addPdfs($builder);
                        break;
                    case 'xml':
                        $builder = self::addXmls($builder);
                        break;
                    case 'html':
                        $builder = self::addHtmls($builder);
                        break;
                    case '3d':
                        $builder = self::add3d($builder);
                        break;
                    case 'dicom':
                        $builder = self::addDicom($builder);
                        break;
                    case 'archive':
                        $builder = self::addArchives($builder);
                        break;
                    case 'text':
                        $builder = self::addTexts($builder);
                        break;
                    case 'document':
                        $builder = self::addDocuments($builder);
                        break;
                    case 'spreadsheet':
                        $builder = self::addSpreadsheets($builder);
                        break;
                    case 'presentation':
                        $builder = self::addPresentations($builder);
                        break;
                }
            }
        });

        $subFiles = $subFiles->get();
        foreach($subFiles as $file) {
            $file->setFileInfo();
        }

        return $subFiles;
    }

    public static function uploadAvatar($file, $user) {
        Storage::delete($user->avatar);
        $filename = $user->id . "." . $file->getClientOriginalExtension();
        return $file->storeAs(
            'avatars',
            $filename
        );
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

    public function deleteFile() {
        Storage::delete($this->name);
        if(isset($this->thumb)) {
            Storage::delete($this->thumb);
        }
        $this->delete();
    }

    public static function getCategory($mimes, $extensions, $mimeWildcards = null, $query = null, $or = false) {
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

    public static function addImages($query, $or = false) {
        return self::getCategory([], [], ['image/'], $query, $or);
    }

    public static function addAudio($query, $or = false) {
        return self::getCategory([], [], ['audio/'], $query, $or);
    }

    public static function addVideo($query, $or = false) {
        return self::getCategory([], [], ['video/'], $query, $or);
    }

    public static function addPdfs($query, $or = false) {
        return self::getCategory(['application/pdf'], ['.pdf'], null, $query, $or);
    }

    public static function addXmls($query, $or = false) {
        $mimeTypes = ['application/xml', 'text/xml', 'text/xml-external-parsed-entity'];
        $extensions = ['.xml'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addHtmls($query, $or = false) {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function add3d($query, $or = false) {
        $mimeTypes = ['model/vnd.collada+xml', 'model/gltf-binary', 'model/gltf+json', 'chemical/x-pdb', 'text/x-gcode', 'application/sla'];
        $extensions = ['.dae', '.obj', '.pdb', '.gltf', '.glb', '.fbx', '.gcode', '.g', '.stl', '.ply'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addDicom($query, $or = false) {
        $mimeTypes = ['application/dicom', 'application/dicom+xml'];
        $extensions = ['.dcm', '.dicom'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addArchives($query, $or = false) {
        $mimeTypes = ['application/gzip', 'application/zip', 'application/x-gtar', 'application/x-tar', 'application/x-ustar', 'application/x-rar-compressed', 'application/x-bzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-compress'];
        $extensions = ['.zip', '.gz', '.gtar', '.tar', '.tgz', '.ustar', '.rar', '.bz', '.bz2', '.xz', '.7z', '.z'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addTexts($query, $or = false) {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv', 'text/x-markdown', 'text/markdown'];
        $mimeWildcards = ['text/'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv'];
        return self::getCategory($mimeTypes, $extensions, $mimeWildcards, $query, $or);
    }

    public static function addDocuments($query, $or = false) {
        $mimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        $extensions = ['.doc', '.docx', '.odt'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addSpreadsheets($query, $or = false) {
        $mimeTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-latex', 'application/x-tex'];
        $extensions = ['.xls', '.xlsx', '.ods'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public static function addPresentations($query, $or = false) {
        $mimeTypes = ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'];
        $extensions = ['.ppt', '.pptx', '.odp'];
        return self::getCategory($mimeTypes, $extensions, null, $query, $or);
    }

    public function getCategoryAttribute() {
        if($this->isImage()) return 'image';
        if($this->isAudio()) return 'audio';
        if($this->isVideo()) return 'video';
        if($this->isPdf()) return 'pdf';
        if($this->is3d()) return '3d';
        if($this->isDicom()) return 'dicom';
        if($this->isArchive()) return 'archive';
        if($this->isDocument()) return 'document';
        if($this->isSpreadsheet()) return 'spreadsheet';
        if($this->isPresentation()) return 'presentation';
        if($this->isHtml()) return 'html';
        if($this->isXml()) return 'xml';
        if($this->isText()) return 'text';
        return 'undefined';
    }

    public static function getCategories($locale = 'en') {
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

    public function linkCount() {
        return EntityFile::where('file_id', $this->id)->count();
    }

    public function getExifAttribute() {
        return $this->getExifData();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entities() {
        return $this->belongsToMany('App\Entity', 'entity_files', 'file_id', 'entity_id');
    }

    public function tags() {
        return $this->belongsToMany('App\ThConcept', 'file_tags', 'file_id', 'concept_id');
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
        }
        return null;
    }

    public function isImage() {
        return Str::startsWith($this->mime_type, 'image/');
    }

    public function isAudio() {
        return Str::startsWith($this->mime_type, 'audio/');
    }

    public function isVideo() {
        return Str::startsWith($this->mime_type, 'video/');
    }

    public function isPdf() {
        return $this->mime_type == 'application/pdf' ||
            Str::endsWith($this->name, '.pdf');
    }

    public function isXml() {
        return in_array($this->mime_type, ['application/xml', 'text/xml', 'text/xml-external-parsed-entity']) ||
            Str::endsWith($this->name, '.xml');
    }

    public function isHtml() {
        $mimeTypes = ['application/xhtml+xml', 'text/html'];
        $extensions = ['.htm', '.html', '.shtml', '.xhtml'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function is3d() {
        $is = false;
        $mimeTypes = ['model/vnd.collada+xml', 'model/gltf-binary', 'model/gltf+json'];
        $extensions = ['.dae', '.obj', '.pdb', '.gltf', '.fbx'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isDicom() {
        $is = false;
        $mimeTypes = ['application/dicom', 'application/dicom+xml'];
        $extensions = ['.dcm', '.dicom'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
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
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isText() {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv', 'text/x-markdown', 'text/markdown'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv', '.xml'];
        $is = Str::startsWith($this->mime_type, 'text/');
        if($is) return true;
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isDocument() {
        $mimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        $extensions = ['.doc', '.docx', '.odt'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isSpreadsheet() {
        $mimeTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-latex', 'application/x-tex'];
        $extensions = ['.xls', '.xlsx', '.ods'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isPresentation() {
        $mimeTypes = ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'];
        $extensions = ['.ppt', '.pptx', '.odp'];
        $is = in_array($this->mime_type, $mimeTypes);
        if($is) return true;
        foreach($extensions as $ext) {
            if(Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }
}
