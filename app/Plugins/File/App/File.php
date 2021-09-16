<?php

namespace App\Plugins\File\App;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
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
        // 'exif'
    ];

    public static function filter($page, $filters, $type) {
        $files = self::with(['entities', 'tags'])
            ->orderBy('id', 'asc');
        if($type == 'unlinked') {
            $files->doesntHave('entities');
        } else if($type == 'linked') {
            $files->has('entitites');
        }
        $files = $files->paginate();
        $files->withPath('/file');

        foreach($files as &$file) {
            $file->setFileInfo();
        }

        return $files;
    }

    public function setFileInfo()
    {
        $this->url = sp_get_public_url($this->name);
        if ($this->isImage()) {
            $this->thumb_url = sp_get_public_url($this->thumb);
        }

        try {
            Storage::get($this->name);
            $this->size = Storage::size($this->name);
            $this->modified_unix = Storage::lastModified($this->name);
        } catch (FileNotFoundException $e) {
        }

        $this->created_unix = strtotime($this->created);
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
        $mimeTypes = ['model/vnd.collada+xml', 'model/gltf-binary', 'model/gltf+json', 'text/x-gcode', 'application/sla'];
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
        if ($is) return true;
        foreach ($extensions as $ext) {
            if (Str::endsWith($this->name, $ext)) {
                $is = true;
                break;
            }
        }
        return $is;
    }

    public function isText()
    {
        $mimeTypes = ['application/javascript', 'application/json', 'application/x-latex', 'application/x-tex', 'text/comma-separated-values', 'text/csv', 'text/x-markdown', 'text/markdown'];
        $extensions = ['.txt', '.md', '.markdown', '.mkd', '.csv', '.json', '.css', '.htm', '.html', '.shtml', '.js', '.rtx', '.rtf', '.tsv', '.xml'];
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
}
