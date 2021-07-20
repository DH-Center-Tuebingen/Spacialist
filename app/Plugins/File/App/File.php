<?php

namespace App\Plugins\File\App;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        // if ($this->isImage()) {
        //     $this->thumb_url = sp_get_public_url($this->thumb);
        // }

        try {
            Storage::get($this->name);
            $this->size = Storage::size($this->name);
            $this->modified_unix = Storage::lastModified($this->name);
        } catch (FileNotFoundException $e) {
        }

        $this->created_unix = strtotime($this->created);
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
}
