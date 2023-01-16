<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Bibliography extends Model implements Searchable
{
    use LogsActivity;

    protected $table = 'bibliography';

    protected $appends = [
        'file_url',
    ];

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author',
        'editor',
        'title',
        'journal',
        'year',
        'pages',
        'volume',
        'number',
        'booktitle',
        'publisher',
        'address',
        'misc',
        'howpublished',
        'type',
        'annote',
        'chapter',
        'crossref',
        'edition',
        'institution',
        'key',
        'month',
        'note',
        'organization',
        'school',
        'series',
        'citekey',
        'user_id',
        'file',
    ];

    protected const searchCols = [
        'author' => 10,
        'editor' => 10,
        'title' => 10,
        'journal' => 10,
        'year' => 10,
        'pages' => 5,
        'volume' => 5,
        'number' => 5,
        'booktitle' => 10,
        'publisher' => 10,
        'address' => 5,
        'misc' => 8,
        'howpublished' => 5,
        'type' => 5,
        'annote' => 5,
        'chapter' => 5,
        'crossref' => 5,
        'edition' => 5,
        'institution' => 5,
        'key' => 5,
        'month' => 5,
        'note' => 5,
        'organization' => 5,
        'school' => 5,
        'series' => 5,
        'citekey' => 5
    ];

    const patchRules = [
        'author'       => 'string',
        'editor'       => 'string',
        'title'        => 'string',
        'journal'      => 'string',
        'year'         => 'string',
        'pages'        => 'string',
        'volume'       => 'string',
        'number'       => 'string',
        'booktitle'    => 'string',
        'publisher'    => 'string',
        'address'      => 'string',
        'misc'         => 'string',
        'howpublished' => 'string',
        'type'         => 'string',
        'annote'       => 'string',
        'chapter'      => 'string',
        'crossref'     => 'string',
        'edition'      => 'string',
        'institution'  => 'string',
        'key'          => 'string',
        'month'        => 'string',
        'note'         => 'string',
        'organization' => 'string',
        'school'       => 'string',
        'series'       => 'string',
        'doi'          => 'string',
        'subtype'      => 'string',
        'file'         => 'file',
    ];

    public const bibtexTypes = [
        "article" => [
            "fields" => [
                'author', 'title', 'journal', 'year', 'volume', 'number', 'pages', 'month', 'note', 'doi', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "journal" => true,
                "year" => true,
            ],
        ],
        "book" => [
            "fields" => [
                'title', 'publisher', 'year', 'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "author" => 'editor',
                "editor" => 'author',
                "title" => true,
                "publisher" => true,
                "year" => true,
            ],
        ],
        "incollection" => [
            "fields" => [
                'author', 'title', 'booktitle', 'publisher', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'subtype', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "booktitle" => true,
                "publisher" => true,
                "year" => true,
            ],
        ],
        "misc" => [
            "fields" => [
                'author', 'title', 'howpublished', 'month', 'year', 'note', 'email', 'url'
            ],
        ],
        "booklet" => [
            "fields" => [
                'title', 'author', 'howpublished', 'address', 'month', 'year', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "conference" => [
            "fields" => [
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "booktitle" => true,
                "year" => true,
            ],
        ],
        "inbook" => [
            "fields" => [
                'title', 'publisher', 'year', 'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note', 'subtype', 'email', 'url'
            ],
            "mandatory" => [
                "author" => "editor",
                "editor" => "author",
                "title" => true,
                "chapter" => "pages",
                "pages" => "chapter",
                "publisher" => true,
                "year" => true,
            ],
        ],
        "inproceedings" => [
            "fields" => [
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "booktitle" => true,
                "year" => true,
            ],
        ],
        "manual" => [
            "fields" => [
                'title', 'author', 'organization', 'address', 'edition', 'month', 'year', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "mastersthesis" => [
            "fields" => [
                'author', 'title', 'school', 'year', 'address', 'month', 'note', 'subtype', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "school" => true,
                "year" => true,
            ],
        ],
        "phdthesis" => [
            "fields" => [
                'author', 'title', 'school', 'year', 'address', 'month', 'note', 'subtype', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "school" => true,
                "year" => true,
            ],
        ],
        "proceedings" => [
            "fields" => [
                'title', 'year', 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url'
            ],
            "mandatory" => [
                "title" => true,
                "year" => true,
            ],
        ],
        "techreport" => [
            "fields" => [
                'author', 'title', 'institution', 'year', 'number', 'address', 'month', 'note', 'subtype', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "institution" => true,
                "year" => true,
            ],
        ],
        "unpublished" => [
            "fields" => [
                'author', 'title', 'note', 'month', 'year', 'email', 'url'
            ],
            "mandatory" => [
                "author" => true,
                "title" => true,
                "note" => true,
            ],
        ],
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public function getSearchResult(): SearchResult {
        return new SearchResult(
            $this,
            $this->title,
        );
    }

    public static function getSearchCols(): array {
        return array_keys(self::searchCols);
    }

    public function fieldsFromRequest($request, $user) {
        $fields = is_array($request) ? $request : $request->toArray();
        foreach($fields as $key => $value){
            $this->{$key} = $value;
        }

        $type = self::bibtexTypes[$this->type];
        // Check if all mandatory fields are set
        foreach($type['mandatory'] as $man => $manType) {
            // if is simple mandatory field, check if present
            // otherwise return false
            if($manType === true) {
                if(!isset($this->{$man}) || empty($this->{$man})) {
                    return false;
                }
            } else {
                if(
                    (!isset($this->{$man}) || empty($this->{$man})) &&
                    (!isset($this->{$manType}) || empty($this->{$manType}))
                ) {
                    return false;
                }
            }
        }
        // Unset all fields that are not allowed for the current type
        $disallowedFields = array_diff(array_keys(self::patchRules), $type['fields'], ['type', 'file']);
        foreach($disallowedFields as $toDel) {
            $this->{$toDel} = null;
        }

        $this->citekey = self::computeCitationKey($this->toArray());
        $this->user_id = $user->id;
        $this->save();
        return true;
    }

    public function referenceCount() {
        return Reference::where('bibliography_id', $this->id)->count();
    }

    public static function computeCitationKey($fields) {
        $key;
        if(isset($fields['author'])) {
            $key = $fields['author'];
        } else {
            $key = $fields['title'];
        }
        // Use first two letters of author/title as key with only first letter uppercase
        $key = ucwords(Str::lower(substr($key, 0, 2))) . ':';
        if(isset($fields['year'])) {
            $key .= $fields['year'];
        } else {
            $key .= '0000';
        }

        $initalKey = $key;
        $suffixes = array_merge(range('a', 'z'), range('A', 'Z'));
        $suffixesCount = count($suffixes);
        $i = 0;
        $j = 0;
        while(self::where('citekey', $key)->first() !== null) {
            // if single letter was not enough to be unique, add another
            if($i == $suffixesCount) {
                if($j == $suffixesCount) $j = 0;
                $initalKey = $initalKey . $suffixes[$j++];
                $i = 0;
            }
            $key = $initalKey . $suffixes[$i++];
        }
        return $key;
    }

    public function uploadFile($file) {
        if(isset($this->file) && Storage::exists($this->file)) {
            Storage::delete($this->file);
        }

        $filename = $this->id . "_" . $file->getClientOriginalName();
        return $file->storeAs(
            'bibliography',
            $filename
        );
    }

    public function deleteFile() {
        if(isset($this->file) && Storage::exists($this->file)) {
            Storage::delete($this->file);
        }

        $this->file = null;
        $this->save();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entities() {
        return $this->belongsToMany('App\Entity', 'references')->withPivot('description', 'attribute_id');
    }

    public function getFileUrlAttribute() {
        return isset($this->file) ? sp_get_public_url($this->file) : null;
    }
}
