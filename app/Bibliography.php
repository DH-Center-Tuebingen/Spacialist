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
        'entry_type',
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
        'type',
        'abstract',
        'doi',
        'isbn',
        'issn',
        'language',
        'file',
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
        'entry_type' => 5,
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
        'entry_type'   => 'string',
        'file'         => 'file',
        // bibtex standard fields
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
        'type'         => 'string',
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
                'abstract',
                'author',
                'doi',
                'email',
                'issn',
                'journal',
                'language',
                'month',
                'note',
                'number',
                'pages',
                'title',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "journal" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "book" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'edition',
                'editor',
                'email',
                'isbn',
                'language',
                'month',
                'note',
                'publisher',
                'series',
                'title',
                'url',
                'year',
            ],
            "mandatory" => [
                "author" => 'editor',
                "editor" => 'author',
                "publisher" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "incollection" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'booktitle',
                'chapter',
                'doi',
                'edition',
                'editor',
                'email',
                'language',
                'month',
                'note',
                'number',
                'organization',
                'pages',
                'publisher',
                'series',
                'title',
                'type',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "booktitle" => true,
                "publisher" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "misc" => [
            "fields" => [
                'abstract',
                'author',
                'doi',
                'email',
                'howpublished',
                'language',
                'month',
                'note',
                'title',
                'url',
                'year',
            ],
        ],
        "booklet" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'email',
                'howpublished',
                'language',
                'month',
                'note',
                'title',
                'url',
                'year',
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "conference" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'booktitle',
                'doi',
                'editor',
                'email',
                'language',
                'month',
                'note',
                'number',
                'organization',
                'pages',
                'publisher',
                'series',
                'title',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "booktitle" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "inbook" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'chapter',
                'doi',
                'edition',
                'editor',
                'email',
                'isbn',
                'language',
                'month',
                'note',
                'number',
                'pages',
                'publisher',
                'series',
                'title',
                'type',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "author" => "editor",
                "chapter" => "pages",
                "editor" => "author",
                "pages" => "chapter",
                "publisher" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "inproceedings" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'booktitle',
                'doi',
                'editor',
                'email',
                'language',
                'month',
                'note',
                'number',
                'organization',
                'pages',
                'publisher',
                'series',
                'title',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "booktitle" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "manual" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'edition',
                'email',
                'language',
                'month',
                'note',
                'organization',
                'title',
                'url',
                'year',
            ],
            "mandatory" => [
                "title" => true,
            ],
        ],
        "mastersthesis" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'email',
                'language',
                'month',
                'note',
                'school',
                'title',
                'type',
                'url',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "school" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "phdthesis" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'email',
                'language',
                'month',
                'note',
                'school',
                'title',
                'type'   ,
                'url',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "school" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "proceedings" => [
            "fields" => [
                'abstract',
                'address',
                'doi',
                'editor',
                'email',
                'language',
                'month',
                'note',
                'number',
                'organization',
                'publisher',
                'series',
                'title',
                'url',
                'volume',
                'year',
            ],
            "mandatory" => [
                "title" => true,
                "year" => true,
            ],
        ],
        "techreport" => [
            "fields" => [
                'abstract',
                'address',
                'author',
                'doi',
                'email',
                'institution',
                'language',
                'month',
                'note',
                'number',
                'title',
                'type',
                'url',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "institution" => true,
                "title" => true,
                "year" => true,
            ],
        ],
        "unpublished" => [
            "fields" => [
                'abstract',
                'author',
                'email',
                'language',
                'month',
                'note',
                'title',
                'url',
                'year',
            ],
            "mandatory" => [
                "author" => true,
                "note" => true,
                "title" => true,
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

    public static function validateType(string $type) : bool {
        return array_key_exists($type, self::bibtexTypes);
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
            ['entry_type', 'file', 'doi']
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

    public function unsetDisallowed(): void {
        $typeFields = self::bibtexTypes[$this->entry_type]['fields'];

        $allFields = $this->fillable;
        foreach($allFields as $field) {
            if(in_array($field, $typeFields)) continue;
            if($field == 'entry_type') continue;
            if($field == 'file') continue;
            if($field == 'citekey') continue;
            if($field == 'user_id') continue;

            $this->{$field} = null;
        }
    }

    private function getEmptyFields(string $type) : array {
        $typeFields = self::bibtexTypes[$type];
        $emptyFields = [];
        foreach($typeFields['fields'] as $field) {
            $emptyFields[$field] = '';
        }
        return $emptyFields;
    }

    // DISCUSS: Wouldnt it be better to throw an error containing the missing fields?
    //          Or at least return an array of missing fields? [SO]
    public function fieldsFromRequest($request, $user) {
        $fields = is_array($request) ? $request : $request->toArray();

        if(!isset($fields['entry_type'])) {
            return false;
        }

        $type = $fields['entry_type'];
        $filteredFields = self::stripDisallowed($fields, $type);
        foreach($filteredFields as $key => $value) {
            if(!in_array($key, $this->fillable)) throw new \Exception("Field $key is not allowed for this type of entry");
            $this->{$key} = $value;
        }
        $this->unsetDisallowed();

        // updating an item does not have to update all fields
        // thus we first set all allowed keys from request and then
        // run validation on the update entry
        $validateFields = array_intersect_key($this->toArray(), self::patchRules);
        $isValid = self::validateMandatory($validateFields, $type);
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

        $type = self::bibtexTypes[$fields['entry_type']];
        if(!array_key_exists('mandatory', $type)) return false;

        $subFields = array_intersect_key($fields, $type['mandatory']);
        $subFields['entry_type'] = $type;
        $duplicateEntry = self::where($subFields)->first();
        if(isset($duplicateEntry)) return $duplicateEntry;

        return false;
    }

    public static function computeCitationKey($fields) {
        $key = '';
        if(isset($fields['author']) || isset($fields['editor'])) {
            $authors = explode(' and ', $fields['author'] ?? $fields['editor']);
            $author = explode(',', $authors[0]);
            $key .= trim($author[0]);
        }
        if(isset($fields['title'])) {
            $title = $fields['title'];
            $words = explode(' ', $title);
            $firstWord = '';
            $shortTitle = '';
            foreach($words as $word) {
                // only keep (ascii) letters and numbers
                $word = preg_replace('/[^A-Za-z0-9]/', '', Str::ascii(trim($word)));
                if(strlen($firstWord) == 0 && strlen($word) > 3) {
                    $firstWord = $word;
                }
                $shortTitle .= strtolower($word[0]);
            }
            if(strlen($firstWord) == 0) {
                $firstWord = strtolower($words[0]);
            }
            $key .= "_{$firstWord}_{$shortTitle}";
        }

        if(isset($fields['year'])) {
            $key .= "_" . $fields['year'];
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
        return Storage::putFileAs(
            'bibliography',
            $file,
            $filename,
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
}
