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
        'subtype',
        'file',
        'abstract',
        'doi',
        'isbn',
        'issn',
        'language',
        'citekey',
        'user_id',
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
        'subtype'      => 'string',
        'file'         => 'file',
        // non-standard fields
        'abstract'     => 'string',
        'doi'          => 'string',
        'isbn'         => 'string',
        'issn'         => 'string',
        'language'     => 'string',
    ];

    public const bibtexTypes = [
        "article" => [
            "fields" => [
                'author', 'title', 'journal', 'year', 'volume', 'number', 'pages', 'month', 'note', 'doi', 'email', 'url', 'issn', 'language', 'abstract'
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
                'title', 'publisher', 'year', 'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note', 'email', 'url', 'isbn', 'language', 'abstract'
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
                'author', 'title', 'booktitle', 'publisher', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'subtype', 'email', 'url', 'language', 'abstract'
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
                'author', 'title', 'howpublished', 'month', 'year', 'note', 'email', 'url', 'language', 'abstract'
            ],
        ],
        "booklet" => [
            "fields" => [
                'title', 'author', 'howpublished', 'address', 'month', 'year', 'note', 'email', 'url', 'language', 'abstract'
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "conference" => [
            "fields" => [
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url', 'language', 'abstract'
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
                'title', 'publisher', 'year', 'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note', 'subtype', 'email', 'url', 'isbn', 'language', 'abstract'
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
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url', 'language', 'abstract'
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
                'title', 'author', 'organization', 'address', 'edition', 'month', 'year', 'note', 'email', 'url', 'language', 'abstract'
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "mastersthesis" => [
            "fields" => [
                'author', 'title', 'school', 'year', 'address', 'month', 'note', 'subtype', 'email', 'url', 'language', 'abstract'
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
                'author', 'title', 'school', 'year', 'address', 'month', 'note', 'subtype', 'email', 'url', 'language', 'abstract'
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
                'title', 'year', 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note', 'email', 'url', 'language', 'abstract'
            ],
            "mandatory" => [
                "title" => true,
                "year" => true,
            ],
        ],
        "techreport" => [
            "fields" => [
                'author', 'title', 'institution', 'year', 'number', 'address', 'month', 'note', 'subtype', 'email', 'url', 'language', 'abstract'
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
                'author', 'title', 'note', 'month', 'year', 'email', 'url', 'language', 'abstract'
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

    public static function validateMandatory(array $fields, string $type) : bool {
        $typeFields = self::bibtexTypes[$type];

        if(!array_key_exists('mandatory', $typeFields) || count($typeFields['mandatory']) == 0) {
            return true;
        }

        foreach($typeFields['mandatory'] as $man => $manType) {
            // if is simple mandatory field, check if present
            // otherwise return false
            if($manType === true) {
                if(!isset($fields[$man]) || empty($fields[$man])) {
                    return false;
                }
            } else {
                if(
                    (!isset($fields[$man]) || empty($fields[$man])) &&
                    (!isset($fields[$manType]) || empty($fields[$manType]))
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function stripDisallowed(array $fields, string $type) : array {
        $typeFields = self::bibtexTypes[$type];
        $allowedFields = array_merge(
            $typeFields['fields'],
            ['type', 'file']
        );
        $strippedFields = [];
        foreach($fields as $key => $field) {
            // do not include disallowed or interal (starting with _) fields
            if(!str_starts_with($key, '_') && in_array($key, $allowedFields)) {
                $strippedFields[$key] = $field;
            }
        }

        return $strippedFields;
    }

    public function fieldsFromRequest($request, $user) {
        $fields = is_array($request) ? $request : $request->toArray();

        
        $filteredFields = self::stripDisallowed($fields, $this->type ?? $request['type']);
        foreach($filteredFields as $key => $value){
            $this->{$key} = $value;
        }

        // updating an item does not have to update all fields
        // thus we first set all allowed keys from request and then
        // run validation on the update entry
        $validateFields = array_intersect_key($this->toArray(), self::patchRules);
        $isValid = self::validateMandatory($validateFields, $this->type ?? $request['type']);
        if(!$isValid) return false;

        $this->citekey = self::computeCitationKey($this->toArray());
        $this->user_id = $user->id;
        $this->save();
        return true;
    }

    public function referenceCount() {
        return Reference::where('bibliography_id', $this->id)->count();
    }

    public static function duplicateCheck(array $fields, bool $searchInCitationKey) : mixed {
        // check if entry with doi exists
        if(isset($fields['doi'])) {
            $duplicateEntry = self::where('doi', $fields['doi'])->first();
            if(isset($duplicateEntry)) return $duplicateEntry;
        }

        if($searchInCitationKey && isset($fields['citekey'])) {
            $duplicateEntry = self::where('citekey', $fields['citekey'])->first();
            if(isset($duplicateEntry)) return $duplicateEntry;
        }

        $type = self::bibtexTypes[$fields['type']];
        $subFields = array_intersect_key($fields, $type['mandatory']);
        $subFields['type'] = $type;
        $duplicateEntry = self::where($subFields)->first();
        if(isset($duplicateEntry)) return $duplicateEntry;

        return false;
    }

    public static function computeCitationKey($fields) {
        $key = '';
        if(isset($fields['author']) || isset($fields['editor'])) {
            $authors = explode(' and ', $fields['author'] ?? $fields['editor']);
            $author = $authors[0];
            $author .= '_';
            $key .= $author;
        }
        if(isset($fields['title'])) {
            $title = $fields['title'];
            $words = explode(' ', $title);
            $firstWord = '';
            $shortTitle = '';
            foreach($words as $word) {
                if(strlen($firstWord) == 0 && strlen($word) > 3) {
                    $firstWord = $word;
                }
                $shortTitle .= strtolower($word[0]);
            }
            if(strlen($firstWord) == 0) {
                $firstWord = $words[0];
            }
            $key .= "{$firstWord}_{$shortTitle}_";
        }

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
        $this->deleteFile(true);

        $filename = $this->id . "_" . $file->getClientOriginalName();
        return $file->storeAs(
            'bibliography',
            $filename
        );
    }

    public function deleteFile(bool $fromStorageOnly = false) {
        if(isset($this->file) && Storage::exists($this->file)) {
            Storage::delete($this->file);
        }

        if(!$fromStorageOnly) {
            $this->file = null;
            $this->save();
        }
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
